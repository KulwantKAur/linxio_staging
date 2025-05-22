package main

import (
	"context"
	"fmt"
	"github.com/aws/aws-sdk-go/service/ecs"
	"log"
	"os"
	"os/exec"
	"os/signal"
	"strconv"
	"syscall"
	"time"

	"cirello.io/dynamolock"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/dynamodb"
)

const (
	ExitCannotAcquireLock = iota + 101
	ExitCannotCreateDynamolock
	ExitCannotCreateDynamodbTable
	ExitCannotGenerateNames
	ExitCannotStartCommand
	ExitUnknownExitError
	ExitProcessKilled
)

func main() {
	c := make(chan os.Signal, 1)
	signal.Notify(c, os.Interrupt, os.Kill)

	ctx, cancel := context.WithCancel(context.Background())

	go func() {
		oscall := <-c
		log.Printf("system call: %+v", oscall)
		cancel()
	}()

	exitCode, err := run(ctx)

	if err != nil {
		log.Printf("failed to start task: %v\n", err)
	}

	os.Exit(exitCode)
}

type Metadata struct {
	TableName *string
	Key       *string
	Data      *string
}

func getMetadata(svc *ecs.ECS) (*Metadata, error) {
	metadata, err := getContainerMetadata()

	if err != nil {
		return nil, err
	}

	tasks, err := svc.DescribeTasks(&ecs.DescribeTasksInput{
		Cluster: metadata.Cluster,
		Tasks:   []*string{metadata.TaskARN},
	})

	if err != nil {
		return nil, err
	}

	if len(tasks.Failures) != 0 {
		return nil, fmt.Errorf("tasks request failed with an error: %v", tasks.Failures)
	}

	tableName := fmt.Sprintf("%s-cron-lock", *metadata.Cluster)

	return &Metadata{&tableName, tasks.Tasks[0].TaskDefinitionArn, metadata.TaskARN}, nil
}

func run(ctx context.Context) (int, error) {
	awsSession := session.Must(session.NewSession())
	dynamodbSvc := dynamodb.New(awsSession)
	ecsSvc := ecs.New(awsSession)

	metadata, err := getMetadata(ecsSvc)
	if err != nil {
		return ExitCannotGenerateNames, err
	}

	c, err := dynamolock.New(dynamodbSvc,
		*metadata.TableName,
		dynamolock.WithLeaseDuration(3*time.Second),
		dynamolock.WithHeartbeatPeriod(1*time.Second),
		dynamolock.WithPartitionKeyName("LockID"),
	)

	if err != nil {
		return ExitCannotCreateDynamolock, err
	}
	defer c.Close()

	if err = createTable(ctx, c, dynamodbSvc, metadata); err != nil {
		return ExitCannotCreateDynamodbTable, err
	}

	ttl := time.Now().Add(time.Hour).Unix()
	lockedItem, err := c.AcquireLockWithContext(
		ctx, *metadata.Key,
		dynamolock.ReplaceData(),
		dynamolock.FailIfLocked(),
		dynamolock.WithDeleteLockOnRelease(),
		dynamolock.WithData([]byte(*metadata.Data)),
		dynamolock.WithAdditionalAttributes(
			map[string]*dynamodb.AttributeValue{
				"ttl": {
					N: aws.String(strconv.FormatInt(ttl, 10)),
				},
			},
		),
	)

	if err != nil {
		return ExitCannotAcquireLock, err
	}

	exitCode, err := runCommand(ctx)

	if err != err {
		fmt.Printf("%+v\n", err)
	}

	success, err := c.ReleaseLock(lockedItem)
	if !success {
		log.Println("lost lock before release")
	}
	if err != nil {
		log.Println("error releasing lock:", err)
	}
	log.Println("done")

	return exitCode, err
}

func createTable(ctx context.Context, c *dynamolock.Client, dynamodbSvc *dynamodb.DynamoDB, metadata *Metadata) error {
	_, err := c.CreateTableWithContext(
		ctx, *metadata.TableName,
		dynamolock.WithProvisionedThroughput(&dynamodb.ProvisionedThroughput{
			ReadCapacityUnits:  aws.Int64(5),
			WriteCapacityUnits: aws.Int64(5),
		}),
		dynamolock.WithCustomPartitionKeyName("LockID"),
	)

	if err != nil {
		if _, ok := err.(*dynamodb.ResourceInUseException); !ok {
			return err
		}
	}

	err = dynamodbSvc.WaitUntilTableExistsWithContext(ctx, &dynamodb.DescribeTableInput{TableName: metadata.TableName})

	ttlInfo, err := dynamodbSvc.DescribeTimeToLive(&dynamodb.DescribeTimeToLiveInput{TableName: metadata.TableName})

	if err != nil {
		return err
	}

	if *ttlInfo.TimeToLiveDescription.TimeToLiveStatus == dynamodb.TimeToLiveStatusDisabled {
		_, err = dynamodbSvc.UpdateTimeToLiveWithContext(ctx, &dynamodb.UpdateTimeToLiveInput{
			TableName: metadata.TableName,
			TimeToLiveSpecification: &dynamodb.TimeToLiveSpecification{
				AttributeName: aws.String("ttl"),
				Enabled:       aws.Bool(true),
			},
		})
	}

	return err
}

func waitCommand(cmd *exec.Cmd) chan error {
	status := make(chan error)

	go func() {
		status <- cmd.Wait()
	}()

	return status
}

func runCommand(ctx context.Context) (int, error) {
	cmd := exec.Command(os.Args[1], os.Args[2:]...)
	cmd.Stderr = os.Stderr
	cmd.Stdout = os.Stdout
	cmd.Stdin = os.Stdin

	log.Println("Start command")
	if err := cmd.Start(); err != nil {
		return ExitCannotStartCommand, err
	}

	select {
	case err := <-waitCommand(cmd):
		if err != nil {
			if exiterr, ok := err.(*exec.ExitError); ok {
				if status, ok := exiterr.Sys().(syscall.WaitStatus); ok {
					return status.ExitStatus(), err
				}
			} else {
				return ExitUnknownExitError, err
			}
		}
		return 0, nil
	case <-ctx.Done():
		err := cmd.Process.Kill()
		return ExitProcessKilled, err
	}

}

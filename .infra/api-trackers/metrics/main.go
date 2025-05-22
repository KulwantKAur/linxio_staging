package main

import (
	"context"
	"encoding/json"
	"fmt"
	"github.com/aws/aws-sdk-go/aws"
	"github.com/aws/aws-sdk-go/aws/session"
	"github.com/aws/aws-sdk-go/service/cloudwatch"
	"github.com/aws/aws-sdk-go/service/ecs"
	"io"
	"log"
	"net/http"
	"os"
	"os/signal"
	"strings"
	"time"
)

type Status struct {
	Pool               string `json:"pool"`
	ProcessManager     string `json:"process manager"`
	StartTime          int    `json:"start time"`
	StartSince         int    `json:"start since"`
	AcceptedConn       int    `json:"accepted conn"`
	ListenQueue        int    `json:"listen queue"`
	MaxListenQueue     int    `json:"max listen queue"`
	ListenQueueLen     int    `json:"listen queue len"`
	IdleProcesses      int    `json:"idle processes"`
	ActiveProcesses    int    `json:"active processes"`
	TotalProcesses     int    `json:"total processes"`
	MaxActiveProcesses int    `json:"max active processes"`
	MaxChildrenReached int    `json:"max children reached"`
	SlowRequests       int    `json:"slow requests"`
}

type CreateMetricDatumFunc func(metricName string, value int) *cloudwatch.MetricDatum

func getPhpFpmStatus(endpoint string) (*Status, error) {
	resp, err := http.Get(endpoint)

	if err != nil {
		return nil, err
	}

	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)

	if err != nil {
		return nil, err
	}

	status := &Status{}

	if err := json.Unmarshal(body, status); err != nil {
		return nil, err
	}

	return status, nil
}

func prepareDimensions(svc *ecs.ECS) ([]*cloudwatch.Dimension, error) {
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
		return nil, fmt.Errorf("tasks request failed with error: %v", tasks.Failures)
	}

	groupParts := strings.Split(*tasks.Tasks[0].Group, ":")

	if len(groupParts) != 2 || groupParts[0] != "service" {
		return nil, fmt.Errorf("seems like this task not a part of any service: %s", *tasks.Tasks[0].Group)
	}

	dimensions := []*cloudwatch.Dimension{
		{
			Name:  aws.String("ServiceName"),
			Value: &groupParts[1],
		},
		{
			Name:  aws.String("ClusterName"),
			Value: metadata.Cluster,
		},
	}

	return dimensions, nil
}

func metricDatumCreator(dimensions []*cloudwatch.Dimension) CreateMetricDatumFunc {
	return func(metricName string, value int) *cloudwatch.MetricDatum {
		return &cloudwatch.MetricDatum{
			MetricName: aws.String(metricName),
			Unit:       aws.String(cloudwatch.StandardUnitCount),
			Value:      aws.Float64(float64(value)),
			Dimensions: dimensions,
		}
	}
}

func main() {
	c := make(chan os.Signal, 1)
	signal.Notify(c, os.Interrupt)

	ctx, cancel := context.WithCancel(context.Background())

	go func() {
		oscall := <-c
		log.Printf("system call: %+v", oscall)
		cancel()
	}()

	if err := runStatGatherer(ctx); err != nil {
		log.Printf("failed to start daemon: %v\n", err)
	}
}

func runStatGatherer(ctx context.Context) error {
	endpoint, ok := os.LookupEnv("PHP_FPM_STATUS_ENDPOINT")

	if !ok {
		endpoint = "http://localhost:8098/statusz?json"
	}

	awsSession := session.Must(session.NewSession(&aws.Config{MaxRetries: aws.Int(10)}))

	ecsSvc := ecs.New(awsSession)

	dimensions, err := prepareDimensions(ecsSvc)

	if err != nil {
		return fmt.Errorf("error while preparing dimentions: %v", err)
	}

	cw := cloudwatch.New(awsSession)

	createMetricDatum := metricDatumCreator(dimensions)
	ticker := time.NewTicker(1 * time.Minute)
	for {
		err = sendMetricData(ctx, cw, endpoint, createMetricDatum)
		if err != nil {
			log.Printf("Error occurred: %v\n", err)
		}

		select {
		case <-ctx.Done():
			ticker.Stop()
			return nil
		case <-ticker.C:
		}
	}
}

func sendMetricData(
	ctx context.Context,
	cw *cloudwatch.CloudWatch,
	endpoint string,
	createMetricDatum CreateMetricDatumFunc,
) error {
	status, err := getPhpFpmStatus(endpoint)
	if err != nil {
		return err
	}

	_, err = cw.PutMetricDataWithContext(ctx, &cloudwatch.PutMetricDataInput{
		Namespace: aws.String("App/ECS/PHP-FPM"),
		MetricData: []*cloudwatch.MetricDatum{
			createMetricDatum("AcceptedConn", status.AcceptedConn),
			createMetricDatum("ListenQueue", status.ListenQueue),
			createMetricDatum("MaxListenQueue", status.MaxListenQueue),
			createMetricDatum("ListenQueueLen", status.ListenQueueLen),
			createMetricDatum("IdleProcesses", status.IdleProcesses),
			createMetricDatum("ActiveProcesses", status.ActiveProcesses),
			createMetricDatum("TotalProcesses", status.TotalProcesses),
			createMetricDatum("MaxActiveProcesses", status.MaxActiveProcesses),
			createMetricDatum("MaxChildrenReached", status.MaxChildrenReached),
			createMetricDatum("SlowRequests", status.SlowRequests),
		},
	})

	return err
}

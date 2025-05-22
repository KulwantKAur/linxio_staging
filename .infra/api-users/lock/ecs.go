package main

import (
	"encoding/json"
	"fmt"
	"io/ioutil"
	"os"
)

type ContainerMetadata struct {
	Cluster *string
	TaskARN *string
}

func getContainerMetadata() (*ContainerMetadata, error) {
	path, ok := os.LookupEnv("ECS_CONTAINER_METADATA_FILE")

	if !ok {
		return nil, fmt.Errorf("ECS_CONTAINER_METADATA_FILE variable is empty")
	}

	data, err := ioutil.ReadFile(path)
	if err != nil {
		return nil, err
	}

	metadata := &ContainerMetadata{}
	if err = json.Unmarshal(data, metadata); err != nil {
		return nil, err
	}

	return metadata, nil
}

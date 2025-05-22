# Show this help
help:
  @just --list --unsorted

# Build DroneCI file
drone-build:
	.infra/drone/build.sh

# Run drone
drone-exec *args:
  just drone-build
  drone exec --env-file .env.drone --branch "`git branch --show-current`" --timeout 3h {{ args }}

profile:
  aws-vault exec linxio

#!/bin/sh

QUIET=0

echoerr() {
  if [ "$QUIET" -ne 1 ]; then printf "%s\n" "$*" 1>&2; fi
}

usage() {
  exitcode="$1"
  cat << USAGE >&2
Usage:
  $cmdname host:port [-- command args]
  -q | --quiet                        Do not output any status messages
  -- COMMAND ARGS                     Execute command with args after the test finishes
USAGE
  exit "$exitcode"
}

wait_for() {
  while true; do
    nc -z "$HOST" "$PORT" > /dev/null 2>&1

    result=$?
    if [ $result -eq 0 ] ; then
      if [ $# -gt 0 ] ; then
        exec "$@"
      fi
      exit 0
    fi
    >&2 echo "Waiting for $HOST:$PORT"
    sleep 1
  done
}

while [ $# -gt 0 ]
do
  case "$1" in
    *:* )
      HOST=$(printf "%s\n" "$1"| cut -d : -f 1)
      PORT=$(printf "%s\n" "$1"| cut -d : -f 2)
      shift 1
      ;;
      -q | --quiet)
      QUIET=1
      shift 1
      ;;
    --help)
      usage 0
      ;;
    --)
      shift
      break
      ;;
  esac
done

if [ "$HOST" = "" -o "$PORT" = "" ]; then
  echoerr "Error: you need to provide a host and port to test."
  usage 2
fi

wait_for "$@"
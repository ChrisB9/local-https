#!/usr/bin/env bash
#!/bin/bash

function startFunction {
  key="$1"
  echo "running script ${key}"
  case ${key} in
     start)
       docker-compose up --build -d
       return
       ;;
     companion)
       docker-compose up --build -d companion && docker-compose logs -f companion
       return
       ;;
  esac
}

startFunction "${@:1}"
        exit $?

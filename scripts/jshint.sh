#!/bin/bash

cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source functions.sh

if ! commandExists jshint
then
    echo "Jshint not found"
    exit 0
fi

jshint --show-non-errors --config=../.jshintrc ../public/backend/js

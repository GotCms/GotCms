#!/bin/bash

cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
jshint --show-non-errors --config=../.jshintrc ../public/backend/js

#!/bin/bash

cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source functions.sh

if ! commandExists phpcs
then
    if ! commandExists ./phpcs
    then
        echo Could not find phpcs.
        exit 0
    else
        phpcs="./phpcs"
    fi
else
    phpcs="phpcs"
fi

standard=Got
if [[ -d "../vendor/gotcms/gotsniffs/Got/" ]]
then
    standard="../vendor/gotcms/gotsniffs/Got/"
fi

$phpcs --standard=$standard ../library/
$phpcs --standard=$standard ../module/

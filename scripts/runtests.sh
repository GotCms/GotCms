#!/bin/bash

cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source functions.sh

if ! commandExists phpunit
then
    if ! commandExists ./phpunit
    then
        echo "Phpunit not found"
        exit 0
    else
        phpunit="./phpunit"
    fi
else
    phpunit="phpunit"
fi

phpunit_opts="-d zend.enable_gc=0 --verbose"
phpunit_groups=

while [ -n "$1" ] ; do
  case "$1" in
    ALL|all)
     phpunit_groups=""
     break ;;

    Gc|Datatypes|Modules|ZfModules)
     phpunit_groups="${phpunit_groups:+"$phpunit_groups,"}$1"
     shift ;;
    *)

     phpunit_file="$1"
     shift ;;
  esac
done

cd "../tests"
$phpunit $phpunit_opts ${phpunit_groups:+--group $phpunit_groups} $phpunit_file


#!/bin/bash

if [[ $# -lt 2 ]]
then
    echo >&2 "You must specify a directory and filename name!"
else
    ext=".php"
    pwd=`pwd`
    deep=`echo $2 | sed 's/\// /g' | sed 's/\\//\/ /g' | wc -w`

    if [[ $deep -lt 2 ]]
    then
        echo >&2 "Filename name $2 invalid. Exiting.."
    else
        dir=`echo $2 | cut -f -$(($deep-1)) -d /`

        class=`echo "${2%.*}" | sed 's/\\//\\\\/g'`
        classTest=`echo $class"Test"`
        file="${2%.*}"$ext
        fileTest="${2%.*}Test"$ext
        destination_directory=$pwd/$1
        source_directory=$pwd/../$1

        if [[ -f $destination_directory/$fileTest ]]
        then
            echo "Test $file already exists, exiting.."
        else
            cd ../$1/

            if [[ "$1" == "module" ]]
            then
                class=$(echo $class| awk -F'\\\\src\\\\' '{ print $2 }')
            fi

            if [ ! -z "$class" ]
            then
                phpunit-skelgen --bootstrap $pwd/Bootstrap.php --test -- "$class" "$source_directory/$file"

                if [[ $? -ne 0 ]]
                then
                    echo >&2 "PHPUnit returned an error when generating the skeleton"
                    cd $pwd
                    echo "Exiting.."
                else
                    if [[ ! -d $(dirname $destination_directory/$fileTest) ]]
                    then
                        mkdir -p $(dirname $destination_directory/$fileTest)
                    fi

                    mv $source_directory/$fileTest $destination_directory/$fileTest

                    cd $pwd
                    echo "done."
                fi
            fi
        fi
    fi
fi

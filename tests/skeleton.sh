#!/bin/bash

if [[ $# -lt 1 ]]
then
    echo >&2 "You must specify a class name!"
else
    ext=".php"
    pwd=`pwd`
    deep=`echo $1 | sed 's/\// /g' | sed 's/\\//\/ /g' | wc -w`

    if [[ $deep -lt 2 ]]
    then
        echo >&2 "Class name $1 invalid. Exiting.."
    else
        dir=`echo $1 | cut -f -$(($deep-1)) -d /`
        class=$(echo $1 | sed -e "s/\//\\\/g")
        file=`echo $1 | cut -f $deep -d /`"Test"$ext
        destination_directory=$pwd/library/$dir
        source_directory=$pwd/../library/$dir

        if [[ -f $destination_directory/$file ]]
        then
            echo "Test $file already exists, exiting.."
        else
            cd ../library/
            phpunit-skelgen --bootstrap $pwd/Bootstrap.php --test $class

            if [[ $? -ne 0 ]]
            then
                echo >&2 "PHPUnit returned an error when generating the skeleton"
                cd $pwd
                echo "Exiting.."
            else
                if [[ ! -d $destination_directory ]]
                then
                    mkdir -p $destination_directory
                fi

                mv $source_directory/$file $destination_directory/$file

                cd $pwd
                echo "done."
            fi
        fi
    fi
fi

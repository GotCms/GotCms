#!/bin/bash

if [[ $# -lt 2 ]]
then
    echo >&2 "You must specify a directory and filename!"
else
    if [[ "$2" == "all" ]]
    then
        if [[ "$1" == "module" ]]
        then
            LISTING=`find ../module/ -name "*.php" | awk '{print substr($1, 11);}'`
        else
            LISTING=`find ../library/ -name "*.php" | awk '{print substr($1, 12);}'`
        fi

        FILES=(`echo $LISTING`)
    else
        FILES=(`echo $2`)
    fi

    EXT=".php"
    PWD=`pwd`
    for FILENAME in "${FILES[@]}"
    do
        DEEP=`echo $FILENAME | sed 's/\// /g' | sed 's/\\//\/ /g' | wc -w`
        if [[ $DEEP -lt 2 ]]
        then
            echo >&2 "filename $FILENAME invalid. Exiting.."
        else
            CLASS_SRC=`echo "${FILENAME%.*}" | sed 's/\\//\\\\/g'`
            FILE_TEST_SRC="${FILENAME%.*}Test"$EXT
            if [[ "$1" == "module" ]]
            then
                CLASS_TEST_DST=$(echo $FILE_TEST_SRC | awk -F'/src/' '{ print $2 }')
            else
                CLASS_TEST_DST=$FILE_TEST_SRC
            fi

            if [[ -z "$CLASS_TEST_DST" ]]
            then
                continue;
            fi

            SRC_DIR=$PWD/../$1
            DST_DIR=$PWD/$1

            if [[ -f $DST_DIR/$CLASS_TEST_DST ]]
            then
                echo "Test $FILENAME already exists, exiting.."
            else
                if [[ "$1" == "module" ]]
                then
                    CLASS_SRC=$(echo $CLASS_SRC | awk -F'\\\\src\\\\' '{ print $2 }')
                fi
                if [ ! -z "$CLASS_SRC" ]
                then

                    phpunit-skelgen --bootstrap $PWD/Bootstrap.php --test -- "$CLASS_SRC" "$SRC_DIR/$FILENAME"

                    if [[ $? -ne 0 ]]
                    then
                        echo >&2 "PHPUnit returned an error when generating the skeleton"
                        cd $PWD
                        echo "Exiting.."
                    else
                        if [[ ! -d $(dirname $DST_DIR/$CLASS_TEST_DST) ]]
                        then
                            mkdir -p $(dirname $DST_DIR/$CLASS_TEST_DST)
                        fi

                        mv $SRC_DIR/$FILE_TEST_SRC $DST_DIR/$CLASS_TEST_DST

                        cd $PWD
                        echo "done."
                    fi
                fi
            fi
        fi
    done
fi

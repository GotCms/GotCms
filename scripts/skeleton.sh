#!/bin/bash

if [[ $# -lt 2 ]]
then
    echo >&2 "You must specify a directory and filename!"
else
    if [[ "$2" == "all" ]]
    then
        if [[ "$1" == "module" ]]
        then
            listing=`find ../module/ -name "*.php" | awk '{print substr($1, 11);}'`
        else
            listing=`find ../library/ -name "*.php" | awk '{print substr($1, 12);}'`
        fi

        files=(`echo $listing`)
    else
        files=(`echo $2`)
    fi

    ext=".php"
    pwd=`pwd`
    for filename in "${files[@]}"
    do
        deep=`echo $filename | sed 's/\// /g' | sed 's/\\//\/ /g' | wc -w`
        if [[ $deep -lt 2 ]]
        then
            echo >&2 "filename $filename invalid. Exiting.."
        else
            class_src=`echo "${filename%.*}" | sed 's/\\//\\\\/g'`
            file_test_src="${filename%.*}Test"$ext
            if [[ "$1" == "module" ]]
            then
                class_test_dst=$(echo $file_test_src | awk -F'/src/' '{ print $2 }')
            else
                class_test_dst=$file_test_src
            fi

            if [[ -z "$class_test_dst" ]]
            then
                continue;
            fi

            src_dir=$pwd/../$1
            dst_dir=$pwd/$1

            if [[ -f $dst_dir/$class_test_dst ]]
            then
                echo "Test $filename already exists, exiting.."
            else
                if [[ "$1" == "module" ]]
                then
                    class_src=$(echo $class_src | awk -F'\\\\src\\\\' '{ print $2 }')
                fi
                if [ ! -z "$class_src" ]
                then

                    phpunit-skelgen --bootstrap $pwd/Bootstrap.php --test -- "$class_src" "$src_dir/$filename"

                    if [[ $? -ne 0 ]]
                    then
                        echo >&2 "PHPUnit returned an error when generating the skeleton"
                        cd $pwd
                        echo "Exiting.."
                    else
                        if [[ ! -d $(dirname $dst_dir/$class_test_dst) ]]
                        then
                            mkdir -p $(dirname $dst_dir/$class_test_dst)
                        fi

                        mv $src_dir/$file_test_src $dst_dir/$class_test_dst

                        cd $pwd
                        echo "done."
                    fi
                fi
            fi
        fi
    done
fi

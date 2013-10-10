#!/bin/bash

cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source functions.sh

if ! commandExists java
then
    echo "Java not found"
    exit 0
fi

if [[ ! -f yuicompressor.jar ]]
then
    echo "yuicompressor.jar not found"
    exit 0
fi

jsDirectory="../public/backend/js"

cat $jsDirectory/vendor/jquery-1.10.2.min.js \
$jsDirectory/bootstrap.min.js \
$jsDirectory/vendor/jquery.browser.js \
$jsDirectory/vendor/jquery-ui-1.10.3.custom.min.js \
$jsDirectory/vendor/codemirror/lib/codemirror.js \
$jsDirectory/vendor/codemirror/mode/xml/xml.js \
$jsDirectory/vendor/codemirror/mode/javascript/javascript.js \
$jsDirectory/vendor/codemirror/mode/css/css.js \
$jsDirectory/vendor/codemirror/mode/clike/clike.js \
$jsDirectory/vendor/codemirror/mode/php/php.js \
$jsDirectory/vendor/jquery.jstree.js \
$jsDirectory/vendor/jquery.contextMenu.js \
$jsDirectory/generic-classes.js \
$jsDirectory/gotcms.js > gotcms.min.js

java -jar yuicompressor.jar gotcms.min.js \
-o $jsDirectory/gotcms.min.js --charset utf-8 --type js

rm gotcms.min.js

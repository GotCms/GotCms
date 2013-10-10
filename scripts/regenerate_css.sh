#!/bin/bash

cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source functions.sh
if ! commandExists lessc
then
    echo "Lessc not found"
    exit 0
fi

lessc -x --yui-compress "../public/backend/css/gotcms.less" > "../public/backend/css/gotcms.min.css"
lessc -x --yui-compress "../public/backend/css/gotcms-install.less" > "../public/backend/css/gotcms-install.min.css"

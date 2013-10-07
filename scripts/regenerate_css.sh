#!/bin/bash

set -e

cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

lessc -x --yui-compress "../public/backend/css/gotcms.less" > "../public/backend/css/gotcms.min.css"
lessc -x --yui-compress "../public/backend/css/gotcms-install.less" > "../public/backend/css/gotcms-install.min.css"

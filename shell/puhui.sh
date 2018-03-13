#!/bin/bash
basepath=$(cd `dirname $0`; pwd)"/../app/public"
cd $basepath
/usr/local/php7/bin/php console.php "$*"

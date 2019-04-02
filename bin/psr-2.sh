#!/bin/bash
BASEDIR=$(dirname "$BASH_SOURCE")
$BASEDIR/../vendor/bin/php-cs-fixer fix $1 $2 $3 $4 $BASEDIR/../

#!/bin/bash
BASEDIR=$(dirname "$BASH_SOURCE")
php $BASEDIR/../vendor/bin/phinx $1 $2 $3 -c $BASEDIR/../config/config.php

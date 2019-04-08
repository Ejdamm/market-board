#!/bin/bash
BASEDIR=$(dirname "$BASH_SOURCE")
php $BASEDIR/../vendor/bin/phinx "$@" -c $BASEDIR/../config/config.php

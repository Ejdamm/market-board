#!/bin/bash
BASEDIR=$(dirname "$BASH_SOURCE")
cat "$BASEDIR/../resources/structure.sql" | mysql -u root -p

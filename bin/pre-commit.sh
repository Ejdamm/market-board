#!/usr/bin/env bash



echo "Running pre-commit hook"
./bin/run-test.sh

# $? stores exit value of the last command
if [ $? -ne 0 ]; then
    echo -e "\e[31mTest failed\e[0m"
    echo "Tests must pass before commit!"
    exit 1
else
    echo -e "\e[32mTest OK!\e[0m"
fi
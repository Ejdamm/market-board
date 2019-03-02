#!/usr/bin/env bash

set -e

# https://rock-it.pl/automatic-code-quality-checks-with-git-hooks/
# magic line to ensure that we're always inside the root of our application,
# no matter from which directory we'll run script
# thanks to it we can just enter `./scripts/run-tests.bash`
cd "${0%/*}/.."

echo "Running tests"
echo "............................"
result=`composer test`
if [[ $result = *"FAILURES!"* ]]; then
    echo $result
    echo "Failed!" && exit 1
else
    echo "Success!" && exit 0
fi
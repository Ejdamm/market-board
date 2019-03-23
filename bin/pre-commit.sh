#!/usr/bin/env bash

echo "Running pre-commit hook"

# Make sure all tests pass
result=`composer test`
if [ $? -ne 0 ]; then
    echo -e "\e[31mTest failed\e[0m"
    echo $result
    echo "Tests must pass before commit!"
    exit 1
else
    echo -e "\e[32mTest OK!\e[0m"
fi

# Make sure PSR-2 code style standard is followed
./vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix . --dry-run
if [ $? -ne 0 ]; then
    echo -e "\e[31mSomething is wrong with the code style!\e[0m"
    echo "To see what is wrong, run bin/psr-2.sh -v --dry-run --diff"
    echo "To automatically fix it, run bin/psr-2.sh"
    exit 1
else
    echo -e "\e[32mCode style OK!\e[0m"
fi

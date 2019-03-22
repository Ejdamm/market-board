#!/usr/bin/env bash



echo "Running pre-commit hook"

# Make sure all tests pass
./bin/run-test.sh
# $? stores exit value of the last command
if [ $? -ne 0 ]; then
    echo -e "\e[31mTest failed\e[0m"
    echo "Tests must pass before commit!"
    exit 1
else
    echo -e "\e[32mTest OK!\e[0m"
fi


# Make sure all PSR-2 code style standard is followed
./vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix . --dry-run
if [ $? -ne 0 ]; then
    echo -e "\e[31mSomething is wrong with the code style!\e[0m"
    echo "To see what is wrong, run vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix . --dry-run --diff"
    echo "To automatically fix it, run vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix ."
    exit 1
else
    echo -e "\e[32mCode style OK!\e[0m"
fi

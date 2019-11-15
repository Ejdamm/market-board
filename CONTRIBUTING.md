# How to Contribute

## Pull Requests
1. Fork the repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the master branch

## Tests
* Testing is done with phpunit.
* `./phpunit.xml` defines the path of where phpunit should look.
* All tests must pass for a pull request to pass and is done automatically with Travis CI
* To run all tests locally: run `composer test` from root.
* Run single test: `composer test tests/Functional/HomepageTest.php`
* Git hooks should be used to prevent untested code to be pushed.

### Troubleshooting for test
* `Error: Class 'path/to/class' not found`
  * Try running: `composer update`
  * Are the tests located under `./tests/` folder?
  * Is the wanted class to test using the correct namespace (`use Startplats\Tests\Unit;`)?

### Git hooks
* To enable that all tests are executed and green before every push is done (`git push`).
* run the script: `./bin/install-hooks.sh`
  * This will create a symlink from your .git-directory to the ./bin/pre-push.sh script.
    * Reason for the symlink is to ease the maintenance of the script since the content of the .git-folder is not included in the repository.
  * Under your .git/hooks there should after executed `ìnstall-hooks.sh` be a symlink like this: ` pre-push -> ../../bin/pre-push.sh`
* "But, I really need to push my changes, a testcase is failing and its not my fault!"
  * Cheating is not allowed, but having that said, `git push --no-verify` will ignore the git hook.

## Standards
### Code style
All pull requests must adhere to the [PSR-2 standard](https://www.php-fig.org/psr/psr-2/)
* To search your files for styling errors, run `composer style -v --dry-run --diff`
* To automatically fix styling errors, run `composer style`


### File structure
Using the file structure described in [pds/skeleton](https://github.com/php-pds/skeleton) should be applied to the extent possible.

# Startplats

## Installation for Apache
Enable mod_rewrite module `sudo a2enmod rewrite`

Setup a virtual host in /etc/apache2/sites-enabled/000-default.conf
```
<VirtualHost *:80>
	DocumentRoot /var/www/html/startplats/public
	ServerName localhost
	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined

	<Directory "/var/www/html/startplats/public">
		AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
```

Restart apache2 `systemctl restart apache2`

[Ref.](http://docs.slimframework.com/routing/rewrite/)
### Install
* Run `composer install` inside the git repository root folder.
* copy /config/config.php.example to /config/config.php and change the credentials to your database setup

### Database migration
Database migration is done with phinx. Usage:
* `bin/phinx create <MyTable>` (Note CamelCase)
  * Will create a file for migration under db/migration. This file will be used to create/migrate/rollback tables.
  * One file for each table.
* `bin/phinx migrate` will migrate whatever config that is written under the function `create()` in `db/migrate`
* `bin/phinx rollback` will rollback whatever config that is written `create()` in `db/migrate`
* Database settings are located under phinx.yaml.
* More info [link]{http://docs.phinx.org/en/latest/intro.html}

### Git hooks
* To enable that all tests are executed and green before every commit is done (`git commit`).
* run the script: `./bin/install-hooks.sh`
  * This will create a symlink from your .git-directory to the ./bin/pre-commit.sh script.
    * Reason for the symlink is to ease the maintenance of the script since the content of the .git-folder is not included in the repository.
  * Under your .git/hooks there should after executed `ìnstall-hooks.sh` be a symlink like this: ` pre-commit -> ../../bin/pre-commit.sh`
  * Could be that the sample script need to be removed before it works. `rm .git/hooks/pre-commit.sample`
* "But, I really need to push my changes, a testcase is failing and its not my fault!"
  * Cheating is not allowed, but having that said, `git commit --no-verify -m "commit message"` will ignore the git hook.

## Test
* `./phpunit.xml` defines the path of where phpunit should look.
* "other important information"


### Execute:
* Run all test `composer test`
* Run single test `composer test tests/Functional/HomepageTest.php`

#### Troubleshooting for test
* `Error: Class 'path/to/class' not found`
  * Try running: `composer update`
  * Are the tests located under `./tests/` folder?
  * Is the wanted class to test included in the test (`use src\objects\classToTest;`)?


## Standards
* Code style: [PSR-2](https://www.php-fig.org/psr/psr-2/)
* File structure: [pds/skeleton](https://github.com/php-pds/skeleton)

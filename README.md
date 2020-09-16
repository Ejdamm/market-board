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
* copy /config/config.php.example to /config/config.php and change the credentials to your database setup. The same goes for phinx.php.example.

## Database migration
Database migration is done with phinx. Usage:
* Migrate
    * `bin/phinx.sh create <MyTable>` (Note CamelCase)
        * Will create a file for migration under db/migration. This file will be used to create/migrate/rollback tables.
    * `bin/phinx.sh migrate` will migrate whatever config that is written under the function `create()` in `db/migrate`
    * `bin/phinx.sh rollback` will rollback whatever config that is written `create()` in `resources/db/migrate`
    * `bin/phinx.sh migrate -e dev -t 0` will reset all migrations
* Seed
    * Seeding will populate the database with predefined data. Located under db/seed
    * To create a new seed, execute `bin/phinx.sh seed:create YourSeedName` (Note camelCase).
    * All seeds are executed with `bin/phinx.sh seed:run`
    * Single seed is executed with `bin/phinx.sh seed:run -s <yourSeedClass>`
    * To populate the database with a language run `bin/phinx.sh seed:run -s DefaultLanguage`
* More info: [link](http://docs.phinx.org/en/latest/intro.html)

## Language
All labels and messages are defined in the database to easy change wordings or language. To make a new set of strings 
copy the DefaultLanguage.php file in resources/db/seeds/language and replace the words. If you edit the default file directly 
you risk to overwrite it upon an code update. As for now it is not possible to change language from the UI. Do it by 
change from default to your own language in index.php or set it in the session `$this->session->set('language', 'english');`.
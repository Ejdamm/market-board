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

[Ref.](http://docs.slimframework.com/routing/rewrite/)

Restart apache2 `systemctl restart apache2`

### Install
* Run `composer install` and `composer update` inside the git repository root folder.
* copy /config/config.php.example to /config/config.php and change the credentials to your database setup



## Test
* `./phpunit.xml` defines the path of where phpunit should look.
* "other important information"


### Execute:
* Run all test `composer test`
* Run single test `composer test tests/Functional/HomepageTest.php`


## Standards
* Code style: [PSR-2](https://www.php-fig.org/psr/psr-2/)
* File structure: [pds/skeleton](https://github.com/php-pds/skeleton)

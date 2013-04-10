# GotCms - Information

## About GotCms

GotCms is a content management system based on [Zend Framework 2.1.4](http://framework.zend.com/).

This product has been made available under the terms of the GNU GPL version 3.
Please read the [LICENSE.txt](LICENSE.txt) file for the exact license details that apply to GotCms.

## Updates in 0.1.5

Please see [CHANGELOG.md](CHANGELOG.md).

## Usage

Installation via composer:

    $ curl -s https://getcomposer.org/installer | php
    $ php composer.phar install

Installation via Git:

    $ git clone https://github.com/PierreRambaud/GotCms.git

If you want to use VirtualHost, copy the .htaccess content otherwise check if "AllowOverride" is set to "All".

Example of VirtualHost:

```
<VirtualHost *:80>
    ServerAdmin admin@got-cms.com
    ServerName got-cms.com
    ServerAlias www.got-cms.com
    DocumentRoot /var/www/got-cms/public
    <Directory /var/www/got-cms/public>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride None
        Order allow,deny
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} -s [OR]
        RewriteCond %{REQUEST_FILENAME} -l [OR]
        RewriteCond %{REQUEST_FILENAME} -d
        RewriteRule ^.*$ - [NC,L]
        RewriteRule ^.*$ index.php [NC,L]
    </Directory>
</VirtualHost>
```

Make sure read and write access are available by apache user/group for these directories:
- config/autoload
- public/frontend
- public/media
- data/cache

##Required

- An HTTP server
- Php version >= 5.3.3
- XML support
- PDO support
- Intl support
- Mbstring support
- Json support
- A database supported by PDO.
    - MySQL
    - PostgreSQL

## Recommended

Actually only tested with Apache HTTP server.
Php configuration:
- Display Errors: Off
- File Uploads: On
- Magic Quotes Runtime: Off
- Magic Quotes GPC: Off
- Register Globals: Off
- Session Auto Start: Off


## Installation

Go to the website, it will redirect you to /install.
Please follow instructions, there'are only five steps:
- Language
- License
- Pre-configuration
- Database connection
- Configuration

Adminstration is accessible with /admin in URL.

After you can manage your website, create documents, documents types, datatypes, views, layouts, scripts, ...

All contents are stored in database and works with stream wrapper.


## Contributing

If you wish to contribute to GotCms, please read the
[CONTRIBUTING.md](CONTRIBUTING.md).


## Notes

Please visits the best framework ever : [Zend Framework 2](http://framework.zend.com/)

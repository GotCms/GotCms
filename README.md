# GotCms - Information

Master status: [![Build Status](https://travis-ci.org/GotCms/GotCms.png?branch=master)](https://travis-ci.org/GotCms/GotCms)
[![Coverage Status](https://coveralls.io/repos/GotCms/GotCms/badge.png)](https://coveralls.io/r/GotCms/GotCms)
[![Latest Stable Version](https://poser.pugx.org/GotCms/GotCms/v/stable.png)](https://packagist.org/packages/GotCms/GotCms)
[![Latest Unstable Version](https://poser.pugx.org/GotCms/GotCms/v/unstable.png)](https://packagist.org/packages/GotCms/GotCms)

## About GotCms

* GotCms is a **Content Management System** (CMS) based on [Zend Framework 2.2.4](http://framework.zend.com/) which enables you to build websites and powerful online applications.
* GotCms [Official site](http://www.got-cms.com)
* This product has been made available under the terms of the GNU Lesser General Public License version 3.
* Please read the [LICENSE.txt](https://github.com/GotCms/GotCms/blob/master/LICENSE.txt) file for the exact license details that apply to GotCms.
* See [features](http://www.got-cms.dev/discover/features)
* Try out [online demo](http://www.got-cms.dev/discover/demo)

## Release information

### Updates in 0.2.1

Please see [CHANGELOG.md](https://github.com/GotCms/GotCms/blob/master/CHANGELOG.md).

### Download

Composer:

    $ curl -s https://getcomposer.org/installer | php
    $ php composer.phar create-project gotcms/gotcms path/ 0.2.1

Git:

    $ git clone https://github.com/GotCms/GotCms.git

Zip archive:

[https://github.com/GotCms/GotCms/archive/0.2.1.zip](https://github.com/GotCms/GotCms/archive/0.2.1.zip)


### Apache configuration

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


###Required

- An HTTP server
- Php version >= 5.3.3
- XML support
- FileInfo support
- Mbstring support
- Json support
- PDO support
- A database supported by PDO.
    - MySQL
    - PostgreSQL


### Recommended

Actually only tested with Apache HTTP server.
Php configuration:
- Display Errors: Off
- File Uploads: On
- Magic Quotes Runtime: Off
- Magic Quotes GPC: Off
- Register Globals: Off
- Session Auto Start: Off


### Installation

Go to the website, it will redirect you to /install.
Please follow instructions, there are only five steps:
- Language
- License
- Pre-configuration
- Database connection
- Configuration

Administration page is accessible by typing /admin after your installation path (i.e : http://yourdomain.tld/admin)

Then you can manage your website, create documents, documents types, datatypes, views, layouts, scripts, ...

All contents are stored in database.


### Contributing

If you wish to contribute to GotCms, please read the
[CONTRIBUTING.md](https://github.com/GotCms/GotCms/blob/master/CONTRIBUTING.md).


### Notes

Please visits the best framework ever : [Zend Framework 2](http://framework.zend.com/)

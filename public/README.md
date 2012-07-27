# GotCms - Information

## About GotCms

GotCms is a content management system based on [Zend Framework 2](http://framework.zend.com/zf2/). 

This product has been made available under the terms of the GNU GPL version 3.
Please read the public/LICENSE.txt file for the exact
license details that apply to GotCms.

## Installation
$ git clone https://github.com/PierreRambaud/GotCms.git

If you want to use VirtualHost, copy .htaccess content otherwise check if "AllowOverride" is set to "All".

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


## More information

Go to the website, it will be redirect to /install, follow instructions and refresh.

Go to /admin to manage website, create documents types, datatypes, views, layouts, scripts, ...

All content are stored in database and work with stream wrapper.

## Notes

Please visits the best framework ever : [Zend Framework 2](http://framework.zend.com/zf2/)

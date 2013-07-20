# CHANGELOG

##0.2.0 (-- --- 2013):
- User without user acl can't log out ([https://github.com/PierreRambaud/GotCms/issues/107](https://github.com/PierreRambaud/GotCms/issues/107))
- Css in chrome ([https://github.com/PierreRambaud/GotCms/issues/108](https://github.com/PierreRambaud/GotCms/issues/108))

##0.1.9 (20 Jul 2013):
- Can't update cms with git ([https://github.com/PierreRambaud/GotCms/issues/76](https://github.com/PierreRambaud/GotCms/issues/76))
- Add Zend Validate translations ([https://github.com/PierreRambaud/GotCms/issues/77](https://github.com/PierreRambaud/GotCms/issues/77))
- Optimize routes ([https://github.com/PierreRambaud/GotCms/issues/79](https://github.com/PierreRambaud/GotCms/issues/79))
- Remove headscript and headlink from Abstract Action ([https://github.com/PierreRambaud/GotCms/issues/80](https://github.com/PierreRambaud/GotCms/issues/80))
- Adding coveralls ([https://github.com/PierreRambaud/GotCms/issues/81](https://github.com/PierreRambaud/GotCms/issues/81))
- Replace Gc\Registry by Service Manager ([https://github.com/PierreRambaud/GotCms/issues/78](https://github.com/PierreRambaud/GotCms/issues/78))
- Update to Zend Framework 2.2.1 ([https://github.com/PierreRambaud/GotCms/issues/83](https://github.com/PierreRambaud/GotCms/issues/83))
- Automatically open the treeview ([https://github.com/PierreRambaud/GotCms/issues/84](https://github.com/PierreRambaud/GotCms/issues/84))
- Sitemap not working properly ([https://github.com/PierreRambaud/GotCms/issues/87](https://github.com/PierreRambaud/GotCms/issues/87))
- Bug in translations with select box ([https://github.com/PierreRambaud/GotCms/issues/88](https://github.com/PierreRambaud/GotCms/issues/88))
- Save all translations during installation ([https://github.com/PierreRambaud/GotCms/issues/90](https://github.com/PierreRambaud/GotCms/issues/90))
- Bug with Gc\Db\AbstractTable during installation ([https://github.com/PierreRambaud/GotCms/issues/91](https://github.com/PierreRambaud/GotCms/issues/91))
- Add russian translation ([https://github.com/PierreRambaud/GotCms/issues/89](https://github.com/PierreRambaud/GotCms/issues/89))
- Add Core\Config helper ([https://github.com/PierreRambaud/GotCms/issues/85](https://github.com/PierreRambaud/GotCms/issues/85))
- Intl is no longer required ([https://github.com/PierreRambaud/GotCms/issues/97](https://github.com/PierreRambaud/GotCms/issues/97))
- Script not working ([https://github.com/PierreRambaud/GotCms/issues/100](https://github.com/PierreRambaud/GotCms/issues/100))
- Remove Gc\Core\Config singleton ([https://github.com/PierreRambaud/GotCms/issues/99](https://github.com/PierreRambaud/GotCms/issues/99))
- Refactoring exception ([https://github.com/PierreRambaud/GotCms/issues/32](https://github.com/PierreRambaud/GotCms/issues/32))
- Refactoring events ([https://github.com/PierreRambaud/GotCms/issues/101](https://github.com/PierreRambaud/GotCms/issues/101))
- Log module ([https://github.com/PierreRambaud/GotCms/issues/33](https://github.com/PierreRambaud/GotCms/issues/33))

### Potential Breakage
`Gc\Core\Config` singleton will not working anymore. You must used in view or layout, the config helper `$this->config()->get()`
and for scripts you must used `$this->getServiceLocator()->get('CoreConfig')`.

Events names have been renamed. You must use the dot separator instead camelCase.


##0.1.8 (02 Jun 2013):
- Problem with replacement and mixed datatype ([https://github.com/PierreRambaud/GotCms/issues/55](https://github.com/PierreRambaud/GotCms/issues/55))
- Use cms_version instead version ([https://github.com/PierreRambaud/GotCms/issues/57](https://github.com/PierreRambaud/GotCms/issues/57))
- Optimize the helper "documents" ([https://github.com/PierreRambaud/GotCms/issues/58](https://github.com/PierreRambaud/GotCms/issues/58))
- Allow dot in identifier pattern ([https://github.com/PierreRambaud/GotCms/issues/59](https://github.com/PierreRambaud/GotCms/issues/59))
- Update to Zend Framework 2.2.0 ([https://github.com/PierreRambaud/GotCms/issues/60](https://github.com/PierreRambaud/GotCms/issues/60))
- Document parent are ignored in IndexController ([https://github.com/PierreRambaud/GotCms/issues/63](https://github.com/PierreRambaud/GotCms/issues/63))
- Session lifetime not working ([https://github.com/PierreRambaud/GotCms/issues/64](https://github.com/PierreRambaud/GotCms/issues/64))
- Missing dot in downloaded files name ([https://github.com/PierreRambaud/GotCms/issues/65](https://github.com/PierreRambaud/GotCms/issues/65))
- No such method 'select' for tabs widget instance ([https://github.com/PierreRambaud/GotCms/issues/66](https://github.com/PierreRambaud/GotCms/issues/66))
- Bug javascript ([https://github.com/PierreRambaud/GotCms/issues/67](https://github.com/PierreRambaud/GotCms/issues/67))
- No need to stay on login page if user has Identity ([https://github.com/PierreRambaud/GotCms/issues/68](https://github.com/PierreRambaud/GotCms/issues/68))
- Upload datatype must return an simple array if isn't multiple ([https://github.com/PierreRambaud/GotCms/issues/69](https://github.com/PierreRambaud/GotCms/issues/69))
- Home page can't display children ([https://github.com/PierreRambaud/GotCms/issues/70](https://github.com/PierreRambaud/GotCms/issues/70))
- Mixed datatype has problems when datatype return more than one element ([https://github.com/PierreRambaud/GotCms/issues/75](https://github.com/PierreRambaud/GotCms/issues/75))
- Make more themes for installation ([https://github.com/PierreRambaud/GotCms/issues/47](https://github.com/PierreRambaud/GotCms/issues/47))

##0.1.7 (30 Apr 2013):
- Get latest version can failed on certain php version ([https://github.com/PierreRambaud/GotCms/issues/53](https://github.com/PierreRambaud/GotCms/issues/53))
- Missing version modification in Gc\Version ([https://github.com/PierreRambaud/GotCms/issues/54](https://github.com/PierreRambaud/GotCms/issues/54))

##0.1.6 (30 Apr 2013):
- Only last key is required to display document ([https://github.com/PierreRambaud/GotCms/issues/42](https://github.com/PierreRambaud/GotCms/issues/42))
- Cache thrown exception ([https://github.com/PierreRambaud/GotCms/issues/43](https://github.com/PierreRambaud/GotCms/issues/43))
- Add specific layout for exception in front ([https://github.com/PierreRambaud/GotCms/issues/44](https://github.com/PierreRambaud/GotCms/issues/44))
- Update to latest GotSniffs version ([https://github.com/PierreRambaud/GotCms/issues/46](https://github.com/PierreRambaud/GotCms/issues/46))
- Update to Zend Framework 2.1.5
- Urls configuration (backend, frontend and cdn) ([https://github.com/PierreRambaud/GotCms/issues/40](https://github.com/PierreRambaud/GotCms/issues/40))
- Translator is not loaded during installation ([https://github.com/PierreRambaud/GotCms/issues/49](https://github.com/PierreRambaud/GotCms/issues/49))
- Add style on table elements ([https://github.com/PierreRambaud/GotCms/issues/48](https://github.com/PierreRambaud/GotCms/issues/48))
- Bug with postgresql during installation ([https://github.com/PierreRambaud/GotCms/issues/51](https://github.com/PierreRambaud/GotCms/issues/51))
- View Stream bug with huge content length ([https://github.com/PierreRambaud/GotCms/issues/52](https://github.com/PierreRambaud/GotCms/issues/52))

##0.1.5 (08 Apr 2013):
- Git updater failed ([https://github.com/PierreRambaud/GotCms/issues/24](https://github.com/PierreRambaud/GotCms/issues/24))
- Add translation directory for installation
- Add captcha for blog module ([https://github.com/PierreRambaud/GotCms/issues/25](https://github.com/PierreRambaud/GotCms/issues/25))
- Refactoring flash messenger ([https://github.com/PierreRambaud/GotCms/issues/26](https://github.com/PierreRambaud/GotCms/issues/26))
- Backup module failed with pgsql ([https://github.com/PierreRambaud/GotCms/issues/27](https://github.com/PierreRambaud/GotCms/issues/27))
- Disable cache on preview ([https://github.com/PierreRambaud/GotCms/issues/29](https://github.com/PierreRambaud/GotCms/issues/29))
- Flash messages failed in view helper script ([https://github.com/PierreRambaud/GotCms/issues/28](https://github.com/PierreRambaud/GotCms/issues/28))
- Update to Zend Framework 2.1.4
- Use new GotSniffs based on PSR2 ([https://github.com/PierreRambaud/GotCms/issues/38](https://github.com/PierreRambaud/GotCms/issues/38))
- Update jquery, jquery ui and modernizr ([https://github.com/PierreRambaud/GotCms/issues/39](https://github.com/PierreRambaud/GotCms/issues/39))
- Composer support ([https://github.com/PierreRambaud/GotCms/issues/35](https://github.com/PierreRambaud/GotCms/issues/35))
- Add plugin in Modules ([https://github.com/PierreRambaud/GotCms/issues/31](https://github.com/PierreRambaud/GotCms/issues/31))
- Unit testing for ZF modules ([https://github.com/PierreRambaud/GotCms/issues/36](https://github.com/PierreRambaud/GotCms/issues/36))

## 0.1.4 (12 Feb 2013):
- Bug with HTTP 404 (undefined variable view)
- Gc\Document\Model::fromUrlKey() not saving original data
- Administrator role must be protected and can't be edit
- Administrator user must be protected and can't be deleted
- User can retrieve his password
- Modules permissions ([https://github.com/PierreRambaud/GotCms/issues/21](https://github.com/PierreRambaud/GotCms/issues/21))
- Methods fetchAll, fetchOne, fetchRow and execute now have the ability to pass parameters
- Backup module ([https://github.com/PierreRambaud/GotCms/issues/20](https://github.com/PierreRambaud/GotCms/issues/20))
- Update to Zend Framework 2.1.1
- Execute script after update ([https://github.com/PierreRambaud/GotCms/issues/19](https://github.com/PierreRambaud/GotCms/issues/19))
- PHPUnit failed with MySQL ([https://github.com/PierreRambaud/GotCms/issues/23](https://github.com/PierreRambaud/GotCms/issues/23))

## 0.1.3 (12 Jan 2013):
- Sitemap module QuickFix set base path ([https://github.com/PierreRambaud/GotCms/issues/14](https://github.com/PierreRambaud/GotCms/issues/14))

## 0.1.2 (12 Jan 2013):
- Updater in module configuration (following) ([https://github.com/PierreRambaud/GotCms/issues/13](https://github.com/PierreRambaud/GotCms/issues/13))
- Indicate if the user can update via git ([https://github.com/PierreRambaud/GotCms/issues/16](https://github.com/PierreRambaud/GotCms/issues/16))
- Sitemap module ([https://github.com/PierreRambaud/GotCms/issues/14](https://github.com/PierreRambaud/GotCms/issues/14))
- Save original data ([https://github.com/PierreRambaud/GotCms/issues/17](https://github.com/PierreRambaud/GotCms/issues/17))

## 0.1.1 (11 Jan 2013):
- Updater in module configuration ([https://github.com/PierreRambaud/GotCms/issues/13](https://github.com/PierreRambaud/GotCms/issues/13))
- Cache manager ([https://github.com/PierreRambaud/GotCms/issues/12](https://github.com/PierreRambaud/GotCms/issues/12))
- Update to Zend Framework 2.0.6
- Refactoring installer ([https://github.com/PierreRambaud/GotCms/issues/15](https://github.com/PierreRambaud/GotCms/issues/15))
- Add translation

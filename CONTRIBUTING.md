# CONTRIBUTING

## RESOURCES

If you wish to contribute to GotCms, please be sure to
read to the following resource:

 -  Coding Standards:
    https://github.com/GotCms/GotSniffs
 -  JsHint:
    http://www.jshint.com/
 -  LessCss:
    http://lesscss.org/
 -  Yui Compressor:
    http://yui.github.io/yuicompressor/


## RUNNING TESTS
To run JsHint tests:
  ```
  $ ./scripts/jshint.sh
  ```

To run PHPUnit tests:

- Make sure you have a recent version of PHPUnit installed; 3.7.0
  minimally.
- Enter the `tests/` subdirectory for phpunit command.
- Copy `phpunit.xml.dist` file to `phpunit.xml`
- Copy `TestConfiguration.php.dist` file to `TestConfiguration.php`
- Edit `TestConfiguration.php` to enable database connection.

- Execute PHPUnit, providing a path to a component directory for which
  you wish to run tests, or a specific test class file.

  ```
  $ phpunit library/Gc/Core/ConfigTest.php
  ```

- You may also provide the `--group` switch; in such cases, provide the
  top-level component name:

  ```
  $ phpunit --group Gc
  ```

- Alternately, use the `runtests.sh` script. This can be executed with no
  arguments to run all tests:

  ```
  $ ./scripts/runtests.sh library/Gc/Core/ConfigTest.php
  ```

  You can also provide group names to run tests.

  ```
  $ ./scripts/runtests.sh Gc
  ```

  ```
  $ ./scripts/runtests.sh Datatypes Gc
  ```

## BUILD CSS AND JS

After install less via npm:
  ```
  $ ./scripts/regenerate_css.sh
  ```

After adding yui-compressor in scripts or install it via composer:
  ```
  $ ./scripts/regenerate_js.sh
  ```

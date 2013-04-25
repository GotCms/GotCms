# CONTRIBUTING

## RESOURCES

If you wish to contribute to GotCms, please be sure to
read to the following resource:

 -  Coding Standards:
    https://github.com/PierreRambaud/GotSniffs


## RUNNING TESTS

To run tests:

- Make sure you have a recent version of PHPUnit installed; 3.7.0
  minimally.
- Enter the `tests/` subdirectory.
- Copy `phpunit.xml.dist` file to `phpunit.xml`
- Copy `TestConfiguration.php.dist` file to `TestConfiguration.php`
- Edit `TestConfiguration.php` to enable database connection.

- Execute PHPUnit, providing a path to a component directory for which
  you wish to run tests, or a specific test class file.

  ```sh
  $ phpunit library/Gc/Core/ConfigTest.php
  ```

- You may also provide the `--group` switch; in such cases, provide the
  top-level component name:

  ```
  $ phpunit --group Gc
  ```

- Alternately, use the `run-tests.php` script. This can be executed with no
  arguments to run all tests:

  ```
  $ ./runtests.sh library/Gc/Core/ConfigTest.php
  ```

  You can also provide group names to run tests.

  ```
  $ ./runtests.sh Gc
  ```

  ```
  $ ./runtests.sh Datatypes Gc
  ```


#!/usr/bin/php5
<?php
chdir(dirname(__DIR__));
require_once (getenv('ZF2_PATH') ?: 'vendor/ZendFramework/library') . '/Zend/Loader/AutoloaderFactory.php';
$app_config = include 'config/application.config.php';
Zend\Loader\AutoloaderFactory::factory(array('Zend\Loader\StandardAutoloader' => $app_config['autoloader']));

$adapter = new Zend\Db\Adapter\Adapter(array(
    'driver' => 'pdo_pgsql',
    'database' => 'gotcms',
    'username' => 'got',
    'password' => ''
));

$ini = new Zend\Config\Reader\Ini();
$roles = $ini->fromFile('scripts/roles.ini');

try
{
    echo "\n>>> Delete user_acl_roles table\n";
    $statement = $adapter->createStatement('TRUNCATE user_acl_role CASCADE');
    $result = $statement->execute();
}
catch (Exception $e)
{
    echo $e->getMessage();
}

try
{
    foreach ($roles['role'] as $key=>$value)
    {
        $statement = $adapter->createStatement("INSERT INTO user_acl_role (name) VALUES ('" . $value . "')");
        $result = $statement->execute();
    }

    echo "\n>>> user_acl_roles table rebuild complete\n\n";
}
catch (Exception $e)
{
    echo $e->getMessage();
}

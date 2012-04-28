#!/usr/bin/php5
<?php
chdir(dirname(__DIR__));
require_once(getenv('ZF2_PATH') ?: 'vendor/ZendFramework/library') . '/Zend/Loader/AutoloaderFactory.php';
$app_config = include 'config/application.config.php';
Zend\Loader\AutoloaderFactory::factory(array('Zend\Loader\StandardAutoloader' => $app_config['autoloader']));

$adapter = new Zend\Db\Adapter\Adapter(array(
    'driver' => 'pdo_pgsql',
    'database' => 'gotcms',
    'username' => 'got',
    'password' => ''
));

$ini = new Zend\Config\Reader\Ini();
$resources = $ini->fromFile('scripts/resources.ini');

try
{
    echo "\n>>> Delete acl_resources table\n";
    $statement = $adapter->createStatement("TRUNCATE user_acl_resources CASCADE");
    $result = $statement->execute();
    $statement = $adapter->createStatement("TRUNCATE user_acl_permissions CASCADE");
    $result = $statement->execute();
}
catch(Exception $e)
{
    echo $e->getMessage();
}

try
{
    foreach($resources as $key => $value)
    {
        $statement = $adapter->createStatement("INSERT INTO user_acl_resources (resource) VALUES ('" . $key . "')");
        $result = $statement->execute();

        $statement = $adapter->createStatement("SELECT id FROM user_acl_resources WHERE resource =  '" . $key . "'");
        $result = $statement->execute();
        $lastInsertId = $result->current();
        $lastInsertId = $lastInsertId['id'];

        foreach($value as $key2 => $value2)
        {
            $statement = $adapter->createStatement("SELECT id FROM user_acl_roles WHERE name = '" . $value2 . "'");
            $result = $statement->execute();
            $role = $result->current();
            if(!empty($role['id']))
            {
                $statement = $adapter->createStatement("INSERT INTO user_acl_permissions (user_acl_role_id, user_acl_resource_id, permission) VALUES ('".$role['id']."', '" . $lastInsertId . "', '" . $key2 . "')");
                $result = $statement->execute();
            }
            else
            {
                echo "ERROR! resource " . $value2 . " not found!";
            }
        }
    }

    echo "\n>>> ACL Resources rebuild complete\n\n";
}
catch(Exception $e)
{
    echo $e->getMessage();
}

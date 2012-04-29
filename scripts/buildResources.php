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
    $statement = $adapter->createStatement("TRUNCATE user_acl_resource CASCADE");
    $result = $statement->execute();
    $statement = $adapter->createStatement("TRUNCATE user_acl_permission CASCADE");
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
        $statement = $adapter->createStatement("INSERT INTO user_acl_resource (resource) VALUES ('" . $key . "')");
        $result = $statement->execute();

        $statement = $adapter->createStatement("SELECT id FROM user_acl_resource WHERE resource =  '" . $key . "'");
        $result = $statement->execute();
        $lastInsertId = $result->current();
        $lastInsertId = $lastInsertId['id'];

        $permissions = array();
        foreach($value as $key2 => $value2)
        {
            if(!in_array($key2, $permissions))
            {
                $statement = $adapter->createStatement("INSERT INTO user_acl_permission (permission, user_acl_resource_id) VALUES ('".$key2."', '".$lastInsertId."')");
                $result = $statement->execute();
                $permissions[] = $key2;
            }
        }
    }

    foreach($resources as $key => $value)
    {
        $statement = $adapter->createStatement("SELECT id FROM user_acl_resource WHERE resource =  '" . $key . "'");
        $result = $statement->execute();
        $lastResourceInsertId = $result->current();
        $lastResourceInsertId = $lastResourceInsertId['id'];

        foreach($value as $key2 => $value2)
        {
            $statement = $adapter->createStatement("SELECT id FROM user_acl_permission WHERE permission =  '" . $key2 . "' AND user_acl_resource_id = '" .$lastResourceInsertId . "'");
            $result = $statement->execute();
            $lastInsertId = $result->current();
            $lastInsertId = $lastInsertId['id'];

            $statement = $adapter->createStatement("SELECT id FROM user_acl_role WHERE name = '" . $value2 . "'");
            $result = $statement->execute();
            $role = $result->current();
            if(!empty($role['id']))
            {
                $statement = $adapter->createStatement("INSERT INTO user_acl (user_acl_role_id, user_acl_permission_id) VALUES ('".$role['id']."', " . $lastInsertId . ")");
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

<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Gc_Test
 * @package  Bootstrap
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

use Gc\Core\Config as GcConfig;
use Gc\Registry;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\TableGateway\TableGateway;

$config = array(
    'db' => array(
        'driver' => GC_DATABASE_DRIVER,
        'username' => GC_DATABASE_USERNAME,
        'password' => GC_DATABASE_PASSWORD,
        'database' => GC_DATABASE_DATABASE,
        'hostname' => GC_DATABASE_HOSTNAME,
    ),
);

try {
    $dbAdapter = new DbAdapter($config['db']);
    $dbAdapter->getDriver()->getConnection()->connect();
} catch (Exception $e) {
    echo 'Can\'t connect to database exiting.' . PHP_EOL;
    exit;
}

\Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($dbAdapter);
Registry::set('Configuration', $config);
Registry::set('Db', $dbAdapter);

/**
 * Install database
 */
$resource   = $dbAdapter->getDriver()->getConnection()->getResource();
$driverName = str_replace('pdo_', '', $config['db']['driver']);
$resource->exec(file_get_contents(sprintf($gcRoot . '/data/install/sql/database-%s.sql', $driverName)));
$resource->exec(file_get_contents($gcRoot . '/data/install/sql/data.sql'));
//Create role
$ini   = new \Zend\Config\Reader\Ini();
$roles = $ini->fromFile($gcRoot . '/data/install/scripts/roles.ini');

foreach ($roles['role'] as $key => $value) {
    $statement = $dbAdapter->createStatement("INSERT INTO user_acl_role (name) VALUES ('" . $value . "')");
    $statement->execute();
}

//resources
$ini       = new \Zend\Config\Reader\Ini();
$resources = $ini->fromFile($gcRoot . '/data/install/scripts/resources.ini');

foreach ($resources as $key => $value) {
    $statement = $dbAdapter->createStatement("INSERT INTO user_acl_resource (resource) VALUES ('" . $key . "')");
    $statement->execute();

    $statement    = $dbAdapter->createStatement("SELECT id FROM user_acl_resource WHERE resource =  '" . $key . "'");
    $result       = $statement->execute();
    $lastInsertId = $result->current();
    $lastInsertId = $lastInsertId['id'];

    $permissions = array();
    foreach ($value as $k => $v) {
        if (!in_array($k, $permissions)) {
            $statement = $dbAdapter->createStatement(
                "INSERT INTO user_acl_permission (permission, user_acl_resource_id)
                VALUES ('" . $k . "', '" . $lastInsertId . "')"
            );
            $statement->execute();
            $permissions[] = $k;
        }
    }
}

foreach ($resources as $key => $value) {
    $statement            = $dbAdapter->createStatement(
        "SELECT id FROM user_acl_resource WHERE resource =  '" . $key . "'"
    );
    $result               = $statement->execute();
    $lastResourceInsertId = $result->current();
    $lastResourceInsertId = $lastResourceInsertId['id'];

    foreach ($value as $k => $v) {
        $statement    = $dbAdapter->createStatement(
            "SELECT id FROM user_acl_permission
            WHERE permission =  '" . $k . "'
                AND user_acl_resource_id = '" . $lastResourceInsertId . "'"
        );
        $result       = $statement->execute();
        $lastInsertId = $result->current();
        $lastInsertId = $lastInsertId['id'];

        $statement = $dbAdapter->createStatement("SELECT id FROM user_acl_role WHERE name = '" . $v . "'");
        $result    = $statement->execute();
        $role      = $result->current();
        if (!empty($role['id'])) {
            $statement = $dbAdapter->createStatement(
                "INSERT INTO user_acl (user_acl_role_id, user_acl_permission_id)
                VALUES ('" . $role['id'] . "', " . $lastInsertId . ')'
            );
            $statement->execute();
        }
    }
}

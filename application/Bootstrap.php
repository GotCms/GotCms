<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected $_modules = array('login','content','config','statistics','development');

    public function run()
    {
        $db = $this->getPluginResource('db')->getDbAdapter();
        Zend_Registry::set('Zend_Db', $db);

        $locale = new Zend_Locale();
        Zend_Registry::set('Zend_Locale', $locale);
        $language = $locale->getLanguage();

        $auth = Zend_Auth::getInstance();

        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Es_Controller_Plugin_Messages());
        $router = $front->getRouter();

        $router->addRoute('controller_redirect', new Zend_Controller_Router_Route('admin', array('controller' => 'admin')));
        $router->addRoute('module_redirect', new Zend_Controller_Router_Route_Regex('('.implode('|', $this->_modules).')', array('module' => 'default', 'controller' => 'index', 'action' => 'index')));
        $router->addRoute('module', new Zend_Controller_Router_Route('admin/:module'));
        $router->addRoute('module_controller', new Zend_Controller_Router_Route('admin/:module/:controller'));
        $router->addRoute('module_controller_action', new Zend_Controller_Router_Route('admin/:module/:controller/:action/*'));

        $router->addConfig(new Zend_Config_Ini(APPLICATION_PATH.'/configs/routes-general.ini', APPLICATION_ENV), 'routes');
        $router->addConfig(new Zend_Config_Ini(APPLICATION_PATH.'/configs/routes-development.ini', APPLICATION_ENV), 'routes');
        $router->addConfig(new Zend_Config_Ini(APPLICATION_PATH.'/configs/routes-content.ini', APPLICATION_ENV), 'routes');

        $front->setParam('useControllerDefaultAlways', TRUE);


        $select = $db->select();
        $select->from(array('t' => 'translate'), array('t.source AS source'))
            ->joinLeft(array('tl' => 'translate_language'), 'tl.translate_id = t.id', new Zend_Db_Expr('CASE WHEN tl.destination IS NULL THEN t.source ELSE tl.destination END AS destination'))
            ->where('tl.language = ? ', $language);

        $translate_data = $db->fetchPairs($select);

        if(!empty($translate_data))
        {
            Zend_Registry::set('Zend_Translate', new Zend_Translate(Zend_Translate::AN_ARRAY, $translate_data, $language));
        }

        Zend_Registry::set('user_id', 1);

        parent::run();
    }
}


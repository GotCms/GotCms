<?php
namespace Config\Form;

use Gc\Form\AbstractForm,
    Zend\Validator\Db,
    Zend\Validator\Identical,
    Zend\Form\Element,
    Gc\User\Permission;

class Role extends AbstractForm
{
    /**
     * Initialize Role form
     * @return void
     */
    public function init()
    {
        $this->setMethod(self::METHOD_POST);

        $name = new Element\Text('name');
        $name->setRequired(TRUE)
            ->setLabel('Name')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $description  = new Element\Text('description');
        $description->setLabel('Description')
            ->setAttrib('class', 'input-text');

        $submit = new Element\Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Save');


        $this->addElements(array($name, $description, $submit));
    }

    /**
     * Initialize permissions
     *
     * @return \Config\Form\Role
     */
    public function initPermissions($user_permissions = NULL)
    {
        $permissions_table = new Permission\Collection();
        $resources = $permissions_table->getPermissions();
        foreach($resources as $resource => $permissions)
        {
            foreach($permissions as $permission_id => $permission)
            {
                $element = new Element\Checkbox((string)$permission_id);
                $element->setBelongsTo('permissions');
                $element->setLabel($permission);

                if(!empty($user_permissions[$resource]) and array_key_exists($permission_id, $user_permissions[$resource]))
                {
                    $element->setValue(TRUE);
                }

                $this->addElement($element);
            }
        }
    }
}

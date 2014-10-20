<?php
/**
 * AttributeController class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * AttributeController class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class AttributeController extends CController
{

    public function filters()
    {
        return $this->getModule()->getFilters('attribute');
    }


    public function accessRules()
    {
        return $this->getModule()->getAccessRules('attribute');
    }


    public function actions()
    {
        return array(
            'index' => 'application.modules.eav.controllers.attribute.IndexAction',
            'create' => 'application.modules.eav.controllers.attribute.CreateAction',
            'update' => 'application.modules.eav.controllers.attribute.UpdateAction',
            'delete' => 'application.modules.eav.controllers.attribute.DeleteAction',
        );
    }
} 
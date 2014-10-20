<?php
/**
 * SetController class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * SetController class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class SetController extends CController
{
    public function filters()
    {
        return $this->getModule()->getFilters('set');
    }


    public function accessRules()
    {
        return $this->getModule()->getAccessRules('set');
    }


    public function actions()
    {
        return array(
            'index' => 'application.modules.eav.controllers.set.IndexAction',
            'create' => 'application.modules.eav.controllers.set.CreateAction',
            'update' => 'application.modules.eav.controllers.set.UpdateAction',
            'delete' => 'application.modules.eav.controllers.set.DeleteAction',
        );
    }
} 
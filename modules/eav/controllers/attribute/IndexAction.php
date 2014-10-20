<?php
/**
 * IndexAction class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * IndexAction class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class IndexAction extends CAction
{
    public function run()
    {
        $criteria = new CDbCriteria();

        $pages = new CPagination(EavAttributeExtended::model()->count());
        $pages->pageSize = $this->getController()->getModule()->getItemsPerPage();
        $pages->applyLimit($criteria);

        $sort = new CSort('EavAttributeExtended');
        $sort->attributes = array('id', 'name', 'label', 'type', 'data_type');
        $sort->applyOrder($criteria);

        $model = EavAttributeExtended::model()->findAll($criteria);

        $this->controller->render('index', array('model' => $model, 'sort' => $sort, 'pages' => $pages));
    }
} 
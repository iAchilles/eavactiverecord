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

        $pages = new CPagination(EavSetExtended::model()->count());
        $pages->pageSize = $this->getController()->getModule()->getItemsPerPage();
        $pages->applyLimit($criteria);

        $sort = new CSort('EavSetExtended');
        $sort->attributes = array('id', 'name');
        $sort->applyOrder($criteria);

        $model = EavSetExtended::model()->findAll($criteria);

        $this->controller->render('index', array('model' => $model, 'sort' => $sort, 'pages' => $pages));
    }
} 
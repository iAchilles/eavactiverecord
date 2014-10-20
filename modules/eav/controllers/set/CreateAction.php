<?php
/**
 * CreateAction class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * CreateAction class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class CreateAction extends CAction
{
    public function run()
    {
        $request = Yii::app()->request;
        $model = new EavSetExtended();
        $formName = get_class($model);

        if (is_null($request->getPost($formName)))
        {
            $this->controller->render('create', array('model' => $model));
            Yii::app()->end();
        }

        $attributes = $request->getPost($formName);
        $attributes = isset($attributes['attachedEavAttributes']) ? $attributes['attachedEavAttributes'] : array();
        $model->setAttributes($request->getPost($formName));
        $model->setAttachedEavAttributes($attributes);

        if ($model->save())
        {
            Yii::app()->getComponent('user')->setFlash('success', Yii::t('EavModule.eavactiverecord', 'The attribute set has been successfully saved'));
            $this->controller->redirect(array('set/index'));
        }

        Yii::app()->getComponent('user')->setFlash('error', Yii::t('EavModule.eavactiverecord', 'A validation error has occurred while processing your request'));
        $this->controller->render('create', array('model' => $model));
    }
} 
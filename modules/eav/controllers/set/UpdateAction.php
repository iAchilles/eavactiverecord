<?php
/**
 * UpdateAction class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * UpdateAction class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class UpdateAction extends CAction
{
    public function run($id)
    {
        $model = EavSetExtended::model()->findByPk($id);

        if (is_null($model))
        {
            throw new CHttpException(404);
        }

        $request = Yii::app()->request;
        $formName = get_class($model);

        if (is_null($request->getPost($formName)))
        {
            $this->controller->render('update', array('model' => $model));
            Yii::app()->end();
        }

        $attributes = $request->getPost($formName);
        $attachedAttributes = isset($attributes['attachedEavAttributes']) ? $attributes['attachedEavAttributes'] : array();
        $orderedAttributes = isset($attributes['orderedEavAttributes']) ? $attributes['orderedEavAttributes'] : array();
        $model->setAttributes($request->getPost($formName));
        $model->setAttachedEavAttributes($attachedAttributes);
        $model->setOrderedEavAttributes($orderedAttributes);

        if ($model->save())
        {
            Yii::app()->getComponent('user')->setFlash('success', Yii::t('EavModule.eavactiverecord', 'The attribute set has been successfully updated'));
        }
        else
        {
            Yii::app()->getComponent('user')->setFlash('error', Yii::t('EavModule.eavactiverecord', 'A validation error has occurred while processing your request'));
        }

        $this->controller->render('update', array('model' => $model));
    }
} 
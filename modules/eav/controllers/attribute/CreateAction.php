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
        $model = new EavAttributeExtended();
        $formName = get_class($model);
        $validator = new BaseValidatorForm();
        $validator->setAttribute($model);

        if (is_null($request->getPost($formName)))
        {
            $this->controller->render('create', array('model' => $model, 'validator' => $validator));
            Yii::app()->end();
        }

        $validator->setValidatorInputs($_POST);
        $attributes = $request->getPost($formName);
        $model->attributes = $attributes;
        $model->setRules($validator->getRules());
        $model->setValues($attributes['values']);

        if ($model->validate() && empty($model->validatorErrors))
        {
            $model->assignPossibleValues();
            if ($model->save(false))
            {
                Yii::app()->getComponent('user')->setFlash('success', Yii::t('EavModule.eavactiverecord', 'The attribute has been successfully saved'));
                $this->controller->redirect(array('attribute/index'));
            }
        }

        Yii::app()->getComponent('user')->setFlash('error', Yii::t('EavModule.eavactiverecord', 'A validation error has occurred while processing your request'));
        $this->controller->render('create', array('model' => $model, 'validator' => $validator));
    }
} 
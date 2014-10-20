<?php
/**
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
 ?>

<ol class="breadcrumb">
    <li><a href="<?php echo $this->createAbsoluteUrl('set/index') ?>"><?php echo Yii::t('EavModule.eavactiverecord', 'EAV attribute sets') ?></a></li>
    <li class="active"><?php echo Yii::t('EavModule.eavactiverecord', 'Create new attribute set') ?></li>
</ol>

<div class="page-header">
    <h4>
        <?php echo Yii::t('EavModule.eavactiverecord', 'Create new attribute set') ?>
    </h4>
</div>

<?php $form = $this->beginWidget('CActiveForm', array('id' => 'set-create-form', 'method' => 'post', 'htmlOptions' => array('class' => 'form-horizontal'))) ?>

    <div class="form-group">
        <?php echo $form->label($model, 'name', array('class' => 'col-md-3 control-label')) ?>
        <div class="col-md-5">
            <?php echo $form->textField($model, 'name', array('class' => 'form-control')) ?>
            <span class="help-block">
            <?php echo $form->error($model, 'name', array('class' => 'label label-danger')) ?>
            </span>
        </div>
    </div>

    <div class="form-group">
        <?php echo $form->label($model, 'attachedEavAttributes', array('class' => 'col-md-3 control-label')) ?>
        <div class="col-md-5">
            <?php echo $form->dropDownList($model, 'attachedEavAttributes', $model->getEavAttributeLabels(), array('class' => 'form-control chosen', 'multiple' => 'multiple', 'data-placeholder' => Yii::t('EavModule.eavactiverecord', 'Please select'))) ?>
            <span class="help-block">
            <?php echo $form->error($model, 'attachedEavAttributes', array('class' => 'label label-danger')) ?>
            </span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-5 col-md-offset-3">
            <?php echo CHtml::submitButton(Yii::t('EavModule.eavactiverecord', 'Save'), array('class' => 'btn btn-primary btn-block')) ?>
        </div>
    </div>

<?php $this->endWidget() ?>

<?php
$js = <<<JS
jQuery(".chosen").chosen();
JS
?>
<?php Yii::app()->getComponent('clientScript')->registerScriptFile($this->getModule()->getAssetsUrl() . '/chosen/chosen.jquery.min.js') ?>
<?php Yii::app()->getComponent('clientScript')->registerCssFile($this->getModule()->getAssetsUrl() . '/chosen/chosen.min.css') ?>
<?php Yii::app()->getComponent('clientScript')->registerScript('js-set-create-form', new CJavaScriptExpression($js), CClientScript::POS_END) ?>
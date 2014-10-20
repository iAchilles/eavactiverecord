<?php
/**
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
?>

<?php $labels = $model->getValidatorLabels() ?>
<?php $validators = $model->getEavValidatorList() ?>

<ol class="breadcrumb">
    <li><a href="<?php echo $this->createAbsoluteUrl('attribute/index') ?>"><?php echo Yii::t('EavModule.eavactiverecord', 'EAV attributes') ?></a></li>
    <li class="active"><?php echo Yii::t('EavModule.eavactiverecord', 'Create new attribute') ?></li>
</ol>

<div class="page-header">
    <h4>
        <?php echo Yii::t('EavModule.eavactiverecord', 'Create new attribute') ?>
    </h4>
</div>

<?php $form = $this->beginWidget('CActiveForm', array('id' => 'attribute-create-form', 'method' => 'post', 'htmlOptions' => array('class' => 'form-horizontal'))) ?>

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
        <?php echo $form->label($model, 'label', array('class' => 'col-md-3 control-label')) ?>
        <div class="col-md-5">
            <?php echo $form->textField($model, 'label', array('class' => 'form-control')) ?>
            <span class="help-block">
            <?php echo $form->error($model, 'label', array('class' => 'label label-danger')) ?>
            </span>
        </div>
    </div>

    <div class="form-group">
        <?php echo $form->label($model, 'type', array('class' => 'col-md-3 control-label')) ?>
        <div class="col-md-5">
            <?php echo $form->dropDownList($model, 'type', $model->getTypeLabels(), array('class' => 'form-control', 'empty' => Yii::t('EavModule.eavactiverecord', 'Please select'))) ?>
            <span class="help-block">
            <?php echo $form->error($model, 'type', array('class' => 'label label-danger')) ?>
            </span>
        </div>
    </div>

    <div class="form-group">
        <?php echo $form->label($model, 'data_type', array('class' => 'col-md-3 control-label')) ?>
        <div class="col-md-5">
            <?php echo $form->dropDownList($model, 'data_type', $model->getDataTypeLabels(), array('class' => 'form-control', 'empty' => Yii::t('EavModule.eavactiverecord', 'Please select'))) ?>
            <span class="help-block">
            <?php echo $form->error($model, 'data_type', array('class' => 'label label-danger')) ?>
            </span>
        </div>
    </div>

    <div class="form-group">
        <?php echo $form->label($model, 'values', array('class' => 'col-md-3 control-label')) ?>
        <div class="col-md-5">
            <?php echo $form->textArea($model, 'values', array('class' => 'form-control', 'placeholder' => Yii::t('EavModule.eavactiverecord', 'A space separated list of pairs "value label"'))) ?>
            <span class="help-block">
            <?php echo $form->error($model, 'values', array('class' => 'label label-danger')) ?>
            </span>
        </div>
    </div>

    <br>

    <h4><?php echo Yii::t('EavModule.eavactiverecord', 'Validators') ?></h4>
    <hr>

    <div class="panel-group" id="accordion">
        <?php foreach ($validator->getHtml() as $name => $html) : ?>

            <div class="panel <?php echo in_array($name, $model->getValidatorErrors()) ? 'panel-danger' : 'panel-default' ?>">
                <div class="panel-heading">
                    <h5 class="panel-title pull-left">
                        <a data-toggle="collapse" data-parent="#accordion" href="<?php echo '#' . $name ?>">
                            <?php echo $labels[$name] ?>
                        </a>
                    </h5>
                    <div class="btn-group pull-right btn-group-xs" data-toggle="buttons">
                        <label class="btn btn-primary <?php echo isset($validators[$name]) ? 'active' : '' ?>">
                            <input data-validator-control value="1" type="radio" name="<?php echo $name ?>"> <?php echo Yii::t('EavModule.eavactiverecord', 'Enabled') ?>
                        </label>
                        <label class="btn btn-primary <?php echo !isset($validators[$name]) ? 'active' : '' ?>">
                            <input data-validator-control value="0" type="radio" name="<?php echo $name ?>"> <?php echo Yii::t('EavModule.eavactiverecord', 'Disabled') ?>
                        </label>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div id="<?php echo $name ?>" class="panel-collapse collapse">
                    <div class="panel-body">
                        <fieldset <?php echo !isset($validators[$name]) ? 'disabled' : '' ?>>
                            <?php echo $html ?>
                        </fieldset>
                    </div>
                </div>
            </div>

        <?php endforeach ?>
    </div>

    <div class="form-group">
        <div class="col-md-5 col-md-offset-3">
            <?php echo CHtml::submitButton(Yii::t('EavModule.eavactiverecord', 'Save'), array('class' => 'btn btn-primary btn-block')) ?>
        </div>
    </div>


<?php $this->endWidget() ?>

<?php
$js = <<<JS
jQuery('[data-validator-control]').change(validatorControlHandler);
function validatorControlHandler(e)
{
    var target = jQuery(e.currentTarget);
    if (target.val() === '1')
    {
        jQuery('#' + target.attr('name') + ' .panel-body fieldset').prop('disabled', false);
    }
    else
    {
        jQuery('#' + target.attr('name') + ' .panel-body fieldset').prop('disabled', true);
    }
}
JS
?>

<?php Yii::app()->getComponent('clientScript')->registerScript('js-attribute-create-form', new CJavaScriptExpression($js), CClientScript::POS_END) ?>

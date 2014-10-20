<?php
/**
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
?>

    <ol class="breadcrumb">
        <li><a href="<?php echo $this->createAbsoluteUrl('set/index') ?>"><?php echo Yii::t('EavModule.eavactiverecord', 'EAV attribute sets') ?></a></li>
        <li class="active"><?php echo Yii::t('EavModule.eavactiverecord', 'Edit attribute set') ?></li>
    </ol>

    <div class="page-header">
        <h4>
            <?php echo Yii::t('EavModule.eavactiverecord', 'Edit attribute set') ?>
        </h4>
    </div>

<?php $form = $this->beginWidget('CActiveForm', array('id' => 'set-update-form', 'method' => 'post', 'htmlOptions' => array('class' => 'form-horizontal'))) ?>

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

<?php if (!empty($model->eavAttributes)) : ?>
    <?php $counter = 0 ?>
    <?php $attributeExtended = EavAttributeExtended::model() ?>
    <br>
    <h5><?php echo Yii::t('EavModule.eavactiverecord', 'Attached EAV attributes') ?></h5>
    <span class="h6 text-muted"><span class="glyphicon glyphicon-info-sign"></span>  &nbsp;<?php echo Yii::t('EavModule.eavactiverecord', 'You can reorder attributes by simply dragging them with your mouse') ?></span>
    <div class="clearfix"></div>
    <hr>
    <table class="table table-striped">
        <thead>
        <th class="col-md-1">#</th>
        <th class="col-md-3"><?php echo $attributeExtended->getAttributeLabel('name') ?></th>
        <th class="col-md-3"><?php echo $attributeExtended->getAttributeLabel('label') ?></th>
        <th class="col-md-2"><?php echo $attributeExtended->getAttributeLabel('type') ?></th>
        <th class="col-md-2"><?php echo $attributeExtended->getAttributeLabel('data_type') ?></th>
        </thead>
        <tbody data-sortable>
        <?php foreach ($model->eavAttributes as $attribute) : ?>
            <tr>
                <td><span data-row-index><?php echo ++$counter ?></span><?php echo $form->hiddenField($model, 'orderedEavAttributes[]', array('value' => $attribute->id)) ?></td>
                <td><?php echo $attribute->name ?></td>
                <td><?php echo CHtml::encode($attribute->label) ?></td>
                <td><?php echo $attributeExtended->typeLabels[$attribute->type] ?></td>
                <td><?php echo $attributeExtended->dataTypeLabels[$attribute->data_type] ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

    <div class="form-group">
        <div class="col-md-5 col-md-offset-3">
            <?php echo CHtml::submitButton(Yii::t('EavModule.eavactiverecord', 'Save'), array('class' => 'btn btn-primary btn-block')) ?>
        </div>
    </div>

<?php $this->endWidget() ?>

<?php
$js = <<<JS
jQuery(".chosen").chosen();
jQuery('[data-sortable]').sortable({cursor: 'move', containment: 'parent', create: prepareRows, update: updateRows});
function prepareRows(event, ui)
{
    jQuery('[data-sortable] td').each(function()
    {
        jQuery(this).css('width', jQuery(this).outerWidth() + 'px');
    });
}
function updateRows(event, ui)
{
    jQuery('tr').each(function(){
        var index = jQuery('tr').index(this);
        jQuery('[data-row-index]', this).text(index);
    });
}
JS
?>
<?php Yii::app()->getComponent('clientScript')->registerCoreScript('jquery.ui') ?>
<?php Yii::app()->getComponent('clientScript')->registerScriptFile($this->getModule()->getAssetsUrl() . '/chosen/chosen.jquery.min.js') ?>
<?php Yii::app()->getComponent('clientScript')->registerCssFile($this->getModule()->getAssetsUrl() . '/chosen/chosen.min.css') ?>
<?php Yii::app()->getComponent('clientScript')->registerScript('js-set-update-form', new CJavaScriptExpression($js), CClientScript::POS_END) ?>
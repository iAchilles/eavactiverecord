<?php
/**
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
?>

<div class="modal fade" id="modal-dialog" tabindex="-1" role="dialog" aria-labelledby="label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="label"><?php echo Yii::t('EavModule.eavactiverecord', 'Delete Attribute')  ?></h4>
            </div>
            <div class="modal-body">
                <strong><?php echo Yii::t('EavModule.eavactiverecord', 'Are you sure you want to delete this EAV attribute?') ?></strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('EavModule.eavactiverecord', 'Cancel') ?></button>
                <a data-action-url href="#" class="btn btn-danger"><?php echo Yii::t('EavModule.eavactiverecord', 'Delete') ?></a>
            </div>
        </div>
    </div>
</div>

<div class="page-header">
    <h4 class="pull-left">
        <?php echo Yii::t('EavModule.eavactiverecord', 'List of EAV attributes') ?>
    </h4>
    <a class="btn btn-primary pull-right" href="<?php echo $this->createAbsoluteUrl('attribute/create') ?>">
        <?php echo Yii::t('EavModule.eavactiverecord', 'Create new attribute') ?>
    </a>
    <div class="clearfix"></div>
</div>

<?php if (empty($model)) : ?>

    <h5><?php echo Yii::t('EavModule.eavactiverecord', 'There are no attributes available.') ?><h5>

<?php else : ?>

    <table class="table table-striped">
        <thead>
        <th class="col-md-1"><?php echo $sort->link('id', '#') ?></th>
        <th class="col-md-3"><?php echo $sort->link('name') ?></th>
        <th class="col-md-3"><?php echo $sort->link('label') ?></th>
        <th class="col-md-2"><?php echo $sort->link('type') ?></th>
        <th class="col-md-2"><?php echo $sort->link('data_type') ?></th>
        <th></th>
        </thead>
        <tbody>
        <?php foreach ($model as $attribute) : ?>
            <tr>
                <td><?php echo $attribute->id ?></td>
                <td><?php echo $attribute->name ?></td>
                <td><?php echo CHtml::encode($attribute->label) ?></td>
                <td><?php echo $attribute->typeLabels[$attribute->type] ?></td>
                <td><?php echo $attribute->dataTypeLabels[$attribute->data_type] ?></td>
                <td>
                    <a data-toggle="tooltip" data-placement="top" title="<?php echo Yii::t('EavModule.eavactiverecord', 'Edit') ?>"  class="btn btn-xs btn-success" href="<?php echo $this->createAbsoluteUrl('attribute/update', array('id' => $attribute->id)) ?>">
                        <span class="glyphicon glyphicon-edit"></span>
                    </a>
                    <a data-action-confirmation onclick="return false;" data-toggle="tooltip" data-placement="top" title="<?php echo Yii::t('EavModule.eavactiverecord', 'Delete') ?>"  class="btn btn-xs btn-danger" href="<?php echo $this->createAbsoluteUrl('attribute/delete', array('id' => $attribute->id)) ?>">
                        <span class="glyphicon glyphicon-remove"></span>
                    </a>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>

    <div class="block">
        <?php $this->widget('CLinkPager', array('pages' => $pages,
                                                'id' => 'attributes-pager',
                                                'header' => '',
                                                'lastPageLabel' => '»',
                                                'firstPageLabel' => '«',
                                                'nextPageCssClass' => 'hidden',
                                                'previousPageCssClass' => 'hidden',
                                                'selectedPageCssClass' => 'active',
                                                'prevPageLabel' => '...',
                                                'nextPageLabel' => '',
                                                'cssFile' => false,
                                                'htmlOptions' => array('class' => 'pagination'))) ?>
    </div>

<?php endif ?>

<?php
$js = <<<JS
jQuery('[data-action-confirmation]').click(clickHandler);
jQuery('[data-toggle="tooltip"]').tooltip();
function clickHandler(e)
{
    var target = jQuery(e.currentTarget);
    jQuery('#modal-dialog [data-action-url]').attr('href', target.attr('href'));
    jQuery('#modal-dialog').modal();
}
JS
?>

<?php Yii::app()->getComponent('clientScript')->registerScript('js-attribute-index', new CJavaScriptExpression($js), CClientScript::POS_END) ?>



<?php
/**
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
?>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'enableClientValidation', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
            <?php echo CHtml::activeRadioButtonList($model, 'enableClientValidation',
                array('true' => Yii::t('EavModule.eavactiverecord', 'Enabled'), 'false' => Yii::t('EavModule.eavactiverecord', 'Disabled')),
                array('container' => 'div',
                      'separator' => '',
                      'template' => '{beginLabel}{input}{labelTitle}{endLabel}',
                      'labelOptions' => array('class' => 'radio-inline')
                ))
            ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'enableClientValidation', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'except', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
        <?php echo CHtml::activeTextField($model, 'except', array('class' => 'form-control', 'placeholder' => Yii::t('EavModule.eavactiverecord', 'A comma separated list of scenarios'))) ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'except', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'on', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
        <?php echo CHtml::activeTextField($model, 'on', array('class' => 'form-control', 'placeholder' => Yii::t('EavModule.eavactiverecord', 'A comma separated list of scenarios'))) ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'on', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'message', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
        <?php echo CHtml::activeTextField($model, 'message', array('class' => 'form-control', 'placeholder' => Yii::t('EavModule.eavactiverecord', 'A validation fault message'))) ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'message', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'tooBig', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
        <?php echo CHtml::activeTextField($model, 'tooBig', array('class' => 'form-control')) ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'tooBig', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'tooSmall', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
        <?php echo CHtml::activeTextField($model, 'tooSmall', array('class' => 'form-control')) ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'tooSmall', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'allowEmpty', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
            <?php echo CHtml::activeRadioButtonList($model, 'allowEmpty',
                array('true' => Yii::t('EavModule.eavactiverecord', 'Yes'), 'false' => Yii::t('EavModule.eavactiverecord', 'No')),
                array('container' => 'div',
                      'separator' => '',
                      'template' => '{beginLabel}{input}{labelTitle}{endLabel}',
                      'labelOptions' => array('class' => 'radio-inline')
                ))
            ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'allowEmpty', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'safe', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
            <?php echo CHtml::activeRadioButtonList($model, 'safe',
                array('true' => Yii::t('EavModule.eavactiverecord', 'Yes'), 'false' => Yii::t('EavModule.eavactiverecord', 'No')),
                array('container' => 'div',
                      'separator' => '',
                      'template' => '{beginLabel}{input}{labelTitle}{endLabel}',
                      'labelOptions' => array('class' => 'radio-inline')
                ))
            ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'safe', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'skipOnError', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
            <?php echo CHtml::activeRadioButtonList($model, 'skipOnError',
                array('true' => Yii::t('EavModule.eavactiverecord', 'Enabled'), 'false' => Yii::t('EavModule.eavactiverecord', 'Disabled')),
                array('container' => 'div',
                      'separator' => '',
                      'template' => '{beginLabel}{input}{labelTitle}{endLabel}',
                      'labelOptions' => array('class' => 'radio-inline')
                ))
            ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'skipOnError', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'integerOnly', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
            <?php echo CHtml::activeRadioButtonList($model, 'integerOnly',
                array('true' => Yii::t('EavModule.eavactiverecord', 'Yes'), 'false' => Yii::t('EavModule.eavactiverecord', 'No')),
                array('container' => 'div',
                      'separator' => '',
                      'template' => '{beginLabel}{input}{labelTitle}{endLabel}',
                      'labelOptions' => array('class' => 'radio-inline')
                ))
            ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'integerOnly', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'max', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
        <?php echo CHtml::activeTextField($model, 'max', array('class' => 'form-control')) ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'max', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'min', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
        <?php echo CHtml::activeTextField($model, 'min', array('class' => 'form-control')) ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'min', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'integerPattern', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
        <?php echo CHtml::activeTextField($model, 'integerPattern', array('class' => 'form-control')) ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'integerPattern', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>

<div class="form-group">
    <?php echo CHtml::activeLabel($model, 'numberPattern', array('class' => 'col-md-3 control-label')) ?>
    <div class="col-md-5">
        <?php echo CHtml::activeTextField($model, 'numberPattern', array('class' => 'form-control')) ?>
        <span class="help-block">
            <?php echo CHtml::error($model, 'numberPattern', array('class' => 'label label-danger')) ?>
        </span>
    </div>
</div>
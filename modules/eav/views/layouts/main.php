<?php
/**
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
?>

<?php Yii::app()->getComponent('clientScript')->registerCssFile($this->getModule()->getAssetsUrl() . '/bootstrap/css/bootstrap.min.css') ?>
<?php Yii::app()->getComponent('clientScript')->registerScriptFile($this->getModule()->getAssetsUrl() . '/bootstrap/js/bootstrap.min.js') ?>
<?php Yii::app()->getComponent('clientScript')->registerCoreScript('jquery') ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<div class="container">
    <?php if (Yii::app()->getComponent('user')->hasFlash('success')) : ?>
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong><?php echo Yii::app()->getComponent('user')->getFlash('success') ?></strong>
        </div>
    <?php endif ?>

    <?php if (Yii::app()->getComponent('user')->hasFlash('error')) : ?>
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong><?php echo Yii::app()->getComponent('user')->getFlash('error') ?></strong>
        </div>
    <?php endif ?>

    <?php echo $content ?>
</div>
</body>
</html>

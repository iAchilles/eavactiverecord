<?php
/**
 * EavModule class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
 
/**
 * EavModule class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class EavModule extends CWebModule
{
    const ITEMS_PER_PAGE = 50;

    const LAYOUT = 'main';

    private $itemsPerPage;

    private $assetsUrl;

    private $filters = array();

    private $accessRules = array();


    protected function init()
    {
        Yii::import('application.modules.eav.models.*');
        $this->layout = is_null($this->layout) ? self::LAYOUT : $this->layout;
        $assetsPath = Yii::getPathOfAlias('application.modules.eav.assets');
        $this->assetsUrl = Yii::app()->getComponent('assetManager')->publish($assetsPath, false, -1, YII_DEBUG);
        $this->itemsPerPage = is_null($this->itemsPerPage) ? self::ITEMS_PER_PAGE : $this->itemsPerPage;
    }


    public function getAssetsUrl()
    {
        return $this->assetsUrl;
    }


    public function setItemsPerPage($size)
    {
        $this->itemsPerPage = $size;
    }


    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }


    public function setFilters($filters)
    {
        $this->filters = $filters;
    }


    public function getFilters($controller = null)
    {
        if (is_null($controller))
        {
            return $this->filters;
        }

        return isset($this->filters[$controller]) ? $this->filters[$controller] : array();
    }


    public function setAccessRules($rules)
    {
        $this->accessRules = $rules;
    }


    public function getAccessRules($controller = null)
    {
        if (is_null($controller))
        {
            return $this->accessRules;
        }

        return isset($this->accessRules[$controller]) ? $this->accessRules[$controller] : array();
    }
} 
<?php
/*
 * EavForm class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
 
/**
 * EavForm class provides dynamically creating form elements for EAV-attributes.
 * It can be used as a widget in a view file:
 * <pre>
 * $this->widget('EavForm', array('model' => $model));
 * </pre>
 * To rendering a form element it looks up a template file, the name of the template file should be the same as the name
 * of the EAV-attribute.
 * There are two variables are available in the context of every template: $attribute and $model.
 * The value of the variable $attribute is a string that contains the name of the EAV-attribute. The variable $model
 * is reference to an instance of EavActiveRecord.
 * The following code may be used in the template file:
 * <pre>
 * echo CHtml::error($model, $attribute);
 * echo CHtml::activeTextField($model, $attribute);
 * </pre>
 *
 * The following code may be used for rendering form elements of a multivalued EAV-attribute:
 * <pre>
 * echo CHtml::error($model, $attribute);
 * echo CHtml::activeLabel($model, $attribute);
 * if ($model->isEavAttributeMultivalued($attribute))
 * {
 *     if (empty($model->$attribute))
 *     {
 *        echo CHtml::activeTextField($model, $attribute . '[]', array('value' => ''));
 *     }
 *     else
 *     {
 *        foreach ($model->$attribute as $value)
 *        {
 *           echo CHtml::activeTextField($model, $attribute . '[]', array('value' => $value));
 *        }
 *     }
 * }
 * </pre>
 *
 *
 * @version 1.0.0
 */
class EavForm extends CWidget
{
    private $model;

    private $viewMap = array();

    private $return = false;

    private $html;

    private $viewPath;

    const VIEW_PATH = 'application.views.eav';


    public function init()
    {
        $this->viewPath = is_null($this->viewPath) ? Yii::getPathOfAlias(self::VIEW_PATH) : $this->viewPath;
    }


    public function run()
    {
        if (is_null($this->model) || !$this->model->getIsEavEnabled())
        {
            return;
        }
        $attributes = $this->model->eavAttributeNames();
        $this->html = '';
        foreach ($attributes as $name)
        {
            if (array_key_exists($name, $this->viewMap) && is_file($this->viewPath . DIRECTORY_SEPARATOR . $this->viewMap[$name] . '.php'))
            {
                $this->html .= $this->render($this->viewMap[$name], array('attribute' => $name, 'model' => $this->model), true);
            }
            else if (is_file($this->viewPath . DIRECTORY_SEPARATOR . $name . '.php'))
            {
                $this->html .= $this->render($name, array('attribute' => $name, 'model' => $this->model), true);
            }
        }
        if (!$this->return)
        {
            echo $this->html;
        }
    }


    /**
     * Returns the path to the directory that contains template files. Default path of the template directory
     * is "protected/views/eav".
     * @param bool $checkTheme
     * @return string Path to the directory that contains template files.
     */
    public function getViewPath($checkTheme = false)
    {
        return $this->viewPath;
    }


    /**
     * Sets the path to the directory that contains template files.
     * @param string $path path to the template directory.
     */
    public function setViewPath($path)
    {
        $this->viewPath = $path;
    }


    /**
     * Sets the model that will be used to creating form elements.
     * @param EavActiveRecord $model
     */
    public function setModel(EavActiveRecord $model)
    {
        $this->model = $model;
    }


    /**
     * Allows to define a custom template file which should be used for the specified attribute.
     * @param array $viewMap An array (attributeName => fileName)
     */
    public function setViewMap($viewMap)
    {
        $this->viewMap = $viewMap;
    }


    /**
     * If the property value is set to true, the rendering result will not be echoed. You can get the rendering result
     * as a string by accessing the property EavForm::$html.
     * @param boolean $return Whether the rendering result should be echoed.
     */
    public function setReturn($return)
    {
        $this->return = $return;
    }


    /**
     * Returns the rendering result as a string.
     * @return string The rendering result.
     */
    public function getHtml()
    {
        return $this->html;
    }

} 
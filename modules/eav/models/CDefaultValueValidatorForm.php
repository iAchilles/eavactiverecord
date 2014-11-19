<?php
/**
 * CDefaultValueValidatorForm class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * CDefaultValueValidatorForm class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class CDefaultValueValidatorForm extends BaseValidatorForm
{
    const ALIAS = 'default';

    public $setOnEmpty = true;

    public $value = '';

    private $attribute;


    public function rules()
    {
        return array_merge(parent::rules(), array(
            array('setOnEmpty', 'boolean', 'trueValue' => 'true', 'falseValue' => 'false'),
            array('value', 'required'),
            array('value', 'filter', 'filter' => array($this, 'convertType'), 'skipOnError' => true)
        ));
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'setOnEmpty' => Yii::t('EavModule.eavactiverecord', 'Set default value only when an attribute is empty'),
            'value' => Yii::t('EavModule.eavactiverecord', 'Default value'),
        ));
    }


    public function setAttribute(EavAttribute $attribute)
    {
        $this->attribute = $attribute;
    }


    public function getHtml()
    {
        $validators = $this->attribute->getEavValidatorList();
        $validators = isset($validators[self::ALIAS]) ? $validators[self::ALIAS] : array();
        $attributes = array_merge($this->getAttributes(), $validators);
        $attributes = array_merge($attributes, $this->prepareInput($attributes));
        $this->setAttributes($attributes);
        return Yii::app()->getController()->renderPartial(self::ALIAS, array('model' => $this), true);
    }


    public function getOutput()
    {
        if (!$this->validate())
        {
            $this->attribute->setValidatorErrors(self::ALIAS);
        }
        return array_merge($this->getAttributes(), $this->prepareOutput());
    }


    public function convertType($value)
    {
        $value = trim($value);

        if (ctype_digit($value))
        {
            return CPropertyValue::ensureInteger($value);
        }

        if (is_numeric($value))
        {
            return CPropertyValue::ensureFloat($value);
        }

        if (strcasecmp($value, 'null') == 0)
        {
            return null;
        }
        else if (strcasecmp($value, 'true') == 0 || strcasecmp($value, 'false') == 0)
        {
            return CPropertyValue::ensureBoolean($value);
        }
        else if (preg_match('/^\(.+\)|\(\)$/', $value))
        {
            return CPropertyValue::ensureArray($value);
        }

        return $value;
    }


    private function prepareInput($attributes)
    {
        $preparedAttributes = array();
        foreach ($attributes as $key => $value)
        {
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'setOnEmpty')))
            {
                $preparedAttributes[$key] = CPropertyValue::ensureString($value);
            }

            if ($key === 'except' || $key === 'on')
            {
                if (is_array($value))
                {
                    $preparedAttributes[$key] = implode(', ', $value);
                }
            }

            if ($key === 'value')
            {
                if ($value === true || $value === false)
                {
                    $preparedAttributes[$key] = CPropertyValue::ensureString($value);
                }
                else if (is_null($value))
                {
                    $preparedAttributes[$key] = 'null';
                }
                else if (is_array($value))
                {
                    $preparedAttributes[$key] = trim(str_replace('array', '', var_export($value, true)));
                }
            }
        }

        return $preparedAttributes;
    }


    private function prepareOutput()
    {
        $preparedAttributes = array();
        foreach ($this->getAttributes() as $key => $value)
        {
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'setOnEmpty')))
            {
                $preparedAttributes[$key] = CPropertyValue::ensureBoolean($value);
            }

            if ($key === 'except' || $key === 'on')
            {
                if (!is_null($value))
                {
                    $preparedAttributes[$key] = array_map('trim', explode(',', $value));
                }
            }
        }

        return $preparedAttributes;
    }
} 
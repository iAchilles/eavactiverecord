<?php
/**
 * CUniqueValidatorForm class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * CUniqueValidatorForm class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class CUniqueValidatorForm extends BaseValidatorForm
{
    const ALIAS = 'unique';

    public $allowEmpty = true;

    public $attributeName;

    public $caseSensitive = true;

    public $className;

    public $criteria = array();

    public $skipOnError = true;


    public function rules()
    {
        return array_merge(parent::rules(), array(
            array('allowEmpty, caseSensitive', 'boolean', 'falseValue' => 'false', 'trueValue' => 'true'),
            array('attributeName, className', 'required'),
            array('criteria', 'default', 'setOnEmpty' => true, 'value' => array()),
        ));
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'allowEmpty' => Yii::t('EavModule.eavactiverecord', 'Allow empty value'),
            'attributeName' => Yii::t('EavModule.eavactiverecord', 'The attribute name'),
            'caseSensitive' => Yii::t('EavModule.eavactiverecord', 'Case-sensitive'),
            'className' => Yii::t('EavModule.eavactiverecord', 'The class name'),
            'criteria' => Yii::t('EavModule.eavactiverecord', 'The additional query criteria'),
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


    private function prepareInput($attributes)
    {
        $preparedAttributes = array();
        foreach ($attributes as $key => $value)
        {
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'allowEmpty', 'caseSensitive')))
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

            if ($key === 'criteria')
            {
                $criteria = var_export($value, true);
                $criteria = substr_replace($criteria, '', 0, 5);
                $preparedAttributes[$key] = trim($criteria);
            }
        }

        return $preparedAttributes;
    }


    private function prepareOutput()
    {
        $preparedAttributes = array();
        foreach ($this->getAttributes() as $key => $value)
        {
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'allowEmpty', 'caseSensitive')))
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

            if ($key === 'criteria')
            {
                if (is_string($value))
                {
                    $value = trim($value);
                }
                $preparedAttributes[$key] = CPropertyValue::ensureArray($value);
            }
        }

        return $preparedAttributes;
    }
} 
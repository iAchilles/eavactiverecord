<?php
/**
 * CStringValidatorForm class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * CStringValidatorForm class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class CStringValidatorForm extends BaseValidatorForm
{
    const ALIAS = 'length';

    public $allowEmpty = true;

    public $encoding;

    public $is;

    public $max;

    public $min;

    public $tooLong;

    public $tooShort;

    private $attribute;


    public function rules()
    {
        return array_merge(parent::rules(), array(
            array('is, max, min', 'numerical'),
            array('tooLong, tooShort, encoding, is, max, min', 'default', 'setOnEmpty' => true, 'value' => null),
            array('allowEmpty', 'boolean', 'trueValue' => 'true', 'falseValue' => 'false')
        ));
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'allowEmpty' => Yii::t('EavModule.eavactiverecord', 'Allow empty value'),
            'encoding' => Yii::t('EavModule.eavactiverecord', 'Encoding'),
            'is' => Yii::t('EavModule.eavactiverecord', 'The exact length'),
            'max' => Yii::t('EavModule.eavactiverecord', 'The maximum length'),
            'min' => Yii::t('EavModule.eavactiverecord', 'The minimum length'),
            'tooShort' => Yii::t('EavModule.eavactiverecord', 'The error message used when the value is too short'),
            'tooLong' => Yii::t('EavModule.eavactiverecord', 'The error message used when the value is too long')
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'allowEmpty')))
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
        }

        return $preparedAttributes;
    }


    private function prepareOutput()
    {
        $preparedAttributes = array();
        foreach ($this->getAttributes() as $key => $value)
        {
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'allowEmpty')))
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

            if($key === 'is' || $key === 'max' || $key === 'min')
            {
                if (!is_null($value))
                {
                    $preparedAttributes[$key] = CPropertyValue::ensureInteger($value);
                }
                else
                {
                    $preparedAttributes[$key] = null;
                }
            }
        }

        return $preparedAttributes;
    }
} 
<?php
/**
 * CountValidatorForm class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * CountValidatorForm class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class CountValidatorForm extends BaseValidatorForm
{
    const ALIAS = 'CountValidator';

    public $min;

    public $max;

    public $safe = false;

    public $tooLittle;

    public $tooMany;

    public $enableClientValidation = false;

    private $attribute;


    public function rules()
    {
        return array_merge(parent::rules(), array(
            array('max, min', 'numerical', 'integerOnly' => true),
            array('max, min, tooMany, tooLittle', 'default', 'setOnEmpty' => true, 'value' => null),
        ));
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'max' => Yii::t('EavModule.eavactiverecord', 'Maximum number of values'),
            'min' => Yii::t('EavModule.eavactiverecord', 'Minimum number of values'),
            'tooMany' => Yii::t('EavModule.eavactiverecord', 'The error message used when the attribute contains more values than allowed'),
            'tooLittle' => Yii::t('EavModule.eavactiverecord', 'The error message used when the attribute contains not enough number of values'),
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
        return Yii::app()->getController()->renderPartial('count', array('model' => $this), true);
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError')))
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError')))
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

            if($key === 'max' || $key === 'min')
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
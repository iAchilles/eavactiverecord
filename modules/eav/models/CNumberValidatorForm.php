<?php
/**
 * CNumberValidatorForm class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * CNumberValidatorForm class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class CNumberValidatorForm extends BaseValidatorForm
{
    const ALIAS = 'numerical';

    public $allowEmpty = true;

    public $integerOnly = false;

    public $integerPattern = '/^\s*[+-]?\d+\s*$/';

    public $max;

    public $min;

    public $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';

    public $tooBig;

    public $tooSmall;

    private $attribute;


    public function rules()
    {
        return array_merge(parent::rules(), array(
            array('allowEmpty, integerOnly', 'boolean', 'trueValue' => 'true', 'falseValue' => 'false'),
            array('max, min', 'numerical'),
            array('max, min, tooBig, tooSmall', 'default', 'setOnEmpty' => true, 'value' => null),
            array('integerPattern, numberPattern', 'required'),
        ));
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'allowEmpty' => Yii::t('EavModule.eavactiverecord', 'Allow empty value'),
            'integerOnly' => Yii::t('EavModule.eavactiverecord', 'The attribute value can only be an integer'),
            'integerPattern' => Yii::t('EavModule.eavactiverecord', 'The regular expression for matching integers'),
            'max' => Yii::t('EavModule.eavactiverecord', 'The upper limit of the number'),
            'min' => Yii::t('EavModule.eavactiverecord', 'The lower limit of the number'),
            'numberPattern' => Yii::t('EavModule.eavactiverecord', 'The regular expression for matching numbers'),
            'tooBig' => Yii::t('EavModule.eavactiverecord', 'The error message used when the value is too big'),
            'tooSmall' => Yii::t('EavModule.eavactiverecord', 'The error message used when the value is too small'),
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'allowEmpty', 'integerOnly')))
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'allowEmpty', 'integerOnly')))
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
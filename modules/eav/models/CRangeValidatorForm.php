<?php
/**
 * CRangeValidatorForm class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * CRangeValidatorForm class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class CRangeValidatorForm extends BaseValidatorForm
{
    const ALIAS = 'in';

    public $allowEmpty = true;

    public $not = false;

    public $range;

    public $strict = false;

    private $attribute;


    public function rules()
    {
        return array_merge(parent::rules(), array(
            array('allowEmpty, not, strict', 'boolean', 'trueValue' => 'true', 'falseValue' => 'false'),
            array('range', 'required')
        ));
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'allowEmpty' => Yii::t('EavModule.eavactiverecord', 'Allow empty value'),
            'not' => Yii::t('EavModule.eavactiverecord', 'Inversion of the validation logic'),
            'strict' => Yii::t('EavModule.eavactiverecord', 'Strict comparison'),
            'range' => Yii::t('EavModule.eavactiverecord', 'The list of valid values'),
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'strict', 'not', 'allowEmpty')))
            {
                $preparedAttributes[$key] = CPropertyValue::ensureString($value);
            }

            if ($key === 'except' || $key === 'on' || $key === 'range')
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'strict', 'not', 'allowEmpty')))
            {
                $preparedAttributes[$key] = CPropertyValue::ensureBoolean($value);
            }

            if ($key === 'except' || $key === 'on' || $key === 'range')
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
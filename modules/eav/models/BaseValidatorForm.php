<?php
/**
 * BaseValidatorForm class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * BaseValidatorForm class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class BaseValidatorForm  extends CFormModel
{
    private $attribute;

    private $validatorInputs;

    private $validators;

    public $enableClientValidation = true;

    public $except;

    public $message;

    public $on;

    public $safe = true;

    public $skipOnError = false;


    public function rules()
    {
        return array(
            array('enableClientValidation, safe, skipOnError', 'boolean', 'trueValue' => 'true',
                  'falseValue' => 'false'),
            array('message, on, except', 'default', 'setOnEmpty' => true, 'value' => null),
        );
    }


    public function attributeLabels()
    {
        return array(
            'enableClientValidation' => Yii::t('EavModule.eavactiverecord', 'Client-side validation'),
            'except' => Yii::t('EavModule.eavactiverecord', 'Except scenarios'),
            'message' => Yii::t('EavModule.eavactiverecord', 'Message'),
            'on' => Yii::t('EavModule.eavactiverecord', 'Include scenarios'),
            'safe' => Yii::t('EavModule.eavactiverecord', 'Safe attribute'),
            'skipOnError' => Yii::t('EavModule.eavactiverecord', 'Skip on an error'),
        );
    }


    public function setAttribute(EavAttribute $attribute)
    {
        $this->attribute = $attribute;
        foreach ($this->attribute->getValidatorLabels() as $key => $value)
        {
            $this->validators[$key] = $this->createValidatorForm($key);
            $this->validators[$key]->setAttribute($attribute);
        }
    }


    public function getAttribute()
    {
        return $this->attribute;
    }


    public function setValidatorInputs($input)
    {
        $this->validatorInputs = $input;
        foreach ($this->validators as $validator)
        {
            if (isset($this->validatorInputs[get_class($validator)]))
            {
                $validator->setAttributes($this->validatorInputs[get_class($validator)]);
            }
        }
    }


    public function getValidatorInputs()
    {
        return $this->validatorInputs;
    }


    public function getHtml()
    {
        $html = array();
        foreach ($this->validators as $name => $validator)
        {
            $html[$name] = $validator->getHtml();
        }

        return $html;
    }


    public function getRules()
    {
        $rules = array();
        foreach ($this->validators as $name => $validator)
        {
            if (isset($this->validatorInputs[get_class($validator)]))
            {
                $rules[$name] = $validator->getOutput();
            }
        }

        return $rules;
    }


    private function createValidatorForm($validator)
    {
        $validators = CValidator::$builtInValidators;
        if (isset($validators[$validator]))
        {
            $class = $validators[$validator] . 'Form';
            return new $class;
        }
        $class = $validator . 'Form';
        return new $class;
    }
} 
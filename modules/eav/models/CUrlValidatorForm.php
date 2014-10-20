<?php
/**
 * CUrlValidatorForm class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * CUrlValidatorForm class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class CUrlValidatorForm extends BaseValidatorForm
{
    const ALIAS = 'url';

    public $allowEmpty = true;

    public $defaultScheme;

    public $pattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';

    public $validSchemes = 'http, https';

    public $validateIDN = false;

    private $attribute;


    public function rules()
    {
        return array_merge(parent::rules(), array(
            array('allowEmpty, validateIDN', 'boolean', 'trueValue' => 'true', 'falseValue' => 'false'),
            array('defaultScheme', 'default', 'setOnEmpty' => true, 'value' => null),
            array('pattern, validSchemes', 'required')
        ));
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'allowEmpty' => Yii::t('EavModule.eavactiverecord', 'Allow empty value'),
            'defaultScheme' => Yii::t('EavModule.eavactiverecord', 'Default URI scheme'),
            'pattern' => Yii::t('EavModule.eavactiverecord', 'Pattern'),
            'validSchemes' => Yii::t('EavModule.eavactiverecord', 'Valid URI schemes'),
            'validateIDN' => Yii::t('EavModule.eavactiverecord', 'Internationalized domain names')
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'allowEmpty', 'validateIDN')))
            {
                $preparedAttributes[$key] = CPropertyValue::ensureString($value);
            }

            if ($key === 'except' || $key === 'on' || $key === 'validSchemes')
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'allowEmpty', 'validateIDN')))
            {
                $preparedAttributes[$key] = CPropertyValue::ensureBoolean($value);
            }

            if ($key === 'except' || $key === 'on' || $key === 'validSchemes')
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
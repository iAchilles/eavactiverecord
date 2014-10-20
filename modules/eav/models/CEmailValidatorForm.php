<?php
/**
 * CEmailValidatorForm class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * CEmailValidatorForm class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class CEmailValidatorForm extends BaseValidatorForm
{
    const ALIAS = 'email';

    public $allowEmpty = true;

    public $allowName = false;

    public $checkMX = false;

    public $checkPort = false;

    public $fullPattern = '/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';

    public $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';

    public $validateIDN = false;

    private $attribute;


    public function rules()
    {
        return array_merge(parent::rules(), array(
            array('allowEmpty, allowName, checkMX, checkPort, validateIDN', 'boolean', 'trueValue' => 'true',
            'falseValue' => 'false'),
            array('fullPattern, pattern', 'required')
        ));
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'allowEmpty' => Yii::t('EavModule.eavactiverecord', 'Allow empty value'),
            'allowName' => Yii::t('EavModule.eavactiverecord', "Include the sender's name in the email"),
            'checkMX' => Yii::t('EavModule.eavactiverecord', 'Check MX records'),
            'checkPort' => Yii::t('EavModule.eavactiverecord', 'Check SMTP port'),
            'fullPattern' => Yii::t('EavModule.eavactiverecord', 'The regular expression used to validate email addresses with the name part'),
            'pattern' => Yii::t('EavModule.eavactiverecord', 'Pattern'),
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'allowEmpty', 'allowName',
                                     'checkMX', 'checkPort', 'validateIDN')))
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
            if (in_array($key, array('enableClientValidation', 'safe', 'skipOnError', 'allowEmpty', 'allowName',
                                     'checkMX', 'checkPort', 'validateIDN')))
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
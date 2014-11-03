<?php
/**
 * EavAttributeExtended class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * EavAttributeExtended class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class EavAttributeExtended extends EavAttribute
{
    private $values;

    private $validatorErrors = array();


    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function rules()
    {
        return array_merge(parent::rules(), array(
           array('values', 'safe'),
           array('name', 'unique', 'className' => 'EavAttribute', 'attributeName' => 'name'),
        ));
    }


    public function attributeLabels()
    {
        return array(
            'name' => Yii::t('EavModule.eavactiverecord', 'Name'),
            'label' => Yii::t('EavModule.eavactiverecord', 'Label'),
            'type' => Yii::t('EavModule.eavactiverecord', 'Type'),
            'data_type' => Yii::t('EavModule.eavactiverecord', 'Data type'),
            'values' => Yii::t('EavModule.eavactiverecord', 'Possible values'),
        );
    }


    public function setValues($values)
    {
        $this->values = $values;
        if ($values === '')
        {
            return;
        }

        $values = preg_split('/[\s,]+/', $values, -1, PREG_SPLIT_NO_EMPTY);
        $key = array();
        $value = array();

        for ($i = 0; $i < count($values); $i++)
        {
            if (($i + 1) % 2 === 0)
            {
                $val = str_replace('+', ' ', $values[$i]);
                $value[] = $val;
            }
            else
            {
                $key[] = $values[$i];
            }
        }
        $this->setPossibleValues(array_combine($key, $value));
    }


    public function getValues()
    {
        if (!$this->getIsNewRecord())
        {
            if (is_null($this->values))
            {
                if (empty($this->possibleValues))
                {
                    $this->values = '';
                    return $this->values;
                }

                $values = '';
                foreach ($this->possibleValues as $key => $value)
                {
                    $value = str_replace(' ', '+', $value);
                    $values .= $key . ' ' . $value . "\n";
                }
                $this->values = $values;

                return $this->values;
            }

            return $this->values;
        }

        return $this->values;
    }


    public function setValidatorErrors($validator)
    {
        $this->validatorErrors[] = $validator;
    }


    public function getValidatorErrors()
    {
        return $this->validatorErrors;
    }


    public function getValidatorLabels()
    {
        return array(
            'required' => Yii::t('EavModule.eavactiverecord', 'Required value validator'),
            'filter' => Yii::t('EavModule.eavactiverecord', 'Filter'),
            'match' => Yii::t('EavModule.eavactiverecord', 'Regular expression validator'),
            'email' => Yii::t('EavModule.eavactiverecord', 'Email validator'),
            'url' => Yii::t('EavModule.eavactiverecord', 'URL validator'),
            'unique' => Yii::t('EavModule.eavactiverecord', 'Unique value validator'),
            'compare' => Yii::t('EavModule.eavactiverecord', 'Compare validator'),
            'length' => Yii::t('EavModule.eavactiverecord', 'String validator'),
            'in' => Yii::t('EavModule.eavactiverecord', 'Range validator'),
            'numerical' => Yii::t('EavModule.eavactiverecord', 'Number validator'),
            'default' => Yii::t('EavModule.eavactiverecord', 'Default value validator'),
            'boolean' => Yii::t('EavModule.eavactiverecord', 'Boolean validator'),
            'date' => Yii::t('EavModule.eavactiverecord', 'Date validator'),
            'safe' => Yii::t('EavModule.eavactiverecord', 'Safe value'),
            'unsafe' => Yii::t('EavModule.eavactiverecord', 'Unsafe value'),
            'CountValidator' => Yii::t('EavModule.eavactiverecord', 'Count validator')
        );
    }


    public function getDataTypeLabels()
    {
        return array(
            self::DATA_TYPE_DATETIME => Yii::t('EavModule.eavactiverecord', 'Date'),
            self::DATA_TYPE_INT => Yii::t('EavModule.eavactiverecord', 'Integer'),
            self::DATA_TYPE_TEXT => Yii::t('EavModule.eavactiverecord', 'Text'),
            self::DATA_TYPE_VARCHAR => Yii::t('EavModule.eavactiverecord', 'String'),
            self::DATA_TYPE_NUMERIC => Yii::t('EavModule.eavactiverecord', 'Numeric'),
            self::DATA_TYPE_MONEY => Yii::t('EavModule.eavactiverecord', 'Money'),
        );
    }


    public function getTypeLabels()
    {
        return array(
            self::TYPE_SINGLE => Yii::t('EavModule.eavactiverecord', 'Single-valued'),
            self::TYPE_MULTIPLE => Yii::t('EavModule.eavactiverecord', 'Multiple-valued')
        );
    }


    protected function afterSave()
    {
        $attribute = new EavAttribute();
        $attribute->id = $this->id;
        $attribute->name = $this->name;
        $attribute->label = $this->label;
        $attribute->type = $this->type;
        $attribute->data_type = $this->data_type;
        $attribute->data = $this->data;
        $attribute->setIsNewRecord(false);
        $this->setCacheEavAttribute($attribute);
    }
} 
<?php
/**
 * EavAttribute class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * EavAttribute class represents methods to manipulate EAV attributes (creating a new attribute, updating an existing
 * attribute, removing an attribute).
 *
 * There are two types of EAV attributes: a multivalued attribute and single valued attribute. The multivalued
 * attribute can have more than one value at a time for an attribute. The single valued attribute can hold only single
 * value at a time.
 * <pre>
 * $attribute1 = new EavAttribute();
 * $attribute1->type = EavAttribute::TYPE_SINGLE; //Defines the attribute type. This attribute can hold only single value.
 *
 * $attribute2 = new EavAttribute();
 * $attribute2->type = EavAttribute::TYPE_MULTIPLE; //This attribute can hold multiple values.
 * </pre>
 *
 * There are four data types of EAV-attribute values (surely, you can create own data types): 'IntDataType', 'VarcharDataType',
 * 'DatetimeDataType' and 'TextDataType'. The name of the data type must be equal to a class name that is derived from
 * the class EavValue. The value of the EAV attribute is stored as a record in a table that is based on the attribute
 * data type. It uses separate tables for each data type.
 * If the value of the attribute must be stored in an integer, you must use the constant EavAttribute::DATA_TYPE_INT
 * to assign a value to the property EavAttribute::$data_type:
 * <pre>
 * $attribute = new EavAttribute();
 * $attribute->data_type = EavAttribute::DATA_TYPE_INT; //Values of this attribute will be stored in an integer.
 * </pre>
 * To specify a data type of an attribute you can use constants EavAttribute::DATA_TYPE_INT ('IntDataType'),
 * EavAttribute::DATA_TYPE_DATETIME ('DatetimeDataType'), EavAttribute::DATA_TYPE_TEXT ('TextDataType')
 * and EavAttribute::DATA_TYPE_VARCHAR ('VarcharDataType').
 *
 * The name of the EAV-attribute must be unique and follow PHP variable naming convention
 * (http://php.net/manual/en/language.variables.basics.php). The following name of the attribute is invalid:
 * <pre>
 * $attribute = new EavAttribute();
 * $attribute->name = 2; //Invalid name
 * $attribute->name = '3abc'; //Invalid name
 * </pre>
 *
 * The following name of the attribute is correct:
 * <pre>
 * $attribute = new EavAttribute();
 * $attribute->name = 'abc3'; //Correct name
 * $attribute->name = '_a2c'; //Correct name
 * </pre>
 *
 * When you create a new EAV-attribute you also can determine validation rules by calling the method EavAttribute::setRules().
 * The following code fragment shows how to add validation rules to an attribute:
 * <pre>
 * $rules = array('length' => array('min' => 3, 'max' => 25), 'required' => array('on' => 'register'));
 * $attribute = new EavAttribute();
 * $attribute->setRules($rules);
 * </pre>
 * Note, if an attribute does not contain validation rules so that it cannot be massively assigned.
 *
 * The following is a complete code of creating a new attribute:
 * <pre>
 * $attribute = new EavAttribute();
 * $attribute->name = 'age';
 * $attribute->label = 'Your age';
 * $attribute->type = EavAttribute::TYPE_SINGLE;
 * $attribute->data_type = EavAttribute::DATA_TYPE_INT;
 * $attribute->setRules(array('numeric' => array('min' => 18, 'max' => 100, 'integerOnly' => true), 'required'));
 * $attribute->save();
 * </pre>
 *
 * 
 * @property integer $id Primary key.
 * @property integer $type The attribute type. If the attribute may hold multiple values it must be set to 1. If the attribute
 * may only hold a single value it must be set to 0. You can use constants EavAttribute::TYPE_SINGLE and
 * EavAttribute::TYPE_MULTIPLE to assign a value to this property.
 * @property string $data_type The attribute value data type. It must contain a name of a class that is derived from
 * the class EavValue. You can use constants EavAttribute::DATA_TYPE_INT, EavAttribute::DATA_TYPE_DATETIME,
 * EavAttribute::DATA_TYPE_TEXT and EavAttribute::DATA_TYPE_VARCHAR to assign a value to this property.
 * @property string $name The attribute name. Must be unique and follow PHP variable naming convention.
 * @property string $label The attribute label.
 * @property string $data  Serialized data is stored and recovered using PHP's serialize() and unserialize() functions.
 * DO NOT set the value of this property directly.
 *
 * @since 1.0.0
 */
class EavAttribute extends CActiveRecord implements Serializable
{

    const DATA_TYPE_INT = 'IntDataType';

    const DATA_TYPE_VARCHAR = 'VarcharDataType';

    const DATA_TYPE_DATETIME = 'DatetimeDataType';

    const DATA_TYPE_TEXT = 'TextDataType';

    const TYPE_SINGLE = 0;
    
    const TYPE_MULTIPLE = 1;
    
    const CACHE_PREFIX = 'eav';
    
    const CACHE_ID = 'eavCache';
    
    public $unserializedObject;

    private $unserializedData;

    private $oldDataType;
    
    private static $cache;

    
    public function serialize()
    {
        if ($this->getIsNewRecord())
        {
            return null;
        }
        return serialize($this->getAttributes());
    }
    
    
    public function unserialize($serialized)
    {
        $attributes = unserialize($serialized);
        $this->unserializedObject = self::model()->populateRecord($attributes);
    }
    
    
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    
    public function tableName()
    {
        return '{{eav_attribute}}';
    }
    
    
    public function init()
    {
        if ($this->getIsNewRecord())
        {
            $this->setUnserializedData(array(
                'rules' => array(),
            ));
        }
    }


    public function rules()
    {
        return array(
            array('name, type, data_type', 'required'),
            array('name', 'match', 'pattern' => '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/'),
            array('data_type', 'eavAttributeDataTypeValidator'),
            array('type', 'in', 'range' => array(self::TYPE_MULTIPLE, self::TYPE_SINGLE)),
            array('label', 'safe'),
        );
    }


    protected function afterFind()
    {
        $this->oldDataType = $this->data_type;
    }


    protected function beforeSave()
    {
        $this->serializeData();
        return parent::beforeSave();
    }


    protected function afterSave()
    {
        parent::afterSave();
        $this->setIsNewRecord(false);
        $this->setCacheEavAttribute($this);
    }


    /**
     * Sets the list of possible values for the attribute. E.g. it can be used to create a drop-down list.
     * <pre>
     * $values = array(1 => 'One', 2 => 'Two');
     * $attribute->setPossibleValues($values);
     * </pre>
     * @param array $values
     * @throws CException
     */
    public function setPossibleValues($values)
    {
        if (!is_array($values))
        {
            throw new CException('Argument 1 passed to ' .  __METHOD__
                . '() must be an array.');
        }
        else
        {
            $data = $this->getUnserializedData();
            $data['values'] = $values;
            $this->setUnserializedData($data);
        }
    }


    /**
     * Returns the list of possible values for the attribute.
     * @return array The list of possible values.
     */
    public function getPossibleValues()
    {
        $data = $this->getUnserializedData();
        return isset($data['values']) ? $data['values'] : array();
    }


    /**
     * Adds validation rules for the EAV attribute.
     * <pre>
     * $rules = array(
     *     'length' => array('max' => 5, 'min' => 2),
     *     'date' => array('format' => 'yyyy-M-d H:m:s'),
     * );
     * $attribute->setRules($rules);
     * </pre>
     * @param array $rules An array that contains nested arrays that are indexed by the name of a validator.
     * Each nested array contains the definition of a validation rule.
     * @throws CException Passed wrong type of argument.
     */
    public function setRules($rules)
    {
        if (!is_array($rules))
        {
            throw new CException('Argument 1 passed to ' .  __METHOD__ 
                    . '() must be an array.');
        }

        foreach ($rules as $key => $value)
        {
            if (is_int($key))
            {
                unset($rules[$key]);
                $rules[$value] = array();
            }
        }
        
        $data = $this->getUnserializedData();
        $data['rules'] = $rules;
        $this->setUnserializedData($data);
    }
    
    
    /**
     * Returns all the validation rules that were defined for the EAV attribute.
     * @return array All the validation rules that were defined for the EAV attribute. If no validation rules exist, an
     * empty array is returned.
     */
    public function getEavValidatorList()
    {
        $data = $this->getUnserializedData();
        return isset($data['rules']) ? $data['rules'] : array();
    }


    /**
     * Returns the list of instances of the class EavAttribute (indexed by the attribute name).
     * @param array $names Names of EAV attributes whose instances should be returned.
     * @return array The list of instances of the class EavAttribute (indexed by the attribute name).
     * If attributes are not found, an empty array is returned.
     * @throws CException Passed wrong type of argument.
     */
    public function getEavAttributes($names)
    {
        if (!is_array($names))
        {
            throw new CException('Argument 1 passed to ' .  __METHOD__ 
                    . '() must be an array.');
        }
        
        $attributes = array();
        $keys = array();
        
        foreach ($names as $name)
        {
            $attribute = $this->getCacheEavAttribute($name);
            if ($attribute === false)
            {
                $keys[] = "'" . $name . "'";
                continue;
            }
            $attributes[$name] = $attribute;
        }
        
        if (!empty($keys))
        {
            $condition = 'name IN (' . implode(', ', $keys) . ')';
            $keys = self::model()->findAll($condition);
        }
        
        foreach ($keys as $attr)
        {
            $attributes[$attr->name] = $attr;
            $this->setCacheEavAttribute($attr);
        }
        
        return $attributes;
    }


    /**
     * Validates the specified value of the property EavAttribute::$data_type.
     * @param $attribute
     * @param $params
     */
    public function eavAttributeDataTypeValidator($attribute, $params)
    {
        if (!$this->getIsNewRecord())
        {
            if ($this->oldDataType !== $this->data_type)
            {
                $this->addError($attribute, 'The value of the property ' . __CLASS__ . '::$' . $attribute . ' cannot be
                changed if it was saved previously.');
            }
        }
        else
        {
            if (!@class_exists($this->data_type))
            {
                $this->addError($attribute, 'The class ' . $this->data_type . ' not found.');
            }
            else
            {
                if (!(EavValue::model($this->data_type) instanceof EavValue))
                {
                    $this->addError($attribute, 'The class ' . $this->data_type . '.php must be '
                        . 'a subclass of the class EavValue.');
                }
            }
        }
    }


    /**
     * Returns the value of the property EavAttribute::$unserializeData.
     * @return array Unserialized data.
     */
    protected function getUnserializedData()
    {
        if (is_null($this->unserializedData))
        {
            $this->setUnserializedData(unserialize($this->data));
        }
        
        return $this->unserializedData;
    }
    
    
    /**
     * Sets the value of the property EavAttribute::$unserializeData.
     * @param array $data Unserialized data.
     */
    protected function setUnserializedData($data)
    {
        $this->unserializedData = $data;
    }
    
    
    /**
     * Generates a storable representation of the property EavAttribute::$unserializeData.
     */
    protected function serializeData()
    {
        $data = $this->getUnserializedData();
        $this->data = serialize($data);
    }
    
    
    /**
     * Saves the given EAV-attribute to the cache store.
     * @param EavAttribute $attribute
     * @return boolean true if the given EAV-attribute is successfully stored into cache,
     * false otherwise.
     */
    protected function setCacheEavAttribute(EavAttribute $attribute)
    {
        $duration = $this->getDbConnection()->schemaCachingDuration;
        if ($duration > 0)
        {
            $key = $this->createCacheKey($attribute->name);
            $cache = $this->getCache();
            return $cache->set($key, serialize($attribute), $duration);
        }
        return false;
    }
    
    
    /**
     * Retrieves a cached instance of the class EavAttribute.
     * @param string $name The name of the attribute whose instance must be fetched from the cache.
     * @return mixed An instance of the class EavAttribute, false otherwise.
     */
    protected function getCacheEavAttribute($name)
    {
        $duration = $this->getDbConnection()->schemaCachingDuration;
        if ($duration > 0)
        {
            $cache = $this->getCache();
            $key = $this->createCacheKey($name);
            $cached = $cache->get($key);
            
            if ($cached === false)
            {
                return false;
            }
            
            $instance = unserialize($cache->get($key));
            return $instance->unserializedObject;
        }
        return false;
    }
    
    
    /**
     * Deletes an instance of the class EavAttribute with the given name from the cache.
     * @param string $name The name of the attribute whose instance must be removed from the cache.
     * @return boolean true if no error happens during deletion.
     */
    protected function deleteCacheEavAttribute($name)
    {
        $cache = $this->getCache();
        $key = $this->createCacheKey($name);
        return $cache->delete($key);
    }
    
    
    /**
     * Returns the cache component.
     * @return ICache An instance of a class that implements the interface ICache.
     * @throws CException If the cache component is not initialized.
     */
    protected function getCache()
    {
        if (!is_null(self::$cache))
        {
            return self::$cache;
        }
        
        if (isset(Yii::app()->{self::CACHE_ID}) 
                  && Yii::app()->{self::CACHE_ID} instanceof ICache)
        {
             self::$cache = Yii::app()->{self::CACHE_ID};
             return self::$cache;
        }

        $id = $this->getDbConnection()->schemaCacheID;
        if (isset(Yii::app()->$id) && Yii::app()->$id instanceof ICache)
        {
            self::$cache = Yii::app()->$id;
            return self::$cache;
        }
        
        throw new CException('The cache component is not initialized and cannot be read.');
    }
    
    
    /**
     * Generates a cache key name for the specified attribute name.
     * @param string $name Attribute name.
     * @return string A cache key name.
     */
    protected function createCacheKey($name)
    {
        return self::CACHE_PREFIX . '_' . $name;
    }
    
}

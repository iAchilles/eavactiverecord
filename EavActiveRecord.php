<?php
/**
 * EavActiveRecord class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * EavActiveRecord is the base class for all classes supporting entity–attribute–value data model.
 * It provides a simple way to work with EAV-attributes. EAV-attributes are stored in the database as separate records
 * but accessed and searched in such a way as if they were columns in the entity's table.
 *
 *
 *
 * @property integer $eav_set_id Foreign key whose value match a primary key in the table eav_set.
 *
 * @since 1.0.0
 */
class EavActiveRecord extends CActiveRecord
{
    const EAV_FIND_RELATION_NAME = 'eav';

    const EAV_SET_RELATION_NAME = 'eavSet';
    
    const EAV_VALUE_RELATION_NAME = 'eavValue';
    
    const EAV_ATTRIBUTE_RELATION_NAME = 'eavAttribute';
    
    private $eavEnable = false;
    
    private $eagerFlag = false;

    private $eavValidators;

    private $findEavAttributes;

    private $oldEavAttributeNames;

    private $oldEavSetPrimaryKey;

    private $storedEavAttributeInstances;
    
    private $eavAttributeInstances = array();
    
    private $newEavAttributes = array();
    
    private $oldEavAttributes = array();


    
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function __set($name, $value)
    {
        try
        {
            parent::__set($name, $value);
            if ($name === 'eav_set_id')
            {
                $this->refreshEavAttributes();
            }
        }
        catch (CException $ex)
        {
            if ($this->setEavAttribute($name, $value) === false)
            {
                throw $ex;
            }
        }
    }


    public function __get($name)
    {
        if (!$this->eavEnable)
        {
            return parent::__get($name);
        }

        try
        {
            return parent::__get($name);
        }
        catch (CException $ex)
        {
            if ($this->hasEavAttribute($name))
            {
                return $this->getEavAttribute($name);
            }
            else
            {
                throw $ex;
            }
        }
    }


    public function __isset($name)
    {
        if (!$this->eavEnable)
        {
            return parent::__isset($name);
        }

        if ($this->hasEavAttribute($name))
        {
            if (isset($this->newEavAttributes[$name]))
            {
                return true;
            }
            else if (array_key_exists($name, $this->oldEavAttributes))
            {
                return false;
            }

            if ($this->getIsNewRecord())
            {
                return false;
            }

            return is_null($this->getEavValue($name)) ? false : true;
        }
        else
        {
            return parent::__isset($name);
        }

    }


    public function __unset($name)
    {
        if (!$this->eavEnable)
        {
            parent::__unset($name);
        }
        else
        {
            if ($this->hasEavAttribute($name))
            {
                $this->setEavAttribute($name, null);
            }
            else
            {
                parent::__unset($name);
                if ($name === 'eav_set_id')
                {
                    $this->refreshEavAttributes();
                }
            }
        }
    }


    public function count($condition = '', $params = array())
    {
        $result = parent::count($this->prepareCondition($condition), $params);
        $this->removeFindEavRelation();
        return $result;
    }


    public function exists($condition = '', $params = array())
    {
        $result = parent::exists($this->prepareCondition($condition), $params);
        $this->removeFindEavRelation();
        return $result;
    }


    public function find($condition = '', $params = array())
    {
        return $this->prepareRecord(parent::find($this->prepareCondition($condition), $params));
    }


    public function findAll($condition = '', $params = array())
    {
        return $this->prepareRecords(parent::findAll($this->prepareCondition($condition), $params));
    }


    public function findAllByAttributes($attributes, $condition = '', $params = array())
    {
        return $this->prepareRecords(parent::findAllByAttributes($attributes, $this->prepareCondition($condition), $params));
    }


    public function findAllByPk($pk, $condition = '', $params = array())
    {
        return $this->prepareRecords(parent::findAllByPk($pk, $this->prepareCondition($condition), $params));
    }


    public function findAllBySql($sql, $params = array())
    {
        return $this->prepareRecords(parent::findAllBySql($sql, $params));
    }


    public function findByAttributes($attributes, $condition = '', $params = array())
    {
        return $this->prepareRecord(parent::findByAttributes($attributes, $this->prepareCondition($condition), $params));
    }


    public function findByPk($pk, $condition = '', $params = array())
    {
        return $this->prepareRecord(parent::findByPk($pk, $this->prepareCondition($condition), $params));
    }


    public function findBySql($sql, $params = array())
    {
        return $this->prepareRecord(parent::findBySql($sql, $params));
    }


    public function populateRecord($attributes, $callAfterFind = true)
    {
        return parent::populateRecord($attributes, false);
    }


    public function validate($attributes = null, $clearErrors = true)
    {
        if (!$this->eavEnable)
        {
            return parent::validate($attributes, $clearErrors);
        }

        if ($clearErrors)
        {
            $this->clearErrors();
        }

        $separatedAttributes = $this->separateAttributes($attributes);
        $modelAttributes = isset($separatedAttributes['attributes']) ? $separatedAttributes['attributes'] : null;
        $eavAttributes = isset($separatedAttributes['eavAttributes']) ? $separatedAttributes['eavAttributes'] : null;

        if($this->beforeValidate())
        {
            foreach ($this->getValidators() as $validator)
            {
                $validator->validate($this, $modelAttributes);
            }

            $this->validateEavAttribute($eavAttributes);
            $this->afterValidate();
            return !$this->hasErrors();
        }
        else
        {
            return false;
        }
    }


    public function setAttribute($name, $value)
    {
        if (parent::setAttribute($name, $value))
        {
            if ($name === 'eav_set_id')
            {
                $this->refreshEavAttributes();
            }
            return true;
        }
        return false;
    }


    public function getSafeAttributeNames()
    {
        if (!$this->eavEnable)
        {
            return parent::getSafeAttributeNames();
        }
        return array_merge($this->getSafeEavAttributeNames(), parent::getSafeAttributeNames());
    }


    public function isAttributeRequired($attribute)
    {
        if (!$this->eavEnable)
        {
            return parent::isAttributeRequired($attribute);
        }

        if ($this->hasEavAttribute($attribute))
        {
            return $this->isEavAttributeRequired($attribute);
        }

        return parent::isAttributeRequired($attribute);
    }


    public function getAttributeLabel($attribute)
    {
        if (!$this->eavEnable)
        {
            return parent::getAttributeLabel($attribute);
        }

        if ($this->hasEavAttribute($attribute))
        {
            if (!is_null($this->eavAttributeInstances[$attribute]->label) && $this->eavAttributeInstances[$attribute]->label !== '')
            {
                return $this->eavAttributeInstances[$attribute]->label;
            }
        }
        return parent::getAttributeLabel($attribute);
    }


    /**
     * This method does the same thing as the method CActiveRecord::save(), except that it also saves EAV attribute
     * values.
     * In cases when tracking of EAV attribute changes is not required, you can use the method CActiveRecord::save().
     * Note, if you assign a new value to the primary key of the model you must use this method, because it takes care
     * of referential integrity.
     *
     * @param bool $runValidation whether to perform validation before saving the record. If the validation fails,
     * the record will not be saved to database.
     * @param array $attributes List of attributes that need to be saved, you can also specify EAV attribute names.
     * Defaults to null, meaning all attributes that are loaded from DB and all related EAV attributes will be saved.
     * Note, IF THE LIST OF ATTRIBUTES DOES NOT CONTAIN "eav_set_id", EAV-attributes WILL NOT be saved.
     * @return boolean Whether the saving succeeds.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function saveWithEavAttributes($runValidation = true, $attributes = null)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            if(!$runValidation || $this->validate($attributes))
            {
                return $this->getIsNewRecord() ? $this->insertWithEavAttributes($attributes)
                    : $this->updateWithEavAttributes($attributes);
            }
            return false;
        }
    }


    /**
     * This method does the same thing as the method CActiveRecord::insert(), except that it also inserts row(s) into
     * table(s) that stores EAV attribute values.
     * If you insert a new record that does not contain EAV attributes, you can use the method CActiveRecord::insert().
     *
     * @param null|array $attributes List of attributes that need to be saved, you can also specify EAV attribute names.
     * Defaults to null, meaning all attributes that are loaded from DB and all related EAV attributes will be saved.
     * Note, IF LIST OF ATTRIBUTES DOES NOT CONTAIN "eav_set_id", EAV-attributes WILL NOT be saved.
     * @return boolean Whether the attributes are valid and the record is inserted successfully.
     * @throws CDbException If the active record is not new.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function insertWithEavAttributes($attributes = null)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {

            if (!$this->getIsNewRecord())
            {
                throw new CDbException(Yii::t('yii', 'The active record cannot be inserted to database because it is not new.'));
            }
            else
            {
                if ($this->beforeSave())
                {
                    $separatedAttributes = $this->separateAttributes($attributes);
                    $modelAttributes = isset($separatedAttributes['attributes']) ? $separatedAttributes['attributes'] : null;
                    $eavAttributes = isset($separatedAttributes['eavAttributes']) ? $separatedAttributes['eavAttributes'] : null;

                    $builder = $this->getCommandBuilder();
                    $table = $this->getMetaData()->tableSchema;
                    $command = $builder->createInsertCommand($table, $this->getAttributes($modelAttributes));

                    if (is_null($this->getDbConnection()->getCurrentTransaction()))
                    {
                        $transaction = $this->getDbConnection()->beginTransaction();
                    }

                    try
                    {
                        if ($command->execute())
                        {
                            $primaryKey = $table->primaryKey;
                            if (!is_null($table->sequenceName))
                            {
                                if (is_string($primaryKey) && is_null($this->$primaryKey))
                                {
                                    $this->$primaryKey = $builder->getLastInsertID($table);
                                }
                                else
                                {
                                    if (is_array($primaryKey))
                                    {
                                        foreach ($primaryKey as $pk)
                                        {
                                            if (is_null($this->$pk))
                                            {
                                                $this->$pk = $builder->getLastInsertID($table);
                                                break;
                                            }
                                        }
                                    }
                                }
                            }

                            if ((is_null($modelAttributes) && !is_null($this->eav_set_id))
                                || (in_array('eav_set_id', $modelAttributes) && !is_null($this->eav_set_id))
                            )
                            {
                                $attributes = is_null($eavAttributes) ? $this->getEavAttributes()
                                    : $this->getEavAttributes($eavAttributes);

                                foreach ($attributes as $name => $value)
                                {
                                    $attribute = $this->eavAttributeInstances[$name];
                                    $class = EavValue::model($attribute->data_type);
                                    $class->insertValue($this, $attribute, $value);
                                    $this->oldEavAttributes[$name] = $value;
                                }
                                $this->setOldEavSetPrimaryKey();
                                $this->storedEavAttributeInstances = $this->eavAttributeInstances;
                            }

                            if (isset($transaction))
                            {
                                $transaction->commit();
                            }
                            $this->setOldPrimaryKey($this->getPrimaryKey());
                            $this->afterSave();
                            $this->setIsNewRecord(false);
                            $this->setScenario('update');

                            return true;
                        }

                        if (isset($transaction))
                        {
                            $transaction->rollback();
                        }
                    }
                    catch (CException $ex)
                    {
                        if (isset($transaction))
                        {
                            $transaction->rollback();
                        }
                        throw $ex;
                    }
                }

                return false;
            }
        }
    }


    /**
     * This method does the same thing as the method CActiveRecord::update(), except that it also updates EAV attribute
     * values.
     * In cases when tracking of EAV attribute changes is not required, you can use the method CActiveRecord::update().
     * Note, if you assign a new value to the primary key of the model you must use this method, because it takes care
     * of referential integrity.
     *
     * @param array $attributes List of attributes that need to be saved, you can also specify EAV attribute names.
     * Defaults to null, meaning all attributes that are loaded from DB and all related EAV attributes will be saved.
     * Note, IF LIST OF ATTRIBUTES DOES NOT CONTAIN "eav_set_id", EAV attributes WILL NOT be saved.
     * @return boolean Whether the update is successful.
     * @throws CDbException If the active record is new.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function updateWithEavAttributes($attributes = null)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            if ($this->getIsNewRecord())
            {
                throw new CDbException(Yii::t('yii', 'The active record cannot be updated because it is new.'));
            }
            else
            {
                if ($this->beforeSave())
                {
                    $separatedAttributes = $this->separateAttributes($attributes);
                    $modelAttributes = isset($separatedAttributes['attributes']) ? $separatedAttributes['attributes'] : null;
                    $eavAttributes = isset($separatedAttributes['eavAttributes']) ? $separatedAttributes['eavAttributes'] : null;

                    if (is_null($this->getDbConnection()->getCurrentTransaction()))
                    {
                        $transaction = $this->getDbConnection()->beginTransaction();
                    }
                    try
                    {
                        if (is_null($this->getOldPrimaryKey()))
                        {
                            $this->setOldPrimaryKey($this->getPrimaryKey());
                        }
                        $this->updateByPk($this->getOldPrimaryKey(), $this->getAttributes($modelAttributes));

                        if (is_null($modelAttributes) || in_array('eav_set_id', $modelAttributes))
                        {
                            $attributes = is_null($eavAttributes) ? $this->getEavAttributes()
                                : $this->getEavAttributes($eavAttributes);

                            if (!empty($this->storedEavAttributeInstances))
                            {
                                foreach ($this->storedEavAttributeInstances as $attribute)
                                {
                                    if (!array_key_exists($attribute->name, $this->eavAttributeInstances))
                                    {
                                        $class = EavValue::model($attribute->data_type);
                                        $class->deleteValue($this, $attribute);
                                    }
                                }
                            }

                            foreach ($attributes as $name => $value)
                            {
                                if ($value !== $this->oldEavAttributes[$name])
                                {
                                    $attribute = $this->eavAttributeInstances[$name];
                                    $class = EavValue::model($attribute->data_type);
                                    $class->saveValue($this, $attribute, $value);
                                    $this->oldEavAttributes[$name] = $value;
                                }
                            }
                            $this->setOldEavSetPrimaryKey();
                            $this->storedEavAttributeInstances = $this->eavAttributeInstances;
                        }

                        $pk = is_null($this->primaryKey()) ? $this->getMetaData()->tableSchema->primaryKey : $this->primaryKey();
                        if (is_null($modelAttributes) || in_array($pk, $modelAttributes))
                        {
                            if ($this->getOldPrimaryKey() != $this->getPrimaryKey())
                            {
                                if (!empty($this->storedEavAttributeInstances))
                                {
                                    $dataTypes = array();
                                    foreach ($this->storedEavAttributeInstances as $attr)
                                    {
                                        if (!in_array($attr->data_type, $dataTypes))
                                        {
                                            array_push($dataTypes, $attr->data_type);
                                            $class = EavValue::model($attr->data_type);
                                            $class->updateEntityPrimaryKey($this);
                                        }
                                    }
                                    unset($dataTypes);
                                }
                            }
                        }

                        $this->setOldPrimaryKey($this->getPrimaryKey());
                        if (isset($transaction))
                        {
                            $transaction->commit();
                        }
                        $this->afterSave();

                        return true;
                    }
                    catch (CException $ex)
                    {
                        if (isset($transaction))
                        {
                            $transaction->rollback();
                        }
                        throw $ex;
                    }
                }

                return false;
            }
        }
    }


    /**
     * This method does the same thing as the method CActiveRecord::delete(), except that it also deletes values of
     * related EAV attributes.
     *
     * @return boolean Whether the deletion is successful.
     * @throws CDbException If the active record is new.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function deleteWithEavAttributes()
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {

            if (!$this->getIsNewRecord())
            {
                if ($this->beforeDelete())
                {
                    if (is_null($this->getDbConnection()->getCurrentTransaction()))
                    {
                        $transaction = $this->getDbConnection()->beginTransaction();
                    }

                    try
                    {
                        $result = $this->deleteByPk($this->getPrimaryKey()) > 0;
                        if ($result && !empty($this->storedEavAttributeInstances))
                        {
                            foreach ($this->storedEavAttributeInstances as $attribute)
                            {
                                $class = EavValue::model($attribute->data_type);
                                $class->deleteValue($this, $attribute);
                            }
                        }
                        if (isset($transaction))
                        {
                            $transaction->commit();
                        }
                    }
                    catch (CException $ex)
                    {
                        if (isset($transaction))
                        {
                            $transaction->rollback();
                        }
                        throw $ex;
                    }
                    $this->afterDelete();

                    return $result;
                }
                else
                {
                    return false;
                }
            }
            throw new CDbException(Yii::t('yii', 'The active record cannot be deleted because it is new.'));
        }
    }


    /**
     * Saves a selected list of EAV attributes. Unlike EavActiveRecord::saveWithEavAttributes(), this method only saves the
     * specified EAV attributes of an existing active record and does NOT call either beforeSave or afterSave.
     * Also note that this method does neither attribute filtering nor validation. So do not use this method with
     * untrusted data (such as user posted data).
     * This method is similar to the method CActiveRecord::saveAttributes() with one exception - it works with EAV
     * attributes.
     * @param array $attributes EAV attributes to be updated. Each element represents an EAV attribute name or an EAV
     * attribute value indexed by its name. If the latter, the record's EAV attribute will be changed accordingly before
     * saving.
     * @return boolean Whether the update is successful.
     * @throws CDbException If the active record is new.
     * @throws CException If the instantiated model does not support EAV attributes.
     * @since Version 1.0.1
     */
    public function saveEavAttributes($attributes)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            if (!$this->getIsNewRecord())
            {
                if (empty($attributes))
                {
                    return false;
                }

                if (is_null($this->getDbConnection()->getCurrentTransaction()))
                {
                    $transaction = $this->getDbConnection()->beginTransaction();
                }
                try
                {
                    $names = array();
                    foreach ($attributes as $name => $value)
                    {
                        if (is_integer($name))
                        {
                            array_push($names, $value);
                        }
                        else
                        {
                            array_push($names, $name);
                        }
                    }
                    if (array_intersect($this->eavAttributeNames(), $names) === array())
                    {
                        if (isset($transaction))
                        {
                            $transaction->commit();
                        }
                        return false;
                    }

                    if (!empty($this->storedEavAttributeInstances))
                    {
                        foreach ($this->storedEavAttributeInstances as $attribute)
                        {
                            if (!array_key_exists($attribute->name, $this->eavAttributeInstances))
                            {
                                $class = EavValue::model($attribute->data_type);
                                $class->deleteValue($this, $attribute);
                            }
                        }
                    }

                    $eavSetPk = is_null($this->getRelated(self::EAV_SET_RELATION_NAME))
                        ? null : $this->getRelated(self::EAV_SET_RELATION_NAME)->id;
                    if ($eavSetPk != $this->getOldEavSetPrimaryKey())
                    {
                        $this->updateByPk($this->getOldPrimaryKey(), array('eav_set_id' => $eavSetPk));
                    }

                    foreach ($attributes as $name => $value)
                    {
                        if (is_integer($name))
                        {
                            if ($this->hasEavAttribute($value))
                            {
                                $val = $this->getEavAttribute($value);
                                if ($val !== $this->oldEavAttributes[$value])
                                {
                                    $attribute = $this->eavAttributeInstances[$value];
                                    $class = EavValue::model($attribute->data_type);
                                    $class->saveValue($this, $attribute, $val);
                                    $this->oldEavAttributes[$value] = $val;
                                }
                            }
                        }
                        else
                        {
                            if ($this->hasEavAttribute($name))
                            {
                                $this->setEavAttribute($name, $value);
                                if ($value !== $this->oldEavAttributes[$name])
                                {
                                    $attribute = $this->eavAttributeInstances[$name];
                                    $class = EavValue::model($attribute->data_type);
                                    $class->saveValue($this, $attribute, $value);
                                    $this->oldEavAttributes[$name] = $value;
                                }
                            }
                        }
                    }
                    $this->setOldEavSetPrimaryKey();
                    $this->storedEavAttributeInstances = $this->eavAttributeInstances;
                    if (isset($transaction))
                    {
                        $transaction->commit();
                    }
                    return true;
                }
                catch (CException $ex)
                {
                    if (isset($transaction))
                    {
                        $transaction->rollback();
                    }
                    throw $ex;
                }
            }
            else
            {
                throw new CDbException(Yii::t('yii','The active record cannot be updated because it is new.'));
            }
        }
    }


    /**
     * Sets a value of the named EAV attribute. You may also use $this->eavAttributeName to set the attribute value.
     * @param string $name The attribute name.
     * @param mixed $value The attribute value.
     * @return boolean Whether the EAV attribute exists and the assignment is conducted successfully.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function setEavAttribute($name, $value)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            if ($this->hasEavAttribute($name))
            {
                if (!$this->getIsNewRecord() && !array_key_exists($name, $this->oldEavAttributes))
                {
                    $this->setOldEavAttribute($name);
                }
                $this->newEavAttributes[$name] = $value;

                return true;
            }

            return false;
        }
    }


    /**
     * Returns the named EAV attribute value. If the given attribute has no value it returns either an empty array for
     * a multivalued attribute or null for a single valued attribute.
     * @param string $name The attribute name.
     * @return mixed The attribute value.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function getEavAttribute($name)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            if ($this->hasEavAttribute($name))
            {
                if (array_key_exists($name, $this->newEavAttributes))
                {
                    return $this->newEavAttributes[$name];
                }
                else
                {
                    if ($this->getIsNewRecord())
                    {
                        $this->newEavAttributes[$name] =
                            $this->eavAttributeInstances[$name]->type == EavAttribute::TYPE_SINGLE ? null : array();

                        return $this->newEavAttributes[$name];
                    }
                    else
                    {
                        if (array_key_exists($name, $this->oldEavAttributes))
                        {
                            $this->newEavAttributes[$name] = $this->oldEavAttributes[$name];

                            return $this->newEavAttributes[$name];
                        }
                        else
                        {
                            $this->setOldEavAttribute($name);
                            $this->newEavAttributes[$name] = $this->oldEavAttributes[$name];

                            return $this->newEavAttributes[$name];
                        }
                    }
                }
            }

            return null;
        }
    }


    /**
     * Returns EAV attribute values indexed by EAV attribute names.
     * @param mixed $names Names of EAV attributes whose values needs to be returned. If this is null (default), then all
     * EAV attribute values will be returned.
     * @return array EAV attribute values indexed by EAV attribute names.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function getEavAttributes($names = null)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            $attributes = array();
            if (is_null($names))
            {
                foreach ($this->eavAttributeNames() as $name)
                {
                    $attributes[$name] = $this->getEavAttribute($name);
                }
            }
            else
            {
                if (is_array($names))
                {
                    foreach ($names as $name)
                    {
                        $attributes[$name] = $this->getEavAttribute($name);
                    }
                }
            }

            return $attributes;
        }
    }


    /**
     * Checks if the model has the named EAV attribute.
     * @param string $name The attribute name.
     * @return boolean Whether the model has the named EAV attribute.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function hasEavAttribute($name)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            return isset($this->eavAttributeInstances[$name]);
        }
    }


    /**
     * Returns the names of all EAV attributes that are attached to the model.
     * @return array The list of all EAV attribute names that are attached to the model.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function eavAttributeNames()
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            return array_keys($this->eavAttributeInstances);
        }
    }


    /**
     * Checks if the given attribute may hold multiple values.
     * @param string $name The attribute name.
     * @return boolean Returns true if an attribute with the specified name may hold multiple values, otherwise false.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function isEavAttributeMultivalued($name)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            if (!$this->hasEavAttribute($name))
            {
                return false;
            }

            return $this->eavAttributeInstances[$name]->type == EavAttribute::TYPE_MULTIPLE;
        }
    }


    /**
     * Performs the validation for EAV attributes.
     * @param array $attributes List of EAV attributes that should be validated. Defaults to null,
     * meaning any EAV attribute listed in the applicable validation rules should be validated. If this parameter is
     * given as a list of attributes, only the listed attributes will be validated.
     * Note, if the model supports EAV attributes this method is called by the method validate(), so you do not need to
     * call this method directly.
     * @return boolean Whether the validation is successful without any error.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function validateEavAttribute($attributes = null)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            foreach ($this->getEavValidators() as $validator)
            {
                $attr = $validator->attributes;
                $keys = array_keys($attr);
                $name = $attr[$keys[0]];

                if ($this->isEavAttributeMultivalued($name) && (is_null($attributes)
                        || in_array($name, $attributes))
                )
                {
                    $originalValue = $this->getEavAttribute($name);
                    if (empty($originalValue))
                    {
                        $this->newEavAttributes[$name] = null;
                        $validator->validate($this, $attributes);
                        $this->setEavAttribute($name, $originalValue);
                    }
                    else
                    {
                        if (!is_array($originalValue))
                        {
                            $this->addError($name, Yii::t('yii', '{attribute} is invalid.',
                                array('{attribute}' => $this->getAttributeLabel($name))));
                        }
                        else
                        {
                            foreach ($originalValue as $value)
                            {
                                $this->newEavAttributes[$name] = $value;
                                $validator->validate($this, $attributes);
                            }
                            $this->setEavAttribute($name, $originalValue);
                        }
                    }
                }
                else
                {
                    $validator->validate($this, $attributes);
                }
            }
        }
    }


    /**
     * Returns a value indicating whether the EAV attribute is required.
     * This is determined by checking if the attribute is associated with a CRequiredValidator validation rule in the
     * current scenario.
     * @param $attribute The attribute name.
     * @return boolean Whether the attribute is required.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function isEavAttributeRequired($attribute)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            foreach ($this->getEavValidators($attribute) as $validator)
            {
                if ($validator instanceof CRequiredValidator)
                {
                    return true;
                }
            }

            return false;
        }
    }


    /**
     * Returns the EAV attribute names that are safe to be massively assigned. A safe attribute is one that is associated
     * with a validation rule in the current scenario.
     * @return array The EAV attribute names that are safe to be massively assigned.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function getSafeEavAttributeNames()
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            $attributes = array();
            $unsafe = array();
            foreach ($this->getEavValidators() as $validator)
            {
                if (!$validator->safe)
                {
                    foreach ($validator->attributes as $name)
                    {
                        $unsafe[] = $name;
                    }
                }
                else
                {
                    foreach ($validator->attributes as $name)
                    {
                        $attributes[$name] = true;
                    }
                }
            }
            foreach ($unsafe as $name)
            {
                unset($attributes[$name]);
            }

            return array_keys($attributes);
        }
    }


    /**
     * Returns all the EAV attribute validators.
     * @return CList All the EAV attribute validators.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function getEavValidatorList()
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            if (is_null($this->eavValidators))
            {
                $this->eavValidators = $this->createEavValidators();
            }

            return $this->eavValidators;
        }
    }


    /**
     * Returns the EAV attribute validators applicable to the current scenario.
     * @param string $attribute The name of the EAV attribute whose validators should be returned.
     * If this is null, the validators for all EAV attributes in the model will be returned.
     * @return array The validators of EAV attributes applicable to the current scenario.
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function getEavValidators($attribute = null)
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            if (is_null($this->eavValidators))
            {
                $this->eavValidators = $this->createEavValidators();
            }

            $validators = array();
            $scenario = $this->getScenario();

            foreach ($this->eavValidators as $validator)
            {
                if ($validator->applyTo($scenario))
                {
                    if (is_null($attribute) || in_array($attribute, $validator->attributes, true))
                    {
                        $validators[] = $validator;
                    }
                }
            }

            return $validators;
        }
    }


    /**
     * Creates validator objects based on the specification in rules of the EAV attribute. This method is mainly used
     * internally.
     * @return CList Validators built based on EavAttribute::getEavValidatorList().
     * @throws CException If the instantiated model does not support EAV attributes.
     */
    public function createEavValidators()
    {
        if (!$this->eavEnable)
        {
            throw new CException('The method ' . __METHOD__ . '() should not be called. The instantiated model does not
            support EAV attributes.');
        }
        else
        {
            $validators = new CList();

            foreach ($this->eavAttributeInstances as $attr)
            {
                $validatorList = $attr->getEavValidatorList();

                foreach ($validatorList as $validator => $params)
                {
                    $validators->add(CValidator::createValidator(
                        $validator, $this, $attr->name, $params));
                }
            }

            return $validators;
        }
    }


    /**
     * Specifies that EAV attributes that are related to the found record should be loaded.
     * This method must be called before calling the find methods.
     * <pre>
     * $record = Foo::model()->withEavAttributes()->findByPk(1); //Lazy loading enabled
     * echo $record->someEavAttr; //A relational query will be initiated to get the value of this EAV attribute
     *
     * $record = Foo::model()->withEavAttributes(true)->findByPk(1); //Eager loading enabled
     * echo $record->someEavAttr; //A relation query will not be initiated
     * </pre>
     *
     * @param boolean $eager If this parameter is set to true then all EAV attribute values will be eagerly loaded.
     * With lazy loading enabled (the parameter is set to false), values of EAV attributes are loaded when
     * they are accessed. It means that a relational query will be initiated when you read a value of an EAV-attribute
     * the first time.
     * If eager loading is enabled all the values of related EAV attributes will be retrieved by performing a UNION
     * query.
     *
     * @return EavActiveRecord EavActiveRecord instance.
     */
    public function withEavAttributes($eager = false)
    {
        $this->eavEnable = true;
        $this->eagerFlag = $eager;
        $this->addDefaultEavRelation();
        $this->getDbCriteria()->mergeWith(array('with' => array(self::EAV_SET_RELATION_NAME)));
        return $this;
    }


    /**
     * Attaches the set of EAV attributes to the model. After calling this method the model may have EAV attributes and
     * you can use special methods to work with these. You also can attach the set of EAV attributes if assign a value to
     * the property EavActiveRecord::$eav_set_id.
     * <pre>
     * $record->eav_set_id = 1; //Attaches the set of EAV attributes to the model
     * $record->attacheEavSet(1); //Attaches the set of EAV attributes to the model
     * $record->setAttribute('eav_set_id', 1);
     * </pre>
     * @param integer $pk The primary key value of an existing set of EAV attributes, that must be attached to the model.
     */
    public function attachEavSet($pk)
    {
        $this->setAttribute('eav_set_id', $pk);
    }


    /**
     * Detaches the set of EAV attributes from the model.
     */
    public function detachEavSet()
    {
        $this->setAttribute('eav_set_id', null);
    }


    /**
     * Returns the name of the entity based on the class name. You can override this method to add own business logic to
     * format entity name.
     * @return string The entity name.
     */
    public function getEntity()
    {
        return strtolower(get_class($this));
    }


    /**
     * Determines whether the model may have EAV attributes.
     * @return boolean whether the model may have EAV attributes.
     */
    public function getIsEavEnabled()
    {
        return $this->eavEnable;
    }


    /**
     * Returns the old primary key value of the EAV attribute set.
     * @return mixed The old primary key value of the EAV attribute set.
     */
    public function getOldEavSetPrimaryKey()
    {
        return $this->oldEavSetPrimaryKey;
    }


    /**
     * Sets the old primary key value of the EAV-attribute set.
     */
    final protected function setOldEavSetPrimaryKey()
    {
        $this->oldEavSetPrimaryKey = $this->getAttribute('eav_set_id');
    }


    /**
     * Attaches EAV-attributes to the model. You do not usually need to call this method.
     * @param mixed $values This is for internal use only. An associative array. Each item of the array represents
     * a specific row in the query result.
     */
    final protected  function attachEavAttributes($values = false)
    {
        $this->eavEnable = true;
        $this->prepareEavAttributes($values);
    }


    /**
     * Prepares EAV-attributes for use with the model.
     * @param mixed $values An associative array that contains all the values of EAV-attributes
     */
    private function prepareEavAttributes($values = false)
    {
        $this->addDefaultEavRelation();
        $set = $this->getRelated(self::EAV_SET_RELATION_NAME);

        if (!is_null($set))
        {
            $attributes = $set->getEavAttributes();
            if (!empty($attributes))
            {
                foreach ($attributes as $attr)
                {
                    if (!$this->hasAttribute($attr->name) && !$this->getMetaData()->hasRelation($attr->name))
                    {
                        $this->eavAttributeInstances[$attr->name] = $attr;
                    }
                }
                $this->storedEavAttributeInstances = $this->eavAttributeInstances;
                if ($values !== false)
                {
                    $this->populateOldEavAttributes($values);
                }
                foreach ($this->oldEavAttributes as $name => $value)
                {
                    if (!array_key_exists($name, $this->newEavAttributes))
                    {
                        $this->newEavAttributes[$name] = $value;
                    }
                }
            }
        }
        $this->removeDefaultEavRelation();
    }


    /**
     * Prepares EAV-attributes of a new set to use with the model.
     * This method is called by EavActiveRecord::refreshEavAttributes().
     */
    private function resetEavAttributes()
    {
        $this->eavValidators = null;
        $this->oldEavAttributeNames = array_flip(array_keys($this->eavAttributeInstances));
        $this->eavAttributeInstances = array();
        $this->addDefaultEavRelation();
        $set = $this->getRelated(self::EAV_SET_RELATION_NAME, true);
        if (!is_null($set))
        {
            $attributes = $set->getEavAttributes();
            if (!empty($attributes))
            {
                foreach ($attributes as $attr)
                {
                    if (!$this->hasAttribute($attr->name) && !$this->getMetaData()->hasRelation($attr->name))
                    {
                        $this->eavAttributeInstances[$attr->name] = $attr;
                    }
                }
                if (!empty($this->oldEavAttributeNames))
                {
                    foreach ($this->oldEavAttributeNames as $name => $val)
                    {
                        if (!array_key_exists($name, $this->eavAttributeInstances))
                        {
                            unset($this->oldEavAttributes[$name]);
                            unset($this->newEavAttributes[$name]);
                        }
                    }
                }
            }
            else
            {
                $this->newEavAttributes = array();
                $this->oldEavAttributes = array();
            }
        }
        else
        {
            $this->newEavAttributes = array();
            $this->oldEavAttributes = array();
        }
    }


    /**
     * This method is called by EavActiveRecord::refreshEavAttributes().
     */
    private function loadStoredEavAttributeInstances()
    {
        $this->eavEnable = true;
        $this->metaData->addRelation(self::EAV_SET_RELATION_NAME,
            array(self::BELONGS_TO, 'EavSet', 'oldEavSetPrimaryKey',
                  'alias' => self::EAV_SET_RELATION_NAME, 'with' => self::EAV_ATTRIBUTE_RELATION_NAME));
        $set = $this->getRelated(self::EAV_SET_RELATION_NAME);
        if (!is_null($set))
        {
            $attributes = $set->getEavAttributes();
            foreach ($attributes as $attr)
            {
                if (!$this->hasAttribute($attr->name) && !$this->getMetaData()->hasRelation($attr->name))
                {
                    $this->storedEavAttributeInstances[$attr->name] = $attr;
                }
            }
            unset($this->{self::EAV_SET_RELATION_NAME});
        }
        $this->resetEavAttributes();
    }


    /**
     * Refreshes EAV-attributes of this model.
     * @return boolean If EAV-attributes were refreshed it returns true, otherwise false.
     */
    private function refreshEavAttributes()
    {
        if (!$this->eavEnable)
        {
            if ($this->getIsNewRecord())
            {
                $this->attachEavAttributes();
                return true;
            }
            else if (is_null($this->oldEavSetPrimaryKey))
            {
                $this->attachEavAttributes();
                return true;
            }
            else
            {
                $this->loadStoredEavAttributeInstances();
                return true;
            }
        }
        if ($this->checkEavSetValidity())
        {
            return false;
        }
        $this->resetEavAttributes();
        return true;
    }


    /**
     * Adds a default EAV relation.
     */
    private function addDefaultEavRelation()
    {
        $this->metaData->addRelation(self::EAV_SET_RELATION_NAME,
            array(self::BELONGS_TO, 'EavSet', 'eav_set_id',
                  'alias' => self::EAV_SET_RELATION_NAME,
                  'with' => self::EAV_ATTRIBUTE_RELATION_NAME));
    }


    /**
     * Removes a default EAV relation.
     */
    private function removeDefaultEavRelation()
    {
        $this->metaData->removeRelation(self::EAV_SET_RELATION_NAME);
    }


    /**
     * Adds a temporary relation.
     * @param string $name The name of the EAV-attribute.
     * @return mixed The name of the added relation. If this record doesn't support
     * EAV-attributes or the record does not contain the given EAV-attribute
     * it returns false. It also returns false if the record is new.
     * @throws CException
     */
    private function addEavRelation($name)
    {
        if ($this->getIsNewRecord() || !$this->eavEnable)
        {
            return false;
        }

        if (!$this->hasEavAttribute($name))
        {
            return false;
        }

        $attribute = $this->eavAttributeInstances[$name];
        $class = EavValue::model($attribute->data_type);

        if (!($class instanceof EavValue))
        {
            throw new CException('The class ' . $attribute->data_type . '.php must be '
                . 'a subclass of the class EavValue.');
        }

        $relation = $attribute->type == EavAttribute::TYPE_SINGLE ? self::HAS_ONE : self::HAS_MANY;

        $name = $this->createEavRelationName($name);
        $condition = $this->quoteColumnName($name . '.eav_attribute_id') . ' = :' . $name . '_eav_attribute_id AND  '
                   . $this->quoteColumnName($name . '.entity') . ' = :' . $name . '_entity';

        $this->metaData->addRelation($name, array($relation, $attribute->data_type, 'entity_id', 'on' => $condition,
                                                  'params' => array(':' . $name . '_eav_attribute_id' => $attribute->id,
                                                                    ':' . $name . '_entity' => $this->getEntity())));
        return $name;
    }


    /**
     * Removes a temporary relation.
     * @param string $name The name of the relation.
     */
    private function removeEavRelation($name)
    {
        unset($this->$name);
        $this->metaData->removeRelation($name);
    }


    /**
     * Adds a temporary relation which will be used while performing the search with EAV-attributes.
     * @throws CException
     */
    private function addFindEavRelation()
    {
        if (is_null($this->findEavAttributes))
        {
            return;
        }
        $attributes = EavAttribute::model()->getEavAttributes($this->findEavAttributes);
        foreach ($attributes as $name => $attribute)
        {
            $class = EavValue::model($attribute->data_type);
            if (!($class instanceof EavValue))
            {
                throw new CException('The class ' . $attribute->data_type . '.php must be '
                    . 'a subclass of the class EavValue.');
            }

            $relation = $attribute->type == EavAttribute::TYPE_SINGLE ? self::HAS_ONE : self::HAS_MANY;
            $name = self::EAV_FIND_RELATION_NAME . ucfirst($name);

            $condition = $this->quoteColumnName($name . '.eav_attribute_id') . ' = :' . $name . '_eav_attribute_id AND  '
                       . $this->quoteColumnName($name . '.entity') . ' = :' . $name . '_entity';

            $this->metaData->addRelation($name, array($relation, $attribute->data_type, 'entity_id', 'select' => false,
                                                      'on' => $condition, 'params' => array(
                    ':' . $name . '_eav_attribute_id' => $attribute->id,
                    ':' . $name . '_entity' => $this->getEntity())));

            $this->getDbCriteria()->mergeWith(array('with' => array($name)));
        }
    }


    /**
     * Removes a temporary relation.
     */
    private  function removeFindEavRelation()
    {
        if (is_null($this->findEavAttributes))
        {
            return;
        }
        foreach ($this->findEavAttributes as $attribute)
        {
            $name = self::EAV_FIND_RELATION_NAME . ucfirst($attribute);
            $this->getMetaData()->removeRelation($name);
        }
        $this->findEavAttributes = null;
    }


    /**
     * Performs a relational query.
     * @param string $name Attribute name.
     * @return mixed A derived class instance from the class EavValue. If the given attribute can hold multiple
     * values it returns list of instances (an array). If the given attribute has no value it returns either an empty array for
     * a multi-value attribute or null for a single-value attribute.
     * If the given attribute does not exist, the value returned will be false.
     */
    private function eavValueQuery($name)
    {
        $name = $this->addEavRelation($name);
        if ($name !== false)
        {
            $result = $this->getRelated($name);
            $this->removeEavRelation($name);
            return $result;
        }
        return false;
    }


    /**
     * Performs a select query to retrieve values of EAV-attributes for the specified record(s).
     * @param mixed $record Either a derived class instance from the class EavActiveRecord or list of instances.
     * @return mixed An associative array. Each item of the array represents a specific row in the query result.
     * Returns false if specified records do not have any EAV-attributes. If EAV-attributes do not have stored values,
     * the returned value will be null.
     */
    private function eavValuesQuery($record)
    {
        $command = $this->createUnionCommand($record);
        if ($command === false)
        {
            return false;
        }

        $rows = $command->queryAll();
        if (empty($rows))
        {
            return null;
        }
        return $rows;
    }


    /**
     * Returns the value of the given EAV-attribute.
     * @param string $name Attribute name.
     * @return mixed The value of an attribute with the specified name. If the given attribute does not exist, the value
     * returned will be false. If the given attribute can hold multiple values it returns either an array containing all
     * the values or an empty array. If the given attribute can hold a single value it returns either a string or null.
     */
    private function getEavValue($name)
    {
        $value = $this->eavValueQuery($name);
        if ($value === false)
        {
            return false;
        }
        if ($this->isEavAttributeMultivalued($name))
        {
            if (!empty($value))
            {
                $values = array();

                foreach ($value as $val)
                {
                    $values[] = $val->value;
                }
                return $values;
            }
            return array();
        }
        else
        {
            if (!is_null($value))
            {
                return $value->value;
            }
            return null;
        }
    }


    /**
     * Assigns values to the EavActiveRecord::$oldEavAttributes.
     * @param array $values Each item of the array represents a specific row in the query result.
     */
    private function populateOldEavAttributes($values)
    {
        if (empty($this->eavAttributeInstances))
        {
            return;
        }
        $attributes = $this->eavAttributeInstances;
        foreach ($attributes as $attr)
        {
            if (is_null($values))
            {
                break;
            }
            foreach ($values as $index => $row)
            {
                if ($row['eav_attribute_id'] == $attr->id
                    && $row['entity'] === $this->getEntity()
                    && $row['entity_id'] == $this->getPrimaryKey())
                {

                    if ($this->isEavAttributeMultivalued($attr->name))
                    {
                        $this->oldEavAttributes[$attr->name][] = $row['value'];
                    }
                    else
                    {
                        $this->oldEavAttributes[$attr->name] = $row['value'];
                    }
                }
            }
        }
        foreach ($attributes as $attr)
        {
            if (!array_key_exists($attr->name, $this->oldEavAttributes))
            {
                if ($this->isEavAttributeMultivalued($attr->name))
                {
                    $this->oldEavAttributes[$attr->name] = array();
                }
                else
                {
                    $this->oldEavAttributes[$attr->name] = null;
                }
            }
        }
    }


    /**
     * Creates an instance of the class CDbCommand that represents an SQL statement to retrieve
     * all the values of EAV-attributes for the specified record(s) from the database.
     * @param mixed $record Either a derived class instance from the class EavActiveRecord or list of instances.
     * @return mixed Returns false if specified records do not have any EAV-attributes, otherwise it returns
     * an instance of the class CDbCommand.
     */
    private function createUnionCommand($record)
    {
        $db = $this->getDbConnection();
        $castType = $this->getCastType();
        $params = array();
        $sql = '';
        $counter = 0;

        if ($record instanceof EavActiveRecord)
        {
            $set = $record->getRelated(self::EAV_SET_RELATION_NAME);
            if (is_null($set))
            {
                return false;
            }
            $attributes = $set->getEavAttributes();
            if(empty($attributes))
            {
                return false;
            }

            if (count($attributes) == 1)
            {
                $key = array_keys($attributes);
                $attr = $attributes[$key[0]];
                $class = EavValue::model($attr->data_type);

                return  $db->createCommand()->select()
                    ->from($class->tableName())
                    ->where(array('and', 'eav_attribute_id = :eav_attribute_id',
                                  'entity = :entity', 'entity_id = :entity_id'), array(':eav_attribute_id' => $attr->id,
                              ':entity' => $record->getEntity(),
                              ':entity_id' => $record->getPrimaryKey()));
            }

            foreach ($attributes as $attr)
            {
                $class = EavValue::model($attr->data_type);

                $command = $db->createCommand()
                    ->select('id, eav_attribute_id, entity_id, entity, CAST(value AS ' . $castType . ') AS value')
                    ->from($class->tableName())
                    ->where(array('and',
                                  'eav_attribute_id = :eav_attribute_id_' . $counter,
                                  'entity = :entity_' . $counter,
                                  'entity_id = :entity_id_' . $counter));

                $params[':eav_attribute_id_' . $counter] = $attr->id;
                $params[':entity_' . $counter] = $record->getEntity();
                $params[':entity_id_' . $counter] = $record->getPrimaryKey();
                $counter++;
                $sql .= $sql === '' ? '(' . $command->text . ')' : ' UNION ALL (' . $command->text . ')';
            }

            $command = $db->createCommand()
                ->select('id, eav_attribute_id, entity_id, entity, CAST(value AS ' . $castType . ') AS value')
                ->from('(' . $sql . ') AS t');
            $command->params = $params;
            return $command;
        }
        else
        {
            if (count($record) == 1)
            {
                $keys = array_keys($record);
                return $this->createUnionCommand($record[$keys[0]]);
            }

            foreach ($record as $rec)
            {
                $set = $rec->getRelated(self::EAV_SET_RELATION_NAME);
                if (is_null($set))
                {
                    continue;
                }
                $attributes = $set->getEavAttributes();
                if (empty($attributes))
                {
                    continue;
                }

                foreach ($attributes as $attr)
                {
                    $class = EavValue::model($attr->data_type);

                    $command = $db->createCommand()
                        ->select('id, eav_attribute_id, entity_id, entity, CAST(value AS ' . $castType . ') AS value')
                        ->from($class->tableName())
                        ->where(array('and',
                                      'eav_attribute_id = :eav_attribute_id_' . $counter,
                                      'entity = :entity_' . $counter,
                                      'entity_id = :entity_id_' . $counter));

                    $params[':eav_attribute_id_' . $counter] = $attr->id;
                    $params[':entity_' . $counter] = $rec->getEntity();
                    $params[':entity_id_' . $counter] = $rec->getPrimaryKey();
                    $counter++;
                    $sql .= $sql === '' ? '(' . $command->text . ')' : ' UNION ALL (' . $command->text . ')';
                }
            }

            if ($counter == 0)
            {
                return false;
            }
            else if ($counter == 1)
            {
                $command->params = $params;
                return $command;
            }

            $command = $db->createCommand()
                ->select('id, eav_attribute_id, entity_id, entity, CAST(value AS ' . $castType . ') AS value')
                ->from('(' . $sql . ') AS t');
            $command->params = $params;
            return $command;
        }
    }


    /**
     * Checks if the property EavActiveRecord::$eav_set_id is valid.
     * @return boolean
     */
    private function checkEavSetValidity()
    {
        $set = $this->getRelated(self::EAV_SET_RELATION_NAME);
        if (is_null($this->eav_set_id) || $this->eav_set_id === '')
        {
            return is_null($set);
        }
        else
        {
            if (is_null($set))
            {
                return false;
            }
            return $this->eav_set_id == $set->id;
        }
    }


    /**
     * Returns an associative array that contains separated attribute names.
     * @param array $attributes List of attribute and EAV-attribute names that must be separated.
     * @return array An associative array that contains separated attribute names.
     * <pre>
     * array(
     *        'attributes' => array('attName1', 'attrName2',),
     *        'eavAttributes' => array('attrName1', 'attrName2,)
     * );
     * </pre>
     */
    private function separateAttributes($attributes)
    {
        if (is_array($attributes))
        {
            $modelAttributes = array();
            $eavAttributes = array();

            foreach ($attributes as $name)
            {
                if ($this->hasEavAttribute($name))
                {
                    array_push($eavAttributes, $name);
                }
                else
                {
                    array_push($modelAttributes, $name);
                }
            }
        }
        $modelAttributes = isset($modelAttributes) ? $modelAttributes : null;
        $eavAttributes = isset($eavAttributes) ? $eavAttributes : null;
        return array('attributes' => $modelAttributes, 'eavAttributes' => $eavAttributes);
    }


    /**
     * This method is called by EavActiveRecord::find* methods.
     * @param null | EavActiveRecord $record
     * @return null | EavActiveRecord
     */
    private function prepareRecord($record)
    {
        if (!$this->eavEnable)
        {
            $this->removeFindEavRelation();
            if (!is_null($record))
            {
                $record->setOldEavSetPrimaryKey();
                $record->afterFind();
            }
            return $record;
        }
        if(is_null($record))
        {
            $this->resetEavFlags();
            $this->removeDefaultEavRelation();
            $this->removeFindEavRelation();
            return null;
        }
        if ($record instanceof EavActiveRecord)
        {
            if (!$this->eagerFlag)
            {
                $record->setOldEavSetPrimaryKey();
                $record->attachEavAttributes();
                $record->afterFind();
            }
            else
            {
                $this->eagerLoadEavAttributes($record);
            }
        }
        $this->resetEavFlags();
        $this->removeDefaultEavRelation();
        $this->removeFindEavRelation();
        return $record;
    }


    /**
     * This method is called by EavActiveRecord::findAll* methods.
     * @param array $record
     * @return array
     */
    private function prepareRecords($record)
    {
        if (!$this->eavEnable)
        {
            $this->removeFindEavRelation();
            foreach ($record as $instance)
            {
                $instance->setOldEavSetPrimaryKey();
                $instance->afterFind();
            }
            return $record;
        }
        if (!$this->eagerFlag)
        {
            foreach ($record as $instance)
            {
                $instance->setOldEavSetPrimaryKey();
                $instance->attachEavAttributes();
                $instance->afterFind();
            }
        }
        else
        {
            $this->eagerLoadEavAttributes($record);
        }
        $this->resetEavFlags();
        $this->removeDefaultEavRelation();
        $this->removeFindEavRelation();
        return $record;
    }


    /**
     * Prepares an SQL condition.
     * @param mixed $condition It must be a string or an array or an instance of the class CDbCriteria.
     * @return mixed Returns an instance of the class CDbCriteria or a string.
     */
    private function prepareCondition($condition)
    {
        $pattern = '/[:]{2}[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
        $matches = array();
        $replace = array();
        if (is_string($condition))
        {
            preg_match_all($pattern, $condition, $matches);
            if (!empty($matches[0]))
            {
                foreach ($matches[0] as $name)
                {
                    $name =  mb_substr($name, 2, mb_strlen($name, Yii::app()->charset) - 2, Yii::app()->charset);
                    $this->findEavAttributes[] = $name;
                    $replace[] = $this->quoteColumnName(self::EAV_FIND_RELATION_NAME . ucfirst($name) . '.value');
                }
                $this->addFindEavRelation();
                return str_replace($matches[0], $replace, $condition);
            }
            return $condition;
        }
        else if (is_array($condition))
        {
            $criteria = new CDbCriteria($condition);
            $condition = $this->prepareCondition($criteria->condition);
            $criteria->condition = $condition;
            return $criteria;
        }
        else if ($condition instanceof CDbCriteria)
        {
            $condition->condition = $this->prepareCondition($condition->condition);
            return $condition;
        }
    }


    /**
     * Initiates eager loading values of EAV-attributes.
     * @param mixed $record Either a derived class instance from the class EavActiveRecord or list of instances.
     */
    private function eagerLoadEavAttributes($record)
    {
        if ($record instanceof EavActiveRecord)
        {
            $values = $this->eavValuesQuery($record);
            $record->setOldEavSetPrimaryKey();
            $record->attachEavAttributes($values);
            $record->afterFind();
        }
        else if (is_array($record))
        {
            $values = $this->eavValuesQuery($record);
            foreach ($record as $instance)
            {
                $instance->setOldEavSetPrimaryKey();
                $instance->attachEavAttributes($values);
                $instance->afterFind();
            }
        }
    }


    /**
     * Sets the stored value to EavActiveRecord::$oldEavAttributes.
     * @param string $name Attribute name.
     */
    private function setOldEavAttribute($name)
    {
        $value = $this->getEavValue($name);
        if ($value !== false)
        {
            $this->oldEavAttributes[$name] = $value;
        }
    }


    /**
     * Sets a default value to properties EavActiveRecord::$eavEnable, EavActiveRecord::$eagerFlag.
     */
    private function resetEavFlags()
    {
        $this->eavEnable = false;
        $this->eagerFlag = false;
    }


    /**
     * Creates a relation name for the specified attribute.
     * @param string $name Attribute name.
     * @return string The name of the relation.
     */
    private function createEavRelationName($name)
    {
        return self::EAV_VALUE_RELATION_NAME . '_' . $name;
    }


    /**
     * Quotes a column name for use in a query. If the column name contains prefix, the prefix will also be properly quoted.
     * @param string $name The column name.
     * @return string The properly quoted column name.
     */
    private function quoteColumnName($name)
    {
        return $this->getDbConnection()->quoteColumnName($name);
    }


    /**
     * Returns the name of the target data type of the cast. It's called by the method EavActiveRecord::createUnionCommand().
     * @return string The name of the target data type of the cast.
     */
    private function getCastType()
    {
        $schema = $this->getDbConnection()->getSchema();
        $type = '';
        if ($schema instanceof CMysqlSchema)
        {
            $type = 'char';
        }
        else if ($schema instanceof CPgsqlSchema)
        {
            $type = 'text';
        }
        return  $type;
    }


    /**
     * Adds the new column "eav_set_id" in the associated database table.
     */
    public function addColumn()
    {
        $db = $this->getDbConnection();
        if (is_null($this->getTableSchema()->getColumn('eav_set_id')))
        {
            if ($db->getSchema() instanceof CMysqlSchema)
            {
                $db->createCommand()->addColumn($this->tableName(), 'eav_set_id', "int(10) unsigned DEFAULT NULL COMMENT 'Foreign key reference eav_set(id)'");
            }
            else if ($db->getSchema() instanceof CPgsqlSchema)
            {
                $db->createCommand()->addColumn($this->tableName(), 'eav_set_id', 'integer');
            }
            $db->createCommand()->createIndex('no_' . $this->tableName() . '_eav_set_id', $this->tableName(), 'eav_set_id');
            $db->createCommand()->addForeignKey('fk_eav_set_id_' . $this->tableName(), $this->tableName(), 'eav_set_id', 'eav_set', 'id');
        }
    }


    /**
     * Removes the column "eav_set_id" from the associated database table.
     */
    public function dropColumn()
    {
        $db = $this->getDbConnection();
        if (!is_null($this->getTableSchema()->getColumn('eav_set_id')))
        {
            $db->createCommand()->dropForeignKey('fk_eav_set_id_' . $this->tableName(), $this->tableName());
            $db->createCommand()->dropIndex('no_' . $this->tableName() . '_eav_set_id', $this->tableName());
            $db->createCommand()->dropColumn($this->tableName(), 'eav_set_id');
        }
    }
}

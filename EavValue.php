<?php
/**
 * EavValue class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * EavValue class represents methods to save, update and delete values of an EAV attribute.
 * Each class that represents a data type of an EAV attribute value must be derived from it.
 *
 * @since 1.0.0
 */
class EavValue extends CActiveRecord
{
    
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    /**
     * Saves a value of the given EAV attribute associated with the given entity instance.
     * @param EavActiveRecord $entity EavActiveRecord instance.
     * @param EavAttribute $attribute EavAttribute instance.
     * @param mixed $value EAV attribute value(s) that must be saved.
     * @return int Returns number of affected rows.
     * @throws CDbException
     */
    public function saveValue(EavActiveRecord $entity, EavAttribute $attribute, $value)
    {
        if ($entity->getIsNewRecord())
        {
            return $this->insertValue($entity, $attribute, $value);
        }

        if ($entity->isEavAttributeMultivalued($attribute->name))
        {
            $deletedRows = $this->deleteValue($entity, $attribute);
            if (empty($value))
            {
                return $deletedRows;
            }

            return $deletedRows + $this->insertValue($entity, $attribute, $value);
        }
        else
        {
            if ($value === '' || is_null($value))
            {
                return $this->deleteValue($entity, $attribute);
            }
            else
            {
                $updatedRows = $this->updateAll(array('value' => $value), 'eav_attribute_id = :eav_attribute_id AND
                    entity_id = :entity_id AND entity = :entity',
                    array(':eav_attribute_id' => $attribute->id, ':entity_id' => $entity->getOldPrimaryKey(),
                          ':entity' => $entity->getEntity()));

                if ($updatedRows === 0)
                {
                    return $this->insertValue($entity, $attribute, $value);
                }

                return $updatedRows;
            }
        }
    }


    /**
     * Insert a new row(s) in the table that contains a value(s) of the given EAV attribute associated with the
     * given entity instance.
     * @param EavActiveRecord $entity EavActiveRecord instance.
     * @param EavAttribute $attribute EavAttribute instance.
     * @param mixed $value EAV attribute value(s) that must be saved.
     * @return int Number of inserted rows.
     * @throws CDbException
     * @since Version 1.0.1
     */
    public function insertValue(EavActiveRecord $entity, EavAttribute $attribute, $value)
    {
        $pk = $entity->getIsNewRecord() ? $entity->getPrimaryKey() : $entity->getOldPrimaryKey();

        if ($entity->isEavAttributeMultivalued($attribute->name))
        {
            if (empty($value))
            {
                return 0;
            }

            if (count($value) === 1)
            {
                $keys = array_keys($value);
                if ($value[$keys[0]] === '' || is_null($value[$keys[0]]))
                {
                    return 0;
                }

                $data = array('eav_attribute_id' => $attribute->id, 'entity_id' => $pk,
                              'entity' => $entity->getEntity(), 'value' => $value[$keys[0]]);
                $table = $this->getMetaData()->tableSchema;

                return $this->getCommandBuilder()->createInsertCommand($table, $data)->execute();
            }
            else
            {
                $data = array();
                foreach ($value as $val)
                {
                    if ($val === '' || is_null($val))
                    {
                        continue;
                    }
                    $data[] = array('eav_attribute_id' => $attribute->id, 'entity_id' => $pk,
                                    'entity' => $entity->getEntity(), 'value' => $val);
                }
                $table = $this->getMetaData()->tableSchema;

                return $this->getCommandBuilder()->createMultipleInsertCommand($table, $data)->execute();
            }
        }
        else
        {
            if ($value === '' || is_null($value))
            {
                return 0;
            }
            $data = array('eav_attribute_id' => $attribute->id, 'entity_id' => $pk,
                          'entity' => $entity->getEntity(), 'value' => $value);
            $table = $this->getMetaData()->tableSchema;

            return $this->getCommandBuilder()->createInsertCommand($table, $data)->execute();
        }
    }


    /**
     * Deletes a value(s) of the given EAV attribute associated with the given entity instance.
     * @param EavActiveRecord $entity EavActiveRecord instance.
     * @param EavAttribute $attribute EavAttribute instance.
     * @return int Returns number of deleted rows.
     */
    public function deleteValue(EavActiveRecord $entity, EavAttribute $attribute)
    {
        return $this->deleteAll('eav_attribute_id = :eav_attribute_id AND entity_id = :entity_id AND entity = :entity',
            array(':eav_attribute_id' => $attribute->id, ':entity_id' => $entity->getOldPrimaryKey(),
                  ':entity' => $entity->getEntity()));
    }


    /**
     * Updates the primary key value of the given entity instance in the table that stores EAV attributes values.
     * @param EavActiveRecord $entity EavActiveRecord instance.
     * @return int Returns number of affected rows.
     * @since Version 1.0.1
     */
    public function updateEntityPrimaryKey(EavActiveRecord $entity)
    {
        return $this->updateAll(array('entity_id' => $entity->getPrimaryKey()), 'entity = :entity AND entity_id = :entity_id',
            array(':entity' => $entity->getEntity(), ':entity_id' => $entity->getOldPrimaryKey()));
    }
}

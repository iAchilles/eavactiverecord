<?php
/**
 * EavValue class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * EavValue class represents methods to save, update and delete values of an EAV-attribute.
 * Each class that represents a data type of an EAV-attribute must be derived from it.
 *
 * @version 1.0.0
 */
class EavValue extends CActiveRecord
{
    
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    /**
     * Saves an attribute value.
     * @param EavActiveRecord $entity
     * @param EavAttribute $attribute
     * @param mixed $value
     * @return int Returns number of affected rows.
     * @throws CDbException
     */
    public function saveValue(EavActiveRecord $entity, EavAttribute $attribute, $value)
    {
        if ($entity->getIsNewRecord())
        {
            if ($attribute->type == EavAttribute::TYPE_MULTIPLE)
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

                    $data = array('eav_attribute_id' => $attribute->id, 'entity_id' => $entity->getPrimaryKey(),
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
                        $data[] = array('eav_attribute_id' => $attribute->id, 'entity_id' => $entity->getPrimaryKey(),
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
                $data = array('eav_attribute_id' => $attribute->id, 'entity_id' => $entity->getPrimaryKey(),
                              'entity' => $entity->getEntity(), 'value' => $value);
                $table = $this->getMetaData()->tableSchema;
                return $this->getCommandBuilder()->createInsertCommand($table, $data)->execute();
            }
        }
        else
        {
            if ($attribute->type == EavAttribute::TYPE_MULTIPLE)
            {
                $deletedRows = $this->deleteValue($entity, $attribute);
                if (empty($value))
                {
                    return $deletedRows;
                }

                if (count($value) === 1)
                {
                    $keys = array_keys($value);
                    if ($value[$keys[0]] === '' || is_null($value[$keys[0]]))
                    {
                        return 0;
                    }

                    $data = array('eav_attribute_id' => $attribute->id, 'entity_id' => $entity->getPrimaryKey(),
                                  'entity' => $entity->getEntity(), 'value' => $value[$keys[0]]);
                    $table = $this->getMetaData()->tableSchema;
                    $insertedRows = $this->getCommandBuilder()->createInsertCommand($table, $data)->execute();
                    return $deletedRows + $insertedRows;
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
                        $data[] = array('eav_attribute_id' => $attribute->id, 'entity_id' => $entity->getPrimaryKey(),
                                        'entity' => $entity->getEntity(), 'value' => $val);
                    }
                    $table = $this->getMetaData()->tableSchema;
                    $insertedRows = $this->getCommandBuilder()->createMultipleInsertCommand($table, $data)->execute();
                    return $deletedRows + $insertedRows;
                }
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
                    entity_id = :entity_id AND entity = :entity', array(':eav_attribute_id' => $attribute->id,
                    ':entity_id' => $entity->getPrimaryKey(), ':entity' => $entity->getEntity()));

                    if ($updatedRows === 0)
                    {
                        $data = array('eav_attribute_id' => $attribute->id, 'entity_id' => $entity->getPrimaryKey(),
                                      'entity' => $entity->getEntity(), 'value' => $value);
                        $table = $this->getMetaData()->tableSchema;
                        return $this->getCommandBuilder()->createInsertCommand($table, $data)->execute();
                    }

                    return $updatedRows;
                }
            }
        }
    }


    /**
     * Deletes an attribute value.
     * @param EavActiveRecord $entity
     * @param EavAttribute $attribute
     * @return int Returns number of affected rows.
     */
    public function deleteValue(EavActiveRecord $entity, EavAttribute $attribute)
    {
        return $this->deleteAll('eav_attribute_id = :eav_attribute_id AND entity_id = :entity_id AND entity = :entity',
            array(':eav_attribute_id' => $attribute->id, ':entity_id' => $entity->getPrimaryKey(),
                  ':entity' => $entity->getEntity()));
    }
}

<?php
/**
 * EavSet class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * EavSet class It represents methods to manipulate a set of EAV-attributes (creating a new set, adding an attribute to
 * a set, removing an attribute from a set, removing an attribute set).
 *
 * To create a new attribute set and save data to a database, you need to write the following code:
 * <pre>
 * $set = new EavSet();
 * $set->name = 'Set'; // The property EavSet::$name is required
 * $set->save();
 * </pre>
 *
 * To add a new EAV-attribute to a set, you need to write the following code:
 * <pre>
 * $attribute = new EavAttribute(); //Create an instance of the class EavAttribute
 * $attribute->name = 'attr1';
 * $attribute->label = 'Attribute Label';
 * $attribute->type = EavAttribute::TYPE_SINGLE;
 * $attribute->data_type = EavAttribute::DATA_TYPE_INT;
 *
 * $set = new EavSet();
 * $set->name = 'Set';
 * $set->addEavAttribute($attribute);
 * $set->save();
 * </pre>
 *
 * When you add a new attribute to a set it will be automatically saved (if the attribute is valid). But you still may
 * call the method EavAttribute::save() before adding an attribute to a set. The following code example is equivalent to
 * the previous example:
 * <pre>
 * $attribute = new EavAttribute();
 * $attribute->name = 'attr1';
 * $attribute->label = 'Attribute Label';
 * $attribute->type = EavAttribute::TYPE_SINGLE;
 * $attribute->data_type = EavAttribute::DATA_TYPE_INT;
 * $attribute->save(); // Call the method EavAttribute::save()
 *
 * $set = new EavSet();
 * $set->name = 'Set';
 * $set->addEavAttribute($attribute);
 * $set->save();
 * </pre>
 *
 * You also can add an existing attribute to a set:
 * <pre>
 * $set = new EavSet();
 * $set->name = 'Set';
 * $set->addEavAttribute(EavAttribute::model()->findByPk(1)); //Adding an instance of EavAttribute
 * $set->addEavAttribute(5); //You can specify a primary key of an attribute you want to add
 * $set->save();
 * </pre>
 *
 * To remove an attribute from a set you must call the method EavSet::removeEavAttribute() and specify an attribute
 * that must be removed:
 * <pre>
 * $set = EavSet::model()->findByPk(1);
 * $set->removeEavAttribute(5); //Primary key of an attribute that must be removed
 * $set->removeEavAttribute(EavAttribute::model()->findByPk(2)); // Or an instance of the class EavAttribute
 * $set->save();
 * </pre>
 *
 * The following code fragment shows how to delete an existing set of EAV-attributes:
 * <pre>
 * $set = EavSet::model()->findByPk(1);
 * $set->delete();
 * </pre>
 * To delete an existing set of EAV-attributes you also can call methods CActiveRecord::deleteAll(),
 * CActiveRecord::deleteByPk(), CActiveRecord::deleteAllByAttributes(), these methods are available in the class
 * EavAttribute because it is derived from CActiveRecord.
 * Note, the set of EAV-attributes cannot be deleted if some records (EavActiveRecord) are referenced
 * to this set (foreign key constraint).
 *
 *
 * @property integer $id Primary surrogate key.
 * @property string $name Name the set of attributes.
 *
 * @version 1.0.0
 */
class EavSet extends CActiveRecord
{
    private $addedAttributes = array();
    
    private $removedAttributes = array();
    
    
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    
    public function tableName()
    {
        return '{{eav_set}}';
    }


    public function rules()
    {
        return array(
            array('name', 'required')
        );
    }
    
    
    public function relations()
    {
        return array(
            EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME => array(self::MANY_MANY, 
                'EavAttribute', 'eav_attribute_set(eav_set_id, eav_attribute_id)', 'index' => 'id',
                'alias' => EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME)
        );
    }


    protected function afterSave()
    {
        if (!empty($this->removedAttributes))
        {
            $this->getDbConnection()->createCommand()->delete('{{eav_attribute_set}}',
                array('and', 'eav_set_id = :eav_set_id', array('in', 'eav_attribute_id', $this->removedAttributes)),
                array(':eav_set_id' => $this->id));
        }

        if (!empty($this->addedAttributes))
        {
            $rows = array();

            if ($this->getIsNewRecord())
            {
                $weight = 0;
            }
            else
            {
                $weight = $this->getMaxWeight($this->id);
            }

            foreach ($this->addedAttributes as $attribute)
            {
                if ($attribute instanceof EavAttribute)
                {
                    if ($attribute->getIsNewRecord())
                    {
                        if ($attribute->save())
                        {
                            $rows[] = array('eav_attribute_id' => $attribute->id, 'eav_set_id' => $this->id, 'weight' => ++$weight);
                        }
                    }
                    else
                    {
                        $rows[] = array('eav_attribute_id' => $attribute->id, 'eav_set_id' => $this->id, 'weight' => ++$weight);
                    }
                }
                else
                {
                    $rows[] = array('eav_attribute_id' => $attribute, 'eav_set_id' => $this->id, 'weight' => ++$weight);
                }
            }
            $this->getDbConnection()->getCommandBuilder()
                ->createMultipleInsertCommand('{{eav_attribute_set}}', $rows)->execute();
        }

        $this->addedAttributes = array();
        $this->removedAttributes = array();
        $this->refresh();
    }
    
    
    /**
     * Adds a new attribute to the set.
     * @param mixed $attribute It must be either an instance of EavAttribute class or the primary key of an attribute
     * which must be added.
     * @return EavSet
     * @throws CException
     */
    public function addEavAttribute($attribute)
    {
        if (!($attribute instanceof EavAttribute) 
                && !ctype_digit($attribute) && !is_int($attribute))
        {
            throw new CException('Argument 1 passed to ' . __METHOD__ . '()'
                    . ' must be an instance of EavAttribute class or integer.');
        }
        
        if ($this->unsetRemovedAttribute($attribute))
        {
            return $this;
        }
        
        $this->setDirtyAttribute($attribute);
        return $this;
    }
    
    
    /**
     * Removes the given attribute from the set.
     * @param mixed $attribute It must be either an instance of EavAttribute class or the primary key of an attribute
     * which must be removed.
     * @return EavSet
     * @throws CException
     */
    public function removeEavAttribute($attribute)
    {
        if (!($attribute instanceof EavAttribute) 
                && !ctype_digit($attribute) && !is_int($attribute))
        {
            throw new CException('Argument 1 passed to ' . __METHOD__ . '()'
                    . ' must be an instance of EavAttribute class or integer.');
        }
        
        if ($this->unsetAddedAttribute($attribute))
        {
            return $this;
        }
        
        $this->setRemovedAttribute($attribute);
        return $this;
    }


    /**
     * Returns the maximum weight of an attribute in the given set.
     * @param integer $id The primary key of the set.
     * @return integer Maximum weight of an attribute in the given set.
     */
    public function getMaxWeight($id)
    {
        $weight = $this->getDbConnection()->createCommand()->select('MAX(weight)')
            ->from('{{eav_attribute_set}}')->where('eav_set_id = :id', array(':id' => $id))->queryScalar();
        return $weight === false ? 0 : (int) $weight;
    }
    
    
    /**
     * Sets an attribute which must be added to the set.
     * @param mixed $attribute
     */
    private function setDirtyAttribute($attribute)
    {
        if (!$this->hasAddedAttribute($attribute) && !$this->hasStoredAttribute($attribute))
        {
            $this->addedAttributes[] = $attribute;
        }
    }
    
    
    /**
     * Unsets an attribute which was set for adding to the set.
     * @param mixed $attribute
     * @return boolean
     */
    private function unsetAddedAttribute($attribute)
    {
        if ($attribute instanceof EavAttribute)
        {
            foreach ($this->addedAttributes as $key => $value)
            {
                if ($value instanceof EavAttribute)
                {
                    if ($value == $attribute)
                    {
                        unset($this->addedAttributes[$key]);
                        return true;
                    }
                }
                else
                {
                    if (!$attribute->getIsNewRecord())
                    {
                        if ($value == $attribute->id)
                        {
                            unset($this->addedAttributes[$key]);
                            return true;
                        }
                    }
                }
            }
        }
        else
        {
            foreach ($this->addedAttributes as $key => $value)
            {
                if ($value instanceof EavAttribute)
                {
                    if (!$value->getIsNewRecord())
                    {
                        if ($value->id == $attribute)
                        {
                            unset($this->addedAttributes[$key]);
                            return true;
                        }
                    }
                }
                else
                {
                    if ($value == $attribute)
                    {
                        unset($this->addedAttributes[$key]);
                        return true;
                    }
                }
            }
        }
  
        return false;
    }
    
    
    /**
     * Sets an attribute which must be deleted from the set.
     * @param mixed $attribute
     */
    private function setRemovedAttribute($attribute)
    {
        if (empty($this->removedAttributes))
        {
            if ($attribute instanceof EavAttribute)
            {
                if (!$attribute->getIsNewRecord())
                {
                    $this->removedAttributes[] = $attribute->id;
                }
            }
            else
            {
                $this->removedAttributes[] = $attribute;
            }
        }
        else
        {
            if ($attribute instanceof EavAttribute)
            {
                if (!$attribute->getIsNewRecord())
                {
                    if (!in_array($attribute->id, $this->removedAttributes))
                    {
                        $this->removedAttributes[] = $attribute->id;
                    }
                }
            }
            else
            {
                if (!in_array($attribute, $this->removedAttributes))
                {
                    $this->removedAttributes[] = $attribute;
               }
            }
        }
    }
    
    
    /**
     * Unsets an attribute which was set for removing from the set.
     * @param mixed $attribute
     * @return boolean
     */
    private function unsetRemovedAttribute($attribute)
    {
        if ($attribute instanceof EavAttribute)
        {
            foreach ($this->removedAttributes as $key => $value)
            {
                if ($value instanceof EavAttribute)
                {
                    if ($value == $attribute)
                    {
                        unset($this->removedAttributes[$key]);
                        return true;
                    }
                }
                else
                {
                    if (!$attribute->getIsNewRecord())
                    {
                        if ($value == $attribute->id)
                        {
                            unset($this->removedAttributes[$key]);
                            return true;
                        }
                    }
                }
            }
        }
        else
        {
            foreach ($this->removedAttributes as $key => $value)
            {
                if ($value instanceof EavAttribute)
                {
                    if (!$value->getIsNewRecord())
                    {
                        if ($value->id == $attribute)
                        {
                            unset($this->removedAttributes[$key]);
                            return true;
                        }
                    }
                }
                else
                {
                    if ($value == $attribute)
                    {
                        unset($this->removedAttributes[$key]);
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    
    /**
     * Checks if the given attribute is already added to EavSet::$addedAttributes.
     * @param mixed $attribute
     * @return boolean true if the given attribute is already added.
     */
    private function hasAddedAttribute($attribute)
    {
        if ($attribute instanceof EavAttribute)
        {
            if ($attribute->getIsNewRecord())
            {
                foreach ($this->addedAttributes as $attr)
                {
                    if ($attr instanceof EavAttribute)
                    {
                        if ($attr == $attribute)
                        {
                            return true;
                        }
                    }
                }
                
                return false;
            }
            
            foreach ($this->addedAttributes as $attr)
            {
                if ($attr instanceof EavAttribute)
                {
                    if (!$attr->getIsNewRecord())
                    {
                        if ($attr == $attribute)
                        {
                            return true;
                        }
                    }
                }
                else
                {
                    if ($attr == $attribute->id)
                    {
                        return true;
                    }
                }
            }
            
            return false;
        }
    }
    
    
    /**
     * Checks if the given attribute is already saved.
     * @param mixed $attribute
     * @return boolean true if the given attribute is already saved.
     */
    private function hasStoredAttribute($attribute)
    {
        if ($this->getIsNewRecord())
        {
            return false;
        }
        
        if ($attribute instanceof EavAttribute)
        {
            if ($attribute->getIsNewRecord())
            {
                return false;
            }
            
            foreach ($this->{EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME} as $attr)
            {
                if ($attr->id == $attribute->id)
                {
                    return true;
                }
            }
            
            return false;
        }
        else
        {
            foreach ($this->{EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME} as $attr)
            {
                if ($attr->id == $attribute)
                {
                    return true;
                }
            }
            return false;
        }
    }
}

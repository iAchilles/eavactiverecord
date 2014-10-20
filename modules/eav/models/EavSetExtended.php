<?php
/**
 * EavSetExtended class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/**
 * EavSetExtended class
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class EavSetExtended extends EavSet
{
    private $attachedEavAttributes;

    private $orderedEavAttributes;

    private $oldEavAttributes;


    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function attributeLabels()
    {
        return array(
            'name' => Yii::t('EavModule.eavactiverecord', 'Name'),
            'attachedEavAttributes' => Yii::t('EavModule.eavactiverecord', 'EAV attributes'),
        );
    }


    protected function beforeSave()
    {
        if ($this->getIsNewRecord())
        {
            foreach ($this->attachedEavAttributes as $attribute)
            {
                $this->addEavAttribute($attribute);
            }
        }
        else
        {
            $this->oldEavAttributes = array_keys($this->getEavAttributes());
            $new = $this->attachedEavAttributes;
            $this->orderedEavAttributes = !is_null($this->orderedEavAttributes)
                ? array_map('intval', $this->orderedEavAttributes) : array();
            $delete = array_diff($this->oldEavAttributes, $new);
            $insert = array_diff($new, $this->oldEavAttributes);

            foreach ($delete as $attribute)
            {
                $this->removeEavAttribute($attribute);
                unset($this->orderedEavAttributes[array_search($attribute, $this->orderedEavAttributes)]);
                unset($this->oldEavAttributes[array_search($attribute, $this->oldEavAttributes)]);
            }

            foreach($insert as $attribute)
            {
                $this->addEavAttribute($attribute);
            }

        }

        return parent::beforeSave();
    }


    protected function afterSave()
    {
        parent::afterSave();

        if (!empty($this->orderedEavAttributes))
        {
            if ($this->oldEavAttributes !== $this->orderedEavAttributes)
            {
                $this->updateEavAttributeOrder($this->orderedEavAttributes);
            }
        }
    }


    public function getAttachedEavAttributes()
    {
        if (is_null($this->attachedEavAttributes))
        {
            $list = array_keys($this->getEavAttributes());
            $this->attachedEavAttributes = $list;
        }

        return $this->attachedEavAttributes;
    }


    public function setAttachedEavAttributes($values)
    {
        $this->attachedEavAttributes = $values;
    }


    public function setOrderedEavAttributes($values)
    {
        $this->orderedEavAttributes = $values;
    }


    public function getEavAttributeLabels()
    {
        $data = $this->dbConnection->createCommand()->select('id, name')->from(EavAttribute::model()->tableName())
            ->queryAll();
        if (empty($data))
        {
            return array();
        }

        $list = array();
        foreach ($data as $row)
        {
            $list[$row['id']] = $row['name'];
        }

        return $list;
    }
} 
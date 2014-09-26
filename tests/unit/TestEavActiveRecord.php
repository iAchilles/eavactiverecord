<?php
/**
 * TestEavActiveRecord class for unit testing.
 *
 */
class TestEavActiveRecord extends EavActiveRecord
{
    public $attr1;
    public $attr2;
    public $attr3;
    public $attr4;
    public $departement_name;
    public $firstName;
    public $LastName;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    
    public function tableName()
    {
        return '{{eav_test_entity}}';
    }


    public function rules()
    {
        return array(
            array('name', 'length', 'min' => 3, 'max' => 255),
            array('attr2,attr1','numerical','max'=>5),
            array('attr1','required'),
            array('attr3', 'unsafe'),
        );
    }
    
    
    public function attributeLabels()
    {
        return array('id' => 'Primary key', 'name' => 'Title');
    }


    protected function afterFind()
    {
        $this->LastName = $this->name . '-' . $this->id;
        if ($this->getIsEavEnabled())
        {
            if ($this->hasEavAttribute('datetimeSingle'))
            {
                $this->LastName .= '-' . $this->datetimeSingle;
            }
            $this->LastName .= '-' . 'eavEnabled';
        }
        parent::afterFind();
    }
}

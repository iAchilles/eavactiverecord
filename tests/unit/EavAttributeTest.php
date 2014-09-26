<?php
/**
 * EavAttributeTest
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class EavAttributeTest extends CDbTestCase
{
    public $fixtures = array(
        'eav_attribute' => ':eav_attribute',
        'eav_attribute_set' => ':eav_attribute_set',
        'eav_attribute_date' => ':eav_attribute_date',
    );


    public function testSave()
    {
        $model = new EavAttribute();
        $model->name = 25;
        $model->label = 'Test Attribute';
        $model->data_type = EavAttribute::DATA_TYPE_INT;
        $model->type = EavAttribute::TYPE_SINGLE;
        $model->setRules(array('numeric' => array('min' => 2, 'max' => 10, 'integerOnly' => true)));
        $this->assertFalse($model->save());
        $this->assertTrue($model->hasErrors('name'));
        $this->assertFalse($model->hasErrors('data_type'));
        $this->assertFalse($model->hasErrors('type'));
        $this->assertFalse($model->hasErrors('label'));
        $this->assertFalse($model->hasErrors('data'));
        $model->name = '25g';
        $this->assertFalse($model->save());
        $this->assertTrue($model->hasErrors('name'));
        $model->name = '_25g';
        $this->assertTrue($model->save());
        $this->assertFalse($model->hasErrors('name'));

        $model = new EavAttribute();
        $model->name = 'lbd_4_f';
        $model->data_type = 'EavActiveRecord';
        $model->type = (string) EavAttribute::TYPE_MULTIPLE;
        $this->assertFalse($model->save());
        $this->assertFalse($model->hasErrors('name'));
        $this->assertTrue($model->hasErrors('data_type'));
        $this->assertFalse($model->hasErrors('type'));
        $this->assertFalse($model->hasErrors('label'));
        $this->assertFalse($model->hasErrors('data'));
        $model->data_type = 'EavActiveRecordh';
        $this->assertFalse($model->save());
        $this->assertTrue($model->hasErrors('data_type'));
        $model->data_type = EavAttribute::DATA_TYPE_INT;
        $this->assertTrue($model->save());

        $model = new EavAttribute();
        $model->name = 'attrAttr';
        $model->data_type = EavAttribute::DATA_TYPE_INT;
        $model->type = 4;
        $this->assertFalse($model->save());
        $this->assertTrue($model->hasErrors('type'));
        $model->type = EavAttribute::TYPE_MULTIPLE;
        $this->assertTrue($model->save());

        $model = new EavAttribute();
        $this->assertFalse($model->save());
        $this->assertTrue($model->hasErrors('name'));
        $this->assertTrue($model->hasErrors('data_type'));
        $this->assertTrue($model->hasErrors('type'));
        $this->assertFalse($model->hasErrors('label'));
        $this->assertFalse($model->hasErrors('data'));

        $model = EavAttribute::model()->findByPk(1);
        $model->data_type = EavAttribute::DATA_TYPE_INT;
        $this->assertFalse($model->save());
        $this->assertTrue($model->hasErrors('data_type'));
        $model->data_type = EavAttribute::DATA_TYPE_DATETIME;
        $this->assertTrue($model->save());

        Yii::app()->fixture->load($this->fixtures);
    }


    public function testSerializeUnserialize()
    {
        $model = new EavAttribute();
        $s = serialize($model);
        $this->assertNull(unserialize($s));
        $model = EavAttribute::model()->findByPk(1);
        $this->assertEquals('datetimeSingle', $model->name);
        $s = serialize($model);
        $d = unserialize($s);
        $this->assertEquals('datetimeSingle', $d->unserializedObject->name);
    }


    public function testSetRules()
    {
        $model = new EavAttribute();
        $model->setRules(array('unsafe', 'numeric' => array('integerOnly' => true)));
        $rules = array('numeric' => array('integerOnly' => true), 'unsafe' => array());
        $this->assertEquals($rules, $model->getEavValidatorList());
    }


    public function testGetEavValidatorList()
    {
        $model = EavAttribute::model()->findByPk(1);
        $rules = array('length' => array('max' => 5, 'min' => 1), 'required' => array());
        $this->assertEquals($rules, $model->getEavValidatorList());
    }


    public function testGetEavAttributesException()
    {
        $model = new EavAttribute();
        $this->setExpectedException('CException');
        $model->getEavAttributes('name');
    }


    public function testGetEavAttributes()
    {
        $model = new EavAttribute();
        $attrs = array('datetimeSingle');
        $attributes = $model->getEavAttributes($attrs);
        $this->assertEquals(1, count($attributes));
        $this->assertArrayHasKey('datetimeSingle', $attributes);
        $this->assertEquals(1, $attributes['datetimeSingle']->id);
        $attributes = $model->getEavAttributes(array('intSingle', 'varcharMultiple'));
        $this->assertArrayNotHasKey('datetimeSingle', $attributes);
        $this->assertArrayHasKey('intSingle', $attributes);
        $this->assertArrayHasKey('varcharMultiple', $attributes);
    }


    public function testDelete()
    {
        $query1 = Yii::app()->db->createCommand()->select('eav_attribute_id')->from('eav_attribute_set')
            ->where('eav_attribute_id = :id')->queryScalar(array(':id' => 1));
        $query2 = Yii::app()->db->createCommand()->select('eav_attribute_id')->from('eav_attribute_date')
            ->where('eav_attribute_id = :id')->queryAll(true, array(':id' => 1));
        $this->assertEquals(1, $query1);
        $this->assertEquals(2, count($query2));
        $model = EavAttribute::model()->findByPk(1);
        $this->assertInstanceOf('EavAttribute', $model);
        $this->assertTrue($model->delete());
        $query1 = Yii::app()->db->createCommand()->select('eav_attribute_id')->from('eav_attribute_set')
            ->where('eav_attribute_id = :id')->queryScalar(array(':id' => 1));
        $query2 = Yii::app()->db->createCommand()->select('eav_attribute_id')->from('eav_attribute_date')
            ->where('eav_attribute_id = :id')->queryAll(true, array(':id' => 1));
        $this->assertFalse($query1);
        $this->assertEquals(0, count($query2));
        $model = EavAttribute::model()->findByPk(1);
        $this->assertNull($model);
        Yii::app()->fixture->load($this->fixtures);
    }

} 
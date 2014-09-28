<?php
 
/**
 * EavSetTest
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 */
class EavSetTest extends CDbTestCase
{
    public $fixtures = array(
        'eav_set' => ':eav_set',
        'eav_attribute' => ':eav_attribute',
        'eav_attribute_set' => ':eav_attribute_set',
        'eav_test_entity' => ':eav_test_entity',
        'eav_attribute_date' => ':eav_attribute_date',
        'eav_attribute_varchar' => ':eav_attribute_varchar',
    );


    public function testAddEavAttributeException1()
    {
        $model = new EavSet();
        $this->setExpectedException('CException');
        $model->addEavAttribute('23f');
    }


    public function testAddEavAttributeException2()
    {
        $model = new EavSet();
        $this->setExpectedException('CException');
        $model->addEavAttribute(new EavSet());
    }


    public function testAddEavAttribute()
    {

        $model = new EavSet();
        $model->addEavAttribute(1);
        $this->assertFalse($model->save());
        $model->name = 'TestSet';
        $this->assertTrue($model->save());
        $attr = $model->getRelated(EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME);
        $this->assertEquals('datetimeSingle', $attr[1]->name);
        $model->addEavAttribute(1);
        $this->assertTrue($model->save());
        $attr = $model->getRelated(EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME);
        $this->assertEquals(1, count($attr));

        $model = new EavSet();
        $model->name = 'Test111';
        $model->addEavAttribute(1);
        $model->addEavAttribute(EavAttribute::model()->findByPk(2));
        $attr = new EavAttribute();
        $attr->name = 'testattr1';
        $attr->type = EavAttribute::TYPE_SINGLE;
        $attr->data_type = EavAttribute::DATA_TYPE_INT;
        $model->addEavAttribute($attr);
        $attr2 = new EavAttribute();
        $attr2->name = 'testre';
        $model->addEavAttribute($attr2);
        $this->assertTrue($model->save());
        $attr = $model->getRelated(EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME);
        $this->assertEquals('datetimeSingle', $attr[1]->name);
        $this->assertEquals(3, count($attr));
        $this->assertEquals('varcharMultiple', $attr[2]->name);
        $this->assertEquals('testattr1', $attr[5]->name);

        Yii::app()->fixture->load($this->fixtures);
    }


    public function testRemoveEavAttributeException1()
    {
        $model = new EavSet();
        $this->setExpectedException('CException');
        $model->removeEavAttribute('23f');
    }


    public function testRemoveEavAttributeException2()
    {
        $model = new EavSet();
        $this->setExpectedException('CException');
        $model->removeEavAttribute(EavActiveRecord::model());
    }


    public function testRemoveEavAttribute()
    {
        $model = EavSet::model()->findByPk(2);
        $attributes = $model->getRelated(EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME);
        $this->assertEquals(2, count($attributes));
        $this->assertEquals('datetimeSingle', $attributes[1]->name);
        $this->assertEquals('varcharMultiple', $attributes[2]->name);
        $model->removeEavAttribute(1);
        $model->removeEavAttribute($attributes[2]);
        $this->assertTrue($model->save());
        $attributes = $model->getRelated(EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME);
        $this->assertEquals(0, count($attributes));
        Yii::app()->fixture->load($this->fixtures);
    }


    public function testRemoveAddEavAttribute()
    {
        $model = new EavSet();
        $model->name = 'fsf';
        $model->addEavAttribute(1);
        $model->removeEavAttribute(EavAttribute::model()->findByPk(1));
        $model->addEavAttribute(EavAttribute::model()->findByPk(1));
        $model->removeEavAttribute(1);
        $this->assertTrue($model->save());
        $attributes = $model->getRelated(EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME);
        $this->assertEquals(0, count($attributes));

        $model = EavSet::model()->findByPk(2);
        $model->removeEavAttribute(1);
        $model->addEavAttribute(EavAttribute::model()->findByPk(1));
        $model->removeEavAttribute(EavAttribute::model()->findByPk(2));
        $model->addEavAttribute(2);
        $model->addEavAttribute(3);
        $this->assertTrue($model->save());
        $attributes = $model->getRelated(EavActiveRecord::EAV_ATTRIBUTE_RELATION_NAME);
        $this->assertEquals(3, count($attributes));
        $this->assertArrayHasKey(1, $attributes);
        $this->assertArrayHasKey(2, $attributes);
        $this->assertArrayHasKey(3, $attributes);

        Yii::app()->fixture->load($this->fixtures);
    }


    public function testGetMaxWeight()
    {
        $model = EavSet::model()->findByPk(2);
        $this->assertEquals(2, $model->getMaxWeight());
    }


    public function testUpdateEavAttributeOrder()
    {
        $model = EavSet::model()->findByPk(2);
        $attr = $model->getEavAttributes();
        $keys = array_keys($attr);
        $this->assertEquals(1, $keys[0]);
        $this->assertEquals(2, $keys[1]);
        $model->updateEavAttributeOrder(array(2,1));


        $model = EavSet::model()->findByPk(2);
        $attr = $model->getEavAttributes();
        $keys = array_keys($attr);
        $this->assertEquals(2, $keys[0]);
        $this->assertEquals(1, $keys[1]);

        Yii::app()->fixture->load($this->fixtures);
    }
} 
<?php
/**
 * TestEavActiveRecordTest class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
*/

require_once('TestEavActiveRecord.php');

/**
 * TestEavActiveRecordTest
 *
 */
class TestEavActiveRecordTest extends CDbTestCase
{
    private $dbConnection;

    public $fixtures = array(
        'eav_set' => ':eav_set',
        'eav_attribute' => ':eav_attribute',
        'eav_attribute_set' => ':eav_attribute_set',
        'eav_attribute_date' => ':eav_attribute_date',
        'eav_attribute_varchar' => ':eav_attribute_varchar',
        'eav_test_entity' => ':eav_test_entity',
    );


    protected function setUp()
    {
        parent::setUp();
        foreach ($this->fixtures as $key => $value)
        {
            Yii::app()->db->getSchema()->resetSequence(Yii::app()->db->getSchema()->getTable($key));
        }
    }


    public function tearDown()
    {
        Yii::app()->db->active = false;
    }


    public function testSetAttributes()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 4;

        $model->attributes = array('intSingle' => 1, 'intMultiple' => array(1,2,3), 'attr3' => 'abc', 'attr1' => 3);
        $this->assertNull($model->intSingle);
        $this->assertContains(1, $model->intMultiple);
        $this->assertContains(2, $model->intMultiple);
        $this->assertContains(3, $model->intMultiple);
        $this->assertEquals(3, $model->attr1);
        $this->assertNull($model->attr3);
        $model->attr3 = 4;
        $model->intSingle = 1;
        $this->assertEquals(4, $model->attr3);
        $this->assertEquals(1, $model->intSingle);
    }


    public function testDeleteWithEavAttributesEavDisabled()
    {
        $model = TestEavActiveRecord::model()->findByPk(1);
        $this->setExpectedException('CException');
        $model->deleteWithEavAttributes();
    }


    public function testDeleteWithEavAttributes()
    {
        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(1);
        $query1 = Yii::app()->db->createCommand()->select()->from(EavValue::model('DatetimeDataType')->tableName())
            ->where(array('and', 'entity = :entity', 'entity_id = :eid', 'eav_attribute_id = :aid'),
                array(':entity' => $model->getEntity(), ':eid' => $model->id, ':aid' => 1))
            ->queryAll();
        $this->assertEquals(1, count($query1));
        $this->assertTrue($model->deleteWithEavAttributes());
        $query1 = Yii::app()->db->createCommand()->select()->from(EavValue::model('DatetimeDataType')->tableName())
            ->where(array('and', 'entity = :entity', 'entity_id = :eid', 'eav_attribute_id = :aid'),
                array(':entity' => $model->getEntity(), ':eid' => $model->id, ':aid' => 1))
            ->queryAll();
        $this->assertEquals(0, count($query1));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(1);
        $this->assertNull($model);
    }


    public function testSetEavAttributeNewRecordEavDisabled()
    {
        $model = new TestEavActiveRecord();
        $this->setExpectedException('CException');
        $model->setEavAttribute('name', 'value');
    }


    public function testSetNotExistingEavAttributeNewRecordEavEnabled1()
    {
        $model = new TestEavActiveRecord();

        $this->setExpectedException('CException');
        $model->datetimeSingle = 1;
    }


    public function testSetNotExistingEavAttributeNewRecordEavEnabled2()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 3;
        $this->assertFalse($model->setEavAttribute('datetimeSingle', 1));
    }


    public function testSetExistingEavAttributeNewRecordEavEnabled1()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 1;

        $model->datetimeSingle = 1;
    }


    public function testSetExistingEavAttributeNewRecordEavEnabled2()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 1;

        $this->assertTrue($model->setEavAttribute('datetimeSingle', 1));
    }


    public function testSetEavAttributeEavDisabled()
    {
        $model = TestEavActiveRecord::model()->findByPk(1);
        $this->setExpectedException('CException');
        $model->setEavAttribute('datetimeSingle', 'value');
    }


    public function testSetEavAttributeExistingRecord()
    {
        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(1);
        $model->datetimeSingle = null;
        $this->assertFalse(isset($model->datetimeSingle));

        $model = TestEavActiveRecord::model()->withEavAttributes(true)->findByPk(1);
        $model->setEavAttribute('datetimeSingle', null);
        $value = $model->getEavAttribute('datetimeSingle');
        $this->assertFalse(isset($value));
    }


    public function testGetEavAttributeNewRecordEavDisabled()
    {
        $model = new TestEavActiveRecord();
        $this->setExpectedException('CException');
        $model->getEavAttribute('datetimeSingle');
    }

    public function testGetNotExistingEavAttributeNewRecordEavEnabled1()
    {
        $model = new TestEavActiveRecord();

        $this->setExpectedException('CException');
        $model->datetimeSingle;
    }


    public function testGetNotExistingEavAttributeNewRecordEavEnabled2()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 1;
        $this->assertNull($model->getEavAttribute('datetimeSingle'));
    }


    public function testGetExistingEavAttributeNewRecordEavEnabled1()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 1;

        $this->assertNull($model->getEavAttribute('datetimeSingle'));
    }


    public function testGetExistingEavAttributeNewRecordEavEnabled2()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 1;

        $this->assertNull($model->datetimeSingle);
    }


    public function testGetEavAttributeEavDisabled()
    {
        $model = TestEavActiveRecord::model()->findByPk(1);
        $this->setExpectedException('CException');
        $model->getEavAttribute('datetimeSingle');
    }


    public function testGetEavAttributeExistingRecord()
    {
        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(1);
        $this->assertEquals('2012-11-01 10:10:25', $model->datetimeSingle);

        $model = TestEavActiveRecord::model()->withEavAttributes(true)->findByPk(1);
        $this->assertEquals('2012-11-01 10:10:25', $model->datetimeSingle);

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(1);
        $this->assertEquals('2012-11-01 10:10:25', $model->getEavAttribute('datetimeSingle'));

        $model = TestEavActiveRecord::model()->withEavAttributes(true)->findByPk(1);
        $this->assertEquals('2012-11-01 10:10:25', $model->getEavAttribute('datetimeSingle'));
    }








    public function testHasEavAttributeNewRecordEavDisabled()
    {
        $model = new TestEavActiveRecord();
        $this->setExpectedException('CException');
        $model->hasEavAttribute('datetimeSingle');
    }


    public function testEavAttributeNamesNewRecordEavDisabled()
    {
        $model = new TestEavActiveRecord();
        $this->setExpectedException('CException');
        $model->eavAttributeNames();
    }


    public function testGetEavValidatorListNewRecordEavDisabled()
    {
        $model = new TestEavActiveRecord();
        $this->setExpectedException('CException');
        $model->getEavValidatorList();
    }


    public function testGetEavValidatorsNewRecordEavDisabled()
    {
        $model = new TestEavActiveRecord();
        $this->setExpectedException('CException');
        $model->getEavValidators();
    }


    public function testCreateEavValidatorsNewRecordEavDisabled()
    {
        $model = new TestEavActiveRecord();
        $this->setExpectedException('CException');
        $model->createEavValidators();
    }


    public function testHasEavAttributeEavDisabled()
    {
        $model = TestEavActiveRecord::model()->findByPk(1);
        $this->setExpectedException('CException');
        $model->datetimeSingle;
    }


    public function testEavAttributeNamesEavDisabled()
    {
        $model = TestEavActiveRecord::model()->findByPk(1);
        $this->setExpectedException('CException');
        $model->eavAttributeNames();
    }


    public function testGetEavValidatorListEavDisabled()
    {
        $model = TestEavActiveRecord::model()->findByPk(1);
        $this->setExpectedException('CException');
        $model->getEavValidatorList();
    }


    public function testGetEavValidatorsEavDisabled()
    {
        $model = TestEavActiveRecord::model()->findByPk(1);
        $this->setExpectedException('CException');
        $model->getEavValidators();
    }


    public function testGetSetEavAttributeNewRecordEavEnabled()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 1;

        $this->assertNull($model->datetimeSingle);
        $this->assertNull($model->getEavAttribute('datetimeSingle'));
        $model->datetimeSingle = 1;
        $this->assertEquals(1, $model->datetimeSingle);
        $model->setEavAttribute('datetimeSingle', 2);
        $this->assertEquals(2, $model->getEavAttribute('datetimeSingle'));
    }


    public function testIssetEmptyEavAttributeNewRecordEavEnabled()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 1;

        $value = $model->getEavAttribute('datetimeSingle');
        $this->assertFalse(isset($value));
        $this->assertFalse(isset($model->datetimeSingle));
        $this->assertTrue(empty($value));
        $this->assertTrue(empty($model->datetimeSingle));
        $model->datetimeSingle = 1;
        $this->assertTrue(isset($model->datetimeSingle));
        $this->assertFalse(empty($model->datetimeSingle));
        unset($model->datetimeSingle);
        $this->assertFalse(isset($model->datetimeSingle));
        $this->assertTrue(empty($model->datetimeSingle));
    }


    public function testGetSafeAttributeNames()
    {
        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(1);
        $array = array_flip($model->getSafeAttributeNames());
        $this->assertArrayHasKey('datetimeSingle', $array);
        $this->assertArrayHasKey('name', $array);

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;

        $array = array_flip($model->getSafeAttributeNames());
        $this->assertArrayHasKey('datetimeSingle', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('varcharMultiple', $array);
    }


    public function testGetEavValidatorList()
    {
        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(1);
        $this->assertEquals(2, $model->getEavValidatorList()->count());

        if ($model->getEavValidatorList()->itemAt(0) instanceof CStringValidator)
        {
            $this->assertArrayHasKey('datetimeSingle', array_flip($model->getEavValidatorList()->itemAt(0)->attributes));

        }
        else
        {
            $this->assertInstanceOf('CRequiredValidator', $model->getEavValidatorList()->itemAt(0));
            $this->assertArrayHasKey('datetimeSingle', array_flip($model->getEavValidatorList()->itemAt(0)->attributes));
        }

        if ($model->getEavValidatorList()->itemAt(1) instanceof CRequiredValidator)
        {
            $this->assertArrayHasKey('datetimeSingle', array_flip($model->getEavValidatorList()->itemAt(1)->attributes));

        }
        else
        {
            $this->assertInstanceOf('CStringValidator', $model->getEavValidatorList()->itemAt(1));
            $this->assertArrayHasKey('datetimeSingle', array_flip($model->getEavValidatorList()->itemAt(1)->attributes));
        }

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;

        $this->assertEquals(3, $model->getEavValidatorList()->count());

    }


    public function testGetEavAttributesEavDisabled1()
    {
        $this->setExpectedException('CException');
        $model = new TestEavActiveRecord();
        $model->getEavAttributes();
    }


    public function testGetEavAttributesEavDisabled2()
    {
        $this->setExpectedException('CException');
        $model = TestEavActiveRecord::model()->findByPk(1);
        $model->getEavAttributes();
    }


    public function testGetEavAttributesEavEnabled()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 3;
        $this->assertEquals(array(), $model->getEavAttributes());

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 1;

        $this->assertEquals(1, count($model->getEavAttributes()));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;

        $this->assertEquals(2, count($model->getEavAttributes()));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 3;

        $this->assertEquals(0, count($model->getEavAttributes()));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;

        $model->datetimeSingle = 'date';
        $values = $model->getEavAttributes();
        $this->assertArrayHasKey('datetimeSingle', $values);
        $this->assertArrayHasKey('varcharMultiple', $values);
        $this->assertEquals('date', $values['datetimeSingle']);
        $this->assertEquals(array(), $values['varcharMultiple']);
        $model->varcharMultiple = array('one', 'two', 'three');
        $values = $model->getEavAttributes();
        $this->assertEquals(3, count($values['varcharMultiple']));
        $this->assertTrue(in_array('one', $values['varcharMultiple']));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(1);
        $values = $model->getEavAttributes();
        $this->assertEquals('2012-11-01 10:10:25', $values['datetimeSingle']);

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;

        $model->datetimeSingle = 'date';
        $model->varcharMultiple = array('one', 'two', 'three');
        $values = $model->getEavAttributes(array('varcharMultiple'));
        $this->assertEquals(1, count($values));
        $this->assertEquals(2, count($model->getEavAttributes()));
        $this->assertArrayHasKey('varcharMultiple', $values);
        $this->assertArrayNotHasKey('datetimeSingle', $values);

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertEquals(2, count($model->getEavAttributes()));
        $values = $model->getEavAttributes(array('datetimeSingle'));
        $this->assertEquals(1, count($values));
        $this->assertArrayHasKey('datetimeSingle', $values);
    }


    public function testInsertWithEavAttributesEavDisabled()
    {
        $model = new TestEavActiveRecord();
        $this->setExpectedException('CException');
        $model->name = 'test';
        $model->insertWithEavAttributes();
    }


    public function testInsertWithEavAttributes()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;

        $model->name = 'test1';
        $model->datetimeSingle = '2011-10-05 09:10:01';
        $model->varcharMultiple = array(1,2,3,4,5);
        $this->assertTrue($model->insertWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk($model->id);
        $this->assertEquals('test1', $model->name);
        $this->assertEquals('2', $model->eav_set_id);
        $this->assertEquals('2011-10-05 09:10:01', $model->datetimeSingle);
        $this->assertEquals(5, count($model->varcharMultiple));
        $this->assertTrue(in_array('1', $model->varcharMultiple));
        $this->assertTrue(in_array('2', $model->varcharMultiple));
        $this->assertTrue(in_array('3', $model->varcharMultiple));
        $this->assertTrue(in_array('4', $model->varcharMultiple));
        $this->assertTrue(in_array('5', $model->varcharMultiple));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;
        $model->name = 'test2';
        $model->datetimeSingle = '';
        $model->varcharMultiple = array(1,2,3,4,5);
        $this->assertTrue($model->insertWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk($model->id);
        $this->assertEquals('test2', $model->name);
        $this->assertEquals('2', $model->eav_set_id);
        $this->assertNull($model->datetimeSingle);
        $this->assertEquals(5, count($model->varcharMultiple));
        $this->assertTrue(in_array('1', $model->varcharMultiple));
        $this->assertTrue(in_array('2', $model->varcharMultiple));
        $this->assertTrue(in_array('3', $model->varcharMultiple));
        $this->assertTrue(in_array('4', $model->varcharMultiple));
        $this->assertTrue(in_array('5', $model->varcharMultiple));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;
        $model->name = 'test3';
        $model->varcharMultiple = array(1,2,3,4,5);
        $this->assertTrue($model->insertWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk($model->id);
        $this->assertEquals('test3', $model->name);
        $this->assertEquals('2', $model->eav_set_id);
        $this->assertNull($model->datetimeSingle);
        $this->assertEquals(5, count($model->varcharMultiple));
        $this->assertTrue(in_array('1', $model->varcharMultiple));
        $this->assertTrue(in_array('2', $model->varcharMultiple));
        $this->assertTrue(in_array('3', $model->varcharMultiple));
        $this->assertTrue(in_array('4', $model->varcharMultiple));
        $this->assertTrue(in_array('5', $model->varcharMultiple));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;
        $model->name = 'test4';
        $model->varcharMultiple = array(1,'',null,4,5);
        $this->assertTrue($model->insertWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk($model->id);
        $this->assertEquals('test4', $model->name);
        $this->assertEquals('2', $model->eav_set_id);
        $this->assertNull($model->datetimeSingle);
        $this->assertEquals(3, count($model->varcharMultiple));
        $this->assertTrue(in_array('1', $model->varcharMultiple));
        $this->assertTrue(in_array('4', $model->varcharMultiple));
        $this->assertTrue(in_array('5', $model->varcharMultiple));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;
        $model->name = 'test5';
        $model->varcharMultiple = array();
        $this->assertTrue($model->insertWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk($model->id);
        $this->assertEquals('test5', $model->name);
        $this->assertEquals('2', $model->eav_set_id);
        $this->assertNull($model->datetimeSingle);
        $this->assertEquals(0, count($model->varcharMultiple));
        $this->assertEquals(array(), $model->varcharMultiple);

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;
        $model->name = 'test6';
        $this->assertTrue($model->insertWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk($model->id);
        $this->assertEquals('test6', $model->name);
        $this->assertEquals('2', $model->eav_set_id);
        $this->assertNull($model->datetimeSingle);
        $this->assertEquals(array(), $model->varcharMultiple);

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;
        $model->name = 'test7';
        $model->datetimeSingle = '2011-10-05 09:10:01';
        $model->varcharMultiple = array('a', 'b');
        $this->assertTrue($model->insertWithEavAttributes(array('eav_set_id', 'datetimeSingle', 'varcharMultiple')));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk($model->id);
        $this->assertNull($model->name);
        $this->assertEquals('2', $model->eav_set_id);
        $this->assertEquals('2011-10-05 09:10:01', $model->datetimeSingle);
        $this->assertEquals(2, count($model->varcharMultiple));
        $this->assertTrue(in_array('a', $model->varcharMultiple));
        $this->assertTrue(in_array('b', $model->varcharMultiple));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;
        $model->name = 'test8';
        $model->datetimeSingle = '2011-10-05 09:10:01';
        $model->varcharMultiple = array('a', 'b');
        $this->assertTrue($model->insertWithEavAttributes(array('eav_set_id', 'name', 'varcharMultiple')));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk($model->id);
        $this->assertEquals('test8', $model->name);
        $this->assertEquals('2', $model->eav_set_id);
        $this->assertNull($model->datetimeSingle);
        $this->assertEquals(2, count($model->varcharMultiple));
        $this->assertTrue(in_array('a', $model->varcharMultiple));
        $this->assertTrue(in_array('b', $model->varcharMultiple));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;
        $model->name = 'test9';
        $model->datetimeSingle = '2011-10-05 18:10:01';
        $model->varcharMultiple = array('e', 'i');
        $this->assertTrue($model->insertWithEavAttributes(array('name', 'varcharMultiple', 'datetimeSingle')));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk($model->id);
        $this->assertEquals('test9', $model->name);
        $this->assertNull($model->eav_set_id);

        $query1 = Yii::app()->db->createCommand()->select()->from(EavValue::model('DatetimeDataType')->tableName())
            ->where(array('and', 'entity = :entity', 'entity_id = :eid', 'eav_attribute_id = :aid'),
            array(':entity' => $model->getEntity(), ':eid' => $model->id, ':aid' => 1))
            ->queryAll();

        $query2 = Yii::app()->db->createCommand()->select()->from(EavValue::model('VarcharDataType')->tableName())
            ->where(array('and', 'entity = :entity', 'entity_id = :eid', 'eav_attribute_id = :aid'),
                array(':entity' => $model->getEntity(), ':eid' => $model->id, ':aid' => 2))
            ->queryAll();

        $this->assertEquals(0, count($query1));
        $this->assertEquals(0, count($query2));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;
        $model->eav_set_id = 1;
        $model->name = 'test10';
        $model->datetimeSingle = '2011-10-05 18:10:01';
        $this->assertTrue($model->insertWithEavAttributes());

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;
        $model->name = 'test11';
        $model->datetimeSingle = '2011-10-05 18:10:01';
        $model->varcharMultiple = array('e', 'i');
        $this->assertTrue($model->insertWithEavAttributes(array('name', 'datetimeSingle', 'varcharMultiple')));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk($model->id);
        $this->assertEquals('test11', $model->name);
        $this->assertNull($model->eav_set_id);

        $query1 = Yii::app()->db->createCommand()->select()->from(EavValue::model('DatetimeDataType')->tableName())
            ->where(array('and', 'entity = :entity', 'entity_id = :eid', 'eav_attribute_id = :aid'),
                array(':entity' => $model->getEntity(), ':eid' => $model->id, ':aid' => 1))
            ->queryAll();

        $query2 = Yii::app()->db->createCommand()->select()->from(EavValue::model('VarcharDataType')->tableName())
            ->where(array('and', 'entity = :entity', 'entity_id = :eid', 'eav_attribute_id = :aid'),
                array(':entity' => $model->getEntity(), ':eid' => $model->id, ':aid' => 2))
            ->queryAll();

        $this->assertEquals(0, count($query1));
        $this->assertEquals(0, count($query2));
    }


    public function testUpdateWithEavAttributesEavDisabled()
    {
        $model = TestEavActiveRecord::model()->findByPk(1);
        $this->setExpectedException('CException');
        $model->updateWithEavAttributes();
    }


    public function testUpdateWithEavAttributesNewRecord()
    {
        $model = new TestEavActiveRecord();

        $this->setExpectedException('CException');
        $model->updateWithEavAttributes();
    }


    public function testUpdateWithEavAttributes()
    {

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('one', $model->varcharMultiple);
        $this->assertContains('two', $model->varcharMultiple);
        $this->assertContains('three', $model->varcharMultiple);
        $this->assertEquals('2015-11-01 14:10:25', $model->datetimeSingle);
        $this->assertEquals('entity2', $model->name);
        $model->name = 'newname';
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('one', $model->varcharMultiple);
        $this->assertContains('two', $model->varcharMultiple);
        $this->assertContains('three', $model->varcharMultiple);
        $this->assertEquals('2015-11-01 14:10:25', $model->datetimeSingle);
        $this->assertEquals('newname', $model->name);
        $model->name = 'abc';
        $model->datetimeSingle = '2015-11-11 14:10:25';
        $model->varcharMultiple = array('one', 2, 7);
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('one', $model->varcharMultiple);
        $this->assertContains(2, $model->varcharMultiple);
        $this->assertContains(7, $model->varcharMultiple);
        $this->assertEquals('2015-11-11 14:10:25', $model->datetimeSingle);
        $this->assertEquals('abc', $model->name);
        $model->datetimeSingle = '';
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('one', $model->varcharMultiple);
        $this->assertContains(2, $model->varcharMultiple);
        $this->assertContains(7, $model->varcharMultiple);
        $this->assertNull($model->datetimeSingle);
        $this->assertEquals('abc', $model->name);
        $model->datetimeSingle = '2015-11-11 14:10:25';
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('one', $model->varcharMultiple);
        $this->assertContains(2, $model->varcharMultiple);
        $this->assertContains(7, $model->varcharMultiple);
        $this->assertEquals('2015-11-11 14:10:25', $model->datetimeSingle);
        unset($model->datetimeSingle);
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('one', $model->varcharMultiple);
        $this->assertContains(2, $model->varcharMultiple);
        $this->assertContains(7, $model->varcharMultiple);
        $this->assertNull($model->datetimeSingle);
        $model->datetimeSingle = '2015-11-11 14:10:25';
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('one', $model->varcharMultiple);
        $this->assertContains(2, $model->varcharMultiple);
        $this->assertContains(7, $model->varcharMultiple);
        $this->assertEquals('2015-11-11 14:10:25', $model->datetimeSingle);
        $model->datetimeSingle = null;
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('one', $model->varcharMultiple);
        $this->assertContains(2, $model->varcharMultiple);
        $this->assertContains(7, $model->varcharMultiple);
        $this->assertNull($model->datetimeSingle);
        $model->datetimeSingle = '2015-11-11 14:10:25';
        $model->varcharMultiple = '';
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals(array(), $model->varcharMultiple);
        $this->assertEquals('2015-11-11 14:10:25', $model->datetimeSingle);
        $model->varcharMultiple = array(1,2);
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('1', $model->varcharMultiple);
        $this->assertContains('2', $model->varcharMultiple);
        unset($model->varcharMultiple);
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals(array(), $model->varcharMultiple);
        $this->assertEquals('2015-11-11 14:10:25', $model->datetimeSingle);
        $model->varcharMultiple = array(1,2);
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('1', $model->varcharMultiple);
        $this->assertContains('2', $model->varcharMultiple);
        $model->varcharMultiple = null;
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals(array(), $model->varcharMultiple);
        $this->assertEquals('2015-11-11 14:10:25', $model->datetimeSingle);
        $model->varcharMultiple = array('', 5, 'dd', null, '', 'cc');
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('5', $model->varcharMultiple);
        $this->assertContains('dd', $model->varcharMultiple);
        $this->assertContains('cc', $model->varcharMultiple);
        $this->assertEquals(3, count($model->varcharMultiple));
        $this->assertEquals('2015-11-11 14:10:25', $model->datetimeSingle);
        $model->varcharMultiple = array('5', null, 'cc');
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertContains('5', $model->varcharMultiple);
        $this->assertContains('cc', $model->varcharMultiple);
        $this->assertEquals(2, count($model->varcharMultiple));
        $this->assertEquals('2015-11-11 14:10:25', $model->datetimeSingle);
        $this->assertEquals(2, $model->eav_set_id);
        $model->eav_set_id = 1;
        $this->assertTrue($model->updateWithEavAttributes());
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertFalse($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals('2015-11-11 14:10:25', $model->datetimeSingle);

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertFalse($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals('2015-11-11 14:10:25', $model->datetimeSingle);
        $this->assertEquals(1, $model->eav_set_id);
        $model->datetimeSingle = '2016-11-11 14:10:25';
        $model->eav_set_id = 1;
        $this->assertEquals('2016-11-11 14:10:25', $model->datetimeSingle);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertFalse($model->hasEavAttribute('varcharMultiple'));
        $model->eav_set_id = 2;
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals('2016-11-11 14:10:25', $model->datetimeSingle);
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals('2016-11-11 14:10:25', $model->datetimeSingle);
        $this->assertEquals(array(), $model->varcharMultiple);
        $model->varcharMultiple = array(1,2,3);
        $model->name = 'g';
        $this->assertTrue($model->updateWithEavAttributes(array('id')));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals('2016-11-11 14:10:25', $model->datetimeSingle);
        $this->assertEquals(array(), $model->varcharMultiple);
        $this->assertEquals('abc', $model->name);
        $model->varcharMultiple = array(1,2,3);
        $model->name = 'g';
        $this->assertTrue($model->updateWithEavAttributes(array('name')));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals('2016-11-11 14:10:25', $model->datetimeSingle);
        $this->assertEquals(array(), $model->varcharMultiple);
        $this->assertEquals('g', $model->name);
        $model->varcharMultiple = array(1,2,3);
        $this->assertTrue($model->updateWithEavAttributes(array('eav_set_id')));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals('2016-11-11 14:10:25', $model->datetimeSingle);
        $this->assertEquals('g', $model->name);
        $this->assertEquals(array(), $model->varcharMultiple);
        $model->varcharMultiple = array(1,2,3);
        $this->assertTrue($model->updateWithEavAttributes(array('id', 'varcharMultiple')));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals('2016-11-11 14:10:25', $model->datetimeSingle);
        $this->assertEquals('g', $model->name);
        $this->assertEquals(array(), $model->varcharMultiple);
        $model->varcharMultiple = array(1,2,3);
        $this->assertTrue($model->updateWithEavAttributes(array('varcharMultiple', 'eav_set_id')));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue($model->hasEavAttribute('varcharMultiple'));
        $this->assertEquals('2016-11-11 14:10:25', $model->datetimeSingle);
        $this->assertEquals('g', $model->name);
        $this->assertContains('1', $model->varcharMultiple);
        $this->assertContains('2', $model->varcharMultiple);
        $this->assertContains('3', $model->varcharMultiple);
        $model->eav_set_id = null;
        $this->assertTrue($model->updateWithEavAttributes());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertTrue(!$model->hasEavAttribute('datetimeSingle'));
        $this->assertTrue(!$model->hasEavAttribute('varcharMultiple'));

    }


    public function testValidate()
    {
        $model = new TestEavActiveRecord();
        $this->assertFalse($model->validate());
        $model->attr1 = 4;
        $this->assertTrue($model->validate());
        $model->attr1 = 6;
        $this->assertFalse($model->validate());
        $model->attr2 = 6;
        $this->assertFalse($model->validate());
        $model->attr1 = 4;
        $this->assertFalse($model->validate());
        $model->attr2 = 4;
        $this->assertTrue($model->validate());
    }


    function testBeforeValidate()
    {
        $model = new TestEavActiveRecord();
        $model->attr1 = 4;
        $this->assertTrue($model->validate());
        $model->onBeforeValidate = array($this, 'beforeValidate');
        $this->assertFalse($model->validate());
    }


    function beforeValidate($event)
    {
        $event->isValid = false;
    }


    public function testIsAttributeRequired()
    {
        $model = new TestEavActiveRecord();
        $this->assertTrue($model->isAttributeRequired('attr1'));
        $this->assertFalse($model->isAttributeRequired('attr2'));

        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;

        $this->assertTrue($model->isAttributeRequired('attr1'));
        $this->assertFalse($model->isAttributeRequired('attr2'));
        $this->assertTrue($model->isEavAttributeRequired('datetimeSingle'));
        $this->assertFalse($model->isEavAttributeRequired('varcharMultiple'));
        $this->assertTrue($model->isAttributeRequired('datetimeSingle'));
        $this->assertFalse($model->isAttributeRequired('varcharMultiple'));
    }


    public function testIsAttributeSafe()
    {
        $model = new TestEavActiveRecord();
        $this->assertTrue($model->isAttributeSafe('attr1'));
        $this->assertFalse($model->isAttributeSafe('attr3'));
        $this->assertFalse($model->isAttributeSafe('attr4'));
    }


    public function testGetSafeAttributeNamesEavDisabled()
    {
        $model = new TestEavActiveRecord();
        $safeAttributes = $model->getSafeAttributeNames();
        $this->assertContains('attr2', $safeAttributes);
        $this->assertContains('attr1', $safeAttributes);
    }


    public function testValidateEavEnable()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;

        $this->assertFalse($model->validate());
        $model->attr1 = 4;
        $this->assertFalse($model->validate());
        $model->datetimeSingle = 'abc';
        $this->assertTrue($model->validate());
        $model->datetimeSingle = 'abcdef';
        $this->assertFalse($model->validate());
        $model->datetimeSingle = 'abcde';
        $this->assertTrue($model->validate());
        $model->attr1 = 6;
        $this->assertFalse($model->validate());
        $model->attr2 = 6;
        $this->assertFalse($model->validate());
        $model->attr1 = 4;
        $this->assertFalse($model->validate());
        $model->attr2 = 4;
        $this->assertTrue($model->validate());
        $model->varcharMultiple = array('a', '123456789012345', 'ab', 'abc');
        $this->assertFalse($model->validate());
        $model->varcharMultiple = array('ab', '123456789012345', 'ab', 'abc');
        $this->assertFalse($model->validate());
        $model->varcharMultiple = array('abc', '123456789012345', 'ab', 'abc');
        $this->assertFalse($model->validate());
        $model->varcharMultiple = array('abc', '123456789012345', 'abc', 'abc');
        $this->assertTrue($model->validate());
        $model->varcharMultiple = array('abc', '1234567890123456', 'abc', 'abc');
        $this->assertFalse($model->validate());
        $model->varcharMultiple = array('abc', '123456789012345', 'abc', 'abc');
        $this->assertTrue($model->validate());
    }


    public function testFindEavDisable()
    {
        $model = TestEavActiveRecord::model()->find('id = :pk', array(':pk' => 1));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals(1, $model->id);
        $this->assertEquals('entity1-1', $model->LastName);
        $this->assertFalse($model->getIsEavEnabled());
    }

    public function testFind()
    {
        $model = TestEavActiveRecord::model()->withEavAttributes()->find('t.id = :pk', array(':pk' => 1));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals(1, $model->id);
        $this->assertEquals('entity1-1-2012-11-01 10:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
        $this->assertTrue($model->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)->find('t.id = :pk', array(':pk' => 1));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals(1, $model->id);
        $this->assertEquals('entity1-1-2012-11-01 10:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
        $this->assertTrue($model->getIsEavEnabled());

        $criteria = new CDbCriteria();
        $criteria->compare('::datetimeSingle', '2015-11-01 14:10:25');
        $model = TestEavActiveRecord::model()->withEavAttributes()->find($criteria);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals(3, $model->id);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
        $this->assertTrue($model->getIsEavEnabled());

        $criteria = new CDbCriteria();
        $criteria->compare('::datetimeSingle', '2015-11-01 14:10:25');
        $criteria->compare('t.id', 3);
        $model = TestEavActiveRecord::model()->withEavAttributes(true)->find($criteria);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals(3, $model->id);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
        $this->assertTrue($model->getIsEavEnabled());

        $criteria = new CDbCriteria();
        $criteria->compare('::datetimeSingle', '2015-11-01 14:10:25');
        $model = TestEavActiveRecord::model()->find($criteria);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals(3, $model->id);
        $this->assertEquals('entity2-3', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
        $this->assertFalse($model->getIsEavEnabled());

        $criteria = array('condition' => 't.id = :id AND ::datetimeSingle = :d',
                          'params' => array(':id' => 3, ':d' => '2015-11-01 14:10:25'));
        $model = TestEavActiveRecord::model()->find($criteria);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals(3, $model->id);
        $this->assertEquals('entity2-3', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
        $this->assertFalse($model->getIsEavEnabled());

        $criteria = array('condition' => 't.id = :id AND ::datetimeSingle = :d',
                          'params' => array(':id' => 3, ':d' => '2015-11-01 14:10:25'));
        $model = TestEavActiveRecord::model()->withEavAttributes()->find($criteria);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals(3, $model->id);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
        $this->assertTrue($model->getIsEavEnabled());

        $criteria = array('condition' => 't.id = :id AND ::datetimeSingle = :d',
                          'params' => array(':id' => 3, ':d' => '2015-11-01 14:10:25'));
        $model = TestEavActiveRecord::model()->withEavAttributes(true)->find($criteria);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals(3, $model->id);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
        $this->assertTrue($model->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->find('::datetimeSingle = :d', array(':d' => '2089-11-01 14:10:25'));
        $this->assertNull($model);

        $model = TestEavActiveRecord::model()->withEavAttributes()->find('::datetimeSingle = :d',
            array(':d' => '2089-11-01 14:10:25'));
        $this->assertNull($model);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
    }


    public function testFindAllEavDisabled()
    {
        $model = TestEavActiveRecord::model()->findAll();
        $this->assertEquals(6, count($model));
        $this->assertEquals(1, $model[0]->id);
        $this->assertEquals('entity1-1', $model[0]->LastName);
        $this->assertEquals(6, $model[5]->id);
        $this->assertEquals('entity4-6', $model[5]->LastName);
        $this->assertFalse($model[2]->getIsEavEnabled());
    }


    public function testFindAll()
    {
        $model = TestEavActiveRecord::model()->withEavAttributes()->findAll(array('order' => 't.id ASC'));
        $this->assertEquals(6, count($model));
        $this->assertEquals('entity1-1-2012-11-01 10:10:25-eavEnabled', $model[0]->LastName);
        $this->assertEquals('entity4-6-eavEnabled', $model[5]->LastName);
        $this->assertEquals(3, count($model[2]->varcharMultiple));

        $criteria = new CDbCriteria();
        $criteria->addInCondition('::varcharMultiple', array('one', 'two'));
        $model = TestEavActiveRecord::model()->withEavAttributes(false)->findAll($criteria);
        $this->assertEquals(1, count($model));
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model[0]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $criteria = new CDbCriteria();
        $criteria->addInCondition('::varcharMultiple', array('one', 'two'));
        $model = TestEavActiveRecord::model()->withEavAttributes(true)->findAll($criteria);
        $this->assertEquals(1, count($model));
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model[0]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $criteria = new CDbCriteria();
        $criteria->addInCondition('::varcharMultiple', array('one', 'two'));
        $model = TestEavActiveRecord::model()->findAll($criteria);
        $this->assertEquals(1, count($model));
        $this->assertEquals('entity2-3', $model[0]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());


        $criteria = new CDbCriteria();
        $criteria->addInCondition('::varcharMultiple', array('one', 'two'));
        $criteria->compare('t.id', 5);
        $model = TestEavActiveRecord::model()->findAll($criteria);
        $this->assertEquals(0, count($model));
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
    }


    public function testFindAllByAttributes()
    {
        $model = TestEavActiveRecord::model()->findAllByAttributes(array('id' => array(1,2,3)));
        $this->assertEquals(3, count($model));
        $this->assertEquals('entity1.2-2', $model[1]->LastName);
        $this->assertFalse($model[1]->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes()
            ->findAllByAttributes(array('id' => 3), '::varcharMultiple = :v', array(':v' => 'one'));
        $this->assertEquals(1, count($model));
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model[0]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)
            ->findAllByAttributes(array('id' => 3), '::varcharMultiple = :v', array(':v' => 'one'));
        $this->assertEquals(1, count($model));
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model[0]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)
            ->findAllByAttributes(array(), '::varcharMultiple = :v OR ::datetimeSingle = :v1',
                array(':v' => 'one', ':v1' => '2012-11-01 10:10:25'));
        $this->assertEquals(2, count($model));
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model[1]->LastName);
        $this->assertEquals('entity1-1-2012-11-01 10:10:25-eavEnabled', $model[0]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)
            ->findAllByAttributes(array('id' => 5555), '::varcharMultiple = :v OR ::datetimeSingle = :v1',
                array(':v' => 'one', ':v1' => '2012-11-01 10:10:25'));
        $this->assertEquals(0, count($model));
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
    }


    public function testFindAllByPk()
    {
        $model = TestEavActiveRecord::model()->findAllByPk(array(2,3,4));
        $this->assertEquals(3, count($model));
        $this->assertEquals('entity1.2-2', $model[0]->LastName);
        $this->assertEquals('entity2-3', $model[1]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes()
            ->findAllByPk(array(1,3,4), '::datetimeSingle = :v', array(':v' => '2012-11-01 10:10:25'));
        $this->assertEquals(1, count($model));
        $this->assertEquals('entity1-1-2012-11-01 10:10:25-eavEnabled', $model[0]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)
            ->findAllByPk(array(1,3,4), '::datetimeSingle = :v', array(':v' => '2012-11-01 10:10:25'));
        $this->assertEquals(1, count($model));
        $this->assertEquals('entity1-1-2012-11-01 10:10:25-eavEnabled', $model[0]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)
            ->findAllByPk(array(2,3,4), '::datetimeSingle = :v', array(':v' => '2012-11-01 10:10:25'));
        $this->assertEquals(0, count($model));
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
    }


    public function testFindAllBySql()
    {
        $sql = 'SELECT * FROM ' . TestEavActiveRecord::model()->tableName() . ' WHERE id <> :pk';
        $model = TestEavActiveRecord::model()->findAllBySql($sql, array(':pk' => 1));
        $this->assertEquals(5, count($model));
        $this->assertEquals('entity2-3', $model[1]->LastName);

        $model = TestEavActiveRecord::model()->withEavAttributes()->findAllBySql($sql, array(':pk' => 1));
        $this->assertEquals(5, count($model));
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model[1]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)->findAllBySql($sql, array(':pk' => 1));
        $this->assertEquals(5, count($model));
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model[1]->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $sql = 'SELECT * FROM ' . TestEavActiveRecord::model()->tableName() . ' WHERE id = :pk';
        $model = TestEavActiveRecord::model()->withEavAttributes(true)->findAllBySql($sql, array(':pk' => 51));
        $this->assertEquals(0, count($model));
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
    }


    public function testFindByAttributes()
    {
        $model = TestEavActiveRecord::model()->findByAttributes(array('id' => 3));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity2-3', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByAttributes(array('id' => 3));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)->findByAttributes(array('id' => 3));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes()
            ->findByAttributes(array('id' => 3), '::datetimeSingle = :v', array(':v' => '2015-11-01 14:10:25'));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)
            ->findByAttributes(array('id' => 3), '::datetimeSingle = :v', array(':v' => '2015-11-01 14:10:25'));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)
            ->findByAttributes(array('id' => 3), '::datetimeSingle = :v', array(':v' => '21015-11-01 14:10:25'));
        $this->assertNull($model);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
    }


    public function testFindByPk()
    {
        $model = TestEavActiveRecord::model()->findByPk(3);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity2-3', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)->findByPk(3);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(false)
            ->findByPk(3, '::datetimeSingle = :v', array(':v' => '2015-11-01 14:10:25'));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)
            ->findByPk(3, '::datetimeSingle = :v', array(':v' => '2015-11-01 14:10:25'));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity2-3-2015-11-01 14:10:25-eavEnabled', $model->LastName);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());

        $model = TestEavActiveRecord::model()->withEavAttributes(true)
            ->findByPk(3, '::datetimeSingle = :v', array(':v' => '21015-11-01 14:10:25'));
        $this->assertNull($model);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
    }


    public function testFindBySql()
    {
        $sql = 'SELECT * FROM ' . TestEavActiveRecord::model()->tableName() . ' WHERE id = :pk';
        $model = TestEavActiveRecord::model()->findBySql($sql, array(':pk' => 1));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity1-1', $model->LastName);

        $model = TestEavActiveRecord::model()->withEavAttributes()->findBySql($sql, array(':pk' => 1));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity1-1-2012-11-01 10:10:25-eavEnabled', $model->LastName);

        $model = TestEavActiveRecord::model()->withEavAttributes(true)->findBySql($sql, array(':pk' => 1));
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('entity1-1-2012-11-01 10:10:25-eavEnabled', $model->LastName);

        $model = TestEavActiveRecord::model()->withEavAttributes(true)->findBySql($sql, array(':pk' => 4541));
        $this->assertNull($model);
        $this->assertFalse(TestEavActiveRecord::model()->getIsEavEnabled());
    }


    public function testHasEavAttribute()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 2;

        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
        $model->eav_set_id = null;
        $this->assertFalse($model->hasEavAttribute('datetimeSingle'));
        $model->eav_set_id = 1;
        $this->assertTrue($model->hasEavAttribute('datetimeSingle'));
    }


    public function testGetAttributeLabel()
    {
        $model = new TestEavActiveRecord();
        $model->eav_set_id = 1;

        $this->assertEquals('This attribute can only hold one value', $model->getAttributeLabel('datetimeSingle'));
        $this->assertEquals('Primary key', $model->getAttributeLabel('id'));
        $this->assertEquals('Last Name', $model->getAttributeLabel('LastName'));
    }


    public function testIsEavAttributeMultivalued()
    {
        $model = new TestEavActiveRecord();
        $model->attachEavSet(2);
        $this->assertTrue($model->isEavAttributeMultivalued('varcharMultiple'));
        $this->assertFalse($model->isEavAttributeMultivalued('datetimeSingle'));
    }


    public function testExists()
    {
        $model = TestEavActiveRecord::model()->exists('id = :id', array(':id' => 1));
        $this->assertTrue($model);
        $model = TestEavActiveRecord::model()->exists('id = :id', array(':id' => 222));
        $this->assertFalse($model);
        $model = TestEavActiveRecord::model()->exists('::varcharMultiple = :id', array(':id' => 'three'));
        $this->assertTrue($model);
        $model = TestEavActiveRecord::model()->exists('::varcharMultiple = :id', array(':id' => 'thrgee'));
        $this->assertFalse($model);
    }


    public function testCount()
    {
        $this->assertEquals(1, TestEavActiveRecord::model()->count('id = :id', array(':id' => 1)));
        $this->assertEquals(0, TestEavActiveRecord::model()->count('t.id = :id AND ::varcharMultiple = :d',
            array(':id' => 1, 'd' => 'one')));
    }


    public function testIssue3()
    {
        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $model->id = 747;
        $model->varcharMultiple = array(1);
        $this->assertTrue($model->updateWithEavAttributes(array('name', 'eav_set_id', 'varcharMultiple')));

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertContains('1', $model->varcharMultiple);
        $model->id = 777;
        $model->updateWithEavAttributes();

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertNull($model);
        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(777);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertContains('1', $model->varcharMultiple);
        $model->id = 3;
        $model->varcharMultiple = array(5,2,3);
        $model->updateWithEavAttributes();

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(3);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertContains('2', $model->varcharMultiple);
        $this->assertContains('5', $model->varcharMultiple);
        $this->assertContains('3', $model->varcharMultiple);

        $model = new TestEavActiveRecord();
        $model->attachEavSet(1);
        $model->datetimeSingle = '1997-12-12 12:12:12';
        $model->id = 787;
        $model->insertWithEavAttributes();

        $model = TestEavActiveRecord::model()->withEavAttributes()->findByPk(787);
        $this->assertInstanceOf('EavActiveRecord', $model);
        $this->assertEquals('1997-12-12 12:12:12', $model->datetimeSingle);
    }


    public function testAddDropColumn()
    {
        $model = TestEavActiveRecord::model();
        $column = $model->tableSchema->getColumn('eav_set_id');
        $this->assertInstanceOf('CDbColumnSchema', $column);
        $this->assertTrue($column->isForeignKey);
        $model->dropColumn();
        $model->getDbConnection()->getSchema()->refresh();
        $model->refreshMetaData();
        $column = $model->tableSchema->getColumn('eav_set_id');
        $this->assertNull($column);
        $model->addColumn();
        $model->getDbConnection()->getSchema()->refresh();
        $model->refreshMetaData();
        $column = $model->tableSchema->getColumn('eav_set_id');
        $this->assertInstanceOf('CDbColumnSchema', $column);
        $this->assertTrue($column->isForeignKey);
    }

}

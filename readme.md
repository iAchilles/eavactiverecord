EavActiveRecord
=========

Implements entity-attribute-value pattern and provides a simple way to work with EAV-attributes. EAV-attributes are stored in the database as separate records but accessed and searched in such a way as if they were columns in the entity's table.

The following features are supported:

  - Eager and lazy loading EAV-attribute values
  - EAV-attribute validation rules
  - Mass assignment
  - Automatically inserting/updating/deleting
  - Multivalued attributes
  - Search by EAV-attribute values using find methods

Requirements
------------

- Yii 1.1.2 or above
- PHP 5.1 or above



Installation
------------

1. Use Composer or just extract release files under protected/components/eav.
2. Run the SQL-script schema.sql (protected/components/eav/schema/schema.sql). It creates tables needed to work with EAV-attributes: eav_set, eav_attribute, eav_attribute_set, eav_attribute_date, eav_attribute_int, eav_attribute_varchar, eva_attribute_text.
3. Add the following code in the application config file:  
```php
array(
'import' => array(
'application.components.eav.*',
'application.components.eav.datatypes.*',
)
```   
4. It requires a cache. If you can't use a cache, add the following code in the application config file: 
```php
'components' => array(
        'eavCache' => array(
            'class' => 'CDummyCache'
        ),
```

### Preparing the model for use with EAV-attributes.  
Each model class that may have EAV-attributes MUST extend the class EavActiveRecord:  
```php
class YourEntityClass extends EavActiveRecord
```
Also you must call the  method [YourEntityClass::addColumn()](#eavactiverecord.m.addColumn). This method must only be called once, it adds the new column "eav_set_id" in the associated database table. After calling this method your model is ready to use EAV-attributes.

<br><br><br><br>


# **Documentation**



# [EavActiveRecord](#eavactiverecord)

EavActiveRecord is the base class for all classes supporting entity–attribute–value data model.
It provides a simple way to work with EAV-attributes. EAV-attributes are stored in the database as separate records
but accessed and searched in such a way as if they were columns in the entity's table.

>An object of the class EavActiveRecord does not support EAV-attributes by default. To use EAV-attributes you must call the method [EavActiveRecord::attachEavSet()](#eavactiverecord.m.attachEavSet) if the object was instantiated by new operator. You must call the method [EavActiveRecord::withEavAttributes()](#eavactiverecord.m.withEavAttributes) before the object will be instantiated by a find method.

####I. Attaching EAV-attributes to the model

To attach EAV-attributes to the model you must link the model to the set of attributes. EAV-attributes from the linked set will be attached to the model:

```php
$model = new Model();
$model->attachEavSet(1);
```
If you want to use EAV-attributes in the model that is already linked to the EAV-attribute set and instantiated by a find method, call the method [EavActiveRecord::withEavAttributes()](#eavactiverecord.m.withEavAttributes):

```php
$model = Model::model()->withEavAttributes()->findByPk(1);
```


####II. Assigning a value to an EAV-attribute

You can assign a value to an EAV-attribute in one of the following ways:

a) Using the object property access syntax and assignment operator

```php
$model = new Model();
$model->attachEavSet(1);
$model->someEavAttr = 'value';
```

b) Using the method [EavActiveRecord::setEavAttribute()](#eavactiverecord.m.setEavAttribute)

```php
$model = new Model();
$model->attachEavSet(1);
$model->setEavAttribute('someEavAttr', 'value');
```

c) Using mass assignment (EAV-attribute must be associated with a validation rule in the current scenario)

```php
$attributes = array('attr1' => 'a', 'attr2' => 'b', 'someEavAttr' => 'value');
$model = new Model();
$model->attachEavSet(1);
$model->attributes = $attributes;
```


####III. Accessing an EAV-attribute value

You can access an EAV-attribute value in one of the following ways:

a) Using the object property access syntax

```php
$model = new Model();
$model->attachEavSet(1);
$model->someEavAttr;
```

b) Using the method [EavActiveRecord::getEavAttribute()](#eavactiverecord.m.getEavAttribute)

```php
$model = new Model();
$model->attachEavSet(1);
$model->getEavAttribute('someEavAttr');
```


####IV. Saving an EAV-attribute value

To save EAV-attribute values you must call the method [EavActiveRecord::saveWithEavAttributes()](#eavactiverecord.m.saveWithEavAttributes):

```php
$model = new Model();
$model->attachEavSet(1);
$model->someEavAttr = 'value';
$model->saveWithEavAttributes();
```

> Note, the method ``CActiveRecord::save()`` is available in the class EavActiveRecord but it only saves the attributes of the model. It ignores EAV-attributes.


####V. Deleting an EAV-attribute value

An EAV-attribute value will be deleted if the following conditions are met:

a) An empty string or null assigned to the single valued EAV-attribute; an empty array or null assigned to the multivalued EAV-attribute

```php
$model = new Model();
$model->attachEavSet(1);
$model->singlevaluedAttr = 'value';
$model->multivaluedAttr = array('a', 'b', 'c');
$model->saveWithEavAtrributes();

$model->singlevaluedAttr = ''; //The value will be deleted
$model->saveWithEavAttributes();

$model->multivaluedAttr = array('a', '', 'c'); 
$model->multivaluedAttr[2] = null;
$model->saveWithEavAttributes(); //Records that contain "b" and "c" values will be deleted


```


b) Using the method [EavActiveRecord::deleteWithEavAttributes()](#eavactiverecord.m.deleteWithEavAttributes)

```php
$model = new Model();
$model->attachEavSet(1);
$model->someEavAttr = 'value';
$model->saveWithEavAtrributes();

/*
 * Deletes the row corresponding to this active record and also deletes linked rows which contain EAV-attribute values.
 */
$model->deleteWithEavAttributes();
```

c) Detaching the EAV-attribute set from the model

```php
$model = new Model();
$model->attachEavSet(1);
$model->someEavAttr = 'value';
$model->saveWithEavAtrributes();

$model->detachEavSet();
$model->saveWithEavAtrributes();
```

d) Attaching a new EAV-attribute set that does not contain the EAV-attribute from previously attached set

```php
$model = new Model();
$model->attachEavSet(1);
$model->someEavAttr = 'value';
$model->saveWithEavAtrributes();

$model->attachEavSet(2); //The attribute "someEavAttr" is not included in the set
$model->saveWithEavAtrributes();
```


####VI. Eager and lazy loading EAV-attribute values

By default, lazy loading is used. With lazy loading enabled, values of EAV-attributes are loaded when
they are accessed. It means that a relational query will be initiated when you read a value of an EAV-attribute
the first time.

```php
$model = Model::model()->withEavAttributes()->findByPk(1); //Lazy loading is enabled
$model->someEavAttr; //A relational query will be initiated to get the value of this EAV-attribute
```

Pass true as an argument to the method [EavActiveRecord::withEavAttributes()](#eavactiverecord.m.withEavAttributes) to activate eager loading. If eager loading is enabled all the values of related EAV-attributes will be retrieved by performing a UNION query.

```php
$model = Model::model()->withEavAttributes(true)->findByPk(1); //Eager loading is enabled
$model->someEavAttr; //A relation query will not be initiated
```


####VII. Searching by EAV-attribute values 

If you want to search a record by EAV-attribute values, you need to add the prefix **"::"** to an EAV-attribute name and call a find method:

a) Using [CDbCriteria](http://www.yiiframework.com/doc/api/1.1/CDbCriteria)

```php
$criteria = new CDbCriteria();
$criteria->compare('modelAttr', 'value');
$criteria->addBetweenCondition('::someEavAttr', 10, 20);

$model = Model::model()->findAll($criteria);
```

b) Using a string condition

```php
$model = Model::model()->find('::someEavAttr = :v1 AND modelAttr = :v2', array(':v1' => 10, ':v2' => 'Jerry'));
```

> Note, when you call methods [CActiveRecod::findByAttributes()](http://www.yiiframework.com/doc/api/1.1/CActiveRecord#findByAttributes-detail) and [CActiveRecord::findAllByAttributes()](http://www.yiiframework.com/doc/api/1.1/CActiveRecord#findAllByAttributes-detail) you only can use EAV-attributes in the additional condition.



####VIII. Priority of attributes

The attributes of the model have higher priority than EAV-attributes, it means that if the model has the attribute or relation whose name is equal to the EAV-attribute name, EAV-attribute WILL NOT be attached to the model.




<br><br><br><br>



# [EavAttribute](#eavattribute)  
EavAttribute class represents methods to manipulate EAV-attributes (creating a new attribute, updating an existing
attribute, removing an attribute).  
There are two types of an EAV-attribute: a **multivalued attribute** and **single valued attribute**. A multivalued attribute can have more than one value at a time for an attribute. A single valued attribute can hold only single value at a time.  
```php
$attribute1 = new EavAttribute();
$attribute1->type = EavAttribute::TYPE_SINGLE; //Defines an attribute type. This attribute can hold only single value.

$attribute2 = new EavAttribute();
$attribute2->type = EavAttribute::TYPE_MULTIPLE; //This attribute can hold multiple values.
```
There are four data types of an EAV-attribute (surely, you can create own data types): **'IntDataType'**, **'VarcharDataType'**, **'DatetimeDataType'** and **'TextDataType'**. The name of the data type must be equal to a class name that is derived from the class EavValue. The value of the EAV-attribute is stored as a record in a table that is based on an attribute data type. It uses separate tables for each data type.
If the value of the attribute must be stored in an integer, you must use the constant ```EavAttribute::DATA_TYPE_INT``` to assign a value to the property [EavAttribute::$data_type](#eavattribute.p.data_type):
```php
$attribute = new EavAttribute();
$attribute->data_type = EavAttribute::DATA_TYPE_INT; //Values of this attribute will be stored in an integer.
```
To specify a data type of an attribute you can use constants ```EavAttribute::DATA_TYPE_INT``` ('IntDataType'),
```EavAttribute::DATA_TYPE_DATETIME``` ('DatetimeDataType'), ```EavAttribute::DATA_TYPE_TEXT``` ('TextDataType')
and ```EavAttribute::DATA_TYPE_VARCHAR``` ('VarcharDataType').    

The [name](#eavattribute.p.name) of the EAV-attribute MUST be unique and follow [PHP variable naming convention](http://php.net/manual/en/language.variables.basics.php).

The following name of the attribute is invalid:
```php
$attribute = new EavAttribute();
$attribute->name = 2; //Invalid name
$attribute->name = '3abc'; //Invalid name
```   
The following name of the attribute is correct:   
```php
$attribute = new EavAttribute();
$attribute->name = 'abc3'; //Correct name
$attribute->name = '_a2c'; //Correct name
```
When you create a new EAV-attribute you also can determine validation rules by calling the method [EavAttribute::setRules()](#eavattribute.m.setRules).
The following code fragment shows how to add **validation rules** to an attribute:
```php
$rules = array('length' => array('min' => 3, 'max' => 25), 'required' => array('on' => 'register'));
$attribute = new EavAttribute();
$attribute->setRules($rules);
```   

> Note, if an attribute does not contain validation rules so that it cannot be massively assigned.   



The following is a complete code of creating a new EAV-attribute:
```php
$attribute = new EavAttribute();
$attribute->name = 'age'; //Required field
$attribute->label = 'Your age';
$attribute->type = EavAttribute::TYPE_SINGLE; //Required field
$attribute->data_type = EavAttribute::DATA_TYPE_INT; // Required field
$attribute->setRules(array('numeric' => array('min' => 18, 'max' => 100, 'integerOnly' => true), 'required'));
$attribute->save();
```   


<br><br><br><br>



# [EavSet](#eavset)
EavSet class represents methods to manipulate a set of EAV-attributes (creating a new set, adding an attribute to
a set, removing an attribute from a set, removing an attribute set). 

To create a new attribute set and save data to a database, you need to write the following code:
```php
$set = new EavSet();
$set->name = 'Set'; // Required field
$set->save();
```

To add a new EAV-attribute to a set, you need to write the following code:
```php
$attribute = new EavAttribute(); //Create an instance of the class EavAttribute
$attribute->name = 'attr1';
$attribute->label = 'Attribute Label';
$attribute->type = EavAttribute::TYPE_SINGLE;
$attribute->data_type = EavAttribute::DATA_TYPE_INT;

$set = new EavSet();
$set->name = 'Set';
$set->addEavAttribute($attribute);
$set->save();
```

When you add a new attribute to a set it will be automatically saved (if the attribute is valid). But you still may
call the method ```EavAttribute::save()``` before adding an attribute to a set. The following code example is equivalent to
the previous example:

```php
$attribute = new EavAttribute();
$attribute->name = 'attr1';
$attribute->label = 'Attribute Label';
$attribute->type = EavAttribute::TYPE_SINGLE;
$attribute->data_type = EavAttribute::DATA_TYPE_INT;
$attribute->save(); // Call the method EavAttribute::save()

$set = new EavSet();
$set->name = 'Set';
$set->addEavAttribute($attribute);
$set->save();
```

You also can add an existing attribute to a set:
```php
$set = new EavSet();
$set->name = 'Set';
$set->addEavAttribute(EavAttribute::model()->findByPk(1)); //Adding an instance of EavAttribute
$set->addEavAttribute(5); //You can specify a primary key of an attribute you want to add
$set->save();
```

To remove an attribute from a set you must call the method [EavSet::removeEavAttribute()](#eavset.m.removeEavAttribute) and specify the attribute that must be removed:

```php
$set = EavSet::model()->findByPk(1);
$set->removeEavAttribute(5); //Primary key of an attribute that must be removed
$set->removeEavAttribute(EavAttribute::model()->findByPk(2)); // Or an instance of the class EavAttribute
$set->save();
```

The following code fragment shows how to delete an existing set of EAV-attributes:

```php
$set = EavSet::model()->findByPk(1);
$set->delete();
```

To delete an existing set of EAV-attributes you also can call methods ```CActiveRecord::deleteAll()```,
```CActiveRecord::deleteByPk()```, ```CActiveRecord::deleteAllByAttributes()```, these methods are available in the class
EavAttribute because it is derived from CActiveRecord.

>Note, the set of EAV-attributes cannot be deleted if some records (EavActiveRecord) are referenced
to the set (foreign key constraint).


<br><br><br><br>


# [EavValue](#eavvalue)

EavValue class represents methods to save, update and delete values of an EAV-attribute.
Each class that represents a data type of an EAV-attribute must be derived from it.

<br><br><br><br>


# [EavForm](#eavform)

EavForm class provides dynamically creating form elements for EAV-attributes.
It can be used as a widget in a view file:
<pre>
Yii::import('application.components.eav.helpers.EavForm');
$this->widget('EavForm', array('model' => $model));
</pre>
To rendering a form element it looks up a template file, the name of the template file should be the same as the name
of the EAV-attribute. <br>
There are two variables are available in the context of every template: $attribute and $model.
The value of the variable $attribute is a string that contains the name of the EAV-attribute. The variable $model
is reference to an instance of EavActiveRecord.
<br>
The following code may be used in the template file:
<pre>
echo CHtml::error($model, $attribute);
echo CHtml::activeTextField($model, $attribute);
</pre>

The following code may be used for rendering form elements of a multivalued EAV-attribute:
<pre>
echo CHtml::error($model, $attribute);
echo CHtml::activeLabel($model, $attribute);
if ($model->isEavAttributeMultivalued($attribute))
{
     if (empty($model->$attribute))
     {
        echo CHtml::activeTextField($model, $attribute . '[]', array('value' => ''));
      }
      else
      {
         foreach ($model->$attribute as $value)
        {
           echo CHtml::activeTextField($model, $attribute . '[]', array('value' => $value));
        }
     }
  }
</pre>


<br><br><br><br>





# **Class reference**

<br>

### <a name="eavactiverecord"></a> **EavActiveRecord** 
**inheritance**: EavActiveRecord -> [CActiveRecord](http://www.yiiframework.com/doc/api/1.1/CActiveRecord) -> [CModel](http://www.yiiframework.com/doc/api/1.1/CModel) -> [CComponent](http://www.yiiframework.com/doc/api/1.1/CComponent)   
**implements**: ArrayAccess, Traversable, IteratorAggregate
<br><br>

###Public properties
(Inherited properies are excluded)  

| Property        | Type          | Description | Defined by
| -------------   | ------------- | ----------- | ---------
| eavAttributes    | array         | Returns EAV-attribute values. | EavActiveRecord
| eav_set_id       | integer       | Foreign key whose value match a primary key in the table eav_set. | EavActiveRecord
| eavValidators    | array         | Returns the EAV-attribute validators applicable to the current scenario. | EavActiveRecord
| eavValidatorList | CList         | Returns all the EAV-attribute validators. | EavActiveRecord
| entity           | string        | Returns the name of the entity based on the class name. | EavActiveRecord
| isEavEnabled     | boolean       | Determines whether the model may have EAV-attributes. | EavActiveRecord
| oldEavSetPrimaryKey | integer    | Returns the old primary key value of the EAV-attribute set. | EavActiveRecord
| safeEavAttributeNames | array    | Returns the EAV-attribute names that are safe to be massively assigned. A safe attribute is one that is associated with a validation rule in the current scenario. | EavActiveRecord

<br>

###Public methods
(Inherited methods are excluded)

| Method                     | Description                                           | Defined by
| -------------------------- | ----------------------------------------------------- | ----------
| [addColumn()](#eavactiverecord.m.addColumn)                |  Adds the new column "eav_set_id" in the associated database table. | EavActiveRecord
| [attachEavSet()](#eavactiverecord.m.attachEavSet)          | Attaches a set of EAV-attributes to the model. After calling this method the model may have EAV-attributes and you can use special methods to work with these. You also can attach a set of EAV-attributes if assign a value to the property EavActiveRecord::$eav_set_id.                                        | EavActiveRecord
| [createEavValidators()](#eavactiverecord.m.createEavValidators)      | Creates validator objects based on the specification in rules of an EAV-attribute. This method is mainly used internally.                                                              | EavActiveRecord
| [deleteWithEavAttributes()](#eavactiverecord.m.deleteWithEavAttributes)  | Deletes the row corresponding to this active record and also deletes linked rows which contain values of EAV-attributes.    | EavActiverRecord
| [detachEavSet()](#eavactiverecord.m.detachEavSet)             | Detaches a set of EAV-attributes from the model.      | EavActiveRecord
| [dropColumn()](#eavactiverecord.m.dropColumn)             | Removes the column "eav_set_id" from the associated database table. | EavActiveRecord
| [eavAttributeNames()](#eavactiverecord.m.eavAttributeNames)       | Returns the list of all EAV-attribute names of the model. | EavActiveRecord
| [getEavAttribute()](#eavactiverecord.m.getEavAttribute)          | Returns the named EAV-attribute value. If the given attribute has no value it returns either an empty array for a multi-value attribute or null for a single-value attribute.        | EavActiveRecord
| [getEavAttributes()](#eavactiverecord.m.getEavAttributes)         | Returns EAV-attribute values indexed by EAV-attribute names. | EavActiveRecord
| [getEavValidators()](#eavactiverecord.m.getEavValidators)         | Returns the EAV-attribute validators applicable to the current scenario. | EavActiveRecord
| [getEavValidatorList()](#eavactiverecord.m.getEavValidatorList)      | Returns all the EAV-attribute validators.             | EavActiveRecord
| [getEntity()](#eavactiverecord.m.getEntity)                | Returns the name of the entity based on the class name. | EavActiveRecord
| [getIsEavEnabled()](#eavactiverecord.m.getIsEavEnabled)          | Determines whether the model may have EAV-attributes. | EavActiveRecord
| [getOldEavSetPrimaryKey()](#eavactiverecord.m.getOldEavSetPrimaryKey)   | Returns the old primary key value of the EAV-attribute set. | EavActiveRecord
| [getSafeEavAttributeNames()](#eavactiverecord.m.getSafeEavAttributeNames) | Returns the EAV-attribute names that are safe to be massively assigned. A safe attribute is one that is associated with a validation rule in the current scenario.                   | EavActiveRecord
| [hasEavAttribute()](#eavactiverecord.m.hasEavAttribute)          | Checks if this record has the named EAV-attribute.    | EavActiveRecord
| [insertWithEavAttributes()](#eavactiverecord.m.insertWithEavAttributes)  | Inserts a row into the table based on this active record attributes. It also inserts rows into tables which stores values of EAV-attributes, included in the attribute set which is attached to this model. Validation is not performed in this method.                                                           | EavActiveRecord
| [isEavAttributeMultivalued()](#eavactiverecord.m.isEavAttributeMultivalued)| Checks if the given attribute may hold multiple values. | EavActiveRecord
| [isEavAttributeRequired()](#eavactiverecord.m.isEavAttributeRequired)   | Returns a value indicating whether the EAV-attribute is required. This is determined by checking if the attribute is associated with a CRequiredValidator validation rule in the current scenario. | EavActiveRecord
| [saveWithEavAttributes()](#eavactiverecord.m.saveWithEavAttributes)    | Saves the current record and also records that contain EAV-attribute values that have been set on this model.                  | EavActiveRecord
| [setEavAttribute()](#eavactiverecord.m.setEavAttribute)          | Sets a value of the named EAV-attribute.             | EavActiveRecord
| [updateWithEavAttributes()](#eavactiverecord.m.updateWithEavAttributes)    | Updates the rows represented by this active record and also updates rows that contain EAV-attribute values. Validation is not performed in this method.                   | EavActiveRecord
| [validateEavAttribute()](#eavactiverecord.m.validateEavAttribute)     | Performs the validation for EAV-attributes.          | EavActiveRecord
| [withEavAttributes()](#eavactiverecord.m.withEavAttributes)        | Attaches EAV-attributes to the found records.        | EavActiveRecord

<br>

### Method details

<a name="eavactiverecord.m.addColumn"></a>

```php
public  void addColumn()
```

Adds the new column "eav_set_id" in the associated database table. 
<br><br>


<a name="eavactiverecord.m.attachEavSet"></a>

```php
public void attachEavSet(integer $pk)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| pk         | integer    | The primary key value of an existing EAV-attributes set that must be attached to the model.


Attaches a set of EAV-attributes to the model. After calling this method the model may have EAV-attributes and you can use special methods to work with these. You also can attach a set of EAV-attributes if assign a value to
the property EavActiveRecord::$eav_set_id.

```php
$record->eav_set_id = 4; //Attaches the set of EAV-attributes to the model
$record->attachEavSet(4); //Attaches the set of EAV-attributes to the model
$record->setAttribute('eav_set_id', 4); //Attaches the set of EAV-attributes to the model
```
<br><br>


<a name="eavactiverecord.m.createEavValidators"></a>

```php
public void createEavValidators()
```
| parameter  | type       | description 
| ---------- |------------| -----------
| {return}   | CList    | Validators built based on the return type of the method EavAttribute::getEavValidatorList()
| {throws}   | CException  | If the instantiated model does not support EAV attributes.

Creates validator objects based on the specification in rules of an EAV-attribute. This method is mainly used internally.
<br><br>


<a name="eavactiverecord.m.deleteWithEavAttributes"></a>

```php
public boolean deleteWithEavAttributes()
```

| parameter  | type       | description 
| ---------- |------------| -----------
| {return}   | boolean    | Whether the deletion is successful.
| {throws}   | CDbException | If the active record is new.
| {throws}   | CException  | If the instantiated model does not support EAV attributes.

Deletes the row corresponding to this active record and also deletes linked rows which contain values of EAV-attributes.
<br><br>


<a name="eavactiverecord.m.detachEavSet"></a>

```php
public void detachEavSet()
```

Detaches a set of EAV-attributes from the model.
<br><br>


<a name="eavactiverecord.m.dropColumn"></a>

```php
public void dropColumn()
```

Removes the column "eav_set_id" from the associated database table.
<br><br>


<a name="eavactiverecord.m.eavAttributeNames"></a>

```php
public array eavAttributeNames()
```

| parameter  | type       | description 
| ---------- |------------| -----------
| {return}   | array   | The list of all EAV-attribute names.
| {throws}   | CException  | If the instantiated model does not support EAV attributes.

Returns the names of all EAV-attributes attached to the model.
<br><br>


<a name="eavactiverecord.m.getEavAttribute"></a>

```php
public mixed getEavAttribute(string $name)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| name       | string     | Attribute name.
| {return}   | mixed   | EAV-attribute value
| {throws}   | CException  | If the instantiated model does not support EAV attributes.

Returns the named EAV-attribute value. If the given attribute has no value it returns either an empty array for a multivalued attribute or null for a single-valued attribute.
<br><br>


<a name="eavactiverecord.m.getEavAttributes"></a>

```php
public array getEavAttributes(array $names = null)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| names      | array      | Names of EAV-attributes whose value needs to be returned. If this is null (default), then all EAV-attribute values will be returned.
| {return}   | array      | EAV-attribute values indexed by EAV-attribute names.
| {throws}   | CException  | If the instantiated model does not support EAV attributes.

Returns EAV-attribute values indexed by EAV-attribute names.
<br><br>


<a name="eavactiverecord.m.getEavValidators"></a>

```php
public array getEavValidators(string $attribute = null)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| attribute  | string     | The name of the EAV-attribute whose validators should be returned. If this is null, the validators for all EAV-attributes in the model will be returned.
| {return}   | array      | The validators of EAV-attributes applicable to the current scenario.
| {throws}   | CException  | If the instantiated model does not support EAV attributes.

Returns the EAV-attribute validators applicable to the current scenario.
<br><br>


<a name="eavactiverecord.m.getEavValidatorList"></a>

```php
public CList getEavValidatorList()
```

| parameter  | type       | description 
| ---------- |------------| -----------
| {return}   | CList      | All the EAV-attribute validators.
| {throws}   | CException  | If the instantiated model does not support EAV attributes.

Returns all the EAV-attribute validators.
<br><br>


<a name="eavactiverecord.m.getEntity"></a>

```php
public string getEntity()
```

| parameter  | type       | description 
| ---------- |------------| -----------
| {return}   | string     | The name of the entity.

Returns the name of the entity based on the class name. You can override this method to add own business logic to
format entity name.
<br><br>


<a name="eavactiverecord.m.getIsEavEnabled"></a>

```php
public boolean getIsEavEnabled()
```

| parameter  | type       | description 
| ---------- |------------| -----------
| {return}   | boolean    | Whether the model may have EAV-attributes.

Determines whether the model may have EAV-attributes.
<br><br>


<a name="eavactiverecord.m.getOldEavSetPrimaryKey"></a>

```php
public mixed getOldEavSetPrimaryKey()
```

| parameter  | type       | description 
| ---------- |------------| -----------
| {return}   | mixed      | The old primary key value of the EAV-attribute set.

Returns the old primary key value of the EAV-attribute set.
<br><br>


<a name="eavactiverecord.m.getSafeEavAttributeNames"></a>

```php
public array getSafeEavAttributeNames()
```

| parameter  | type       | description 
| ---------- |------------| -----------
| {return}   | array      | The EAV-attribute names that are safe to be massively assigned.
| {throws}   | CException  | If the instantiated model does not support EAV attributes.

Returns the EAV-attribute names that are safe to be massively assigned. A safe attribute is one that is associated with a validation rule in the current scenario.
<br><br>


<a name="eavactiverecord.m.hasEavAttribute"></a>

```php
public boolean hasEavAttribute(string $name)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| name       | string     | The attribute name.
| {return}   | boolean    | Whether this record has the named EAV-attribute.
| {throws}   | CException | If the instantiated model does not support EAV attributes.

Checks if this record has the named EAV-attribute.
<br><br>


<a name="eavactiverecord.m.insertWithEavAttributes"></a>

```php
public boolean insertWithEavAttributes(array $attributes = null)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| attributes | array      | List of attributes that need to be saved (you can also specify EAV-attributes). Defaults to null, meaning all attributes that are loaded from DB and all EAV-attributes will be saved. <br> Note, IF LIST OF ATTRIBUTES DOES NOT CONTAIN "eav_set_id", values of EAV-attribute WILL NOT be saved.
| {return}   | boolean    | Whether the attributes are valid and the records are inserted successfully.
| {throws}   | CDbException | If the active record is not new.
| {throws}   | CException | If the instantiated model does not support EAV attributes.

Inserts a row into the table based on this active record attributes. It also inserts rows into tables whichstore EAV-attributes values. Note, validation is not performed in this method. After the records are inserted to DB successfully, its isNewRecord property will be set false, and its scenario property will be set to be 'update'.
<br><br>


<a name="eavactiverecord.m.isEavAttributeMultivalued"></a>

```php
public boolean isEavAttributeMultivalued(string $name)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| name       | string     | The attribute name.
| {return}   | boolean    | Returns true if an attribute with the specified name may hold multiple values, otherwise false.
| {throws}   | CException | If the instantiated model does not support EAV attributes.

Checks if the given attribute may hold multiple values.
<br><br>


<a name="eavactiverecord.m.isEavAttributeRequired"></a>

```php
public boolean isEavAttributeRequired(string $name)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| name       | string     | The attribute name.
| {return}   | boolean    | Whether the attribute is required.
| {throws}   | CException | If the instantiated model does not support EAV attributes.

Returns a value indicating whether the EAV-attribute is required. This is determined by checking if the attribute is associated with a CRequiredValidator validation rule in the current scenario.
<br><br>


<a name="eavactiverecord.m.saveWithEavAttributes"></a>

```php
public boolean saveWithEavAttributes(boolean $runValidation = true, array $attributes = null)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| runValidation | boolean | Whether to perform validation before saving the record. If the validation fails, the record will not be saved to database.
| attributes | array      | List of attributes that need to be saved (you can also specify EAV-attributes). Defaults to null, meaning all attributes that are loaded from DB and all related EAV-attributes will be saved. <br>Note, IF LIST OF ATTRIBUTES DOES NOT CONTAIN "eav_set_id", EAV-attributes WILL NOT be saved.
| {return}   | boolean    | Whether the saving succeeds.
| {throws}   | CException | If the instantiated model does not support EAV attributes.

Saves the current record and also records that contain EAV-attribute values that have been set on this model. The record is inserted as a row into the database table if its $isNewRecord property is true (usually the case when the record is created using the 'new' operator). Otherwise, it will be used to update the corresponding row in the table (usually the case if the record is obtained using one of those 'find' methods.) <br> Validation will be performed before saving the record. If the validation fails, the record will not be saved. You can call the method getErrors() to retrieve the
validation errors. <br>
If the record is saved via insertion, its $isNewRecord property will be set false, and its $scenario property will be set to be 'update'. And if its primary key is auto-incremental and is not set before insertion, the primary key will be populated with the automatically generated key value.
<br><br>


<a name="eavactiverecord.m.setEavAttribute"></a>

```php
public boolean setEavAttribute(string $name, mixed $value)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| name       | string     | The attribute name.
| value      | mixed      | The attribute value.
| {return}   | boolean    | Whether the EAV-attribute exists and the assignment is conducted successfully.
| {throws}   | CException | If the instantiated model does not support EAV attributes.

Sets a value of the named EAV-attribute. You may also use $this->eavAttributeName to set the attribute value.
<br><br>


<a name="eavactiverecord.m.updateWithEavAttributes"></a>

```php
public boolean updateWithEavAttributes(array $attributes = null)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| attributes | array      | List of attributes that need to be saved (you can also specify EAV-attributes). Defaults to null, meaning all attributes that are loaded from DB and all EAV-attributes will be saved. <br> Note, IF LIST OF ATTRIBUTES DOES NOT CONTAIN "eav_set_id", values of EAV-attribute WILL NOT be saved.
| {return}   | boolean    | Whether the update is successful.
| {throws}   | CDbException | If the active record is new.
| {throws}   | CException | If the instantiated model does not support EAV attributes.

Updates the rows represented by this active record and also updates rows that contain EAV-attributes values. Validation is not performed in this method.
<br><br>


<a name="eavactiverecord.m.validateEavAttribute"></a>

```php
public boolean validateEavAttribute(array $attributes = null)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| attributes | array      | List of attributes that should be validated. Defaults to null, meaning any EAV-attribute listed in the applicable validation rules should be validated. If this parameter is given as a list of attributes, only the listed attributes will be validated.
| {return}   | boolean    | Whether the validation is successful without any error.
| {throws}   | CException | If the instantiated model does not support EAV attributes.

Performs the validation for EAV-attributes. If the model supports EAV attributes this method is called by the method validate(), so you do not need to call this method directly.
<br><br>


<a name="eavactiverecord.m.withEavAttributes"></a>

```php
public EavActiveRecord withEavAttributes(boolean $eager = false)
```

| parameter  | type       | description 
| ---------- |------------| -----------
| eager      | boolean    | If this parameter is set to true then all the values of EAV-attributes will be eagerly loaded. With lazy loading enabled (the parameter $eager is set to false), values of EAV-attributes are loaded when they are accessed. It means that a relational query will be initiated when you read a value of an EAV-attribute the first time. <br> If eager loading is enabled all the values of related EAV-attributes will be retrieved by performing a UNION query.
| {return}   | EavActiveRecord    | An instance of the class EavActiveRecord

Attaches EAV-attributes to the found record. You must call this method to attach EAV-attributes to the found records.

```php
$model = Model::model()->withEavAttributes()->findByPk(1);
```


<br><br><br><br>

### <a name="eavattribute"></a> **EavAttribute**
**inheritance**: EavAttribute -> [CActiveRecord](http://www.yiiframework.com/doc/api/1.1/CActiveRecord) -> [CModel](http://www.yiiframework.com/doc/api/1.1/CModel) -> [CComponent](http://www.yiiframework.com/doc/api/1.1/CComponent)   
**implements**: ArrayAccess, Traversable, IteratorAggregate, Serializable   

<br>

###Public properties
(Inherited properties are excluded)   

| Property        | Type          | Description | Defined by
| ------------- | -------------------- | ----------- | -----------
| eavValidatorList     | array| Returns all the validation rules for an attribute. If no validation rules exist, an empty array is returned. | EavAttribute
| <a name="eavattribute.p.data_type"></a>data_type | integer | Data type in which all attribute values are stored. To specify a data type of an attribute you can use constants EavAttribute::DATA_TYPE_INT ('IntDataType'), EavAttribute::DATA_TYPE_DATETIME ('DatetimeDataType'), EavAttribute::DATA_TYPE_TEXT ('TextDataType') and EavAttribute::DATA_TYPE_VARCHAR ('VarcharDataType').| EavAttribute
| label      | string | The attribute lable | EavAttribute
|<a name="eavattribute.p.name"></a>name | string | The name of the EAV-attribute MUST be unique and follow PHP variable naming convention. | EavAttribute
|type | integer | The attribute type. To specify the data type of the attribute you can use constants EavAttribute::TYPE_SINGLE, EavAttribute::MULTIPLE | EavAttribute

<br>

###Public methods
(Inherited methods are excluded)  

| Method | Description | Defined by 
| -------| -----------|------------
|[getEavAttributes()](#eavattribute.m.getEavAttributes) |Returns list of instances of the EavAttribute class (indexed by an attribute name).| EavAttribute
|[getEavValidatorList()](#eavattribute.m.getEavValidatorList) |Returns all the validation rules for an attribute. If no validation rules exist, an empty array is returned. |EavAttribute
|[setRules()](#eavattribute.m.setRules)| Adds validation rules to an attribute. | EavAttribute

<br>

### Method details


<a name="eavattribute.m.getEavAttributes"></a>

```php 
public array getEavAttributes( array $names)
```

| parameter  | type | description 
| ------- |----------| ----------
| name| array| Names of attributes whose instances should be returned.
| {return} | array | List of instances of the class EavAttribute (indexed by an attribute name).
| {throws} | CException | If the given argument is not an array.

Returns list of instances of the EavAttribute class (indexed by an attribute name). If attributes are not found, an empty array is returned.
<br><br>



<a name="eavattribute.m.getEavValidatorList"></a>

```php
public array getEavValidatorList()

```
| parameter  | type | description 
| ------- |----------| ---------
| {return} | array | All the validation rules for an attribute. If no validation rules exist, an empty array is returned.

Returns all the validation rules for an attribute.
<br><br>


<a name="eavattribute.m.setRules"></a>

```php
public void setRules(array $rules)
```

| parameter  | type | description 
| ------- |----------| -----------
| rules   | array| An array contains nested arrays that are indexed by the name of  a validator. Each nested array contains the definition of a validation rule.
| {throws} | CException | If the given argument is not an array.

Adds validation rules to the EAV-attribute.

```php
$rules = array(
      'length' => array('max' => 5, 'min' => 2),
      'date' => array('format' => 'yyyy-M-d H:m:s'));
$attribute->setRules($rules);
```

<br><br><br><br>

### <a name="eavset"></a> **EavSet**

**inheritance**: EavSet -> [CActiveRecord](http://www.yiiframework.com/doc/api/1.1/CActiveRecord) -> [CModel](http://www.yiiframework.com/doc/api/1.1/CModel) -> [CComponent](http://www.yiiframework.com/doc/api/1.1/CComponent)   
**implements**: ArrayAccess, Traversable, IteratorAggregate
<br>
<br>

###Public properties
(Inherited properties are excluded)

| Property        | Type          | Description | Defined by
| -------              | -------            | ------          | -------
| name | string | The name of the EAV-attributes set. | EavSet


<br>
###Public methods
(Inherited methods are excluded)  

| Method | Description | Defined by 
| -------| -----------|------------
|[addEavAttribute()](#eavset.m.addEavAttribute) |Adds EAV-attribute to the set.| EavSet
|[removeEavAttribute()](#eavset.m.removeEavAttribute) |Removes the given EAV-attribute from the set. |EavSet

<br>

### Method details


<a name="eavset.m.addEavAttribute"></a>

```php
public EavSet addEavAttribute( mixed $attribute)
```

| parameter  | type | description 
| ------- |----------| ----
| attribute| mixed| It must be either an instance of EavAttribute class or the primary key of an attribute which must be saved.
| {return} | EavSet | EavSet instance.
| {throws} | CException | Incorrect argument passed.

Adds EAV-attribute to the set.
<br><br>


<a name="eavset.m.removeEavAttribute"></a>


```php
public EavSet removeEavAttribute( mixed $attribute)
```

| parameter  | type | description 
| ------- |----------| -----
| attribute| mixed| It must be either an instance of EavAttribute class or the primary key of an attribute which must be removed.
| {return} | EavSet | EavSet instance.
| {throws} | CException | Incorrect argument passed.

Removes the given attribute from the set.
<br><br><br><br>


### <a name="eavvalue"></a> **EavValue**

**inheritance**: EavValue -> [CActiveRecord](http://www.yiiframework.com/doc/api/1.1/CActiveRecord) -> [CModel](http://www.yiiframework.com/doc/api/1.1/CModel) -> [CComponent](http://www.yiiframework.com/doc/api/1.1/CComponent)   
**implements**: ArrayAccess, Traversable, IteratorAggregate

<br>
###Public methods
(Inherited methods are excluded)  

| Method | Description | Defined by 
| -------| -----------|------------
|[deleteValue()](#eavvalue.m.deleteValue) |Deletes an attribute value.| EavValue
|[saveValue()](#eavvalue.m.saveValue) |Saves an attribute value.|EavValue

<br>

### Method details


<a name="eavvalue.m.deleteValue"></a>

```php
public int deleteValue(EavActiveRecord $entity, EavAttribute $attribute)
```

| parameter  | type | description 
| ------- |----------| ------
| entity| EavActiveRecord| An instance of the derived class.
| attribute| EavAttribute| EavAttribute instance
| {return} | int | Returns number of affected rows.

Deletes an attribute value.
<br><br>


<a name="eavvalue.m.saveValue"></a>

```php
public int saveValue(EavActiveRecord $entity, EavAttribute $attribute, mixed $value)
```

| parameter  | type | description 
| ------- |----------| ------
| entity| EavActiveRecord| An instance of the derived class.
| attribute| EavAttribute| EavAttribute instance
| attribute| mixed| EavAttribute value(s) that must be saved.
| {return} | int | Returns number of affected rows.

Saves an attribute value.
<br><br><br><br>


### <a name="eavform"></a> **EavForm**

**inheritance**: EavForm -> [CWidget](http://www.yiiframework.com/doc/api/1.1/CWidget) -> [CBaseController](http://www.yiiframework.com/doc/api/1.1/CBaseController) -> [CComponent](http://www.yiiframework.com/doc/api/1.1/CComponent)   


<br>
###Public properties
(Inherited properties are excluded)

| Property        | Type          | Description | Defined by
| ------              | ------            | ------          | -------
| html | string | The rendering result as a string. | EavForm
| model | EavActiveRecord | the model that will be used to creating form elements. | EavForm
| return | boolean | If the property value is set to true, the rendering result will not be echoed. You can get the rendering result as a string by accessing the property EavForm::$html. | EavForm
| viewMap | array | Defines a custom template file which should be used for the specified attribute (attributeName => fileName). |EavForm
|viewPath | string | The path to the directory that contains template files. Default path of the template directory is "protected/views/eav" |EavForm
<br>

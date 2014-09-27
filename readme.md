EavActiveRecord
=========

Implements entity-attribute-value pattern and provides a simple way to work with EAV-attributes. EAV-attributes are stored in the database as separate records but accessed and searched in such a way as if they were columns in the entity's table.

The following features are supported:

  - Eager and lazy loading of EAV-attributes.
  - Dynamic validation rules. The validation rules defined for the EAV-attribute will be added to the model dynamically.
  - Automatically inserting/updating/deleting EAV-attribute values.
  - Accessing and editing EAV-attributes in the same way as if they were real attributes of the model.
  - A simple search by EAV-attributes with using the find methods.

Requirements
------------

- Yii 1.1.2 or above
- PHP 5.1 or above



Installation
------------

1. Download and extract the release files under the folder "protected/components/eavactiverecord".
1. Run the SQL-script schema.sql (It is located in the following folder: "protected/components/eavactiverecord/schema/schema.sql). It creates tables needed to work with EAV-attributes: eav_set, eav_attribute, eav_attribute_set, eav_attribute_date, eav_attribute_int, eav_attribute_varchar, eav_attribute_text.
1. Add the following lines in the file "protected/config/main.php":

   ```php
array(
     'import' => array(
     'application.components.eavactiverecord.*',
     'application.components.eavactiverecord.datatypes.*',
)
```

1. It requires cache to be activated in the application: 

   ```php
array(
    …
    'components'=>array(
        …
        'cache'=>array(
            'class'=>'system.caching.CMemCache',
            'servers'=>array(
                array('host'=>'server1', 'port'=>11211, 'weight'=>60),
                array('host'=>'server2', 'port'=>11211, 'weight'=>40),
            ),
        ),
    ),
);
```

   The extension will use own cache component if it is defined as the following:

   ```php
array(
    …
    'components'=>array(
        …
        'eavCache'=>array(
            'class'=>'system.caching.CMemCache',
            'servers'=>array(
                array('host'=>'server1', 'port'=>11211, 'weight'=>60),
                array('host'=>'server2', 'port'=>11211, 'weight'=>40),
            ),
        ),
    ),
);
```
   If you do not use cache, add the following code in the file "protected/config/main.php":

   ```php
'components' => array(
        'eavCache' => array(
            'class' => 'system.caching.CDummyCache'
        ),
     )
```

1. Extend your model class from the class EavActiveRecord

   ```php
class Foo extends EavActiveRecord
```

1. Call the method Foo::addColumn(). This method must only be called once for each model that extends EavActiveRecord class. 
It adds the new column "eav_set_id" in the associated database table.

   ```php
Foo::model()->addColumn();
```

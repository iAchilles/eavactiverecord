<?php
return array(
    'datetimeSingle' => array(
        'id' => 1,
        'type' => 0,
        'data_type' => 'DatetimeDataType',
        'name' => 'datetimeSingle',
        'label' => 'This attribute can only hold one value',
        'data' => 'a:1:{s:5:"rules";a:2:{s:6:"length";a:2:{s:3:"max";i:5;s:3:"min";i:1;}s:8:"required";a:0:{}}}'
    ),
    'varcharMultiple' => array(
        'id' => 2,
        'type' => 1,
        'data_type' => 'VarcharDataType',
        'name' => 'varcharMultiple',
        'label' => 'This attribute can hold multiple values',
        'data' => 'a:1:{s:5:"rules";a:1:{s:6:"length";a:2:{s:3:"max";i:15;s:3:"min";i:3;}}}'
    ),
    'intSingle' => array(
        'id' => 3,
        'name' => 'intSingle',
        'type' => 0,
        'data_type' => 'IntDataType',
        'data' => 'a:1:{s:5:"rules";a:0:{}}'
    ),
    'intMultiple' => array(
         'id' => 4,
         'name' => 'intMultiple',
         'type' => 1,
         'data_type' => 'IntDataType',
         'label' => 'Integer',
         'data' => 'a:1:{s:5:"rules";a:1:{s:4:"safe";a:0:{}}}'
    ),
    'test' => array(
        'id' => 1000,
        'name' => 'test',
        'type' => 1,
        'label' => 'test label',
        'data_type' => 'VarcharDataType',
        'data' => 'a:2:{s:5:"rules";a:3:{s:8:"required";a:9:{s:13:"requiredValue";N;s:6:"strict";b:0;s:4:"trim";b:1;s:22:"enableClientValidation";b:1;s:6:"except";a:1:{i:0;s:6:"except";}s:7:"message";s:9:"Required!";s:2:"on";a:1:{i:0;s:7:"include";}s:4:"safe";b:1;s:11:"skipOnError";b:0;}s:2:"in";a:10:{s:10:"allowEmpty";b:1;s:3:"not";b:0;s:5:"range";a:2:{i:0;s:7:"#FFFFFF";i:1;s:7:"#000000";}s:6:"strict";b:0;s:22:"enableClientValidation";b:1;s:6:"except";N;s:7:"message";N;s:2:"on";N;s:4:"safe";b:1;s:11:"skipOnError";b:0;}s:14:"CountValidator";a:10:{s:3:"min";i:1;s:3:"max";i:4;s:4:"safe";b:0;s:9:"tooLittle";N;s:7:"tooMany";N;s:22:"enableClientValidation";b:0;s:6:"except";N;s:7:"message";N;s:2:"on";a:1:{i:0;s:7:"include";}s:11:"skipOnError";b:0;}}s:6:"values";a:2:{s:7:"#FFFFFF";s:5:"white";s:7:"#000000";s:5:"black";}}'
    )
);
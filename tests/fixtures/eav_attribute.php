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
);
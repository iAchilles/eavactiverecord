<?php
/*
 * TextDataType class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
 
/**
 * TextDataType class
 *
 * @version 1.0.0
 */
class TextDataType extends EavValue
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function tableName()
    {
        return '{{eav_attribute_text}}';
    }
} 
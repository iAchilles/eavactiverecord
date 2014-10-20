<?php
/**
 * CountValidator class file
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 * @link https://github.com/iAchilles/eavactiverecord/
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
 
/**
 * CountValidator validates that the multivalued attribute has the allowed number of values.
 *
 * @author Igor Manturov, Jr. <igor.manturov.jr@gmail.com>
 *
 * @since 1.0.2
 */
class CountValidator extends MultivaluedValidator
{
    /**
     * @var Minimum number of values.
     */
    public $min;

    /**
     * @var Maximum number of values.
     */
    public $max;

    /**
     * @var boolean Whether attributes listed with this validator should be considered safe for massive assignment.
     */
    public $safe = false;

    /**
     * @var string The error message used when the attribute contains not enough number of values.
     */
    public $tooLittle;

    /**
     * @var string The error message used when the attribute contains more values than allowed.
     */
    public $tooMany;

    /**
     * @var boolean Whether to perform client-side validation.
     */
    public $enableClientValidation = false;


    protected function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;

        if (!is_array($value))
        {
            return;
        }

        if (!is_null($this->min))
        {
            if (count($value) < $this->min)
            {
                $message = !is_null($this->tooLittle) ? $this->tooLittle
                    : Yii::t('eavactiverecord', '{attribute} contains not enough values (expected at least {min})');
                $this->addError($object, $attribute, $message, array('{min}' => $this->min));
            }
        }


        if (!is_null($this->max))
        {
            if (count($value) > $this->max)
            {
                $message = !is_null($this->tooMany) ? $this->tooMany
                    : Yii::t('eavactiverecord', '{attribute} contains too many values (expected no more than {max})');
                $this->addError($object, $attribute, $message, array('{max}' => $this->max));
            }
        }
    }
} 
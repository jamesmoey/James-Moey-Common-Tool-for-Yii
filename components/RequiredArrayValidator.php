<?php
class RequiredArrayValidator extends CRequiredValidator {

  /**
   * Validates a single attribute.
   * This method should be overriden by child classes.
   * @param CModel $object the data object being validated
   * @param string $attribute the name of the attribute to be validated.
   */
  protected function validateAttribute($object, $attribute) {
    $values = $object->$attribute;
    if (is_array($values) && count($values)>0) return true;
    else {
      $this->addError($object, $attribute, 'Please select a value from the list above.');
      return false;
    }
  }
}

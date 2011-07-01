<?php
class JsonDataTypeBehavior extends CActiveRecordBehavior {
  
  public $variables;
  protected $values = array();

  public function __get($name) {
    $field = "_".strtolower($name);
    /** @var CActiveRecord $model*/
    $model = $this->getOwner();
    if (!isset($this->values[$field])) {
      $this->values[$field] = new CList(json_decode($model->getAttribute($field), true));
    }
    return $this->values[$field];
  }

  public function __set($name, $value) {
    $field = "_".strtolower($name);
    /** @var CActiveRecord $model*/
    $model = $this->getOwner();
    if (is_array($value)) {
      $this->values[$field] = new CList(json_encode($value));
    } else if ($value instanceof CList) {
      $this->values[$field] = $value;
    } else {
      $this->values[$field] = new CList(json_encode(array($value)));
    }
  }

  public function beforeSave($event) {
    /** @var CActiveRecord $model*/
    $model = $this->getOwner();
    foreach ($this->values as $field=>$list) {
      /** @var CList $list */
      $model->setAttribute($field, json_encode($list->toArray()));
    }
    return true;
  }

  public function canGetProperty($name) {
    $field = "_".strtolower($name);
    return (array_search($field, $this->variables) !== false);
  }

  public function canSetProperty($name) {
    $field = "_".strtolower($name);
    return (array_search($field, $this->variables) !== false);
  }
}
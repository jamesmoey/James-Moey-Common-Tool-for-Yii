<?php

class FormatConverterBehavior extends CActiveRecordBehavior {

  public $dateVariables;
  public $datetimeVariables;

  private $dateFormat;
  private $dateTimeFormat;
  
  private $_dateVariables = array();
  private $_datetimeVariables = array();

  /**
   * @param CActiveRecord $model
   */
  public function attach($model) {
    parent::attach($model);
    if ($this->dateVariables == "*")
      $this->_dateVariables = $this->getColumnWithDbType("date");
    else if (strlen($this->dateVariables) > 3)
      $this->_dateVariables = $this->getColumnWithDbType("date", explode(",", $this->dateVariables));

    if ($this->datetimeVariables == "*")
      $this->_datetimeVariables = $this->getColumnWithDbType("datetime");
    else if (strlen($this->datetimeVariables) > 3)
      $this->_datetimeVariables = $this->getColumnWithDbType("datetime", explode(",", $this->dateVariables));
  }

  protected function getColumnWithDbType($type, $filter = array()) {
    /** @var CActiveRecord $model */
    $model = $this->getOwner();
    /** @var CDbColumnSchema $column */
    $columns = array();
    foreach ($model->getMetaData()->columns as $column) {
      if ($column->dbType == $type) {
        if (count($filter) == 0 || in_array($column->name, $filter)) {
          $columns[] = $column->name;
        }
      }
    }
    return $columns;
  }

  public function afterFind($event) {
    $this->afterConstruct($event);
  }

  public function afterConstruct($event) {
    $model = $this->getOwner();
    $attributes = $model->getAttributes();
    $this->convert(array($attributes), array($this, 'setModelValue'));
  }

  public function setModelValue($field, $value) {
    $model = $this->getOwner();
    $model->setAttribute($field, $value);
  }

  public function convert($attributesRef, $function = NULL) {
    $attributes =& $attributesRef[0];
    foreach ($this->_dateVariables as $field) {
      $time = strtotime($attributes[$field]);
      if ($time <= 0) {
        if ($function != NULL) call_user_func($function, $field, '');
        else $attributes[$field] = '';
        continue;
      }
      if (!empty($this->dateFormat)) {
        $date = Yii::app()->getDateFormatter()->format($this->dateFormat, $time);
      }
      if (strlen($date) == 0 && Yii::app()->hasComponent("formatter")) {
        /** @var CFormatter $formatter */
        $formatter = Yii::app()->getComponent('formatter');
        $date = $formatter->formatDate($time);
      }
      if (strlen($date) == 0) $date = date('d-m-Y', $time);
      if (strlen($date) != 0) {
        Yii::trace("Converting ".$attributes[$field].' to '.$date);
        if ($function != NULL) call_user_func($function, $field, $date);
        else $attributes[$field] = $date;
      }
    }
    foreach ($this->_datetimeVariables as $field) {
      $time = strtotime($attributes[$field]);
      if ($time <= 0) {
        call_user_func($function, $field, '');
        continue;
      }
      if (!empty($this->dateTimeFormat)) {
        $date = Yii::app()->getDateFormatter()->format($this->dateTimeFormat, $time);
      }
      if (strlen($date) == 0 && Yii::app()->hasComponent("formatter")) {
        /** @var CFormatter $formatter */
        $formatter = Yii::app()->getComponent('formatter');
        $date = $formatter->formatDatetime($time);
      }
      if (strlen($date) == 0) $date = date('d-m-Y H:i:s', $time);
      if (strlen($date) != 0) {
        Yii::trace("Converting ".$attributes[$field].' to '.$date);
        if ($function != NULL) call_user_func($function, $field, $date);
        else $attributes[$field] = $date;
      }
    }
  }

  public  function revert($attributesRef, $function = NULL) {
    $attributes =& $attributesRef[0];
    foreach ($this->_dateVariables as $field) {
      $time = 0;
      $date = $attributes[$field];
      if (!empty($this->dateFormat)) $time = CDateTimeParser::parse($date, $this->dateFormat);
      if ($time == 0 && Yii::app()->hasComponent("formatter")) {
        /** @var CFormatter $formatter */
        $formatter = Yii::app()->getComponent('formatter');
        $time = CDateTimeParser::parse($date, $formatter->dateFormat);
      }
      if ($time == 0) $time = CDateTimeParser::parse($date, Yii::app()->getLocale()->getDateFormat());
      if ($time == 0) $time = strtotime($date);
      if ($time > 0) {
        if ($function != NULL) call_user_func($function, $field, date('Y-m-d', $time));
        else $attributes[$field] = date('Y-m-d', $time);
      }
    }
    foreach ($this->_datetimeVariables as $field) {
      $time = 0;
      $date = $attributes[$field];
      if (!empty($this->dateTimeFormat)) $time = CDateTimeParser::parse($date, $this->dateTimeFormat);
      if ($time == 0 && Yii::app()->hasComponent("formatter")) {
        /** @var CFormatter $formatter */
        $formatter = Yii::app()->getComponent('formatter');
        $time = CDateTimeParser::parse($date, $formatter->datetimeFormat);
      }
      if ($time == 0) $time = CDateTimeParser::parse($date, Yii::app()->getLocale()->getDateTimeFormat());
      if ($time == 0) $time = strtotime($date);
      if ($time > 0) {
        if ($function != NULL) call_user_func($function, $field, date('Y-m-d H:i:s', $time));
        else $attributes[$field] = date('Y-m-d', $time);
      }
    }
  }

  public function beforeSave($event) {
    /** @var CActiveRecord $model */
    $model = $this->getOwner();
    $attributes = $model->getAttributes();
    $this->revert(array($attributes), array($this, 'setModelValue'));
    return true;
  }

  public function setDateFormat($format) {
    $this->dateFormat = $format;
  }

  public function setDateTimeFormat($format) {
    $this->dateTimeFormat = $format;
  }
}
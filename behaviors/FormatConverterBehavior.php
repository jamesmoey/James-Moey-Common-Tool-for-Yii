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

  public function beforeSave($event) {
    /** @var CActiveRecord $model */
    $model = $this->getOwner();
    foreach ($this->_dateVariables as $field) {
      $time = 0;
      $date = $model->getAttribute($field);
      if (!empty($this->dateFormat)) $time = CDateTimeParser::parse($date, $this->dateFormat);
      if ($time == 0 && Yii::app()->hasComponent("formatter")) {
        /** @var CFormatter $formatter */
        $formatter = Yii::app()->getComponent('formatter');
        $time = CDateTimeParser::parse($date, $formatter->dateFormat);
      }
      if ($time == 0) $time = CDateTimeParser::parse($date, Yii::app()->getLocale()->getDateFormat());
      if ($time == 0) $time = strtotime($date);
      if ($time > 0) $model->setAttribute($field, date('Y-m-d', $time));
    }
    foreach ($this->_datetimeVariables as $field) {
      $time = 0;
      $date = $model->getAttribute($field);
      if (!empty($this->dateTimeFormat)) $time = CDateTimeParser::parse($date, $this->dateTimeFormat);
      if ($time == 0 && Yii::app()->hasComponent("formatter")) {
        /** @var CFormatter $formatter */
        $formatter = Yii::app()->getComponent('formatter');
        $time = CDateTimeParser::parse($date, $formatter->datetimeFormat);
      }
      if ($time == 0) $time = CDateTimeParser::parse($date, Yii::app()->getLocale()->getDateTimeFormat());
      if ($time == 0) $time = strtotime($date);
      if ($time > 0) $model->setAttribute($field, date('Y-m-d H:i:s', $time));
    }
    return true;
  }

  public function setDateFormat($format) {
    $this->dateFormat = $format;
  }

  public function setDateTimeFormat($format) {
    $this->dateTimeFormat = $format;
  }
}
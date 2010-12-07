<?php
/**
 * Created by JetBrains PhpStorm.
 * User: james_2
 * Date: 4/12/10
 * Time: 9:36 PM
 * To change this template use File | Settings | File Templates.
 */
 
class CustomFormatter extends CFormatter {
  public function formatDatetime($value) {
    return Yii::app()->getDateFormatter()->formatDateTime($value);
  }

  public function formatDate($value) {
    if (!is_numeric($value)) $value = strtotime($value);
    return parent::formatDate($value);
  }

  public function formatTime($value) {
    if (!is_numeric($value)) $value = strtotime($value);
    return parent::formatTime($value);
  }

  public function formatCurrency($value) {
    return Yii::app()->getNumberFormatter()->formatCurrency($value, 'AUD');
  }

  public function formatCheckbox($value) {
    return CHtml::checkBox("", $value, array("disabled"=>"disabled"));
  }
}

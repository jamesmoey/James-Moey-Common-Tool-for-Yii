<?php

class UuidConversionBehavior extends CActiveRecordBehavior
{
  public $column = array('id');

  public function beforeFind(CModelEvent $event) {
    $criteria = $event->criteria;
    foreach ($this->column as $column) {
      if (preg_match_all(
        '/`'.preg_quote($column).'`\s*=\s*[\\]*[\'"]*([:0-9a-z-]+)[\\]*[\'"]*/',
        $criteria->condition,
        $matches,
        PREG_SET_ORDER
      ) !== false) {
        foreach($matches as $match) {
          if (strpos($match[1], ':') !== false) {
            $binary = self::convertStringUuid($criteria->params[$match[1]]);
            $criteria->params[$match[1]] = $binary;
          } else {
            $binary = self::convertStringUuid($match[1]);
            $replacement = str_replace($match[1], $binary, $match[0]);
            $criteria->condition = str_replace($match[0], $replacement, $criteria->condition);
          }
        }
      }
    }
  }

  public function afterFind($event) {
    /** @var $model CActiveRecord */
    $model = $event->sender;
    foreach ($this->column as $column) {
      $model->{$column} = self::convertBinaryUuid($model->{$column});
    }
  }

  public function beforeSave(CModelEvent $event) {
    /** @var $model CActiveRecord */
    $model = $event->sender;
    foreach ($this->column as $column) {
      $model->{$column} = self::convertStringUuid($model->{$column});
    }
  }

  public function afterSave($event) {
    /** @var $model CActiveRecord */
    $model = $event->sender;
    foreach ($this->column as $column) {
      $model->{$column} = self::convertBinaryUuid($model->{$column});
    }
  }

  public static function convertBinaryUuid($binary) {
    if (empty($binary)) return null;
    $uuid = array_shift(unpack('H*', $binary));
    return preg_replace("/([0-9a-f]{8})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{12})/", "$1-$2-$3-$4-$5", $uuid);
  }

  public static function convertStringUuid($string) {
    if (empty($string)) return null;
    $uuid = str_replace("-", "", $string);
    return pack('H*', $uuid);
  }
}

<?php
class UuidGeneratorBehavior extends CActiveRecordBehavior
{
  public function beforeValidate($e) {
    /** @var CActiveRecord $model **/
    $model = $this->owner;
    if ($model->getIsNewRecord() && $model->getPrimaryKey() == NULL) {
      try {
        if (Yii::app()->getDb()->getDriverName() == "mysql") {
          $command = Yii::app()->getDb()->createCommand('SELECT UUID()');
          /** @var $reader CDbDataReader */
          $reader = $command->query();
          $uuid = $reader->readColumn(0);
        }
      } catch (CDbException $e) {
      }
      if (empty($uuid)) {
        if (function_exists('com_create_guid') === true) {
          $uuid = trim(com_create_guid(), '{}');
        } else {
          $uuid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', 
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535));
        }
      }
      $model->setPrimaryKey($uuid);
    }
  }
}

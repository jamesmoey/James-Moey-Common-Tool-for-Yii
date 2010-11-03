<?php
class GeneratorBehavior extends CActiveRecordBehavior
{
  public function beforeValidate($e) {
    /** @var CActiveRecord $model **/
    $model = $this->owner;
    if ($model->getIsNewRecord() && $model->getPrimaryKey() == NULL) {
      $command = Yii::app()->getDb()->createCommand('SELECT UUID()');
      /** @var $reader CDbDataReader */
      $reader = $command->query();
      $uuid = $reader->readColumn(0);
      $model->setPrimaryKey($uuid);
    }
  }
}

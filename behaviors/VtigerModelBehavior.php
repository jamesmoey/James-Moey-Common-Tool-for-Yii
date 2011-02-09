<?php

class VtigerModelBehavior extends CActiveRecordBehavior {

  protected $cf = false;

  public function beforeSave($event) {
    /** @var CActiveRecord $model */
    $model = $this->getOwner();
    if ($model->getIsNewRecord() && ($model->getPrimaryKey() == null || $model->getPrimaryKey() == "")) {
      $model->getDbConnection()->createCommand("UPDATE vtiger_crmentity_seq SET id = id + 1")->execute();
      $id = $model->getDbConnection()->createCommand("SELECT id FROM vtiger_crmentity_seq")->queryScalar();
      $crmentity = new Crmentity();
      $crmentity->crmid = $id;
      $crmentity->smownerid = $crmentity->smcreatorid = 1;
      $crmentity->createdtime = $crmentity->modifiedtime = date('Y-m-d H:i:s');
      $crmentity->setype = isset($model->type) ? $model->type : get_class($model);
      $crmentity->save();
      $classname = false;
      /** @var $schema CDbSchema */
      $schema = $crmentity->getDbConnection()->getSchema();
      if ($schema->getTable(get_class($model) . 'cf') != null) $classname = get_class($model).'cf';
      else if ($schema->getTable(substr(get_class($model),0, -1) . 'cf') == null) $classname = substr(get_class($model),0, -1) . 'cf';
      if ($classname != false) {
        /** @var $cf CActiveRecord */
        $this->cf = new $classname();
        $this->cf->setPrimaryKey($id);
      }
      $model->setPrimaryKey($id);
    }
    return true;
  }

  public function afterSave($event) {
    if ($this->cf != false) {
      $this->cf->save();
    }
    return true;
  }
}

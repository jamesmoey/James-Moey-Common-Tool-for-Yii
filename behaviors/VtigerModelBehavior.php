<?php

class VtigerModelBehavior extends CActiveRecordBehavior {
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
      $model->setPrimaryKey($id);
    }
  }
}

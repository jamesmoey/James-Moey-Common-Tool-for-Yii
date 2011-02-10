<?php

class ActiveModelHelper {
  /**
   * Check if a model has the behavior
   * @static
   * @param CComponent $record
   * @param string $behaviorClassName The class name of the behavior
   * @return boolean
   */
  public static function modelHasBehavior($record, $behaviorClassName) {
    $behaviors = $record->behaviors();
    foreach($behaviors as $behavior=>$config) {
      if (isset($record->$behavior))  {
        if ($behaviorClassName == get_class($record->$behavior)) {
          return true;
        }
      }
    }
    return false;
  }
}

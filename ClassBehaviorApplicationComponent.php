<?php

class ClassBehaviorApplicationComponent extends CApplicationComponent {

  protected $behaviorList = array();

  public function setBehaviorsConfiguration($configurations) {
    foreach ($configurations as $name => $behaviors) {
      if (strpos($name, '.') !== false) {
        $class = Yii::import($name, true);
      } else {
        $class = $name;
      }
      $this->behaviorList[$name] = $behaviors;
    }
  }

  public function getBehaviors($name) {
    if (isset($this->behaviorList[$name])) return $this->behaviorList[$name];
    else array();
  }
}

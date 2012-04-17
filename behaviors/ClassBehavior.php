<?php
class ClassBehavior extends CBehavior {

  public $alias;

  /**
   * Attach behavior to a component
   *
   * @param \CComponent $owner
   */
  public function attach($owner) {
    parent::attach($owner);
    if (Yii::app()->hasComponent("behaviors")) {
      /** @var $behaviorComponent ClassBehaviorApplicationComponent */
      $behaviorComponent = Yii::app()->getComponent("behaviors");
      if (!empty($this->alias)) {
        $behaviors = $behaviorComponent->getBehaviors($this->alias);
      } else {
        $behaviors = $behaviorComponent->getBehaviors(get_class($owner));
      }
      if (!empty($behaviors)) {
        $owner->attachBehaviors($behaviors);
      }
    }
  }
}

<?php
class ClassBehavior extends CBehavior {

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
      $behaviors = $behaviorComponent->getBehaviors(get_class($owner));
      if (!empty($behaviors)) {
        $owner->attachBehaviors($behaviors);
      }
    }
  }
}

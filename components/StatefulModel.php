<?php
/**
 * Model that allow tracking of attribute setting. It also allow user to commit changes and rollback to original state.
 */
abstract class StatefulModel extends CModel {

  private $attributeValues = array();
  private $changedAttribute = array();

  private $track = false;

  public function toArray($changedOnly = false, $includeEmpty = true) {
    $list = array();
    if ($changedOnly) {
      foreach ($this->changedAttribute as $name=>$value) {
        if ($includeEmpty || $value !== null) $list[$name] = $value;
      }
    } else {
      foreach ($this->attributeNames() as $field) {
        if (array_key_exists($field, $this->changedAttribute)) {
          if ($includeEmpty || $this->changedAttribute[$field] !== null) $list[$field] = $this->changedAttribute[$field];
        } else if (isset($this->attributeValues[$field]) && $this->attributeValues[$field] != null) {
          $list[$field] = $this->attributeValues[$field];
        } else if ($includeEmpty) {
          $list[$field] = null;
        }
      }
    }
    return $list;
  }

  /**
   * Set an Attribute. Chain-able
   *
   * @param $name
   * @param $value
   * @return StatefulModel
   */
  public function set($name, $value) {
    $this->__set($name, $value);
    return $this;
  }

  public function __set($name, $value) {
    if (in_array($name, $this->attributeNames()) === true) {
      if ($this->track) {
        if (!isset($this->attributeValues[$name])) $this->attributeValues[$name] = null;
        if ($this->attributeValues[$name] !== $value) {
          $this->changedAttribute[$name] = $value;
        }
      } else $this->attributeValues[$name] = $value;
    } else {
      parent::__set($name, $value);
    }
  }

  public function __get($name) {
    if (in_array($name, $this->attributeNames()) === true) {
      if (isset($this->changedAttribute[$name])) return $this->changedAttribute[$name];
      else return $this->attributeValues[$name];
    } else {
      return parent::__get($name);
    }
  }

  public function __isset($name) {
    if (in_array($name, $this->attributeNames()) === true) {
      if (isset($this->changedAttribute[$name]) && $this->changedAttribute[$name] != null) return true;
      else if (isset($this->attributeValues[$name]) && $this->attributeValues[$name] != null) return true;
      return false;
    } else {
      return parent::__isset($name);
    }
  }

  public function __unset($name) {
    if (in_array($name, $this->attributeNames()) === true) {
      $this->changedAttribute[$name] = null;
    } else {
      parent::__unset($name);
    }
  }

  /**
   * Start / Stop a tracking for attributes changes. Chain-able.
   *
   * @param bool $startTracking
   * @return StatefulModel
   */
  public function trackChanges($startTracking = true) {
    $this->track = $startTracking;
    return $this;
  }

  /**
   * Commit changes. Chain-able.
   *
   * @return StatefulModel
   */
  public function commitChanges() {
    foreach ($this->changedAttribute as $name=>$value) {
      $this->attributeValues[$name] = $value;
    }
    $this->changedAttribute = array();
    return $this;
  }

  /**
   * Rollback the changes. Chain-able
   *
   * @return StatefulModel
   */
  public function rollbackChanges() {
    $this->changedAttribute = array();
    return $this;
  }

  /**
   * Get the changed field.
   *
   * @param string $name optional empty to return all.
   * @return array|null
   */
  public function getChanges($name = "") {
    if ($name == "") return $this->changedAttribute;
    else if (isset($this->changedAttribute[$name])) return $this->changedAttribute[$name];
    else return null;
  }

  /**
   * Get the original value.
   *
   * @param string $name optional empty to return all.
   * @return array|null
   */
  public function getOriginal($name = "") {
    if ($name == "") return $this->attributeValues;
    else if (isset($this->attributeValues[$name])) return $this->attributeValues[$name];
    else return null;
  }
}

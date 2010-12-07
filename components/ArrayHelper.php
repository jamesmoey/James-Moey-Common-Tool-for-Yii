<?php
class ArrayHelper {

  /**
   * Search for an value using key provided in the second and etc parameter
   * @static
   * @param array Search Context
   * @param string keys.....
   * @return mixed
   */
  public static function searchOnPath() {
    $array = func_get_arg(0);
    $params = func_get_args();
    array_shift($params);
    if (is_array($array)) {
      $key = array_shift($params);
      if (isset($array[$key])) {
        if (isset($params[0])) {
          return call_user_func_array(
            'ArrayHelper::searchOnPath',
            array_merge(
              $array[$key],
              $params
            )
          );
        } else return $array[$key];
      }
    }
    return false;
  }

  /**
   * Deep array search for a key. Return the value of the matched array. Return @param $default if not found.
   *
   * @static
   * @param string $key
   * @param array $array
   * @param mixed $default
   * @return mixed
   */
  public static function deepSearchOnKey($key, $array, $default = false) {
    foreach ($array as $k=>$v) {
      if ($k == $key) {
        return $v;
      } else if (is_array($v)) {
        if (($v = ArrayHelper::deepSearchOnKey($key, $v, false)) !== false) {
          return $v;
        }
      }
    }
    return $default;
  }

  /**
   * Deep array search for a value. The value is expected to be a value of an array not key. Return if it exist or not.
   *
   * @static
   * @param string $value
   * @param array $array
   * @param bool $exactmatch
   * @return bool
   */
  public static function deepSearchContainValue($value, $array, $exactmatch = true) {
    foreach ($array as $k=>$v) {
      if (is_array($v)) {
        if (ArrayHelper::deepSearchContainValue($value, $v, $exactmatch) !== false) {
          return true;
        }
      } else if ($exactmatch && (string)$v == (string)$value) {
        return true;
      } else if (!$exactmatch && stripos((string)$v, (string)$value) !== false) {
        return true;
      }
    }
    return false;
  }

  /**
   * Recursive implode an array.
   *
   * @static
   * @param string $glue
   * @param array $array
   * @return string
   */
  public static function recursiveImplode($glue, $array) {
    foreach ($array as $key=>$value) {
      if (is_array($value)) {
        $array[$key] = ArrayHelper::recursiveImplode($glue,$value);
      }
    }
    return implode($glue, $array);
  }

  /**
   * Recursively remove empty value from the array.
   * @static
   * @param array $array by reference
   * @return void
   */
  public static function trimEmptyValue(&$array) {
    foreach ($array as $key=>$value) {
      if (is_array($value)) {
        ArrayHelper::trimEmptyValue($array[$key]);
      } else if (strlen($value) == 0) {
        unset($array[$key]);
      }
    }
  }

  /**
   * Extract an array of value from the model attribute
   * @static
   * @param CActiveRecord[] $models
   * @param string $attribute
   * @return array
   */
  public static function extractListOfValuesFromModels($models, $attribute) {
    $result = array();
    foreach ($models as $m) {
      $result[] = $m->$attribute;
    }
    return $result;
  }

  /**
   * Flatten Models Attribute.
   *
   * @static
   * @param CActiveRecord[] $models
   * @param array|true $extraAttributes, empty array as default. Set to true to collect all the properties by reflection.
   * @return array of 'Model ID' => { 'Attribute' => 'Attribute Value' }
   */
  public static function flattenModelsAttribute($models, $extraAttributes = array()) {
    $result = array();
    foreach ($models as $m) {
      $values = $m->getAttributes();
      if ($extraAttributes === true) {
        $refObj = new ReflectionObject($m);
        foreach($refObj->getProperties() as $refProp) {
          $attr = $refProp->getName();
          $value = $refProp->getValue($m);
          $values[$attr] = $value;
        }
      } else {
        foreach($extraAttributes as $attr) {
          $values[$attr] = $m->$attr;
        }
      }
      ArrayHelper::removeObjectFromArray($values);
      $result[$m->getPrimaryKey()] = $values;
    }
    return $result;
  }

  public static function removeObjectFromArray(&$array) {
    foreach ($array as $attr=>$value) {
      if (is_object($value)) unset($array[$attr]);
      else if (is_object($attr)) unset($array[$attr]);
      else if (is_callable($value)) unset($array[$attr]);
      else if (is_resource($value)) unset($array[$attr]);
      else if (is_array($value)) ArrayHelper::removeObjectFromArray($array[$attr]);
    }
  }

  /**
   * Run through a list of CActiveRecord models and remove all the duplicate.
   * @static
   * @param CActiveRecord[] $list
   * @return CActiveRecord[] Unique array of CActiveRecord
   */
  public static function uniqueModelList($list) {
    $keys = array();
    $result = array();
    foreach ($list as $model) {
      $key = $model->getPrimaryKey();
      if (array_search($key, $keys) === false) {
        $keys[] = $model->getPrimaryKey();
        $result[] = $model;
      }
    }
    return $result;
  }

  public static function generateListOfMonth($format = 'm') {
    $list = array();
    for($i = 1; $i <= 12; $i++) {
      $list[$i] = date($format, mktime(0,0,0,$i,1,2000));
    }
    return $list;
  }

  public static function generateListOfYear($format = 'Y') {
    $list = array();
    for($i = 0; $i < 10; $i++) {
      $year = date($format)+$i;
      $list[$year] = $year;
    }
    return $list;
  }
}

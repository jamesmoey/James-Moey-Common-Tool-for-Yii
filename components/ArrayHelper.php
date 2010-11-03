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
   * @return bool
   */
  public static function deepSearchContainValue($value, $array) {
    foreach ($array as $k=>$v) {
      if ($v == $value) {
        return true;
      } else if (is_array($v)) {
        if (ArrayHelper::deepSearchContainValue($value, $v, false) !== false) {
          return true;
        }
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
}

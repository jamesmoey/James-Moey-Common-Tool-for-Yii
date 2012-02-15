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
   * Extract subset of the pass in array. Match based on the array of value pass in.
   *
   * @static
   * @param array $value
   * @param array $array Array of array.
   * @param bool $exactmatch
   */
  public static function extractSubsetArray($value, $array, $exactmatch = true) {
    $value = array_unique($value);
    $map = $array;
    foreach ($value as $v) {
      $result = array();
      foreach ($map as $key => $row) {
        if (ArrayHelper::deepSearchContainValue($v, $row)) {
          $result[$key] = $row;
        }
      }
      $map = $result;
    }
    if ($exactmatch) {
      $result = array();
      foreach ($map as $key=>$row) {
        if (count($row) == count($value)) {
          $result[$key] = $row;
        }
      }
      $map = $result;
    }
    return $map;
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
   * Extract a single array from the array of models
   * @static
   * @param CActiveRecord[] $models
   * @param string $attribute
   * @return array
   */
  public static function extractListOfValuesFromModels($models, $attribute) {
    $result = array();
    foreach ($models as $m) {
      $result[] = ArrayHelper::extractValueFromModel($m, $attribute);
    }
    return $result;
  }

  /**
   * Extract multiple attribute from the array of models
   *
   * @static
   * @param CActiveRecord[] $models
   * @param array $attributes, array of New Fieldname => Reference To Attribute Name. Use dot to refer to relation's atttribute name.
   * @param bool $treatMultipleAsNew Treat result in array as new row.
   * @return array
   */
  public static function extractMultipleListOfValuesFromModels($models, $attributes, $treatMultipleAsNew = false, $excludeNull = false) {
    $result = array();
    foreach ($models as $m) {
      $values = array();
      foreach ($attributes as $key=>$attr) {
        $values[$key] = ArrayHelper::extractValueFromModel($m, $attr, $excludeNull);
        if ($excludeNull && $values[$key] == NULL) continue 2;
      }
      if ($treatMultipleAsNew) {
        foreach ($attributes as $key=>$attr) {
          if (is_array($values[$key])) {
            $treatedRow = $values;
            unset($treatedRow[$key]);
            foreach ($values[$key] as $value) {
              if ($excludeNull && $value == NULL) continue 3;
              $result[] = $treatedRow + array($key=>$value);
            }
          }
        }
        if (!isset($treatedRow)) $result[] = $values;
      } else {
        $result[] = $values;
      }
    }
    return $result;
  }

  /**
   * Extract a single value from a model
   * @static
   * @param CActiveRecord $model
   * @param string $attribute, use dot to specify relation's attribute
   * @return mixed
   */
  public static function extractValueFromModel($model, $attribute, $excludeNull = false) {
    if (strpos($attribute, '.') === false) {
      if (isset($model->$attribute)) return $model->$attribute;
      else return null;
    } else {
      $listAttribute = explode(".", $attribute);
      $attr = array_shift($listAttribute);
      $relation = $model->getActiveRelation($attr);
      if ($relation === null) return null;
      if (!isset($model->$attr)) return null;
      if ($relation instanceof CBelongsToRelation || $relation instanceof CHasOneRelation) {
        return ArrayHelper::extractValueFromModel($model->$attr, join('.', $listAttribute));
      } else {
        $list = array();
        foreach ($model->$attr as $relations) {
          $value = ArrayHelper::extractValueFromModel($relations, join('.', $listAttribute));
          if ($excludeNull && $value == null) continue;
          $list[] = $value;
        }
        return $list;
      }
    }
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
      if (is_array($m->getPrimaryKey())) {
        $result[implode(':', $m->getPrimaryKey())] = $values;
      } else {
        $result[$m->getPrimaryKey()] = $values;
      }
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
      if (! method_exists($model, "getPrimaryKey")) continue;
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

  /**
   * Flatten multi dimension array to single dimension.
   * 
   * @static
   * @param array $array
   * @param bool $prepandKey
   * @return array
   */
  public static function flatternArray($array, $prepandKey = false) {
    $result = array();
    foreach ($array as $k=>$v) {
      if (is_array($v)) {
        if ($prepandKey === false) {
          $result = self::flatternArray($v, false) + $result;
        } else if (($prepandKey === true) && (!is_integer($k))) {
          $result += self::flatternArray($v, $k);
        } else if ($prepandKey === true) {
          $result += self::flatternArray($v, $k);
        } else {
          $result += self::flatternArray($v, $prepandKey.'.'.$k);
        }
      } else {
        if (($prepandKey === false) || ($prepandKey === true)) $result[$k] = $v;
        else $result[$prepandKey.'.'.$k] = $v;
      }
    }
    return $result;
  }

  /**
   * Extract a column from an array.
   * @example
   * <code>
   * <?php
   *   $array = array(
   *     array('name'=>array('first'=>'abc', 'last'=>'zzz')),
   *     array('name'=>array('first'=>'efg', 'last'=>'kkk')),
   *   );
   *   assert(array("abc","efg") == extractArray($array, "name.first"));
   * ?>
   * </code>
   *
   * @static
   * @param array $array
   * @param string $extractionPoint
   * @return array
   */
  public static function extractArray($array, $extractionPoint) {
    if (is_array($array)) {
      $list = array();
      $points = explode('.', $extractionPoint);
      $pointKey = array_shift($points);
      foreach ($array as $key=>$value) {
        if (is_int($key)) {
          $result = ArrayHelper::extractArray($value, $extractionPoint);
          if ($result !== null) {
            $list[] = $result;
          }
        } else {
          if ($key == $pointKey) {
            if (count($points) == 0) return $value;
            else return ArrayHelper::extractArray($value, join('.', $points));
          }
        }
      }
      if (count($list) == 0) return null;
      else return $list;
    } else return $array;
  }

  /**
   * Sort Model by the attribute value.
   *
   * @static
   * @param CActiveRecord[] $list
   * @param string $attribute
   * @param int $sort_flag
   *  SORT_REGULAR - compare items normally (don't change types)
   *  SORT_NUMERIC - compare items numerically
   *  SORT_STRING - compare items as strings
   *  SORT_LOCALE_STRING - compare items as strings, based on the current locale. Added in PHP 4.4.0 and 5.0.2, it uses the system locale, which can be changed using setlocale().
   *
   * @return array
   */
  public static function sortModelList($list, $attribute, $sort_flag = SORT_REGULAR) {
    $sort = array();
    foreach ($list as $item) {
      $type = $item->getTableSchema()->getColumn($attribute)->dbType;
      if (($type == "date") && ($type == "datetime")) $key = strtotime($item->getAttribute($attribute));
      else $key = $item->getAttribute($attribute);
      $sort[$key] = $item;
    }
    ksort($sort, $sort_flag);
    return $sort;
  }

  /**
   * Concat String from multiple array together and form a new array.
   * Example
   * <code>
   * $name = array("apple", "pear", ..);
   * $price = array(1.2, 0.6, ..);
   * $nameWithPrice = ArrayHelper.concatStringArray("{1} - {2}", $name, $price);
   * </code>
   * $nameWithPrice will contain array("apple - 1.2", "pear - 0.6", ...)
   *
   * @static
   * @param string $format of the result, {x} for replacement holder, x start from 1
   * @param array 2 or more array.
   *
   * @return array
   */
  public static function concatStringArray() {
    $format = func_get_arg(0);
    $resultArray = array();
    for ($i = 1; $i < func_num_args(); $i++) {
      $array = func_get_arg($i);
      foreach ($array as $k=>$value) {
        if (!isset($resultArray[$k])) $resultArray[$k] = $format;
        $resultArray[$k] = str_replace('{'.$i.'}', $value, $resultArray[$k]);
      }
    }
    return $resultArray;
  }

  public static function intersectAssocRecursive($arr1, $arr2) {
    if (!is_array($arr1) || !is_array($arr2)) {
      return $arr1 == $arr2;
    }
    $commonkeys = array_intersect(array_keys($arr1), array_keys($arr2));
    $ret = array();
    foreach ($commonkeys as $key) {
      $ret[$key] = ArrayHelper::intersectAssocRecursive($arr1[$key], $arr2[$key]);
    }
    return $ret;
  }

  public static function diffRecursive($aArray1, $aArray2) {
    $aReturn = array();
    foreach ($aArray1 as $mKey => $mValue) {
      if (array_key_exists($mKey, $aArray2)) {
        if (is_array($mValue)) {
          $aRecursiveDiff = ArrayHelper::diffRecursive($mValue, $aArray2[$mKey]);
          if (count($aRecursiveDiff)) $aReturn[$mKey] = $aRecursiveDiff;
        } else {
          if ($mValue != $aArray2[$mKey]) $aReturn[$mKey] = $mValue;
        }
      } else $aReturn[$mKey] = $mValue;
    }
    return $aReturn;
  }

  /**
   * Create array of model with column value in array index.
   *
   * @static
   * @param CActiveRecord $models[]
   * @param string $column
   * @return array
   */
  public static function createArrayFromModelsWithColumn($models, $column) {
    $list = array();
    foreach ($models as $model) {
      $list[$model->$column] = $model;
    }
    return $list;
  }

  /**
   * Prepend a string to the index of an array. All the row will be prepended.
   *
   * @static
   * @param array $array
   * @param string $prependString
   * @return array
   */
  public static function prependArrayIndex($array, $prependString) {
    $newArray = array();
    foreach ($array as $index=>$value) {
      $newArray[$prependString.$index] = $value;
    }
    return $newArray;
  }
}

<?php

class ArrayHelperTest extends CTestCase {

  protected $fixture = array(
    'asd',
    't1' => array('qwe', 'test'),
    't2' => array('yuyu', 10, 15),
    array('34', 'khlj', 123)
  );

  public function testDeepSearchContainValueInString() {
    $this->assertTrue(ArrayHelper::deepSearchContainValue('test', $this->fixture));
    $this->assertTrue(ArrayHelper::deepSearchContainValue('15', $this->fixture));
    $this->assertFalse(ArrayHelper::deepSearchContainValue('1', $this->fixture));
  }

  public function testDeepSearchContainValueInInteger() {
    $this->assertTrue(ArrayHelper::deepSearchContainValue(123, $this->fixture));
    $this->assertTrue(ArrayHelper::deepSearchContainValue(34, $this->fixture));
    $this->assertFalse(ArrayHelper::deepSearchContainValue(1, $this->fixture));
  }

  public function testDeepSearchContainValueExactMatch() {
    $this->assertTrue(ArrayHelper::deepSearchContainValue('yuyu', $this->fixture));
    $this->assertFalse(ArrayHelper::deepSearchContainValue('yu', $this->fixture));
    $this->assertFalse(ArrayHelper::deepSearchContainValue('yuy', $this->fixture));
  }

  public function testDeepSearchContainValuePartialMatch() {
    $this->assertTrue(ArrayHelper::deepSearchContainValue('yuyu', $this->fixture, false));
    $this->assertTrue(ArrayHelper::deepSearchContainValue('yu', $this->fixture, false));
    $this->assertFalse(ArrayHelper::deepSearchContainValue('yi', $this->fixture, false));
  }
}

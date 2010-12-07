<?php

include_once(dirname(__FILE__).'/../../components/ArrayHelper.php');

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

  public function testFlattenModel() {
    $m1 = new TestModel();
    $m1->id = time().rand(1,10000);
    $m1->var1 = 'AA';
    $m2 = new TestModel();
    $m2->id = time().rand(1,10000);
    $m2->var1 = new TestModel();
    $list = ArrayHelper::flattenModelsAttribute(array($m1, $m2), true);
    $this->assertEquals(2, count($list));
    $this->assertArrayNotHasKey('var1', $list[$m2->id]);
    $this->assertArrayHasKey('var1', $list[$m1->id]);
    $this->assertContains('test1', $list[$m1->id]);
    $this->assertContains('test1', $list[$m2->id]);
    $list = ArrayHelper::flattenModelsAttribute(array($m1, $m2));
    $this->assertArrayNotHasKey('var1', $list[$m1->id]);
    $list = ArrayHelper::flattenModelsAttribute(array($m1, $m2), array('var1'));
    $this->assertArrayHasKey('var1', $list[$m1->id]);
  }

  /**
   * @expectedException CException
   */
  public function testFlatterModelNotSuchAttribute() {
    $m1 = new TestModel();
    $m1->id = time().rand(1,10000);
    $m1->var1 = 'AA';
    $list = ArrayHelper::flattenModelsAttribute(array($m1), array('var2'));
  }
}

class TestModel extends CModel {
  public
    $id,
    $var1;

  public function getPrimaryKey() {
    return $this->id;
  }
  
  public function getAttributes() {
    return array('test1'=>'test1');
  }

  /**
   * Returns the list of attribute names of the model.
   * @return array list of attribute names.
   * @since 1.0.1
   */
  public function attributeNames() {
    return array('test1');
  }
}
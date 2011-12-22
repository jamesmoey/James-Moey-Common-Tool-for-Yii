<?php

include_once(dirname(__FILE__).'/../../components/StatefulModel.php');

class StatefulModelTest extends CTestCase {

  /** @var MockUpObject $test */
  protected $test;

  public function setUp() {
    $this->test = Yii::createComponent(array(
      "class"=>"MockUpObject",
      "id"=>123412,
      "name"=>"Abc",
    ));
  }

  public function testSameSetAttribute() {
    $this->test->trackChanges();
    $this->test->name = "Abc";
    $this->assertArrayNotHasKey("name", $this->test->getChanges());
    $this->test->address = null;
    $this->assertArrayNotHasKey("address", $this->test->getChanges());
    $this->test->address = "Bc";
    $this->assertArrayHasKey("address", $this->test->getChanges());
  }

  public function testSetAttribute() {
    $this->test->id = 10;
    $this->assertEquals(10, $this->test->id);
    $this->test->name = "JJ";
    $this->assertEquals("JJ", $this->test->name);
    $this->test->address = "";
    $this->assertEquals("", $this->test->address);
  }

  public function testTrackAttributeChanges() {
    $this->test->trackChanges();
    $this->test->address = "";
    $this->test->name = "Me";
    $this->assertEquals("Me", $this->test->getChanges("name"));
    $this->assertEquals("", $this->test->getChanges("address"));
    $this->assertEquals(null, $this->test->getChanges("id"));
    $this->assertArrayHasKey("name", $this->test->getChanges());
    $this->assertArrayHasKey("address", $this->test->getChanges());
  }

  public function testCommitChanges() {
    $this->test->trackChanges();
    $this->test->address = "George St";
    $this->test->name = "Me";
    $this->test->commitChanges();
    $this->test->name = "Bbb";
    $this->assertEquals(null, $this->test->getChanges("address"));
    $this->assertEquals("Bbb", $this->test->getChanges("name"));
  }

  public function testRollback() {
    $this->test->trackChanges();
    $this->test->address = "George St";
    $this->test->name = "Me";
    $this->test->rollbackChanges();
    $this->assertEquals(null, $this->test->getChanges("address"));
    $this->assertEquals(null, $this->test->getChanges("name"));
  }

  public function testAttributeUnset() {
    $this->test->trackChanges();
    unset($this->test->name);
    $this->assertEquals(null, $this->test->getChanges("name"));
    $this->assertArrayHasKey("name", $this->test->getChanges());
    $this->test->rollbackChanges();
    $this->assertEquals("Abc", $this->test->name);
    $this->assertEquals(false, isset($this->test->address));
  }

  public function testToArray() {
    $this->test->trackChanges();
    unset($this->test->name);
    $this->test->address = "George St";
    $this->test->age = '';

    $array = $this->test->toArray(false, false);
    $this->assertArrayHasKey("id", $array);
    $this->assertArrayNotHasKey("name", $array);
    $this->assertArrayHasKey("address", $array);
    $this->assertArrayNotHasKey("code", $array);
    $this->assertArrayHasKey("age", $array);
    
    $array = $this->test->toArray(false, true);
    $this->assertArrayHasKey("id", $array);
    $this->assertArrayHasKey("name", $array);
    $this->assertArrayHasKey("address", $array);
    $this->assertArrayHasKey("code", $array);
    $this->assertEquals(null, $array['name']);
    $this->assertEquals(null, $array['code']);
    $this->assertArrayHasKey("age", $array);

    $array = $this->test->toArray(true, false);
    $this->assertArrayNotHasKey("id", $array);
    $this->assertArrayNotHasKey("name", $array);
    $this->assertArrayHasKey("address", $array);
    $this->assertArrayNotHasKey("code", $array);
    $this->assertArrayHasKey("age", $array);

    $array = $this->test->toArray(true, true);
    $this->assertArrayNotHasKey("id", $array);
    $this->assertArrayHasKey("name", $array);
    $this->assertArrayHasKey("address", $array);
    $this->assertArrayNotHasKey("code", $array);
    $this->assertArrayHasKey("age", $array);
  }
}

class MockUpObject extends StatefulModel {

  /**
   * Returns the list of attribute names of the model.
   * @return array list of attribute names.
   * @since 1.0.1
   */
  public function attributeNames() {
    return array(
      "id",
      "name",
      "age",
      "address",
      "code",
    );
  }
}
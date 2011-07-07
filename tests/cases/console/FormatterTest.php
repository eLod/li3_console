<?php

namespace li3_console\tests\cases\console;

use li3_console\console\Formatter;
use \lithium\data\collection\DocumentSet as Collection;
use \lithium\data\Entity;

class FormatterTest extends \lithium\test\Unit {
    protected function assertFormatted($formatted, $obj, $msg = false) {
	$this->assertEqual($formatted, Formatter::format($obj), $msg);
    }

    public function testFormatCollection() {
	$data = array(
	    "foo" => "bar",
	    "int" => 1,
	    "float" => 3.4,
	    "array" => array(1,2,"string"),
	);
	$collection = new Collection(array('data' => $data));
	$this->assertFormatted(json_encode($data), $collection);
	$mdata = array_fill(0, 10, $data);
	$collection = new Collection(array('data' => $mdata));
	$this->assertFormatted(json_encode($mdata), $collection);
    }

    public function testFormatEntity() {
	$data = array(
	    "foo" => "bar",
	    "int" => 1,
	    "float" => 3.4,
	    "array" => array(1,2,"string"),
	);
	$entity = new Entity(array('data' => $data));
	$this->assertFormatted(json_encode($data), $entity);
    }

    public function testFormatArray() {
	$data = array(
	    "foo" => "bar",
	    "int" => 1,
	    "float" => 3.4,
	    "array" => array(1,2,"string"),
	);
	$this->assertFormatted(json_encode($data), $data);
    }

    public function testFormatClosure() {
	$closure = function() {};
	$this->assertFormatted("closure", $closure);
    }

    public function testFormatBasicTypes() {
	$this->assertFormatted("true", true);
	$this->assertFormatted("false", false);
	$this->assertFormatted("null", null);
	$string = "abcdefg1234'\"+!%/=(){}";
	$this->assertFormatted($string, $string);
    }
}

?>

<?php

namespace li3_console\tests\cases\console;

use li3_console\console\Formatter;
use li3_console\tests\mocks\data\Collection;
use lithium\data\Entity;
use lithium\action\Request;
use lithium\action\Response;
use lithium\action\Controller;
use lithium\core\Object;

class FormatterTest extends \lithium\test\Unit {
    protected function assertFormatted($formatted, $obj, $msg = false) {
	$this->assertEqual($formatted, Formatter::format($obj), $msg);
    }

    public function testFormatCollection() {
	$data = array(
	    "foo" => "bar",
	    "int" => 1,
	    "float" => 3.4,
	    "array" => array(1,2,"string")
	);
	$collection = new Collection(array('data' => $data));
	$this->assertFormatted("<Collection data:" . json_encode($data) . ">", $collection);
	$mdata = array_fill(0, 5, $data);
	$collection = new Collection(array('data' => $mdata));
	$this->assertFormatted("<Collection data:" . json_encode($mdata) . ">", $collection);
    }

    public function testFormatEntity() {
	$data = array(
	    "foo" => "bar",
	    "int" => 1,
	    "float" => 3.4,
	    "array" => array(1,2,"string")
	);
	$entity = new Entity(array('data' => $data));
	$this->assertFormatted("<Entity data:" . json_encode($data) . ">", $entity);
    }

    public function testFormatRequest() {
	$request = new Request(array('url' => '/test/url'));
	$this->assertFormatted("<Request url:'/test/url'>", $request);
    }

    public function testFormatResponse() {
	$response = new Response(array('body' => array('Test response.')));
	$this->assertFormatted(
	    "<Response status:200/'OK' type:'text/html' body:'Test response.'>",
	    $response
	);
    }

    public function testFormatController() {
	$controller = new Controller(array('request' => new Request(array('url' => '/test/url'))));
	$controller->response = new Response(array('body' => array('Test response.')));
	$this->assertFormatted("<Controller " .
				"request:<Request url:'/test/url'> " .
				"response:<Response status:200/'OK' type:'text/html' body:'Test response.'>>",
				$controller);
    }

    public function testFormatPlainObject() {
	$object = new Object();
	$this->assertFormatted("<Object >", $object);
    }

    public function testFormatTruncating() {
	$entity = new Entity(array('data' => array('string' => str_repeat("a", 500))));
	$should_show = 25 - strlen("{\"string\":\"");
	$this->assertPattern(
	    '/^<Entity data:{"string":"a{' . $should_show . '}\.\.\.>$/',
	    Formatter::format($entity, array('max_length' => 25))
	);
    }

    public function testFormatNestedTruncating() {
	$controller = new Controller(array('response' => array('body' => array(str_repeat("a", 500)))));
	$this->assertPattern(
	    '/response:<Response [^b]+ body:\'a{49}\.\.\.>/',
	    Formatter::format($controller, array('max_length' => 200, 'decay' => 2))
	);
    }

    public function testFormatNestedTruncatingDoesNotGoBelowLimit() {
	$controller = new Controller(array('response' => array('body' => array(str_repeat("a", 500)))));
	$options = array('max_length' => 500, 'decay' => 5, 'min_max_length' => 50);
	$this->assertPattern(
	    '/response:<Response [^b]+ body:\'a{49}\.\.\.>/',
	    Formatter::format($controller, $options)
	);
	for ($i = 0;$i < 4;$i++) {
	    $options = Formatter::propertyOptions($options);
	}
	$this->assertEqual($options, Formatter::propertyOptions($options));
    }
}

?>
<?php

namespace li3_console\console;

use \lithium\data\Collection;
use \lithium\data\Entity;

class Formatter extends \lithium\core\StaticObject {
    public static function format($obj) {
	$params = compact('obj');
	return static::_filter(__FUNCTION__, $params, function($self, $params) {
	    $obj = $params['obj'];
	    if ($obj instanceof Collection) {
		return json_encode($obj->data());
	    } else if ($obj instanceof Entity) {
		return json_encode($obj->data());
	    } else if (is_array ($obj)) {
		return json_encode($obj);
	    } else if ($obj instanceof \Closure) {
		return "closure";
	    } else if (is_bool ($obj)) {
		return $obj ? "true" : "false";
	    } else if (is_null ($obj)) {
		return "null";
	    } else {
		return "" . $obj;
	    }
	});
    }
}

?>

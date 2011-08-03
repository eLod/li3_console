<?php

namespace li3_console\console;

use Closure;
use lithium\data\Collection;
use lithium\data\Entity;
use lithium\action\Request;
use lithium\action\Response;
use lithium\action\Controller;
use dobie\Formatter as DobieFormatter;

class Formatter extends \lithium\core\StaticObject {
    public static function format($obj, array $options = array()) {
	$options = static::options($options);
	$params = compact('obj', 'options');
	return static::_filter(__FUNCTION__, $params, function($self, $params) {
	    extract($params);
	    if (is_object($obj) && !($obj instanceof Closure)) {
		return $self::formatObj($obj, $options);
	    } else {
		return DobieFormatter::format($obj);
	    }
	});
    }

    public static function formatObj($obj, array $options = array()) {
	$properties = static::properties($obj);
	$options = static::options($options);
	$params = compact('obj', 'properties', 'options');
	return static::_filter(__FUNCTION__, $params, function($self, $params) {
	    extract($params);
	    if (is_callable($properties)) {
		$properties_string = $properties($obj, $options);
	    } else {
		$prop_options = $self::propertyOptions($options);
		$property_list = array_map(function ($prop, $cb) use ($obj, $self, $prop_options) {
		    if (is_int($prop)) {
			$prop = $cb;
			$cb = false;
		    }
		    $prop_value = isset($obj->$prop) ? $obj->$prop : null;
		    if (is_callable($cb)) {
			$formatted = $cb($prop_value, $prop_options);
		    } else {
			$formatted = $self::format($prop_value, $prop_options);
		    }
		    return "{$prop}:{$formatted}";
		}, array_keys($properties), array_values($properties));
		$properties_string = $self::truncate(join(" ", $property_list), $options['max_length']);
	    }
	    $s = explode("\\", get_class($obj));
	    $class = array_pop($s);
	    return "<{$class} {$properties_string}>";
	});
    }

    public static function properties($obj) {
	$params = compact('obj');
	return static::_filter(__FUNCTION__, $params, function($self, $params) {
	    extract($params);
	    if (($obj instanceof Collection) || ($obj instanceof Entity)) {
		$properties = function ($obj, $options) use ($self) {
		    $formatted = $self::truncate(json_encode($obj->data()), $options['max_length']);
		    return "data:{$formatted}";
		};
	    } else if ($obj instanceof Request) {
		$properties = array('url');
	    } else if ($obj instanceof Response) {
		$properties = array(
		    'status' => function ($status, $options) use ($self) {
			$status = $self::format($status['code']) . '/' . $self::format($status['message']);
			return $self::truncate($status, $options['max_length']);
		    },
		    'type',
		    'body' => function ($body, $options) use ($self) {
			return $self::truncate($self::format(join(" ", $body)), $options['max_length']);
		    }
		);
	    } else if ($obj instanceof Controller) {
		$properties = array('request', 'response');
	    } else {
		$properties = array();
	    }
	    return $properties;
	});
    }

    public static function options(array $options = array()) {
	$params = compact('options');
	return static::_filter(__FUNCTION__, $params, function($self, $params) {
	    extract($params);
	    $defaults = array(
		'max_length' => 4096,
		'min_max_length' => 32,
		'decay' => 2
	    );
	    return $options + $defaults;
	});
    }

    public static function propertyOptions(array $options = array()) {
	$params = compact('options');
	return static::_filter(__FUNCTION__, $params, function($self, $params) {
	    extract($params);
	    if ($options['max_length'] == $options['min_max_length']) {
		return $options;
	    }
	    $max_length = ceil($options['max_length'] / $options['decay']);
	    if ($max_length < $options['min_max_length']) {
		$max_length = $options['min_max_length'];
	    }
	    return compact('max_length') + $options;
	});
    }

    public static function truncate($str, $length) {
	$params = compact('str', 'length');
	return static::_filter(__FUNCTION__, $params, function($self, $params) {
	    extract($params);
	    if (strlen($str) > $length) {
		$str = substr($str, 0, $length) . "...";
	    }
	    return $str;
	});
    }
}

?>
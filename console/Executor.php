<?php

namespace li3_console\console;

use lithium\core\Libraries;

abstract class Executor extends \lithium\core\Object {
    protected $_use_classes = array();
    protected $_resources_path;
    protected $_result_prompt;
    protected $_output;
    protected $_error;
    protected $_classes = array(
	'formatter' => 'li3_console\console\Formatter',
    );
    protected $_autoConfig = array('use_classes' => 'merge', 'resources_path', 'result_prompt', 'classes' => 'merge', 'output', 'error');

    public function __construct(array $config = array()) {
	$defaults = array(
	    'add_classes' => array(
		array('library' => 'app', 'options' => array('recursive' => true, 'exclude' => '/tests|resources|webroot|index$|^app\\\\config/')),
		array('library' => 'lithium', 'options' => array('recursive' => true, 'path' => '/core')),
		array('library' => 'lithium', 'options' => array('recursive' => true, 'path' => '/util')),
	    ),
	    'use_classes' => array(),
	    'resources_path' => null,
	    'result_prompt' => '=> ',
	    'classes' => $this->_classes,
	);
	parent::__construct($config + $defaults);
    }

    protected function _init() {
	parent::_init();
	foreach($this->_config['add_classes'] as $add) {
	    $this->_use_classes = array_merge($this->_use_classes, Libraries::find($add['library'], $add['options']));
	}
    }

    abstract public function execute(array $code);

    public function stop() {
    }

    protected function codeString(array $code, array $options = array()) {
	$options += array(
	    "add_return" => true,
	    "add_uses" => true,
	    "enclose_php" => false,
	    "glue" => PHP_EOL,
	    "add_bootstrap" => false,
	);
	if ($options["add_return"]) {
	    $level = array_reduce($code, function($level, $line) {
		return $level + strlen(preg_replace('/[^{(]/', "", $line)) - strlen(preg_replace('/[^})]/', "", $line));
	    }, 0);
	    if ($level == 0) {
		$line = count($code) - 1;
		$code[$line] = preg_replace('/;*$/', "", $code[$line]);
		for(;$line >= 0;$line--) {
		    $parts = explode(";", $code[$line]);
		    for($i = count($parts) - 1;$i >= 0;$i--) {
			$level += strlen(preg_replace('/[^{(]/', "", $parts[$i])) - strlen(preg_replace('/[^})]/', "", $parts[$i]));
			if ($level == 0) { //should be reached eventually
			    $foundpos = $i == 0 ? -1 : strlen(join(";", array_slice($parts, 0, $i)));
			    break;
			}
		    }
		    if ($level == 0) {
			break;
		    }
		}
		$returning = trim(substr($code[$line], $foundpos + 1));
		if (!preg_match('/^(echo|return|unset)/', $returning)) {
		    $code[$line] = substr($code[$line], 0, $foundpos + 1)." return (".$returning;
		    $code[count($code) - 1] .= ");";
		} else {
		    $code[] = ";return null;";
		}
	    } else {
		$code[] = ";return null;";
	    }
	}
	if ($options["add_uses"]) {
	    $code = array_merge(array_map(function ($class) { return "use {$class};"; }, $this->_use_classes), array(""), $code);
	}
	if ($options["add_bootstrap"]) {
	    $bootstrap = $options["add_bootstrap"] == true ? LITHIUM_APP_PATH . '/config/bootstrap.php' : $options["add_bootstrap"];
	    $code = array_merge(array_map(function ($file) { return "require \"{$file}\";"; }, (array) $bootstrap), array(""), $code);
	}
	if ($options["enclose_php"]) {
	    $code = array_merge(array("<?php", ""), $code, array("", "?>"));
	}
	return join($options["glue"], $code);
    }

    protected function resource($name) {
	return $this->_resources_path.$name;
    }

    protected function out($output = null, $options = array('nl' => 1)) {
	if (is_callable($this->_output)) {
	    return call_user_func($this->_output, $output, $options);
	} else {
	    return false;
	}
    }

    protected function error($output = null, $options = array('nl' => 1)) {
	if (is_callable($this->_error)) {
	    return call_user_func($this->_error, $output, $options);
	} else {
	    return false;
	}
    }
}

?>

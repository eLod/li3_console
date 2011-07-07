<?php

namespace li3_console\console;

use lithium\core\Libraries;

abstract class Executor extends \lithium\core\Object {
    protected $_vars = array();
    protected $_use_classes = array();
    protected $_resources_path;
    protected $_autoConfig = array('use_classes' => 'merge', 'resources_path');

    public function __construct(array $config = array()) {
	$defaults = array(
	    'add_classes' => array(
		array('library' => 'app', 'options' => array('recursive' => true, 'exclude' => '/tests|resources|webroot|index$|^app\\\\config/')),
		array('library' => 'lithium', 'options' => array('recursive' => true, 'path' => '/core')),
		array('library' => 'lithium', 'options' => array('recursive' => true, 'path' => '/util')),
	    ),
	    'use_classes' => array(),
	    'resources_path' => null,
	);
	parent::__construct($config + $defaults);
    }

    protected function _init() {
	parent::_init();
	foreach($this->_config['add_classes'] as $add) {
	    $this->_use_classes = array_merge($this->_use_classes, Libraries::find($add['library'], $add['options']));
	}
    }

    public function run($code) {
	return $this->execute($code);
    }

    public function stop() {
    }

    abstract protected function execute(array $code);

    protected function codeString(array $code, array $options = array()) {
	$options += array(
	    "add_return" => true,
	    "add_uses" => true,
	    "enclose_php" => false,
	    "glue" => PHP_EOL,
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
		$code[$line] = substr($code[$line], 0, $foundpos + 1)." return (".trim(substr($code[$line], $foundpos + 1));
		$code[count($code) - 1] .= ");";
	    } else {
		$code[] = ";return null;";
	    }
	}
	if ($options["add_uses"]) {
	    $code = array_merge(array_map(function ($class) { return "use {$class};"; }, $this->_use_classes), array(""), $code);
	}
	if ($options["enclose_php"]) {
	    $code = array_merge(array("<?php", ""), $code, array("", "?>"));
	}
	return join($options["glue"], $code);
    }

    protected function extractAndStoreVars($code, array $filter_names = array ()) {
	$filtered = array_merge ($this->varNames(), array('this'), $filter_names);
	foreach ($this->extractVarNames($code) as $name) {
	    if (!in_array($name, $filtered)) {
		$this->setVar($name, null);
	    }
	}
    }

    protected function extractVarNames(array $code) {
	$code_str = "<?php\n" . join("\n", $code) . "\n?>\n";
	$tokens = token_get_all($code_str);
	$declaring_function = false;
	$declaring_function_opening_found = false;
	$level = 0;
	$varnames = array();
	foreach($tokens as $tok) {
	    if (count($tok) > 1) {
		$type = $tok[0];
		$token = trim($tok[1]);
	    } else {
		$type = false;
		$token = trim($tok[0]);
	    }
	    if ($token == "{") {
		$level += 1;
	    } else if ($token == "}") {
		$level -= 1;
	    } else if ($type == T_FUNCTION) {
		$declaring_function = true;
	    } else if ($token == "(" && $declaring_function) {
		$declaring_function_opening_found = true;
	    } else if ($token == ")" && $declaring_function_opening_found) {
		$declaring_function = false;
		$declaring_function_opening_found = false;
	    }
	    if ($type == T_VARIABLE && $level == 0 && !$declaring_function) {
		$varnames[] = substr($token, 1);
	    }
	}
	return array_unique($varnames);
    }

    protected function setVar($name, $val) {
	$this->_vars[$name] = $val;
    }

    protected function getVar($name = null) {
	return array_key_exists($name, $this->_vars) ? $this->_vars[$name] : null;
    }

    protected function varNames() {
	return array_keys($this->_vars);
    }

    protected function resource($name) {
	return $this->_resources_path.$name;
    }
}

?>

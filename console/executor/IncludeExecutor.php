<?php

namespace li3_console\console\executor;

class IncludeExecutor extends \li3_console\console\Executor {
    protected $_vars = array();

    protected function _init() {
	parent::_init();
	set_error_handler(array($this, "errorHandler"), E_ALL | E_STRICT);
    }

    public function stop() {
	parent::stop();
	restore_error_handler();
	foreach (glob($this->resource("include.*.php")) as $path) {
	    unlink($path);
	}
    }

    public function errorHandler($errno, $errstr, $errfile = null, $errline = null) {
	if (!is_null($errfile)) {
	    $errfstr = " (in {$errfile}".(!is_null($errline) ? " at line {$errline}" : "").")";
	} else {
	    $errfstr = "";
	}
	$this->error("[ERROR] ".$errstr.$errfstr);
	return true;
    }

    public function execute(array $code) {
	$this->extractAndStoreVars($code, array('_please_dont_overwrite_path'));
	$_please_dont_overwrite_path = $this->resource("include.".str_replace(" ", "_", microtime()).".php");
	file_put_contents($_please_dont_overwrite_path, $this->codeString($code, array("enclose_php" => true)));
	foreach ($this->varNames() as $name) {
	    $$name = $this->getVar($name);
	}
	$_i_dont_overwrite_return = include($_please_dont_overwrite_path);
	foreach ($this->varNames() as $name) {
	    $this->setVar($name, $$name);
	}
	$formatter = $this->_classes['formatter'];
	$this->out($this->_result_prompt . $formatter::format($_i_dont_overwrite_return));
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
	$tokens = token_get_all($this->codeString($code, array('add_uses' => false, 'add_return' => false, 'enclose_php' => true)));
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
}

?>

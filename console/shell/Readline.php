<?php

namespace li3_console\console\shell;

use lithium\core\Libraries;

class Readline extends \li3_console\console\Shell {
    protected $_use_history;
    protected $_history_file;
    protected $_use_completion;
    protected $_autoConfig = array('prompts' => 'merge', 'exit_command', 'use_history', 'history_file', 'use_completion');

    public function __construct(array $config = array()) {
	$defaults = array(
	    'use_history' => true,
	    'history_file' => null,
	    'use_completion' => true,
	);
	parent::__construct($config + $defaults);
    }

    protected function _init() {
	parent::_init();
	if ($this->_use_history && is_file($this->_history_file)) {
	    readline_read_history($this->_history_file);
	}
	if ($this->_use_completion) {
	    $this->_completions = array_map(function($name) {
		$s = explode("\\", $name);
		return $s[count($s) - 1];
	    }, array_merge (
		Libraries::find('app', array('recursive' => true, 'exclude' => '/tests|resources|webroot|index$|^app\\\\config/')),
		Libraries::find('lithium', array('recursive' => true, 'path' => '/core')),
		Libraries::find('lithium', array('recursive' => true, 'path' => '/util'))
	    ));
	    readline_completion_function(array($this, 'complete'));
	}
    }

    public function read() {
	$lines = parent::read();
	if ($this->_use_history && count($lines) > 0 && $lines[0] != "") {
	    readline_add_history(join(" ", $lines));
	}
	return $lines;
    }

    public function stop() {
	if ($this->_use_history) {
	    readline_write_history($this->_history_file);
	}
	parent::stop();
    }

    protected function readline($prompt_type) {
	return readline($this->_prompts[$prompt_type]);
    }

    protected function complete($input, $index) {
	if ($input == "") {
	    return $this->_completions;
	}
	$return = array_filter($this->_completions, function($name) use ($input) {
	    return strpos($name, $input) === 0;
	});
	return $return;
    }
}

?>

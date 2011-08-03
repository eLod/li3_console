<?php

namespace li3_console\tests\mocks\extensions\command;

class Console extends \li3_console\extensions\command\Console {
    public $history = array();
    public $output = array();

    public function greet() {
	parent::greet();
	$this->history[] = 'greet';
    }

    public function run() {
	parent::run();
	$this->history[] = 'quit';
    }

    public function out($output = null, $options = array()) {
	$this->output[] = $output;
    }

    public function environment() {
	return $this->_environment;
    }
}

?>
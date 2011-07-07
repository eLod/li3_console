<?php

namespace li3_console\tests\mocks\extensions\command;

use li3_console\tests\mocks\console\MockExecutor;
use li3_console\tests\mocks\console\MockShell;

class MockConsole extends \li3_console\extensions\command\Console {
    public $history = array();
    public $output = array();
    public $errors = array();
    public $readCodes = array();
    public $eval_in_execute = false;

    protected function _init() {
	parent::_init();
	$this->shell = new MockShell();
	$this->shell->readLines = array(false);
	$this->executor = new MockExecutor();
    }

    public function out($output = null, $options = array()) {
	$this->output[] = $output;
    }

    public function error($error = null, $options = array()) {
	$this->errors[] = $error;
    }

    public function stop($status = 0, $message = "Exiting") {
	$this->history[] = 'stop';
	parent::stop($status, $message);
    }

    public function shell() {
	return $this->shell;
    }

    protected function readCode() {
	$this->history[] = 'readCode';
	parent::readCode();
	$act = array_shift($this->readCodes);
	return $act;
    }

    protected function execute(array $code = array()) {
	$this->history[] = 'execute';
	if ($this->eval_in_execute) {
	    eval(join(" ", $code).";");
	}
	parent::execute($code);
	return 'test';
    }

    protected function format($obj) {
	$this->history[] = 'format';
	return parent::format($obj);
    }
}

?>

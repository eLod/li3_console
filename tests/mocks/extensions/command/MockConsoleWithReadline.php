<?php

namespace li3_console\tests\mocks\extensions\command;


class MockConsoleWithReadline extends \li3_console\extensions\command\Console {
    public $errors = array();

    protected function _init() {
	new \li3_console\tests\mocks\console\shell\MockReadline(); //loads dummy functions
	parent::_init();
    }

    public function error($error = null, $options = array()) {
	$this->errors[] = $error;
    }

    public function shell() {
	return $this->shell;
    }

    protected function supportsReadline() {
	return true;
    }
}

?>

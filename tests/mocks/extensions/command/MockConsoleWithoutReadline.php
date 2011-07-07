<?php

namespace li3_console\tests\mocks\extensions\command;

class MockConsoleWithoutReadline extends \li3_console\extensions\command\Console {
    public $errors = array();

    public function error($error = null, $options = array()) {
	$this->errors[] = $error;
    }

    public function shell() {
	return $this->shell;
    }

    protected function supportsReadline() {
	return false;
    }
}

?>

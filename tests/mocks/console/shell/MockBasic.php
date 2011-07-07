<?php

namespace li3_console\tests\mocks\console\shell;

class MockBasic extends \li3_console\console\shell\Basic {
    public $output = array();

    protected function _init() {
	parent::_init();
	$this->_input = fopen('php://temp', 'r+');
	$this->_output = fopen('/dev/null', 'w');
    }

    public function stop() {
	fclose($this->_input);
	fclose($this->_output);
    }

    public function input() {
	return $this->_input;
    }

    protected function out($output) {
	$this->output[] = $output;
	parent::out($output);
    }
}

?>

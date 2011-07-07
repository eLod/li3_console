<?php

namespace li3_console\tests\mocks\console;

class MockShell extends \li3_console\console\Shell {
    public $output = array();
    public $errors = array();
    public $history = array();
    public $readLines = array();

    protected function _init() {
	parent::_init();
	$this->_output = fopen('/dev/null', 'w');
	$this->_error = fopen('/dev/null', 'w');
    }

    public function stop() {
	fclose($this->_output);
	fclose($this->_error);
    }

    public function exitCommand() {
	return $this->_exit_command;
    }

    public function mainPrompt() {
	return $this->_prompts['main'];
    }

    public function subPrompt() {
	return $this->_prompts['sub'];
    }

    protected function readline($prompt_type) {
	$this->history[] = array('readline', $prompt_type);
	$line = array_shift($this->readLines);
	return $line;
    }

    protected function out($output) {
	$this->output[] = $output;
	parent::out($output);
    }

    protected function error($error) {
	$this->errors[] = $error;
	parent::error($error);
    }
}

?>

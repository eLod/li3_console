<?php

namespace li3_console\console\shell;

function readline($prompt) {
    ReadlineProxy::out($prompt);
    return ReadlineProxy::readline();
}

function readline_read_history($path) {
    ReadlineProxy::history('read_history');
    return true;
}

function readline_add_history($line) {
    ReadlineProxy::history('add_history');
    ReadlineProxy::cmdhistory($line);
    return true;
}

function readline_write_history($path) {
    ReadlineProxy::history('write_history');
    return true;
}

function readline_completion_function($cb) {
}

class ReadlineProxy extends \lithium\core\StaticObject {
    protected static $_proxy;

    public static function connect($proxy) {
	static::$_proxy = $proxy;
    }

    public static function readline() {
	return static::$_proxy->proxyReadline();
    }

    public static function out($output) {
	static::$_proxy->proxyOut($output);
    }

    public static function history($line) {
	static::$_proxy->proxyHistory($line);
    }

    public static function cmdhistory($line) {
	static::$_proxy->proxyCmdHistory($line);
    }
}


namespace li3_console\tests\mocks\console\shell;

class MockReadline extends \li3_console\console\shell\Readline {
    public $output = array();
    public $history = array();
    public $cmdhistory = array();
    public $stopped = false;

    protected function _init() {
	\li3_console\console\shell\ReadlineProxy::connect($this);
	parent::_init();
	$this->_input = fopen('php://temp', 'r+');
	$this->_output = fopen('/dev/null', 'w');
    }

    public function stop() {
	parent::stop();
	fclose($this->_input);
	fclose($this->_output);
	$this->stopped = true;
    }

    public function input() {
	return $this->_input;
    }

    public function historyFile() {
	return $this->_history_file;
    }

    public function completions() {
	return $this->_completions;
    }

    public function proxyReadline() {
	return fgets($this->_input);
    }

    public function proxyOut($output) {
	$this->out($output);
    }

    public function proxyHistory($line) {
	$this->history[] = $line;
    }

    public function proxyCmdHistory($line) {
	$this->cmdhistory[] = $line;
    }

    protected function out($output) {
	$this->output[] = $output;
	parent::out($output);
    }
}

?>

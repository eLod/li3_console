<?php

namespace li3_console\console;

abstract class Shell extends \lithium\core\Object {
    protected $_input = STDIN;
    protected $_output = STDOUT;
    protected $_error = STDERR;
    protected $_prompts = array(
	'main' => '> ',
	'sub' => '',
    );
    protected $_exit_command;
    protected $_autoConfig = array('prompts' => 'merge', 'exit_command');

    public function __construct(array $config = array()) {
	$defaults = array('prompts' => array(), 'exit_command' => 'quit');
	parent::__construct($config + $defaults);
    }

    public function read() {
	$lines = array ();
	$level = 0;
	while (true) {
	    $line = $this->readline(count($lines) > 0 ? 'sub' : 'main');
	    if ($line === false) { //CTRL-D is pressed
		$this->out("\n");
		$lines = array($this->_exit_command);
		break;
	    }
	    $line = trim($line);
	    $level += $this->getNesting($line);
	    $lines[] = $line;
	    if ($level == 0 && substr($line, -1) != ";") {
		break;
	    }
	}
	return $lines;
    }

    public function stop() {
    }

    abstract protected function readline($prompt_type);

    protected function getNesting($line) {
	return strlen(preg_replace('/[^{(]/', "", $line)) - strlen(preg_replace('/[^})]/', "", $line));
    }

    protected function out($output) {
	return fwrite($this->_output, $output);
    }

    protected function error($error) {
	return fwrite($this->_error, $error);
    }
}

?>

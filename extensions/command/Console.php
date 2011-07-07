<?php

namespace li3_console\extensions\command;

use lithium\core\Environment;
use lithium\core\Libraries;
use li3_console\console\shell\Readline as ReadlineShell;
use li3_console\console\shell\Basic as BasicShell;
use li3_console\console\executor\IncludeExecutor as Executor;
use li3_console\console\Formatter;

class Console extends \lithium\console\Command {
    public $exit_commands = array ('quit', 'exit', 'q');
    public $env = 'development';

    protected function _init() {
	parent::_init();
	Environment::set($this->env);
	$res_path = Libraries::get(true, 'resources').'/tmp/console/';
	$shell_config = array(
	    'prompts' => array('main' => "[{$this->env}]> "),
	    'exit_command' => $this->exit_commands[0],
	);
	if ($this->supportsReadline()) {
	    $this->shell = new ReadlineShell($shell_config + array('history_file' => $res_path."history"));
	} else {
	    $this->error("[WARN] Readline is not supported, falling back to basic shell (no edit mode, history, completion, etc.).");
	    $this->shell = new BasicShell($shell_config);
	}
	$this->executor = new Executor(array('resources_path' => $res_path));
    }

    public function run() {
	set_error_handler(array($this, "errorHandler"), E_ALL | E_STRICT);
	$this->out ("Console running PHP".phpversion()." (". PHP_OS .")");
	while(true) {
	    $code = $this->readCode();
	    if (count ($code) == 1 && in_array ($code[0], $this->exit_commands)) {
		$this->stop();
		break;
	    } else if (count ($code) == 0 || (count($code) == 1 && $code[0] == "")) {
		continue;
	    }
	    $this->out("=> ".$this->format($this->execute($code)));
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

    public function stop($status = 0, $message = "Exiting.") {
	$this->shell->stop();
	$this->executor->stop();
	restore_error_handler();
	if ($message) {
	    ($status == 0) ? $this->out($message) : $this->error($message);
	}
	//parent::stop($status, $message); //we shouldn't terminate as it kills testing, yuck
    }

    protected function readCode() {
	return $this->shell->read();
    }

    protected function execute(array $code = array()) {
	return $this->executor->run($code);
    }

    protected function format($obj) {
	return Formatter::format($obj);
    }

    protected function supportsReadline() {
	return is_callable('readline');
    }
}

?>

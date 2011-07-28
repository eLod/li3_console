<?php

namespace li3_console\extensions\command;

use lithium\core\Environment;
use lithium\core\Libraries;
use li3_console\console\shell\Readline as ReadlineShell;
use li3_console\console\shell\Basic as BasicShell;
use li3_console\console\executor\ExternalExecutor as Executor;

class Console extends \lithium\console\Command {
    public $exit_commands = array ('quit', 'exit', 'q');
    public $env = 'development';

    protected function _init() {
	parent::_init();
	Environment::set($this->env);
	$res_path = Libraries::get(true, 'resources').'/tmp/console/';
	$shell_config = array('prompts' => array('main' => "[{$this->env}]> "), 'exit_command' => $this->exit_commands[0], 'output' => array($this, 'out'));
	if ($this->supportsReadline()) {
	    $this->shell = new ReadlineShell($shell_config + array('history_file' => $res_path."history"));
	} else {
	    $this->error("[WARN] Readline is not supported, falling back to basic shell (no edit mode, history, completion, etc.).");
	    $this->shell = new BasicShell($shell_config);
	}
	$this->executor = new Executor(array('resources_path' => $res_path, 'output' => array($this, 'out'), 'error' => array($this, 'error')));
    }

    public function run() {
	$this->out ("Console running PHP".phpversion()." (". PHP_OS .")");
	while(true) {
	    $code = $this->shell->read();
	    if (count ($code) == 1 && in_array ($code[0], $this->exit_commands)) {
		$this->stop();
		break;
	    } else if (count ($code) == 0 || (count($code) == 1 && $code[0] == "")) {
		continue;
	    }
	    $this->executor->execute($code);
	}
    }

    public function stop($status = 0, $message = "Exiting.") {
	$this->shell->stop();
	$this->executor->stop();
	//parent::stop($status, $message); //we shouldn't terminate as it kills testing, yuck
	if ($message) {
	    ($status == 0) ? $this->out($message) : $this->error($message);
	}
    }

    protected function supportsReadline() {
	return is_callable('readline');
    }
}

?>

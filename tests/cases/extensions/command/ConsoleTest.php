<?php

namespace li3_console\tests\cases\extensions\command;

use lithium\core\Environment;
use li3_console\tests\mocks\extensions\command\Console;

class ConsoleTest extends \lithium\test\Unit {
    protected function getConsole($config = array()) {
	if (is_resource($config)) {
	    $config = array('console' => array('shell' => array('input' => $config)));
	}
	$config += array('console' => array());
	$config['console'] += array(
	    'readline' => false,
	    'greet' => false,
	    'shell' => array()
	);
	$config['console']['shell'] += array('prompts' => array());
	$config['console']['shell']['prompts'] += array('main' => 'test>');
	if (is_null($config['console']['greet'])) {
	    unset($config['console']['greet']);
	}
	return new Console($config);
    }

    public function testSetsEnvironment() {
	$console = $this->getConsole();
	$this->assertEqual("development", $console->environment());
	$this->assertEqual("development", Environment::get());
    }

    public function testRunAndQuit() {
	$input = fopen("php://memory", "r+");
	$console = $this->getConsole(array('console' => array(
	    'greet' => null,
	    'shell' => array('input' => $input)
	)));
	$this->assertEqual(array(), $console->history);
	fputs($input, "quit\n");
	rewind($input);
	$console->run();
	$this->assertEqual(array('greet', 'quit'), $console->history);
	fclose($input);
    }

    public function testOutput() {
	$input = fopen("php://memory", "r+");
	$console = $this->getConsole($input);
	$this->assertEqual(array(), $console->output);
	fputs($input, "\"test output\"\nquit\n");
	rewind($input);
	$console->run();
	$this->assertEqual(array('test>', "=> 'test output'", 'test>', "Exiting."), $console->output);
	fclose($input);
    }

    public function testErrorGoesToOutput() {
	$console = $this->getConsole(array('console' => array('resources' => '/hopefully/invalid')));
	$this->assertEqual(1, count($console->output));
	$this->assertPattern('/^\[ERROR\] resources directory not writable/', $console->output[0]);
    }
}

?>
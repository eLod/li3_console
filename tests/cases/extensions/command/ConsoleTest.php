<?php

namespace li3_console\tests\cases\extensions\command;

use lithium\console\Request;
use lithium\core\Environment;
use li3_console\tests\mocks\extensions\command\MockConsole;

class ConsoleTest extends \lithium\test\Unit {
    public function testSetsEnvironment() {
	$command = new MockConsole();
	$this->assertEqual("development", $command->env);
	$this->assertEqual("development", Environment::get());
    }

    public function testRunAndQuit() {
	$command = new MockConsole();
	$command->readCodes = array(array('quit'));
	$this->assertEqual(array(), $command->history);
	$command->run();
	$this->assertEqual(array('readCode', 'stop'), $command->history);
	$this->assertEqual('Exiting', $command->output[count($command->output) - 1]);
    }

    public function testRunEmptyAndQuit() {
	$command = new MockConsole();
	$command->readCodes = array(array(''), array('quit'));
	$this->assertEqual(array(), $command->history);
	$command->run();
	$this->assertEqual(array('readCode', 'readCode', 'stop'), $command->history);
	$this->assertEqual('Exiting', $command->output[count($command->output) - 1]);
    }

    public function testRun() {
	$command = new MockConsole();
	$command->readCodes = array(array('$a = 3+4'), array('quit'));
	$this->assertEqual(array(), $command->history);
	$command->run();
	$this->assertEqual(array('readCode', 'execute', 'format', 'readCode', 'stop'), $command->history);
	$this->assertEqual('=> test', $command->output[count($command->output) - 2]);
	$this->assertEqual('Exiting', $command->output[count($command->output) - 1]);
    }

    public function testErrorHandler() {
	$command = new MockConsole();
	$command->readCodes = array(array('$a = 3/0'), array('quit'));
	$command->eval_in_execute = true;
	$command->run();
	$this->assertPattern('/^\[ERROR\] Division by zero/', $command->errors[count($command->errors) - 1]);
    }

    public function testErrorHandlerWithoutFile() {
	$command = new MockConsole();
	$command->errorHandler('dontcare', 'test error string');
	$this->assertPattern('/^\[ERROR\] test error string$/', $command->errors[count($command->errors) - 1]);
    }

    public function testUsesReadlineIfPresented() {
	$command = new \li3_console\tests\mocks\extensions\command\MockConsoleWithReadline();
	$this->assertTrue($command->shell() instanceof \li3_console\console\shell\Readline);
	$this->assertEqual(array(), $command->errors);
    }

    public function testShowsWarningIfReadlineNotPresented() {
	$command = new \li3_console\tests\mocks\extensions\command\MockConsoleWithoutReadline();
	$this->assertFalse($command->shell() instanceof \li3_console\console\shell\Readline);
	$this->assertPattern('/^\[WARN\] Readline is not supported/', $command->errors[count($command->errors) - 1]);
    }
}

?>

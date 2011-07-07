<?php

namespace li3_console\tests\cases\console;

use li3_console\tests\mocks\console\MockShell;

class ShellTest extends \lithium\test\Unit {
    public function setUp() {
	$this->shell = new MockShell();
    }

    public function tearDown() {
	$this->shell->stop();
    }

    public function testReadExit() {
	$this->shell->readLines = array(false);
	$this->assertEqual(array(), $this->shell->history);
	$this->shell->read();
	$this->assertEqual(array(array('readline', 'main')), $this->shell->history);
    }

    public function testReadMultipleLines() {
	$this->shell->readLines = array('function() {', '}');
	$this->assertEqual(array(), $this->shell->history);
	$this->shell->read();
	$this->assertEqual(array(array('readline', 'main'), array('readline', 'sub')), $this->shell->history);
    }

    public function testGetNesting() {
	$cases = array(
	    '$a = 3' => 0,
	    '$a = 3; $b = 4' => 0,
	    'function() {}' => 0,
	    '($a = 3' => 1,
	    '$a = 3)' => -1,
	    'function() { $a = function() { }' => 1,
	    'function() {} }' => -1,
	);
	foreach($cases as $case => $expected) {
	    $this->assertEqual($expected, $this->shell->invokeMethod('getNesting', array($case)));
	}
    }

    public function testError() {
	$this->assertEqual(array(), $this->shell->errors);
	$this->shell->invokeMethod('error', array('test error'));
	$this->assertEqual(array('test error'), $this->shell->errors);
    }

    public function testSetsConfig() {
	$this->shell->stop();
	$this->shell = new MockShell(array('exit_command' => 'test', 'prompts' => array('main' => 'testmain', 'sub' => 'testsub')));
	$this->assertEqual('test', $this->shell->exitCommand());
	$this->assertEqual('testmain', $this->shell->mainPrompt());
	$this->assertEqual('testsub', $this->shell->subPrompt());
    }
}

?>

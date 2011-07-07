<?php

namespace li3_console\tests\cases\console\shell;

use li3_console\tests\mocks\console\shell\MockReadline;

class ReadlineTest extends \lithium\test\Unit {
    public function setUp() {
	$this->shell = new MockReadline(array('prompts' => array('test' => 'testprompt> ')));
    }

    public function tearDown() {
	if (! $this->shell->stopped) {
	    $this->shell->stop();
	}
    }

    public function testReadline() {
	fputs($this->shell->input(), "test input line\n");
	rewind($this->shell->input());
	$line = $this->shell->invokeMethod('readline', array('test'));
	$this->assertEqual(array('testprompt> '), $this->shell->output);
	$this->assertEqual("test input line\n", $line);
    }

    public function testUsesHistory() {
	$this->shell->stop();
	$this->shell = new MockReadline(array('history_file' => __FILE__));
	$this->assertEqual(__FILE__, $this->shell->historyFile());
	$this->assertEqual(array('read_history'), $this->shell->history);
	$this->shell->stop();
	$this->assertEqual(array('read_history', 'write_history'), $this->shell->history);
    }

    public function testAddsToHistory() {
	$this->shell->stop();
	$this->shell = new MockReadline(array('history_file' => __FILE__));
	fputs($this->shell->input(), "test input line\n");
	rewind($this->shell->input());
	$this->shell->read();
	$this->assertEqual(array('read_history', 'add_history'), $this->shell->history);
	$this->assertEqual(array("test input line"), $this->shell->cmdhistory);
	$this->shell->stop();
    }

    public function testCompletion() {
	$cases = array(
	    'Env' => array('Environment'),
	    'Lib' => array('Libraries'),
	    'S' => array('StaticObject', 'Set', 'String'),
	);
	foreach($cases as $for => $expected) {
	    $this->assertEqual($expected, array_values($this->shell->invokeMethod('complete', array($for, 0))));
	}
	$this->assertEqual($this->shell->completions(), $this->shell->invokeMethod('complete', array('', 0)));
    }
}

?>

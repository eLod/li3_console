<?php

namespace li3_console\tests\cases\console\shell;

use li3_console\tests\mocks\console\shell\MockBasic;

class BasicTest extends \lithium\test\Unit {
    public function setUp() {
	$this->shell = new MockBasic(array('prompts' => array('test' => 'testprompt> ')));
    }

    public function tearDown() {
	$this->shell->stop();
    }

    public function testReadline() {
	fputs($this->shell->input(), "test input line\n");
	rewind($this->shell->input());
	$line = $this->shell->invokeMethod('readline', array('test'));
	$this->assertEqual(array('testprompt> '), $this->shell->output);
	$this->assertEqual("test input line\n", $line);
    }
}

?>

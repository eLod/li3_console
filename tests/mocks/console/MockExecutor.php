<?php

namespace li3_console\tests\mocks\console;

class MockExecutor extends \li3_console\console\Executor {
    public $executed = 0;

    protected function execute(array $code) {
	$this->executed++;
    }
}

?>

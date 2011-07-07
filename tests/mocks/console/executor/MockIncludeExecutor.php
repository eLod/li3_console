<?php

namespace li3_console\tests\mocks\console\executor;

use \lithium\core\Libraries;

class MockIncludeExecutor extends \li3_console\console\executor\IncludeExecutor {
    protected function resource($name) {
	return self::tmpPath().$name;
    }

    public static function tmpPath() {
	return Libraries::get('li3_console', 'path').'/tests/tmp/';
    }
}

?>

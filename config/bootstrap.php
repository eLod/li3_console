<?php

use lithium\core\Libraries;

$res_dir = Libraries::get('app', 'resources') . '/tmp/console';
if (!is_dir($res_dir) || !is_writable($res_dir)) {
    trigger_error(
	"Resources dir for console (`{$res_dir}`) should be writable for console to work!",
	E_USER_ERROR
    );
}

/**
 * Add libraries from submodules.
 */
Libraries::add('dobie', array(
    'path' => dirname(__DIR__) . '/libraries/dobie/lib'
));

?>
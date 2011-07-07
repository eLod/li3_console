<?php

namespace li3_console\console\executor;

class IncludeExecutor extends \li3_console\console\Executor {
    public function stop() {
	parent::stop();
	foreach (glob($this->resource("include.*.php")) as $path) {
	    unlink($path);
	}
    }

    protected function execute(array $code) {
	$this->extractAndStoreVars($code, array('_please_dont_overwrite_path'));
	$_please_dont_overwrite_path = $this->resource("include.".str_replace(" ", "_", microtime()).".php");
	file_put_contents($_please_dont_overwrite_path, $this->codeString($code, array("enclose_php" => true)));
	foreach ($this->varNames() as $name) {
	    $$name = $this->getVar($name);
	}
	$_i_dont_overwrite_return = include($_please_dont_overwrite_path);
	foreach ($this->varNames() as $name) {
	    $this->setVar($name, $$name);
	}
	return $_i_dont_overwrite_return;
    }
}

?>

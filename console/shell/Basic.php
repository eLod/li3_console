<?php

namespace li3_console\console\shell;

class Basic extends \li3_console\console\Shell {
    protected function readline($prompt_type = 'main') {
	$this->out ($this->_prompts[$prompt_type]);
	return fgets($this->_input);
    }
}

?>

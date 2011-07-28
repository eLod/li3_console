<?php

namespace li3_console\console\shell;

class Basic extends \li3_console\console\Shell {
    protected $_input = STDIN; //todo use request's input?

    protected function readline($prompt_type = 'main') {
	$this->out($this->_prompts[$prompt_type], array('nl' => 0));
	return fgets($this->_input);
    }
}

?>

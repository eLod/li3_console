<?php

namespace li3_console\extensions\command;

use lithium\core\Environment;
use lithium\core\Libraries;
use dobie\Console as DobieConsole;

/**
 * An interactive console (REPL, shell) to interact with your li3 application.
 */
class Console extends \lithium\console\Command {
    /**
     * The environment the console is running in.
     *
     * @var string
     */
    protected $_environment = 'development';

    /**
     * Console configuration. See `dobie\Console`.
     *
     * @see dobie\Console
     * @var array
     */
    protected $_console = array();

    /**
     * Auto configuration.
     *
     * @var array
     */
    protected $_autoConfig = array('environment', 'classes' => 'merge', 'console' => 'merge');

    /**
     * dobie\Console instance.
     *
     * @see dobie\Console
     * @var string
     */
    protected $console;

    /**
     * Initializes the console command.
     *
     * @return void
     */
    protected function _init() {
	parent::_init();
	Environment::set($this->_environment);
	$console = $this->_console + array(
	    'resources' => Libraries::get(true, 'resources') . '/tmp/console/',
	    'greet' => array($this, 'greet'),
	    'output' => array($this, 'out'),
	    'error' => array($this, 'out'),
	    'executor' => array(),
	    'shell' => array()
	);
	$li3_classes = array_merge(
	    Libraries::find('app', array(
		'recursive' => true,
		'exclude' => '/tests|resources|webroot|index$|^app\\\\config/'
	    )),
	    Libraries::find('lithium', array('recursive' => true, 'path' => '/core')),
	    Libraries::find('lithium', array('recursive' => true, 'path' => '/util'))
	);
	$console['executor'] += array(
	    'bootstrap' => array(
		"require '" . LITHIUM_APP_PATH . "/config/bootstrap.php';",
		"lithium\core\Environment::set('{$this->_environment}');"
	    ),
	    'uses' => $li3_classes,
	    'formatter' => 'li3_console\console\Formatter'
	);
	$phpList = get_defined_functions();
	$completions = array_merge(
	    $phpList['internal'],
	    array_keys(get_defined_constants()),
	    array_map(function ($class) {
		$s = explode("\\", $class);
		return array_pop($s);
	    }, $li3_classes)
	);
	$console['shell'] += array('completion' => $completions, 'prompts' => array());
	$console['shell']['prompts'] += array('main' => "[{$this->_environment}]> ");
	$this->console = new DobieConsole($console);
    }

    /**
     * Output greeting to the user.
     *
     * @return void
     */
    public function greet() {
	$this->out("li3 console running in {$this->_environment} environment," .
		    " PHP" . phpversion() . " (" . PHP_OS . ")");
    }

    /**
     * Run the console.
     *
     * @return void
     */
    public function run() {
	$this->console->run();
    }
}

?>
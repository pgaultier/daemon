<?php
/**
 * File Write.php
 *
 * PHP version 5.3+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.0.0
 * @link      http://www.sweelix.net
 * @category  demo
 * @package   sweelix.demo
 */

namespace app;

/**
 * Write is a command line which can be runned By the command runner
 * @see sweelix/command
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.0.0
 * @link      http://www.sweelix.net
 * @category  demo
 * @package   sweelix.demo
 */
class Write {

	/**
	 * This command will be called by the runner using a
	 * command line like this one : php runner.php <command class name> [<subcommand method name>]
	 * where
	 *  * command : it is the class name of the command with first letter in lowercase (here Write -> write)
	 *  * subcommand : it is the wanted method name of the target subcommand (here start)
	 *
	 * php runner.php cmd start
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function start() {
		// Init the "Daemon"
		$daemon = new Writed();
		// Run it
		$daemon->run();
		// successfull status code
		return 0;
	}

	/**
	 * This command will be called by the runner using a
	 * command line like this one : php runner.php <command class name> [<subcommand method name>]
	 * where
	 *  * command : it is the class name of the command with first letter in lowercase (here Write -> write)
	 *  * subcommand : it is the wanted method name of the target subcommand (here start)
	 *
	 * php runner.php cmd start
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function stop() {
		// Init the "Daemon"
		$daemon = new Writed();
		// Stop it
		$daemon->stop();
		// successfull status code
		return 0;
	}
}

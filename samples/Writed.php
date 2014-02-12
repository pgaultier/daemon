<?php
/**
 * File Writed.php
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

namespace demo;
use sweelix\daemon\Daemon;

/**
 * Writed is a useless daemon which write dots in the CLI.
 * This daemon is just here to demonstrate how it can work.
 * @see sweelix/daemon
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.0.0
 * @link      http://www.sweelix.net
 * @category  demo
 * @package   sweelix.demo
 */
class Writed extends Daemon {

	/**
	 * @var integer var used to count the number of dots to display
	 */
	private $_loopNum=0;

	/**
	 * Init our daemon. Here we do nothing except showing we are starting
	 * @see \sweelix\daemon\Daemon::setUp()
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function setUp() {
		$this->write('Warming up engine'."\n");
	}

	/**
	 * Clean up our daemon. Here we do nothing except showing we are stopping
	 * @see \sweelix\daemon\Daemon::tearDown()
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function tearDown() {
		$this->write('Cool down the engine'."\n");
	}

	/**
	 * The task we are performing on each loop
	 * We are only writing dots on the screen
	 * @see \sweelix\daemon\Daemon::task()
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function task() {
		$this->_loopNum++;
		sleep(1);
		$this->write('.');
		if($this->_loopNum >= 10) {
			$this->_loopNum = 0;
			$this->write("\n");
		}
	}
}
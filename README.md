# Sweelix\daemon for PHP

## About

Sweelix\daemon is a PHP 5.3+ library which is used to create long running php scripts

## Requirements

This tiny script depends on 

* ext-pcntl : Process control PHP extension
* ext-posix : PHP Posix extension

It's also recommended to install

* ext-proctitle : Extension which allow the proecss name to be changed

## Examples

The most useless daemon :

```php
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
```

## Installation

The preferred method of installation is via [Packagist][] and [Composer][]. Run
the following command to install the package and add it as a requirement to
`composer.json`:

```bash
composer.phar require sweelix/daemon=1.0.0
```

## Running

Now we can run it 

```php
<?php
/**
 * File test.php
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

/**
 * include composer autoloader
 */
require('vendor/autoload.php');
require('Writed.php');

$daemon = new Writed();
// Run it
$daemon->run();
```


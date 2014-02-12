<?php
/**
 * Daemon.php
 *
 * PHP version 5.3+
 *
 * Command file to run a task as a daemon
 *
 * @author	  Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  daemon
 * @package   sweelix.daemon
 */

namespace sweelix\daemon;

/**
 * This is a basic php daemon some kind of event loop
 *
 *
 * @author	  Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  daemon
 * @package   sweelix.daemon
 */
class Daemon {
	/**
	 * @var string tmp tmp path
	 */
	protected $tmp='/tmp';


	/**
	 * @var boolean keepRunning process will run until false
	 */
	protected $isRunning;

	/**
	 * @var boolean allowOutput drives the write() method, @see SIGHUP
	 */
	protected $allowConsoleOutput;

	/**
	 * @var string path to pid file
	 */
	private $_pidFile;

	/**
	 * @var string daemon name
	 */
	private $_daemonName;

	/**
	 * Prepare everything before daemon
	 * starts
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setUp() {

	}

	/**
	 * Clean up daemon before stopping it
	 *
	 * @return void
	 * @since  XXX
	*/
	public function tearDown() {

	}

	/**
	 * Unit task. This method will be looped in
	 * a while(true)
	 *
	 * @return void
	 * @since  XXX
	*/
	public function task() {

	}

	/**
	 * Save information to file
	 *
	 * @param string $file where to store data
	 * @param string $data data to store
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function saveData($file, $data) {
		$fh = fopen($file, 'w');
		fwrite($fh, $data);
		fclose($fh);
	}

	/**
	 * Load data from file
	 *
	 * @param string $file where data has been store
	 *
	 * @return string
	 * @since  XXX
	 */
	protected function loadData($file) {
		$fh = fopen($file, 'r');
		$data = fread($fh, filesize($file));
		fclose($fh);
		return $data;
	}

	/**
	 * Init the daemon and run everything
	 *
	 * @param integer $uid uid to set to the process
	 * @param integer $gid gid to set to the process
	 *
	 * @return void
	 * @since  XXX
	 */
	public function run($uid = null, $gid = null) {
		try {
			// before forking we must go in /tmp
			if(is_dir($this->tmp) === true) {
				chdir($this->tmp);
			} elseif(function_exists('sys_get_temp_dir') === true) {
				$this->tmp = sys_get_temp_dir();
				chdir($this->tmp);
			} else {
				echo 'Cannot change to temporary directory. Current directory may be locked'."\n";
			}
			$pid = pcntl_fork();
			if($pid === -1) {
				echo 'Cannot fork current process'."\n";
			} elseif($pid >0) {
				// we are the parent
				echo 'Daemon '.$this->getDaemonName().' started with pid : '.$pid."\n";
				$activePids = $this->getActivePids(true);
				$activePids[$this->getDaemonName()][] = $pid;
				$this->saveData($this->getPidFile(), json_encode($activePids));
			} else {
				if($uid !== null) {
					if(posix_seteuid($uid) === false) {
						echo "Cannot change process owner to uid : ".$uid."\n";
					}
				}
				if($gid !== null) {
					if(posix_setegid($gid) === false) {
						echo "Cannot change process owner to gid : ".$gid."\n";
					}
				}
				if(function_exists('setproctitle') === true) {
					setproctitle($this->getDaemonName());
				}
				// we are the daemon
				$this->runAsDaemon();
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 * Stop the daemon with pid
	 *
	 * @param integer $pid pid id
	 *
	 * @return void
	 * @since  XXX
	 */
	public function stop($pid = null) {
		try {
			$this->int($pid);
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 * Send sigint to the daemon
	 *
	 * @param integer $pid pid id
	 *
	 * @return void
	 * @since  XXX
	 */
	public function int($pid = null) {
		try {
			if($pid === null) {
				$pid = $this->selectPid();
			}
			if($pid !== null) {
				if(posix_kill($pid, SIGINT) === true) {
					$this->removePidFromPool($pid);
				}
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}
	/**
	 * Terminate the daemon with pid
	 *
	 * @param integer $pid pid id
	 *
	 * @return void
	 * @since  XXX
	 */
	public function term($pid=null) {
		try {
			if($pid === null) {
				$pid = $this->selectPid();
			}
			if($pid !== null) {
				if(posix_kill($pid, SIGTERM) === true) {
					$this->removePidFromPool($pid);
				}
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 * Send signal Usr1 to the daemon with pid
	 * execute @see self::handleSignalUsr1
	 *
	 * @param integer $pid pid id
	 *
	 * @return void
	 * @since  XXX
	 */
	public function usr1($pid=null) {
		try {
			if($pid === null) {
				$pid = $this->selectPid();
			}
			if($pid !== null) {
				posix_kill($pid, SIGUSR1);
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 * Send signal Usr1 to the daemon with pid
	 * execute @see self::handleSignalUsr2
	 *
	 * @param integer $pid pid id
	 *
	 * @return void
	 * @since  XXX
	 */
	public function usr2($pid=null) {
		try {
			if($pid === null) {
				$pid = $this->selectPid();
			}
			if($pid !== null) {
				posix_kill($pid, SIGUSR2);
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 * Run the task once and return
	 *
	 * @param boolean $setUp    if true, execute the startup method
	 * @param boolean $tearDown if true, execute the stop method
	 *
	 * @return void
	 * @since  XXX
	 */
	public function runOnce($setUp = true, $tearDown = true) {
		try {
			$this->allowConsoleOutput = true;
			if($setUp === true) $this->setUp();
			$this->task();
			if($tearDown === true) $this->tearDown();
		} catch (\Exception $e) {
			throw $e;
		}
	}
	/**
	 * Remove the pid from current pool of services launched
	 *
	 * @param integer $pid pid to remove from the pool
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function removePidFromPool($pid) {
		try {
			$activePids = $this->getActivePids(true);
			$daemonPids = $activePids[$this->getDaemonName()];
			unset($activePids[$this->getDaemonName()]);
			$newDaemonPids = array();
			foreach($daemonPids as $i => $daemonPid) {
				if($daemonPid !== $pid) {
					$newDaemonPids[] = $daemonPid;
				}
			}
			if(count($newDaemonPids)>0) {
				$activePids[$this->getDaemonName()] = $newDaemonPids;
			}
			if(count($activePids) > 0) {
				$this->saveData($this->getPidFile(), json_encode($activePids, JSON_PRETTY_PRINT));
			} else {
				unlink($this->getPidFile());
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 * Build the path file to access pid files
	 *
	 * @return string
	 * @since  XXX
	 */
	protected function getPidFile() {
		if($this->_pidFile === null) {
			$this->_pidFile = $this->tmp.DIRECTORY_SEPARATOR.md5(get_class($this)).'-daemons.pid';
		}
		return $this->_pidFile;
	}

	/**
	 * Build the daemon name
	 *
	 * @return string
	 * @since  XXX
	 */
	protected function getDaemonName() {
		if($this->_daemonName === null) {
			$name = str_replace('\\', '-', get_class($this));
			$this->_daemonName = $name;
		}
		return $this->_daemonName;
	}

	/**
	 * Open the pid file and get current active pids
	 *
	 * @param boolean $allDaemons if true all pids for all daemons will be returned
	 *
	 * @return array
	 * @since  XXX
	 */
	protected function getActivePids($allDaemons = false) {
		$activePids = array();
		if(file_exists($this->getPidFile()) === true) {

			$activePids = json_decode($this->loadData($this->getPidFile()), true);
			if($allDaemons === false) {
				if(isset($activePids[$this->getDaemonName()]) === true) {
					$activePids = $activePids[$this->getDaemonName()];
				} else {
					$activePids = array();
				}
			}
		}
		return $activePids;
	}

	/**
	 * Ask the user to choose a pid
	 *
	 * @return integer
	 * @since  XXX
	 */
	protected function selectPid() {
		$activePids = $this->getActivePids(false);
		$result = null;
		if(count($activePids) > 1) {
			$message = 'Select the process (with his pid) to manage : '."\n";
			foreach($activePids as $i => $activePid) {
				$message .= ' '.$i.') '.$activePid."\n";
			}
			$result = $this->prompt($message, null);
			if(isset($activePids[$result]) === true) {
				$result = $activePids[$result];
			} else {
				$result = null;
			}
		} elseif(count($activePids) == 1) {
			$result = $activePids[0];
		}
		return $result;
	}

	/**
	 * Override this method to handle the usr1 signal
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function handleSignalUsr1() {
	}

	/**
	 * Override this method to handle the usr2 signal
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function handleSignalUsr2() {
	}

	/**
	 * Override this method to handle the hup signal.
	 * Output should be stopped
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function handleSignalHup() {
		// no more console output allowed
		$this->allowConsoleOutput = false;
	}

	/**
	 * Override this method to handle the term signal
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function handleSignalTerm() {
		//terminated
		$this->isRunning = false;
	}

	/**
	 * Override this method to handle the int signal
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function handleSignalInt() {
		//ctrl-c
		$this->isRunning = false;
	}

	/**
	 * Global handler
	 *
	 * @param integer $posixSignal posix signal
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function signalHandler($posixSignal) {
		try {
			switch ($posixSignal) {
				case SIGHUP:
					$this->handleSignalHup();
					break;
				case SIGTERM:
					$this->handleSignalTerm();
					break;
				case SIGINT:
					$this->handleSignalInt();
					break;
				case SIGUSR1:
					$this->handleSignalUsr1();
					break;
				case SIGUSR2:
					$this->handleSignalUsr2();
					break;
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 * Write text to the console output
	 * use it instead of echo
	 *
	 * @param string $text text to write
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function write($text) {
		if($this->allowConsoleOutput === true) {
			echo $text;
		}
	}

	/**
	 * Prepare everything and run daemon
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function runAsDaemon() {
		try {
			pcntl_signal(SIGTERM, array($this, 'signalHandler'));
			pcntl_signal(SIGINT, array($this, 'signalHandler'));
			pcntl_signal(SIGHUP, array($this, 'signalHandler'));
			pcntl_signal(SIGUSR1, array($this, 'signalHandler'));
			pcntl_signal(SIGUSR2, array($this, 'signalHandler'));
			declare(ticks=1) {
				$this->allowConsoleOutput = true;
				$this->isRunning = true;
				$this->setUp();
				while($this->isRunning) {
					// running daemon
					$this->task();
				}
				$this->tearDown();
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}
}
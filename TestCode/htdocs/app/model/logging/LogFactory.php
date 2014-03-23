<?php
/*
 * Created on Mar 19, 2008
 * Singleton Log provider
 * Use only the following Zend_Log logging levels
 * Zend_Log::CRIT - Fatal/Critical conditions.  Things that should raise immediate red flags.
 * Zend_Log::ERR - Error conditions
 * Zend_Log::NOTICE - Noteworthy, but not erroneous.
 * Zend_Log::INFO - Discardable diagnostic info
 * Zend_Log::DEBUG - Debugging info.
 */

class LogFactory
{
	private static $_loggers = array();

	public function __destruct()
	{
		foreach (self::$_loggers as $log) {
			$log = null;
		}
	}

	public static function getInstance($logfile = 'default', $user = null)
	{
		if (isset(self::$_loggers[$logfile])) {
			// return existing instance
			return self::$_loggers[$logfile];

		} else {
			// create one
 			if (isset($GLOBALS['config'])) {
 				$config = &$GLOBALS['config'];
				require_once("Zend/Log.php");
				require_once("Zend/Log/Writer/Stream.php");

				// Use default name from config file is none was specified
				if ($logfile == 'default') { $logfile = $config->logging->defaultFilename; }
				if ($logfile == 'email') { $logfile = $config->logging->emailLog; }
				if ($logfile == 'admin') { $logfile = $config->logging->adminLog; }

				self::$_loggers[$logfile] = new Zend_Log();
				$logFormat = $config->logging->format;

				if ($user != null) {
					$logFormat = $config->logging->userFormat . ' [' . $user->id . ' / ' . $user->firstName . ' ' . $user->lastName . '] ';
					$formatter = new Zend_Log_Formatter_Simple($logFormat);
				} else {
					$formatter = new Zend_Log_Formatter_Simple($logFormat);
				}

				$writer = new Zend_Log_Writer_Stream($config->logging->path . '/' . $logfile . '.log');
				$writer->setFormatter($formatter);
				self::$_loggers[$logfile]->addWriter($writer);

				return self::$_loggers[$logfile];

 			} else {
 				throw new Exception('Logging configuration unavailable.');
 			}
		}
	}

}

?>

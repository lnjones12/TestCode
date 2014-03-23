<?php

class OdbcConnectionFactory
{
	private static $_odbcConnection;

	public static function getConnection($connString, $userName, $passCode)
	{
		$odbcConnection = odbc_connect($connString, $userName, $passCode, SQL_CUR_USE_ODBC);

		if ($odbcConnection == false) {
			throw new OdbcException('Failed to connect to database.');
		}

		odbc_autocommit($odbcConnection, false);
		return $odbcConnection;
	}

	public static function getInstance()
	{
		if (isset(self::$_odbcConnection)) {
			// Use the existing connection
			return self::$_odbcConnection;

		} else {
			if (isset($GLOBALS['config'])) {
				$config = &$GLOBALS['config'];

				self::$_odbcConnection = odbc_connect($config->database->odbc->dsn,
							$config->database->odbc->username, $config->database->odbc->password,
							SQL_CUR_USE_ODBC);

				if (self::$_odbcConnection == false) {
					throw new OdbcException('Failed to connect to database.');
				}

				odbc_autocommit(self::$_odbcConnection, false);
				return self::$_odbcConnection;

			} else {
				throw new OdbcException('Database configuration unavailable.');
			}
		}
	}
}

class OdbcException extends Exception {}

?>

<?php

return array(
	'database' => array(
		'mysql' => array(
			'server' => '127.0.0.1',
			'UID' => 'volunteer',
			'PWD' => 'volunteer',
			'Database' => 'test'
			)
	),

	'logging' => array(
		// TODO: figure out all these production paths...
		'path' => 'C:\Apache2.2\sites\TestCode\htdocs\logs',
		'defaultFilename' => 'competitorWireless_com',
		'emailLog' => 'sendMailLog',
		'emailLog' => 'sendMailLog',
		'format' => '%timestamp% (%priorityName%): %message%' . PHP_EOL,
		'userFormat' => '%timestamp% (%priorityName%): %message%',
		'transactionLogName' => 'transactions'
	),

	'documentRoot' => 'C:\Apache2.2\sites\TestCode\htdocs',
	'websiteUrl' => 'http://localhost:8082/',
	'staticHtmlPath' => 'C:\Apache2.2\sites\TestCode\htdocs\app\static',
	'htmlStubPath' => 'C:\Apache2.2\sites\TestCode\htdocs\app\htmlstubs',
	'timeZone' => 'America/New_York',
	'environment' => 'DEV',
	'errors' => E_ERROR,
	'exceptions' => false,
	'platformSlash' => '\\',

	'modules' => array(
		'app/modules/web/controllers' => 'web',
		'app/modules/ajax/controllers' => 'ajax'
	),

	'cache' => array(
		'frontOpts' => array(
			'lifetime' => 60 * 60 * 24, // lifetime (in seconds), null is forever
			'automatic_serialization' => true,
			'caching' => true
			),
		'backOpts' => array(
			'cache_dir' => 'C:/Apache2.2/sites/TestCode/httpdocs/cache',
			'hashed_directory_level' => 2 // probably overkill here, but might help w/ performance
			),
		'frontName' => 'Core',
		'backName' => 'File'
	)
);

?>

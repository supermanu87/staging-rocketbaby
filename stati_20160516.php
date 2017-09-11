<?php

require_once('PHPMailer/class.phpmailer.php');

//To Ensure not hitting by any limit
set_time_limit(0);
ini_set('memory_limit', '1024M');
// Turn off output buffering
ini_set('output_buffering', 'off');
// Turn off PHP output compression
ini_set('zlib.output_compression', false);
ob_implicit_flush(true);
header('Cache-Control: no-cache');
date_default_timezone_set('Europe/Rome');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Config
$config = new Config();
$config->load('cron_analyze');
$registry->set('config', $config);

// Checking are we must to start import process or exit form cron job
new Start($config->get('config_start_filename'));

// Log
$log = new Log($config->get('config_log_filename'));
$registry->set('log', $log);

function error_handler($code, $message, $file, $line) {
	global $log, $config;

	// error suppressed with @
	if (error_reporting() === 0) {
		return false;
	}

	switch ($code) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;
		default:
			$error = 'Unknown';
			break;
	}

	if ($config->get('config_error_log')) {
		$log->write('PHP ' . $error . ':  ' . $message . ' in ' . $file . ' on line ' . $line);
	}

	return true;
}

// Error Handler
#set_error_handler('error_handler');
echo "viva la figa";
#$log->write('ANALYZE PROCEDURE WAS STARTED!!!');

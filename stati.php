<?php
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
$config->load('cron_stati');
$registry->set('config', $config);

// Checking are we must to start import process or exit form cron job
new Start($config->get('config_start_filename'));


//da eliminare
//echo"<pre>";print_r($config->get('config_log_filename'));die;


error_log ('your_content', 3, 'your_log_file');

// Log
$log = new Log($config->get('/var/www/html/staging-rocketbaby/log.txt'));
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
set_error_handler('error_handler');

$log->write('STATE PROCEDURE WAS STARTED!!!');
$log->write('File '.$config->get('config_log_filename').' was successfully deleted');

// Database
$db = new DB($registry, DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

// Url
//$url = new Url(HTTP_SERVER, $config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER);
//$registry->set('url', $url);
//echo"<pre>";print_r($config->get('config_log_filename'));die;

// Request
$request = new Request();
$registry->set('request', $request);

// Files
$files = new Filesystem ($registry);
$registry->set('files', $files);

/*
//Get and check dashboard table.
$db_dashboard = $config->get('config_db_dashboard');
if (!$db->checkExist($db_dashboard)) {
	die;
}

//Get and check shopify table.
$db_shopify = $config->get('config_db_shopify');
//echo"<pre>";print_r($config->get('config_log_filename'));die;
if (!$db->checkExist($db_shopify) || empty($db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM `" . DB_PREFIX . $db_shopify . "`) THEN TRUE ELSE FALSE END) as exist")->row['exist'])) {
	die;
}
//Get and check cambi table.
$db_cambi = $config->get('config_db_cambi');
if (!$db->checkExist($db_cambi)) {
	die;
}
//Get and check spe table.
$db_spe = $config->get('config_db_spe');
if (!$db->checkExist($db_spe) || empty($db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM `" . DB_PREFIX . $db_spe . "`) THEN TRUE ELSE FALSE END) as exist")->row['exist'])) {
	die;
}
//Get and check dashboard table.
$db_simfdb = $config->get('config_db_simfdb');
if (!$db->checkExist($db_simfdb) || empty($db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM `" . DB_PREFIX . $db_simfdb . "`) THEN TRUE ELSE FALSE END) as exist")->row['exist'])) {
	die;
}
//Get and check pro table.
$db_pro = $config->get('config_db_pro');
if (!$db->checkExist($db_pro)) {
	die;
}
//Get and check ric table.
$db_ric = $config->get('config_db_ric');
if (!$db->checkExist($db_ric)) {
	die;
}
//Get and check soldout table.
$db_soldout = $config->get('config_db_soldout');
if (!$db->checkExist($db_soldout)) {
	die;
}
//Get and check cancellati table.
$db_cancellati = $config->get('config_db_cancellati');
if (!$db->checkExist($db_cancellati)) {
	die;
}

*/
//Initialise the Excel row number
$rowCount = 1;

$rowCount++;

//Iterate through each result from the SQL query in turn
//We fetch each database result row into $row in turn
foreach($result->rows as $row) {
	reset($row);
	$log->write('ERROR: The "' .$row['Name'] . '" table return null rows!');
    $rowCount++;
}


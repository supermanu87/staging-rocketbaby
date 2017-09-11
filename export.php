<?php

echo "<script>
function Download(url) {
        document.location.href = url;
};

</script>";


//To Ensure not hitting by any limit
set_time_limit(0);
ini_set('memory_limit', '1024M');
//Increase the the float value for 'TrackingNumero'
ini_set("precision", "15");
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
// Uncomment if you want to check analyze.ok
//new Start($config->get('config_start_filename'));

// Log
$log = new Log($config->get('config_export_log_filename'));
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


require_once DIR_SYSTEM . '/PHPExcel/PHPExcel.php';


// Database
$db = new DB($registry, DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

if(isset($_POST['table'])){
	$table = $_POST['table'];
$log->write('Selezionata tabella '.$table);
}
echo $table;

$result = $db->query("SELECT * FROM " . $table );

//Exit if nothing to export
if (empty($result->num_rows)){
	$log->write('ERROR: The "' . $table . '" table return null rows!');
	die;
}

$columnArray = $config->get('config_export_file_columns');
//Instantiate a new PHPExcel object
$objPHPExcel = new PHPExcel();
//Set the active Excel worksheet to sheet 0
$objPHPExcel->setActiveSheetIndex(0);
//Initialise the Excel row number
$rowCount = 1;
//Fetch result set column information
$header_info = array_keys($result->row);
//Initialise columnlenght counter                
$columnlenght = 0;
foreach ($header_info as $val) {
	//Set column header values                   
	$objPHPExcel->getActiveSheet()->SetCellValue($columnArray[$columnlenght++] . $rowCount, $val);
}
//Make the column headers bold
$objPHPExcel->getActiveSheet()->getStyle($columnArray[0]."1:".$columnArray[$columnlenght]."1")->getFont()->setBold(true);

$rowCount++;
//Iterate through each result from the SQL query in turn
//We fetch each database result row into $row in turn
foreach($result->rows as $row) {
	reset($row);
    for ($i = 0; $i < $columnlenght; $i++) {
        $objPHPExcel->getActiveSheet()->SetCellValue($columnArray[$i] . $rowCount, ((empty($i))?current($row):next($row)));
    }
    $rowCount++;
}

//Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file              
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
//Write the Excel file to filename export_filename in the DIR_OUT directory
//$export_filename = $config->get('config_export_cambi');
$export_filename = $table.'_'.date("YmdHi").'.xlsx';
$objWriter->save(DIR_EXPORT . $export_filename);
$log->write('SUCCES: Export file  "' . $export_filename . '" was saved!');
echo 'SUCCES: Export file  "' . $export_filename . '" was saved!';

$f2d='EXPORT/'.$export_filename;
#echo '<script type="text/javascript">document.location.href = "'.DIR_EXPORT . $export_filename.'";</script';
echo '<script type="text/javascript" >Download("'.$f2d.'");</script>';

?>
<?php
require_once('PHPMailer/class.phpmailer.php');
require_once('time.php');


$time_start = microtime_float();


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
set_error_handler('error_handler');

$log->write('ANALYZE PROCEDURE WAS STARTED!!!');
$log->write('File '.$config->get('config_log_filename').' was successfully deleted');

require_once DIR_SYSTEM . '/PHPExcel/PHPExcel.php';

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

//Get and check dashboard table.
$db_dashboard = $config->get('config_db_dashboard');
if (!$db->checkExist($db_dashboard)) {
	die;
}

//Get and check stk dashboard table.
$db_stk_dashboard = $config->get('config_db_stk_dashboard');
if (!$db->checkExist($db_stk_dashboard)) {
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


//Move all data present in dashboard table to dashboard archive table
if ($db->query("INSERT INTO dashboard_archive SELECT * FROM dashboard")){
			$log->write("SUCCESS: Import data from dashboard table was successfully copy to dashboard_archive");
			if ($db->query("TRUNCATE table dashboard")){
				$log->write("SUCCESS: truncate dashboard table");
				if ($db->query("ALTER TABLE dashboard AUTO_INCREMENT = 1")){
				$log->write("SUCCESS: set autoincrement=1 for dashbpard table");
			}
		}

}



//echo"<pre>";print_r();die;
//Get next analyze id
$next_analyze_id = $db->query("SELECT MAX(analyze_id)+1 as next_analyze_id FROM " . DB_PREFIX . $db_dashboard . "_archive")->row['next_analyze_id'];
//Analyzes and save it on the dashboard file
if($db->query("INSERT INTO " . DB_PREFIX . $db_dashboard . " (analyze_id, Name, CreatedAt, LineitemQuantity, LineitemName, LineitemSku, PaymentMethod, Vendor, OrderSKU_Shopify, SKU_ITM, OrderITM, OrderSKU_ITM, StatusFinale, TrackCode, SPE, SIMFDB_Richiesta, SIMFDB_PRESENTE, PRO,  RIC, Soldout_Report, DBPO_Report, Canceled_Report) SELECT DISTINCT	" . (int)$next_analyze_id . ", CONCAT('#', shpf.Name) as Name, shpf.CreatedAt, shpf.LineitemQuantity, shpf.LineitemName, shpf.LineitemSku, paymnt.Payment, vnd.Vendor, shpf.OrderSKU as OrderSKU_Shopify,  IF(cmb.newSKU IS NULL, shpf.LineitemSku, cmb.newSKU) as SKU_ITM, IF(cmb.newNumber IS NULL, CONCAT('#',shpf.Name), cmb.newNumber)  as OrderITM,  IF(cmb.newSKU IS NULL OR cmb.newNumber IS NULL , shpf.OrderSKU, CONCAT(cmb.newNumber, '-', cmb.newSKU) ) as OrderSKU_ITM,  '' as StatusFinale,  IF(shpf.OrderSKU = spe.OrderSku, CONCAT('', spe.TrackingNumero), '' ) as TrackCode, IF(shpf.OrderSKU = spe.OrderSku, spe.ArtQta, 0) as SPE, IF(simfdb.OrderSku = shpf.OrderSKU, simfdb.QtaRichiesta,0) as SIMFDB_Richiesta, IF(simfdb.OrderSku = shpf.OrderSKU, simfdb.QtaImpegnata,0 ) as SIMFDB_PRESENTE, IF(pro.ArtQta = shpf.OrderSKU, pro.ArtQta,0) as PRO, IF(ric.OrderSku = shpf.OrderSKU, ric.QuantitaRicevuta, 0) as RIC, IF(sldt.OrderSku = shpf.OrderSKU, sldt.QuantitaRichiesta, 0) as Soldout_Report, '' as DBPO_Report, IF(cncl.OrderSku = shpf.OrderSKU, cncl.QuantitaRichiesta, 0) as Canceled_Report FROM " . DB_PREFIX . $db_shopify. " shpf LEFT JOIN " . DB_PREFIX . $db_cambi . " cmb ON (shpf.OrderSKU = cmb.OriginalNumberSKU) LEFT JOIN " . DB_PREFIX . $db_spe . " spe ON (shpf.OrderSku = spe.OrderSku) LEFT JOIN " . DB_PREFIX . $db_simfdb . " simfdb ON (shpf.OrderSku = simfdb.OrderSku) LEFT JOIN " . DB_PREFIX . $db_pro . " pro ON (shpf.OrderSku = pro.OrderSku) LEFT JOIN " . DB_PREFIX . $db_ric . " ric ON (shpf.OrderSku = ric.OrderSku) LEFT JOIN " . DB_PREFIX . $db_soldout . " sldt ON (shpf.OrderSku = sldt.OrderSku) LEFT JOIN " . DB_PREFIX . $db_cancellati . " cncl ON (shpf.OrderSku = cncl.OrderSku) LEFT JOIN vendor vnd ON shpf.Vendor=vnd.Id LEFT JOIN payment_method paymnt on shpf.PaymentMethod=paymnt.Id order by shpf.CreatedAt desc")){


$log->write('SUCCESS: Analyze was generated to "' . DB_PREFIX . $db_dashboard . '" table!');
} else {
	$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $db_dashboard . '" table!');
	die;
}			


$con = mysqli_connect("127.0.0.1","root","huNsB!-1EraS","rocketbabySTG") or die("Some error occurred during connection " . mysqli_error($con));
//$result=$db->query("CALL cambi();");
if (mysqli_multi_query($con, "CALL cambi();")) {
   do {
       if ($result = mysqli_store_result($con)) {
           //
       }
   } while (mysqli_next_result($con));
}


//$result=$db->query("CALL simfdb();");
if (mysqli_multi_query($con, "CALL simfdb();")) {
   do {
       if ($result = mysqli_store_result($con)) {
           //
       }
   } while (mysqli_next_result($con));
}

//$result=$db->query("CALL stati();");
if (mysqli_multi_query($con, "CALL stati();")) {
   do {
       if ($result = mysqli_store_result($con)) {
           //
       }
   } while (mysqli_next_result($con));
}


//Currently limited to queries with less than 27 columns
$columnArray = $config->get('config_export_file_columns');
//Execute the database query
//$result = $db->query("SELECT * FROM " . DB_PREFIX . $db_dashboard );


$result = $db->query("SELECT Name, CreatedAt, LineitemQuantity, LineitemName, LineitemSku, PaymentMethod, Vendor, OrderSKU_Shopify, OrderITM, SKU_ITM, OrderSKU_ITM, StatusFinale, TrackCode, SIMFDB_Richiesta, SIMFDB_PRESENTE, PRO, SPE, RIC, Soldout_Report, DBPO_Report, DBPO_Total, Canceled_Report, DataEvento, SpedizioneDoppia, PossibileSpedizioneDoppia, SplitOrdine FROM " . DB_PREFIX . $db_dashboard );

//Exit if nothing to export
if (empty($result->num_rows)){
	$log->write('ERROR: The "' . DB_PREFIX . $db_dashboard . '" table return null rows!');
	die;
}
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
$export_filename = $config->get('config_export_filename');
$objWriter->save(DIR_OUT . $export_filename);
$log->write('SUCCES: File analyze "' . $export_filename . '" was saved!');

$analFile=$export_filename;

//Save info about analyze to input_file table.
//$export_filename = $config->get('export_time');
$db_input_file = $config->get('config_db_input_file');
if($db->query("INSERT INTO " . DB_PREFIX . $db_input_file . " SET FileName = '" . $export_filename . "', Date = '" . $export_filename . "'")){
	$log->write('SUCCESS: Export info about "' . $export_filename . '" was successfully added to "' . DB_PREFIX . $db_input_file . '" table.');
	$export_file_id = $db->getLastId();
} else {
	$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $db_input_file . '" table!');
	die;
}

//Save info about analyze to dashboard_files table.
$db_dashboard_files = $config->get('config_db_dashboard_files');


$result = $db->query("SELECT analyze_id FROM " . DB_PREFIX . $db_dashboard );

//Get analyze id from dashboard table.
$analyze_id = $result->row['analyze_id'];
//Save info about analyze to dashboard_files table.
if($db->query("INSERT INTO " . DB_PREFIX . $db_dashboard_files . " SET IdDashboard = '" . (int)$analyze_id . "', File = '" . (int)$export_file_id . "'")){
	$log->write('SUCCESS: Export data about "' . $export_filename . '" was successfully added to "' . DB_PREFIX . $db_dashboard_files . '" table.');
} else {
	$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $db_dashboard_files . '" table!');
	die;
}

//Start Dashboard STK excel creation


$result = $db->query("SELECT * from " . DB_PREFIX . $db_stk_dashboard );

//Exit if nothing to export
if (empty($result->num_rows)){
	$log->write('ERROR: The "' . DB_PREFIX . $db_stk_dashboard . '" table return null rows!');
	die;
}

//Unset previous Excel object
unset($objPHPExcel);

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

//Unset previous Excel writer object
unset($objWriter);

//Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file              
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
//Write the Excel file to filename export_filename in the DIR_OUT directory
$export_filename_stk = $config->get('config_export_stk_filename');
$objWriter->save(DIR_OUT . $export_filename_stk);
$log->write('SUCCES: File analyze STK"' . $export_filename_stk . '" was saved!');

$analFile_stk=$export_filename_stk;

$dashboard_date=date("Y-m-d H:i:s");

//Save info about analyze to input_file table.
//$export_filename = $config->get('export_time');
$db_input_file = $config->get('config_db_input_file');
if($db->query("INSERT INTO " . DB_PREFIX . $db_input_file . " SET FileName = '" . $export_filename_stk . "', Date = '" . $dashboard_date . "'")){
	$log->write('SUCCESS: Export info about "' . $export_filename_stk . '" was successfully added to "' . DB_PREFIX . $db_input_file . '" table.');
	$export_file_id = $db->getLastId();
} else {
	$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $db_input_file . '" table!');
	die;
}

//Save info about analyze to dashboard_files table.
$db_stk_dashboard_files = $config->get('config_db_stk_dashboard_files');


$result = $db->query("SELECT stkAnalize_id FROM " . DB_PREFIX . $db_stk_dashboard );

//Get analyze stk id from dashboard stk table.
$stkAnalize_id = $result->row['stkAnalize_id'];
//Save info about analyze to stk_dashboard_files table.
if($db->query("INSERT INTO " . DB_PREFIX . $db_stk_dashboard_files . " SET Date = '" . $dashboard_date . "', stkAnalize_id = '" . (int)$stkAnalize_id . "', FileName = '" . $export_filename_stk . "'")){
	$log->write('SUCCESS: Export data about "' . $export_filename_stk . '" was successfully added to "' . DB_PREFIX . $db_stk_dashboard_files . '" table.');
} else {
	$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $db_stk_dashboard_files . '" table!');
	die;
}

//End creation STK Dashboard


//echo "Num rows ".$result->fetchColumn();  
//echo $result->row['FileName'];

$time_end = microtime_float();
$time = $time_end - $time_start;
$time = round($time, 2);
?><h1><?php echo "Analyze completed in $time seconds\n";?></h1>
<h3><?php echo "Controlla la posta ricevuta per visualizzare la dashboard\n";?></h3>
<?php

//Move temp import tables to archive tables
//foreach ($db_tables as $table){
//	if (strtolower($table) != "shopify" && strtolower($table) != "cambi" && strtolower($table) != "ric" && strtolower($table) != "cancellati" && strtolower($table) != "soldout" && strtolower($table) != "spe"){	
//		if ($db->query("INSERT INTO " . DB_PREFIX . $table . "_archive SELECT * FROM " . DB_PREFIX . $table)){
//			$log->write('SUCCESS: Import data from "' . DB_PREFIX . $table . '" table was successfully copy to "' . DB_PREFIX . $table . '_archive" table.');
//			if ($db->query("TRUNCATE " . DB_PREFIX . $table)){
//				$log->write('SUCCESS: Import data from "' . DB_PREFIX . $table . '" table was successfully deleted.');
//			}
//		}
//	}	
//}

$d=date("l jS \of F Y h:i:s A", strtotime('+2 hours'));
$attachFile=DIR_OUT.$export_filename;
$email = new PHPMailer();
$email->setFrom('giovanni.dsanto@gmail.com','RocketBaby');
//$email->FromName  = 'RocketBaby';
$email->Subject   = 'Dashboard - '.$d;
$email->Body      = "Dashboard Rocketbaby - ".$d;

//$email->AddAddress( 'aj@letisan.com' );
//$email->AddAddress( 'supply@rocketbaby.it' );
$email->AddAddress( 'giovanni.dsanto@gmail.com' );
//$email->AddAddress( 'mario.marra1974@libero.it' );


//$file_to_attach = 'OUT/analyze_20170512172442.xlsx';
$file_to_attach = DIR_OUT.$analFile;
$file_to_attach_stk = DIR_OUT.$export_filename_stk;

$email->AddAttachment( $file_to_attach );
$email->AddAttachment( $file_to_attach_stk);

if(!$email->send())
{
   $log->write('Mailer Error: ' . $email->ErrorInfo.'\n');
   echo "Mailer Error: " . $email->ErrorInfo."\n";
   ?>
   <br>
   <form action="export.php" method="post">
    <select name="table">   
		<option>dashboard</option> 
    <select>
    <input id='submit' type='submit' name = 'export' value = 'Esporta Tabella'>
</form>
   <?php
}
else
{
   $log->write('Message has been sent successfully\n');
   echo "Message has been sent successfully\n";
}

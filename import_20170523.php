<?php
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

require_once('util/files_util.php');


// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

require_once('time.php');


$time_start = microtime_float();

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Config
$config = new Config();
$config->load('cron_import');
$registry->set('config', $config);

// Checking are we must to start import process or exit form cron job
//Uncomment if you want to check import.ok
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

$log->write('IMPORTING PROCEDURE WAS STARTED!!!');
$log->write('File '.$config->get('config_log_filename').' was successfully deleted');

require_once DIR_SYSTEM . '/PHPExcel/PHPExcel.php';

// Database
$db = new DB($registry, DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

//example calling stored procedure
//$result =$db->query("CALL test();");
  //loop the result set
  
//echo "Num rows ".$result->fetchColumn();  
//echo $result->row['FileName'];


// Url
//$url = new Url(HTTP_SERVER, $config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER);
//$registry->set('url', $url);
//echo"<pre>";print_r($config->get('config_log_filename'));die;

// Request
$request = new Request();
$registry->set('request', $request);

// Files
$files = new Filesystem ($registry);
$files->renamingDuplicates();
$registry->set('files', $files);

$importFiles = $files->getIn();

$importFilesSimfdb = $files->getSimfdb();
$importFilesDBPO = $files->getDBPO();
$importFilesPro = $files->getPro();
$importFilesBrt = $files->getBrt();

$fu= new File_Util();
$found_simfdb=0;
$found_dbpo=0;
$found_pro=0;
$found_brt=0;

foreach($importFiles as $i => $file) {
	
/*	if (strpos(strtolower($file->getFilename()), 'brt') !== false) {
		if($found_brt){
			continue;
		}
		
		//Cancellare e fare truncate della tabella brt
		if ($db->query("truncate table brt")){
			$log->write('SUCCESS: Truncate table brt');
			}
		
		$brt_key=$fu->getLastBrt($importFilesBrt);
		$importFilesBrt->seek($brt_key);
		if($file){
			$filetype = $importFilesBrt->getExtension();
			$filePath = $importFilesBrt->getPathname();
			$fileBasename = $importFilesBrt->getBasename('.' . $filetype);
			$splitFileName = $files->splitFilename($fileBasename);
			$fileName = $importFilesBrt->getFilename();
			$found_brt=1;
			$log->write("\n" . 'START FOR ' . $fileName);
		}
	}else */
	
	if (strpos(strtolower($file->getFilename()), 'simfdb') !== false) {
		if($found_simfdb){
			continue;
		}
		
		//Cancellare e fare truncate della tabella simfdb
		if ($db->query("truncate table simfdb")){
			$log->write('SUCCESS: Truncate table SIMFDB');
			}
		
		$simfdb_key=$fu->getLastSimfdb($importFilesSimfdb);
		$importFilesSimfdb->seek($simfdb_key);
		if($file){
			$filetype = $importFilesSimfdb->getExtension();
			$filePath = $importFilesSimfdb->getPathname();
			$fileBasename = $importFilesSimfdb->getBasename('.' . $filetype);
			$splitFileName = $files->splitFilename($fileBasename);
			$fileName = $importFilesSimfdb->getFilename();
			$found_simfdb=1;
			$log->write("\n" . 'START FOR ' . $fileName);
		}
	}elseif (strpos(strtolower($file->getFilename()), 'dbpo') !== false) {
		if($found_dbpo){
			continue;
		}
		
		//Cancellare e fare truncate della tabella dbpo
		if ($db->query("truncate table dbpo")){
			$log->write('SUCCESS: Truncate table DBPO');
			}
		
		$dbpo_key=$fu->getLastDbpo($importFilesDBPO);
		$importFilesDBPO->seek($dbpo_key);
		if($file){
			$filetype = $importFilesDBPO->getExtension();
			$filePath = $importFilesDBPO->getPathname();
			$fileBasename = $importFilesDBPO->getBasename('.' . $filetype);
			$splitFileName = $files->splitFilename($fileBasename);
			$fileName = $importFilesDBPO->getFilename();
			$found_dbpo=1;
			$log->write("\n" . 'START FOR ' . $fileName);
		}
	}elseif (strpos(strtolower($file->getFilename()), 'pro') !== false) {
		if($found_pro){
			continue;
		}
		
		//Cancellare e fare truncate della tabella pro
		if ($db->query("truncate table pro")){
			$log->write('SUCCESS: Truncate table pro');
			}
		
		$pro_key=$fu->getLastPro($importFilesPro);
		$importFilesPro->seek($pro_key);
		if($file){
			$filetype = $importFilesPro->getExtension();
			$filePath = $importFilesPro->getPathname();
			$fileBasename = $importFilesPro->getBasename('.' . $filetype);
			$splitFileName = $files->splitFilename($fileBasename);
			$fileName = $importFilesPro->getFilename();
			$found_pro=1;
			$log->write("\n" . 'START FOR ' . $fileName);
		}
	}
	
	else{
		
	$filetype = $file->getExtension();
	$filePath = $file->getPathname();
	$fileBasename = $file->getBasename('.' . $filetype);
	$splitFileName = $files->splitFilename($fileBasename);
	$fileName = $file->getFilename();
	$log->write("\n" . 'START FOR ' . $fileName);
	}
	//Check if in file name missing split delimiter then we add this informin in log file, end this loop and switch to next input file
	if (count($splitFileName)==1) {
		$log->write('In the file name ' . $fileBasename . ' missing split delimiter "' . $config->get('config_split_delimiter') . '"');
		continue;
	} else if (count($splitFileName)>2) {
		unset($splitFileName[2]);
	}
	
	//Get table name from the split file name array and check is this table name exist in database. If not exist, switch to next import file.
	$table = strtolower($splitFileName[0]);
	
	if (!$db->checkExist($table)) {
		continue;
	}
	
	//Get date and time from the split file name array and convert it to right format.
	$datetime = $splitFileName[1];
	$datetime = substr($datetime, 0, 14);
	$datetime = strtotime($datetime);
	$datetime = date("Y-m-d H:i:s", $datetime);
	
	$db_input_file = $config->get('config_db_input_file');
	
	//Check is this table name exist in database. If not exist, switch to next import file.
	if (!$db->checkExist($db_input_file)) {
		continue;
	}
	
	if($db->query("INSERT INTO " . DB_PREFIX . $db_input_file . " SET FileName = '" . $fileName . "', Date = '" . $datetime . "'")){
		$log->write('SUCCESS: Import data about "' . $fileName . '" was successfully added to "' . DB_PREFIX . $db_input_file . '" table.');
		$input_file_id = $db->getLastId();
	} else {
		$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $db_input_file . '" table!');
		continue;
	}
	
	// Create new PHPExcel object	
	$objReader = PHPExcel_IOFactory::createReaderForFile($filePath);

	if (strtolower($filetype) == 'csv'){
		$objReader->setDelimiter($config->get('config_csv_delimiter_vertical_bar'));
	}

	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filePath);
	
	//index of orders sheet in excel files
	$objPHPExcel->setActiveSheetIndex(0);
	
	//Get the asosietive masive of the import data from the file
	$rows = $objPHPExcel->getActiveSheet()->toArray(null, false, true, true);
	
	//Unset the еitle strings
	$first_row = $config->get('config_import_file_first_row');
	if (!empty($first_row)){
		for ($i = 0; $i < $first_row; $i++) {
			unset($rows[$i]);
		}
	}
	//echo"<pre>";print_r($rows);die;
	
	//Switch to the correct import script using the name of the table
	switch ($table) {
		case 'cambi':
			$add = false;
			$error = false;
			$warning= false;
			$query = $db->query("SELECT count(*) as count from cambi");
			$ricPre=$query->row['count'];

			$virtualRic=$ricPre+sizeof($rows);
			foreach ($rows as $row_number => $row){
				
				$OriginalNumber = str_replace("#","",$row['A']);
				//CAMBI.ORIGINAL NUMBER > max(cambi_archive.OriginalNumber)
				$query = $db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM `" . DB_PREFIX . $table . "_archive` WHERE OriginalNumber >= " . (int)$OriginalNumber . ") THEN TRUE ELSE FALSE END) as exist");
				if (empty($query->row['exist'])){
					if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET DateImported = '" . $datetime . "', OriginalNumber = '" . (int)$OriginalNumber . "', OriginalSku = '" . $db->escape($row['B']) . "', OriginalQuantity = '" . (int)$row['C'] . "', NewNumber = '" . $db->escape($row['D']) . "', NewSku = '" . $db->escape($row['E']) . "', NewQuantity = '" . (int)$row['F'] . "', OriginalNumberSKU = '" . $db->escape($row['A'] . "-" . $row['B']) . "', NewNumberSku = '" . $db->escape($row['D'] . "-" . $row['E']) . "', File = '" . (int)$input_file_id . "' ON DUPLICATE KEY UPDATE DateImported = '" . $datetime . "', OriginalNumber = '" . (int)$OriginalNumber . "', OriginalSku = '" . $db->escape($row['B']) . "', OriginalQuantity = '" . (int)$row['C'] . "', NewNumber = '" . $db->escape($row['D']) . "', NewSku = '" . $db->escape($row['E']) . "', NewQuantity = '" . (int)$row['F'] . "', OriginalNumberSKU = '" . $db->escape($row['A'] . "-" . $row['B']) . "', NewNumberSku = '" . $db->escape($row['D'] . "-" . $row['E']) . "', File = '" . (int)$input_file_id . "'")){
						$add = true;
					} else {
						$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table.');
					}
				} else {
					$error = true;
					$log->write('ERROR: Import data from the row with number ' . $row_number . ' was discarded!');
				}
			}
			$query = $db->query("SELECT count(*) as count from cambi");
			$ricTotal=$query->row['count'];	
			if($ricTotal != $virtualRic)
				$warning=true;
			//If we add any row to the temp import table then we move all data to archive table
			if ($add){
				if ($error){
					$log->write('WARRING: Import data from "' . $fileName . '" was added to "' . DB_PREFIX . $table . '" with some errors!');
				} else {
					$log->write('SUCCESS: Import data from "' . $fileName . '" was successfully added to "' . DB_PREFIX . $table  . '" table.');
				}
			} else {
				$log->write('WARRING: Nothing was added to "' . DB_PREFIX . $table  . '" table from "' . $fileName . '" file!');
			}
			if($warning){
				echo "<script>alert('Attenzione! Non sono stati importate tutte le righe del file CAMBI. Controllare');</script>";
			}
			break;
			
		
		// aka FILE STATUS AL 10.05.2017.Xls  
		case 'brt':   
			$add = false;   
			$error = false;   
			$warning= false;
			$query = $db->query("SELECT count(*) as count from brt");
			$ricPre=$query->row['count'];
			$virtualRic=$ricPre+sizeof($rows);
			foreach ($rows as $row_number => $row){    
			//brt    
				if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET Spedizione = '" . $db->escape($row['E']) . "', DescrizioneEvento = '" . $db->escape($row['N']) . "', Note = '" . $db->escape($row['O']) . "', LinkBrt = '" . $row['P'] . "', Evento = '" . $row['L'] . "', DataEvento = '" . date("y-m-d", strtotime($row['J'])) . "'")){     
					$add = true;    
				} else {
						$error = true;     
						$log->write(' ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table.');    
						} 
				}
				$query = $db->query("SELECT count(*) as count from brt");
				$ricTotal=$query->row['count'];	
				if($ricTotal != $virtualRic)
					$warning=true;	
				if($warning){
					echo "<script>alert('Attenzione! Non sono stati importate tutte le righe del file BRT. Controllare');</script>";
				}	
				
				break;
		
		
		
		case 'dbpo':   
			$add = false;   
			$error = false;   
			$warning= false;
			$query = $db->query("SELECT count(*) as count from ric");
			$ricPre=$query->row['count'];
			$virtualRic=$ricPre+sizeof($rows);
			foreach ($rows as $row_number => $row){    
			//DBPO    
			$log->write("\n" . "INSERT INTO " . DB_PREFIX . $table . " SET InArrivo = (CASE WHEN ((" . (int)$row['T'] . " + " . (int)$row['U'] . ") <= " . (int)$row['S'] . ") THEN (" . (int)$row['S'] . " - " . (int)$row['T'] . " - " . (int)$row['U'] . ") ELSE 0 END), PoNumber = '" . $db->escape($row['B']) . "', TrackingLinkDelivery = '" . $db->escape($row['F']) . "', Stato = '" . $db->escape($row['M']) . "', File = '" . (int)$input_file_id . "', QtaOrdinata = '" . (int)$row['R'] . "', QtaConsegnabile = '" . (int)$row['S'] . "', QtaArrivata = '" . (int)$row['T'] . "', QtaManuale = '" . (int)$row['U'] . "', SkuFornitore = '" . $db->escape($row['V']) . "', NomeProdotto = '" . $db->escape($row['W']) . "', Sku = '" . $db->escape($row['X']) . "'");     
			//if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET InArrivo = " . (int)$row['A'] . ", PoNumber = '" . $db->escape($row['B']) . "', TrackingLinkDelivery = '" . $db->escape($row['F']) . "', Stato = '" . $db->escape($row['M']) . "', File = '" . (int)$input_file_id . "', QtaOrdinata = '" . (int)$row['R'] . "', QtaConsegnabile = '" . (int)$row['S'] . "', QtaArrivata = '" . (int)$row['T'] . "', QtaManuale = '" . (int)$row['U'] . "', SkuFornitore = '" . $db->escape($row['V']) . "', NomeProdotto = '" . $db->escape($row['W']) . "', Sku = '" . $db->escape($row['X']) . "'")){    
			if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET InArrivo = (CASE WHEN ((" . (int)$row['T'] . " + " . (int)$row['U'] . ") <= " . (int)$row['S'] . ") THEN (" . (int)$row['S'] . " - " . (int)$row['T'] . " - " . (int)$row['U'] . ") ELSE 0 END), PoNumber = '" . $db->escape($row['B']) . "', TrackingLinkDelivery = '" . $db->escape($row['F']) . "', Stato = '" . $db->escape($row['M']) . "', File = '" . (int)$input_file_id . "', QtaOrdinata = '" . (int)$row['R'] . "', QtaConsegnabile = '" . (int)$row['S'] . "', QtaArrivata = '" . (int)$row['T'] . "', QtaManuale = '" . (int)$row['U'] . "', SkuFornitore = '" . $db->escape($row['V']) . "', NomeProdotto = '" . $db->escape($row['W']) . "', Sku = '" . $db->escape($row['X']) . "'")){     
	
					$add = true;    
				} else {     
					$error = true;     
					$log->write(' ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table.');    
					}        
				
			}
			$query = $db->query("SELECT count(*) as count from ric");
			$ricTotal=$query->row['count'];	
			$ricTotal=16;
			if($ricTotal != $virtualRic)
				$warning=true;
			if($warning){
				echo "<script>alert('Attenzione! Non sono stati importate tutte le righe del file RIC. Controllare');</script>";
			}else{
				echo "<script>alert('Tutte le righe sono state importate!');</script>";
			}	
			
			break;
		
		case 'shopify':
			$warning= false;
			$query = $db->query("SELECT count(*) as count from shopify");
			$ricPre=$query->row['count'];
			$virtualRic=$ricPre+sizeof($rows);
			$db_payment_method = $config->get('config_db_payment_method');
			$db_vendor = $config->get('config_db_vendor');
			if ($db->checkExist($db_payment_method) && $db->checkExist($db_vendor)) {
				$add = false;
				$error = false;
				foreach ($rows as $row_number => $row){
					
					$Name = str_replace("#","",$row['A']);
					//SHOPIFY.Name > max(shopify_archive.Name)
					$query = $db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM `" . DB_PREFIX . $table . "_archive` WHERE Name >= " . (int)$Name . ") THEN TRUE ELSE FALSE END) as exist");
					if (empty($query->row['exist'])){
						
						//Payment Method
						$payment_method_id = false;
						//Validation
						//if(array_key_exists('F', $row) && !empty($row['F']) && $db->checkExist($db_payment_method)){
							$query = $db->query("SELECT Id FROM " . DB_PREFIX . $db_payment_method . " WHERE Payment = '" . $db->escape($row['F']) . "'");
							if (empty($query->row['Id'])){
								if($db->query("INSERT INTO " . DB_PREFIX . $db_payment_method . " SET Payment = '" . $db->escape($row['F']) . "'")){
									$payment_method_id = $db->getLastId();
								} else {
									$error = true;
									$log->write('Unknown error for the row with number ' . $row_number . ' during add information to "' . DB_PREFIX . $db_payment_method  . '" table.');
									continue;
								}
							} else {
								$payment_method_id = $query->row['Id'];
							}
						//} else {
						//	$error = true;
						//	$log->write('ERROR: The payment method data from the row with number ' . $row_number . ' was empty! Import data from this row was discarded!');
						//	continue;
						//}
						
						//Vendor
						$vendor_id = false;
						//Validation
						//if(array_key_exists('G', $row) && !empty($row['G']) && $db->checkExist($db_vendor)){
							$query = $db->query("SELECT Id FROM " . DB_PREFIX . $db_vendor . " WHERE Vendor = '" . $db->escape($row['G']) . "'");
							if (empty($query->row['Id'])){
								if($db->query("INSERT INTO " . DB_PREFIX . $db_vendor . " SET Vendor = '" . $db->escape($row['G']) . "'")){
									$vendor_id = $db->getLastId();
								} else {
									$error = true;
									$log->write('Unknown error for the row with number ' . $row_number . ' during add information to "' . DB_PREFIX . $db_vendor  . '" table.');
									continue;
								}
							} else {
								$vendor_id = $query->row['Id'];
							}
						//} else {
						//	$error = true;
						//	$log->write('ERROR: The Vendor data from the row with number ' . $row_number . ' was empty!');
						//	continue;
						//}
						
						//Shopify
						if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET Name = '" . $db->escape($Name) . "', LineitemQuantity = '" . (int)$row['C'] . "', PaymentMethod = '" . (int)$payment_method_id . "', OrderSku = '" . $db->escape($row['A'] . "-" . $row['E']) . "', File = '" . (int)$input_file_id . "', Vendor = '" . (int)$vendor_id . "', CreatedAt = '" . str_replace(" +0200","", $row['B']) . "', LineitemName = '" . $db->escape($row['D']) . "', LineitemSku = '" . $db->escape($row['E']) . "'")){
							$add = true;
						} else {
							$error = true;
							$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table.');
						}
						
					} else {
						$error = true;
						$log->write('ERROR: Import data from the row with number ' . $row_number . ' was discarded!');
					}
				}
				
				$query = $db->query("SELECT count(*) as count from shopify");
				$ricTotal=$query->row['count'];	
				if($ricTotal != $virtualRic)
					$warning=true;
				
				
				//If we add any row to the temp import table then we move all data to archive table
				if ($add){
					if ($error){
						$log->write('WARRING: Import data from "' . $fileName . '" was added to "' . DB_PREFIX . $table . '" with some errors!');
					} else {
						$log->write('SUCCESS: Import data from "' . $fileName . '" was successfully added to "' . DB_PREFIX . $table  . '" table.');
					}
				} else {
					$log->write('WARRING: Nothing was added to "' . DB_PREFIX . $table  . '" table from "' . $fileName . '" file!');
				}
			} else {
				$log->write('Table with name "' . DB_PREFIX . $db_payment_method . '" missing in "' . DB_DATABASE . '" database');
				break;
			}
			
			if($warning){
				echo "<script>alert('Attenzione! Non sono stati importate tutte le righe del file shopify. Controllare');</script>";
			}
		 	break;
			
		case 'spe':
			$add = false;
			$error = false;
			$warning= false;
			$query = $db->query("SELECT count(*) as count from spe");
			$ricPre=$query->row['count'];
			$virtualRic=$ricPre+sizeof($rows);
			foreach ($rows as $row_number => $row){
				$NumeroOrdine = str_replace("#","",$row['A']);
				//SPE.SpeNumeroOrdine > max(spe_archive.NumeroOrdine)
				$query = $db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM `" . DB_PREFIX . $table . "_archive` WHERE NumeroOrdine >= " . (int)$NumeroOrdine . ") THEN TRUE ELSE FALSE END) as exist");
				if (empty($query->row['exist'])){
					if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET OrderSku = '" . $db->escape($row['A'] . "-" . $row['G']) . "', NumeroOrdine = '" . (int)$NumeroOrdine . "', ArtCodice = '" . $db->escape($row['G']) . "', TrackingNumero = '" . (int)$row['D'] . "', ArtQta = '" . (int)$row['I'] . "', File = '" . (int)$input_file_id . "' ON DUPLICATE KEY UPDATE ArtCodice = '" . $db->escape($row['G']) . "', TrackingNumero = '" . (int)$row['D'] . "', ArtQta = '" . (int)$row['I'] . "', File = '" . (int)$input_file_id . "' ")){
						$add = true;
					} else {
						$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table. '.mysql_errno());
					}
				} else {
					$error = true;
					$log->write('ERROR: Import data from the row with number ' . $row_number . ' was discarded!');
				}
			}
			$query = $db->query("SELECT count(*) as count from spe");
			$ricTotal=$query->row['count'];	
			if($ricTotal != $virtualRic)
				$warning=true;
			
			$query = $db->query("SELECT count(*) as count from spe");
			$ricTotal=$query->row['count'];	
			if($ricTotal != $virtualRic)
				$warning=true;
			
			//If we add any row to the temp import table then we move all data to archive table
			if ($add){
				if ($error){
					$log->write('WARRING: Import data from "' . $fileName . '" was added to "' . DB_PREFIX . $table . '" with some errors!');
				} else {
					$log->write('SUCCESS: Import data from "' . $fileName . '" was successfully added to "' . DB_PREFIX . $table  . '" table.');
				}
			} else {
				$log->write('WARRING: Nothing was added to "' . DB_PREFIX . $table  . '" table from "' . $fileName . '" file!');
			}
			
			if($warning){
				echo "<script>alert('Attenzione! Non sono stati importate tutte le righe del file spe. Controllare');</script>";
			}
			break;
			
		case 'simfdb':
			$add = false;
			$error = false;
			$warning= false;
			$query = $db->query("SELECT count(*) as count from simfdb");
			$ricPre=$query->row['count'];
			$virtualRic=$ricPre+sizeof($rows);
			foreach ($rows as $row_number => $row){
				$NumeroOrdine = str_replace("#","",$row['C']);
				//SIMFDB.SimEsiExpNumeroOrdine > max(simfdb_archive.NumeroOrdine)
				$query = $db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM `" . DB_PREFIX . $table . "_archive` WHERE NumeroOrdine >= " . (int)$NumeroOrdine . ") THEN TRUE ELSE FALSE END) as exist");
				if (empty($query->row['exist'])){
					if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET OrderSku = '" . $db->escape($row['C'] . "-" . $row['F']) . "', NumeroOrdine = '" . (int)$NumeroOrdine . "', ArtCodice = '" . $db->escape($row['F']) . "', QtaRichiesta = '" . (int)$row['H'] .  "', File = '" . (int)$input_file_id . "', QtaImpegnata = '". (int)$row['I']  ."', DataOrdine='".date("Y-m-d", strtotime($row['D']))."'")){
						$add = true;
					} else {
						$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table.');
					}
				} else {
					$error = true;
					$log->write('ERROR: Import data from the row with number ' . $row_number . ' was discarded!');
				}
			}
			//If we add any row to the temp import table then we move all data to archive table
			if ($add){
				if ($error){
					$log->write('WARRING: Import data from "' . $fileName . '" was added to "' . DB_PREFIX . $table . '" with some errors!');
				} else {
					$log->write('SUCCESS: Import data from "' . $fileName . '" was successfully added to "' . DB_PREFIX . $table  . '" table.');
				}
			} else {
				$log->write('WARRING: Nothing was added to "' . DB_PREFIX . $table  . '" table from "' . $fileName . '" file!');
			}
			break;
			
		case 'pro':
			$add = false;
			$error = false;
			$warning= false;
			$query = $db->query("SELECT count(*) as count from pro");
			$ricPre=$query->row['count'];
			$virtualRic=$ricPre+sizeof($rows);
			foreach ($rows as $row_number => $row){
				$NumeroOrdine = str_replace("#","",$row['A']);
				//PRO.NumeroOrdine> max(pro_archive.NumeroOrdine)
				$query = $db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM `" . DB_PREFIX . $table . "_archive` WHERE NumeroOrdine >= " . (int)$NumeroOrdine . ") THEN TRUE ELSE FALSE END) as exist");
				if (empty($query->row['exist'])){
					if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET NumeroOrdine = '" . (int)$NumeroOrdine . "', ArtCodice = '" . $db->escape($row['E']) . "', ArtQta = '" . (int)$row['G'] . "', OrderSku = '" . $db->escape($row['A'] . "-" . $row['E']) . "', File = '" . (int)$input_file_id . "'")){
						$add = true;
					} else {
						$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table.');
					}
				} else {
					$error = true;
					$log->write('ERROR: Import data from the row with number ' . $row_number . ' was discarded!');
				}
			}
			$query = $db->query("SELECT count(*) as count from pro");
			$ricTotal=$query->row['count'];	
			if($ricTotal != $virtualRic)
				$warning=true;
			
			//If we add any row to the temp import table then we move all data to archive table
			if ($add){
				if ($error){
					$log->write('WARRING: Import data from "' . $fileName . '" was added to "' . DB_PREFIX . $table . '" with some errors!');
				} else {
					$log->write('SUCCESS: Import data from "' . $fileName . '" was successfully added to "' . DB_PREFIX . $table  . '" table.');
				}
			} else {
				$log->write('WARRING: Nothing was added to "' . DB_PREFIX . $table  . '" table from "' . $fileName . '" file!');
			}
			if($warning){
				echo "<script>alert('Attenzione! Non sono stati importate tutte le righe del file pro. Controllare');</script>";
			}
			break;
			
		case 'ric':
			$add = false;
			$error = false;
			$warning= false;
			$query = $db->query("SELECT count(*) as count from ric");
			$ricPre=$query->row['count'];
			
			$virtualRic=$ricPre+sizeof($rows);
			foreach ($rows as $row_number => $row){
				//replace(replace(NumeroOriginal,"PO",""),"RESO ORD. ","")
				$a=strtolower($row['A']);
				$a=str_replace("reso","",$a);
				$a=str_replace("ord","",$a);
				$str=trim($a, " ");	
				$str = substr( $a, ( $pos = strpos( $a, '.' ) ) === false ? 0 : $pos + 1 );
				$str = substr( $a, ( $pos = strpos( $a, ' ' ) ) === false ? 0 : $pos + 1 );
					
					//echo trim($str," ")."\n";
					//check if order is present in dashboard
					$str='#'.$str;
					
					$str = preg_replace('/\s+/', '', $str);
					$query = $db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM dashboard WHERE OrderITM ='".$str."') THEN TRUE ELSE FALSE END) as exist");
					if (!empty($query->row['exist'])){
						//Adesso posso importare in ric
						if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET NumeroOriginal = '" . $db->escape($str) . "', AnaArtCodice = '" . $db->escape($row['I']) . "', QuantitaRicevuta = '" . (int)$row['L'] . "', Numero = '" . $db->escape($str) . "', File = '" . (int)$input_file_id . "', OrderSku = '" .$db->escape($str . "-" . $row['I']) . "' ON DUPLICATE KEY UPDATE NumeroOriginal = '" . $db->escape($str) . "', AnaArtCodice = '" . $db->escape($row['I']) . "', QuantitaRicevuta = '" . (int)$row['L'] . "', Numero = '" . $db->escape($str) . "', File = '" . (int)$input_file_id . "', OrderSku = '" .$db->escape($str . "-" . $row['I']) . "'")){
							$add = true;
						} else {
							$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table.');
						}					
					}
					//$Numero = str_replace(array("PO", "RESO ORD. "), array("", ""), $row['A']);
					/*$NumeroOriginal= str_replace("RESO ORD. ", "", $row['A']);
					if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET NumeroOriginal = '" . $db->escape($NumeroOriginal) . "', AnaArtCodice = '" . $db->escape($row['I']) . "', QuantitaRicevuta = '" . (int)$row['L'] . "', Numero = '" . $db->escape("#" . $NumeroOriginal) . "', File = '" . (int)$input_file_id . "', OrderSku = '" . '#'.$db->escape($NumeroOriginal . "-" . $row['I']) . "'")){
						$add = true;
					} else {
						$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table.');
					}*/
				
				}
			$query = $db->query("SELECT count(*) as count from ric");
			$ricTotal=$query->row['count'];	
			if($ricTotal != $virtualRic)
				$warning=true;
			//If we add any row to the temp import table then we move all data to archive table
			if ($add){
				if ($error){
					$log->write('WARRING: Import data from "' . $fileName . '" was added to "' . DB_PREFIX . $table . '" with some errors!');
				} else {
					$log->write('SUCCESS: Import data from "' . $fileName . '" was successfully added to "' . DB_PREFIX . $table  . '" table.');
				}
			} else {
				$log->write('WARRING: Nothing was added to "' . DB_PREFIX . $table  . '" table from "' . $fileName . '" file!');
			}
			if($warning){
				echo "<script>alert('Attenzione! Non sono stati importate tutte le righe del file RIC. Controllare');</script>";
			}
			break;
			
		case 'cancellati':
			$add = false;
			$error = false;
			$warning= false;
			$query = $db->query("SELECT count(*) as count from cancellati");
			$ricPre=$query->row['count'];
			$virtualRic=$ricPre+sizeof($rows);
			foreach ($rows as $row_number => $row){
				$Ordern  = str_replace("#","",$row['B']);
				//PRO.replace(Order N. Puri.,"#",'')> max(pro_archive.OrderId)
				$query = $db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM `" . DB_PREFIX . $table . "_archive` WHERE OrderId >= " . (int)$Ordern . ") THEN TRUE ELSE FALSE END) as exist");
				if (empty($query->row['exist'])){
					if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET OrderId = '" . (int)$Ordern . "', ORDERN = '" . $db->escape($row['A']) . "', SKU = '" . $db->escape($row['C']) . "', QuantitaRichiesta = '" . (int)$row['E'] . "', OrderSku = '" . $db->escape($row['A'] . "-" . $row['C']) . "', File = '" . (int)$input_file_id . "'")){
						$add = true;
					} else {
						$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table.');
					}
				} else {
					$error = true;
					$log->write('ERROR: Import data from the row with number ' . $row_number . ' was discarded!');
				}
			}
			$query = $db->query("SELECT count(*) as count from cancellati");
			$ricTotal=$query->row['count'];	
			if($ricTotal != $virtualRic)
				$warning=true;
			
			//If we add any row to the temp import table then we move all data to archive table
			if ($add){
				if ($error){
					$log->write('WARRING: Import data from "' . $fileName . '" was added to "' . DB_PREFIX . $table . '" with some errors!');
				} else {
					$log->write('SUCCESS: Import data from "' . $fileName . '" was successfully added to "' . DB_PREFIX . $table  . '" table.');
				}
			} else {
				$log->write('WARRING: Nothing was added to "' . DB_PREFIX . $table  . '" table from "' . $fileName . '" file!');
			}
			
			if($warning){
				echo "<script>alert('Attenzione! Non sono stati importate tutte le righe del file cancellati. Controllare');</script>";
			}
			break;
			
		case 'soldout':
			$add = false;
			$error = false;
			$warning= false;
			$query = $db->query("SELECT count(*) as count from soldout");
			$ricPre=$query->row['count'];
			$virtualRic=$ricPre+sizeof($rows);
			foreach ($rows as $row_number => $row){
				$Ordern  = str_replace("#","",$row['B']);
				//soldout.replace(Order N. Puri.,"#",'')> max(soldout_archive.OrderId)
				$query = $db->query("SELECT (CASE WHEN EXISTS(SELECT * FROM `" . DB_PREFIX . $table . "_archive` WHERE OrderId >= " . (int)$Ordern . ") THEN TRUE ELSE FALSE END) as exist");
				if (empty($query->row['exist'])){
					if($db->query("INSERT INTO " . DB_PREFIX . $table . " SET OrderId = '" . (int)$Ordern . "', ORDERN = '" . $db->escape($row['A']) . "', SKU = '" . $db->escape($row['C']) . "', QuantitaRichiesta = '" . (int)$row['E'] . "', OrderSku = '" . $db->escape($row['A'] . "-" . $row['C']) . "', File = '" . (int)$input_file_id . "'")){
						$add = true;
					} else {
						$log->write('ERROR: Unknown error during add information to "' . DB_PREFIX . $table  . '" table.');
					}
				} else {
					$error = true;
					$log->write('ERROR: Import data from the row with number ' . $row_number . ' was discarded!');
				}
			}
			$query = $db->query("SELECT count(*) as count from soldout");
			$ricTotal=$query->row['count'];	
			if($ricTotal != $virtualRic)
				$warning=true;
			
			//If we add any row to the temp import table then we move all data to archive table
			if ($add){
				if ($error){
					$log->write('WARRING: Import data from "' . $fileName . '" was added to "' . DB_PREFIX . $table . '" with some errors!');
				} else {
					$log->write('SUCCESS: Import data from "' . $fileName . '" was successfully added to "' . DB_PREFIX . $table  . '" table.');
				}
			} else {
				$log->write('WARRING: Nothing was added to "' . DB_PREFIX . $table  . '" table from "' . $fileName . '" file!');
			}
			if($warning){
				echo "<script>alert('Attenzione! Non sono stati importate tutte le righe del file soldout. Controllare');</script>";
			}
			break;
	}

	


}
//Move import files from IN directory to ARCHIVE directory
$importFiles = $files->getIn();
foreach($importFiles as $file) {
	$fileName = $file->getFilename();
	if($files->move(DIR_IN . $fileName, DIR_ARCHIVE . $fileName)){
		$log->write('SUCCESS: FIle "' . DIR_IN . $fileName  . '" was moved to "' . DIR_ARCHIVE . $fileName . '"');
	} else {
		$log->write('ERROR: FIle "' . DIR_IN . $fileName  . '" wasn\'t moved to "' . DIR_ARCHIVE . $fileName . '" !');
	}
}

//Create analyze.ok file for start analyze procedure.
//$files->analyze_ok();

$time_end = microtime_float();
$time = $time_end - $time_start;
$time=round($time,2);
?><h1><?php echo "Import completed in $time seconds\n";?></h1>


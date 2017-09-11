<?php
class Log {
	private $handle;

	public function __construct($filename) {
		$this->handle = fopen(DIR_LOGS . $filename, 'a');
	}

	public function write($message) {
		fwrite($this->handle, print_r($message, true) . ' - ' . date('Y-m-d G:i:s') . "\n");
	}

	public function __destruct() {
		fclose($this->handle);
	}
}
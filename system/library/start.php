<?php
class Start {

	public function __construct($filename) {
		if (file_exists(DIR_IN . $filename)){
			if (!@unlink(DIR_IN . $filename)) {
				die;
			} 
		} else {
			die;
		} 
		
	}

}
<?php

class File_Util{

        private $found_simfdb=false;
        private $found_dbpo=false;
        private $found_pro=false;
        private $found_brt=false;
        private $found_stk=false;

public function getLastBrt($importFiles) {

        $brt_new=0;
        $file_new_key = false;

foreach($importFiles as $i => $file) {

        $fileName = $file->getFilename();
        if (strpos(strtolower($fileName), 'brt') !== false AND !$this->found_brt) {
        if ($file->getMTime()>$brt_new){
                        $brt_new=$file->getMTime();
                        $file_new_key= $i;

                }
    }

}

        if($file_new_key!=""){
                $this->found_brt=true;
                return $file_new_key;
        }
}


public function getLastSimfdb($importFiles) {

        $simfdb_new=0;
        $file_new_key = false;

foreach($importFiles as $i => $file) {

        $fileName = $file->getFilename();
        if (strpos(strtolower($fileName), 'simfdb') !== false AND !$this->found_simfdb) {
        if ($file->getMTime()>$simfdb_new){
                        $simfdb_new=$file->getMTime();
                        $file_new_key= $i;

                }
    }

}

        if($file_new_key!=""){
                $this->found_simfdb=true;
                return $file_new_key;
        }
}


public function getLastDbpo($importFiles) {

        $dbpo_new=0;
        $file_new_key = false;

foreach($importFiles as $i => $file) {

        $fileName = $file->getFilename();
        if (strpos(strtolower($fileName), 'dbpo') !== false AND !$this->found_dbpo) {
        if ($file->getMTime()>$dbpo_new){
                        $dbpo_new=$file->getMTime();
                        $file_new_key= $i;

                }
    }

}

        if($file_new_key!=""){
                $this->found_dbpo=true;
                return $file_new_key;
        }
}

public function getLastPro($importFiles) {

	$pro_new=0;
	$file_new_key = false;

	foreach($importFiles as $i => $file) {

			$fileName = $file->getFilename();
			if (strpos(strtolower($fileName), 'pro') !== false AND !$this->found_pro) {
			if ($file->getMTime()>$pro_new){
							$pro_new=$file->getMTime();
							$file_new_key= $i;

					}
		}

	}

	if($file_new_key!=""){
			$this->found_pro=true;
			return $file_new_key;
	}
}

public function getLastStk($importFiles) {

	$pro_stk=0;
	$file_new_key = false;

	foreach($importFiles as $i => $file) {
		$fileName = $file->getFilename();
		if (strpos(strtolower($fileName), 'stk') !== false AND !$this->found_stk) {
			if ($file->getMTime()>$pro_stk){
				$pro_stk=$file->getMTime();
				$file_new_key= $i;
			}
		}
	}

	if($file_new_key!=""){
			$this->found_stk=true;
			return $file_new_key;
	}
}


}


?>

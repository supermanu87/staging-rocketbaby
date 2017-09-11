<?php
class Filesystem {
	protected $registry;
	protected $config;
	private $In;
	private $InNames = array();
	private $Archive;
	private $ArchiveNames = array();
	
    /**
     * Construct filesystem object. 
	 * Get an array of all necessary files in IN and ARCHIVE directories and filtering by file types.
	 * Get an array of all necessary files names in IN and ARCHIVE directories.
     *
     * @param  object  $registry
     * @return object
     */
    public function __construct($registry)
    {
        $this->registry = $registry;
		$this->config = $this->registry->get('config');
		$types = $this->config->get('config_import_filetypes');
		
		//Get an array of all necessary files in IN directory and filtering by file types
		$this->In = new DirectoryIterator(DIR_IN);
		if($types){
			$this->In = new RegexIterator($this->In, '/\.(' . $types . ')$/');
		}
		//Get an array of all files names
		foreach($this->In as $entry) {
			$this->InNames[$this->In->key()] = $entry->getFilename();
		}
		
		//Get an array of all necessary files in ARCHIVE directory and filtering by file types
		$this->Archive = new DirectoryIterator(DIR_ARCHIVE);
		if($types){
			$this->Archive = new RegexIterator($this->Archive, '/\.(' . $types . ')$/');
		}
		//Get an array of all files names
		foreach($this->Archive as $entry) {
			$this->ArchiveNames[$this->Archive->key()] = $entry->getFilename();
		}
    }
	
    /**
	 * Check for identical names from IN directory in the ARCHIVE directory and if there is already a file with the same name, replace filenames (add to the end of the filename '-1' - '-9999'). Append this information into log;
     *
     * @return object
     */
    public function renamingDuplicates()
    {
		$types = $this->config->get('config_import_filetypes');
		//Get file names for rename from IN directory that are same for ARCHIVE directory
		$files_for_rename = array_intersect($this->InNames, $this->ArchiveNames);
		//Try to rename
		if($files_for_rename){
			foreach ($files_for_rename as $key => $file_name){
				$new_file_name = $file_name;
				$this->In->seek($key);
				$fileBasename = $this->In->getBasename('.' . $this->In->getExtension());
				$splitFileName = $this->splitFilename($fileBasename);
				if (count($splitFileName)>1) {
					//Try to find correct new file name.
					for ($i = 1; in_array($new_file_name, $this->ArchiveNames); $i++){
						$new_file_name = $splitFileName[0] . $this->config->get('config_split_delimiter') . $splitFileName[1] . $this->config->get('config_number_delimiter') . $i . '.' . $this->In->getExtension();
					}
				}
				//Change file name and put this information to log file
				@rename(DIR_IN . $file_name, DIR_IN . $new_file_name);
				$this->registry->get('log')->write('The file name for ' . DIR_IN . $file_name . ' was change to ' . DIR_IN . $new_file_name);
			}
			
			//Update In and InNames after changing the filenames
			$this->In = new DirectoryIterator(DIR_IN);
			if($types){
				$this->In = new RegexIterator($this->In, '/\.(' . $types . ')$/');
			}		
			foreach($this->In as $entry) {
				$this->InNames[$this->In->key()] = $entry->getFilename();
			}
		}
    }
	
    /**
     * Get an array of all necessary files in IN directory.
     *
     * @return DirectoryIterator object
     */	
	public function getIn()
    {
		return $this->In;
    }
	
    /**
     * Get an array of all files names in IN directory.
     *
     * @return array
     */		
	public function getInNames()
    {
		return $this->InNames;
    }
	
    /**
     * Get an array of all necessary files in ARCHIVE directory.
     *
     * @return DirectoryIterator object
     */		
	public function getArchive()
    {
		return $this->Archive;
    }
	
    /**
     * Get an array of all files names in ARCHIVE directory.
     *
     * @return array
     */		
	public function getArchiveNames()
    {
		return $this->ArchiveNames;
    }
	
    /**
     * Get an array from the splitting the file name using custom delimiter.
     *
	 * @param  string  $fileName
     * @return array   $result
     */	
	public function splitFilename($fileName)
    {
		$result = explode($this->config->get('config_split_delimiter'), $fileName);

		return $result;
    }	

    /**
     * Get an array of all files in a directory.
     *
     * @param  object  $files
     * @return array
     */
    public function filesNames()
    {
		
		$filelist = array();
		foreach($this->files as $entry) {
			$filelist[] = $entry->getFilename();
		}
		
		return $filelist;
    }
	
    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return boolean
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (! @unlink($path)) {
                    $success = false;
                }
            } catch (ErrorException $e) {
                $success = false;
            }
        }

        return $success;
    }
	
    /**
     * Move a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return bool
     */
    public function move($path, $target)
    {
        return rename($path, $target);
    }
	
    /**
     * Copy a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return bool
     */
    public function copy($path, $target)
    {
        return copy($path, $target);
    }
	
    /**
     * Create analyze.ok file.
     */
    public function analyze_ok()
    {
        $content = "File for starting analyze procedure";
		$analyze_ok_filename = $this->config->get('config_start_analyze_filename');
		$fp = fopen(DIR_IN . $analyze_ok_filename,"wb");
		fwrite($fp,$content);
		fclose($fp);
		$this->registry->get('log')->write('SUCCESS: File ' . DIR_IN . $analyze_ok_filename . ' for start analyze procedure was created!');
    }
	
}
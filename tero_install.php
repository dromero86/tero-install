class tero_install
{ 
	function __construct()
	{
		return $this;
	}
	
	private function _os_path($folder)
	{ 
		if(PHP_OS=="WINNT") 
		{
			if( strpos($folder, "/") > 0 )
				$folder = str_replace("/","\\", $folder);
		}
		
		return $folder;
	}
	
	
	private function _exec($command) 
	{
		$result = array();
		
		exec($command, $result);
		
		foreach ($result as $line) 
		{
			echo "{$line}\n";
		}
		
		return implode("\n", $result);
	}
	
	private function _read($message)
	{ 
		echo "{$message}\n";
		
		return trim(fgets(STDIN));
	}
	
	private function _mkdir($folder)
	{
		$folder = $this->_os_path($folder);
		
		echo "mkdir {$folder}\n";
		
		mkdir($folder);
	}
	
	private function _chdir($folder)
	{
		$folder = $this->_os_path($folder);
		
		echo "chdir {$folder}\n";
		
		chdir("{$folder}"); 
	}
	
	private function _copy($from, $to="")
	{ 
		echo "copy from: {$from} to: {$to}\n";
	
		$from = $this->_os_path($from);
	
		if(PHP_OS=="WINNT") 
		{
			if($to)
			{
				$this->_exec("Xcopy {$from} {$to} /E /H /C /I"); 
			}
			else
			{
				$this->_exec("Xcopy {$from}"); 
			}
		}
		else
		{
			if($to)
			{
				$this->_exec("copy -R {$from} {$to}"); 
			}
			else
			{
				$this->_exec("copy {$from}"); 
			}
		}
	}
	
	private function _rmdir($folder)
	{
		if(PHP_OS=="WINNT") 
		{
			$this->_exec("rd /s /q {$folder}");
		}
		else
		{
			$this->_exec("rm -rf {$folder}");
		}
	}
	
	public function build()
	{
		$value = $this->_exec("git --version");
		
		if( strpos($value, "version") != 4 )
		{
			echo "GIT NOT FOUND \n";
			return;
		}
		 
		$folder = $this->_read("Escribe el nombre del proyecto:");
		
		$tmp    = "tmp";
		 
		$this->_mkdir($folder);
 
		$this->_mkdir("{$folder}/{$tmp}");
 
		$this->_mkdir("{$folder}/app");
 
		$this->_mkdir("{$folder}/sdk");
		
		$this->_chdir("{$folder}/{$tmp}"); 
		
		echo "git clone tero\n";
		$this->_exec("git clone https://github.com/dromero86/tero.git");
		
		if( !is_dir("tero/app") )
		{
			echo "TERO NOT FOUND \n";
			return;
		}

		echo "git clone halcon\n";
		$this->_exec("git clone https://github.com/dromero86/halcon.git");
		
		if( !is_dir("halcon/sdk") )
		{
			echo "HALCON NOT FOUND \n";
			return;
		}
		
		$folder_app = $this->_os_path("{$tmp}/tero/app");
		$folder_sdk = $this->_os_path("{$tmp}/halcon/sdk");
		
		$this->_chdir("../"); 
		
		$this->_copy($folder_app, "app"); 
		$this->_copy("{$tmp}/tero/index.php"); 
		$this->_copy("{$tmp}/tero/.htaccess"); 
		 
		$this->_copy($folder_sdk, "sdk"); 
		
		$this->_rmdir($tmp);
	}
 
	
}


$ti = new tero_install(); $ti->build(); 
<?php 

class tero_package {

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
            $this->_exec("cp -R {$from} ./"); 
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

    private function _open_json($path)
    {
        if(is_file($path))
        {
            $raw = file_get_contents($path);
            return json_encode($raw);
        }
        else
        {
            return false;
        }
    }

    private function install($package)
    {
        $tmp    = "tmp"; 
 
        $this->_mkdir( $tmp );

        $this->_chdir( $tmp ); 
 
        $this->_exec("git clone https://github.com/dromero86/{$package}.git");

        $path_package = "{$tmp}/{$package}";

        $this->_chdir($path_package); 

        $manifest = $this->_open_json("{$path_package}/manifest.json");

        if(!$manifest)
        {
            echo "MANIFEST NOT FOUND\n";
            return;
        }

        echo "Installing {$package}\n";

        //check engine 
        $path_engine_tero   = "../../app/vendor/core.php";
        $path_engine_halcon = "../../sdk/sys/core/loader.js";

        $tero_v    = $this->get_tero_version($path_engine_tero); 
        $halcon_v  = $this->get_halcon_version($path_engine_halcon);
  
        //compare
        
        if( !$this->check_engine($tero_v, $manifest->engine->tero) )
        {
            echo "Incompatible version of tero {$tero_v}: Required {$manifest->engine->tero}.\n";
            return;
        }

        if( !$this->check_engine($halcon_v, $manifest->engine->halcon) )
        {
            echo "Incompatible version of halcon {$halcon_v}: Required {$manifest->engine->halcon}.\n";
            return;
        }

        //check forge packages
        if( isset($manifest->tero_forge) )
        if( count($manifest->tero_forge) )
        {
            //copy packages
        }

        //copy files
        if( isset($manifest->files) ) 
        {
            //copy files 
            foreach ($manifest->files as $from => $to) 
            {
                $to = "../../{$to}";

                $this->_copy($from, $to);
            }
        }
 
        //install sql
        // * para instalar la query se debe conocer -user -pass -db 
        // * que estan en db.json
        if( isset($manifest->boot_sql) ) 
        {
            $db_conf = $this->_open_json("../../app/config/db.json");
            
            foreach($manifest->boot_sql->files as $item)
            {
                $this->_exec("mysql -u {$db_conf->database->user} -p{$db_conf->database->pass} {$db_conf->database->db} < {$item}");
            }  
        }

        //check config
        if( isset($manifest->config) ) 
        {
            foreach($manifest->config as $key=>$new_config)
            {
                $cur_config = $this->_open_json("../../app/config/{$key}.json");

                //check if config set
                //replace valor
            }
        }

    }

    private function update($package)
    {
        echo "UPDATE NO IMPLEMENTED\n";
        return;
    }


    public function build($cmd, $package="")
    {
        $value = $this->_exec("git --version");
        
        if( strpos($value, "version") != 4 )
        {
            echo "GIT NOT FOUND \n";
            return;
        }

        if( !in_array($cmd, array("install", "update")) )
        {
            echo "COMMAND ERROR> {$cmd}\n";
            return;
        }

        switch($cmd)
        {
            case "install": $this->install($package); break;
            case "update" : $this->update ($package); break;
        }

    }
}
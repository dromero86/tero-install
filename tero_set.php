<?php

class tero_set
{
    private $config = null;

    private function def_query()
    {
        $this->config = new stdclass;
        $this->config->section = ""; 
        $this->config->options = new stdclass;
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


    function __construct($a)
    { 
        $this->def_query();

        if( !isset($a[0]) ) die("Main undefined\n");
        if( !isset($a[1]) ) die("Section undefined\n"); 

        $this->config->section = $a[1];

        unset($a[0]);
        unset($a[1]);

        foreach($a as $arg)
        {
            list($option, $value) = explode("=", $arg);

            $this->config->options->{$option}=$value;

        }
    }

    private function pretty_dump($var)
    {
        $str = var_export($var, true);
 

        $str = str_replace("=>\r\n","=>", $str);
        
        $str = str_replace(" [","[", $str);
        $str = str_replace("[","", $str);
        $str = str_replace("]","", $str);
        $str = str_replace("\"","", $str);
        $str = str_replace("'","", $str);
        $str = str_replace(">","", $str);
        $str = str_replace(",","", $str);
        $str = str_replace("(object) array","", $str); 
        $str = str_replace("(","", $str);
        $str = str_replace(")","", $str);

        echo $str."\n";
    }

    public function build()
    { 
        $config_file = "app/config/{$this->config->section}.json";

        if( !file_exists($config_file) ) die("{$this->config->section}.json not found\n"); 

        $config_raw = file_get_contents($config_file);

        file_put_contents($config_file.".old", $config_raw);

        $config_json = json_decode($config_raw);

        $setup = true; 

        while($setup)
        {
            echo "{$this->config->section}> ";
            
            $exp =  trim(fgets(STDIN));

            if($exp == "exit")
            {
                $setup = false; 
            }
            else
            {
                $vp = explode(" ", $exp);
                $cmd = $vp[0];

                if( !in_array($cmd , array("SHOW","ADD","SET", "REM", "SAVE", "FIND", "INFO") ) ) 
                {
                    echo ("command {$cmd} not found\n"); 
                }
                else
                {
                    if($cmd == "SHOW")
                    {
                        $option = $vp[1];

                        $value = eval(" if(isset( \$config_json->{$option} )) return \$config_json->{$option};  else return false; ");

                        if($value!=false)
                            $this->pretty_dump($value);
                        else
                            echo ("{$this->config->section}> {$option} not found\n"); 
                    }
                                  
                    if($cmd == "SET")
                    {   
                        list($option, $value) = explode("=", $vp[1]);

                        eval(" if(isset( \$config_json->{$option} )) \$config_json->{$option}= \"{$value}\"; else echo \"Option {$option} not found\n\"; ");
                    } 

                    if($cmd == "SAVE")
                    {
                        file_put_contents($config_file, json_encode($config_json, JSON_PRETTY_PRINT));
                    }

                    if($cmd == "FIND")
                    {
                        $option = $vp[1];
                        list($expn, $expv) = explode("=", $vp[2]);

                        $value = eval(" if(isset( \$config_json->{$option} )) return \$config_json->{$option};  else return false; ");
                    
                        if($value==false) echo ("{$this->config->section}> {$option} not found\n"); 

                        if(is_array($value)==false) echo ("{$this->config->section}> {$option} isnt list\n"); 

                        foreach($value as $k=>$item)
                        {
                            $item_value = eval(" if(isset( \$item->{$expn} )) return \$item->{$expn};  else return false; ");

                            if($item_value!=false)
                            {
                                if($item_value == $expv)
                                {
                                    echo ("{$this->config->section}> found in key {$k} \n"); 
                                }
                            }
                        }
                    }

                    if($cmd == "REM")
                    {
                        $option = $vp[1];
                        eval(" if(!isset( \$config_json->{$option} )) echo \"{$option} not found\n\"; else unset(\$config_json->{$option}); ");
                    }

                    if($cmd == "ADD")
                    {
                        $option = $vp[1];

                        list($expn, $expv) = explode("=", $vp[2]);

                        $jsval = json_decode($expv);
                         
                        eval(" if(isset( \$config_json->{$option} )) if(is_array( \$config_json->{$option} )) \$config_json->{$option}[]=\$jsval; ");
                    }

                    if($cmd == "INFO")
                    {
                        echo 
"
TERO CONFIG

COMANDS AVAILABLES 
SHOW phpex
SET phpex=value
SAVE
FIND phpex key=value
REM phpex
ADD phpex value=json_str_value
INFO
exit
";
                    }
                }
            }
	
        }


    }
}

$ts = new tero_set($argv); $ts->build();

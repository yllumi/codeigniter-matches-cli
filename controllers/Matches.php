<?php
#!/usr/bin/php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Copyright (C) 2014 @avenirer [avenir.ro@gmail.com]
* Everyone is permitted to copy and distribute verbatim or modified copies of this license document, 
* and changing it is allowed as long as the name is changed.
* DON'T BE A DICK PUBLIC LICENSE TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
*
***** Do whatever you like with the original work, just don't be a dick.
***** Being a dick includes - but is not limited to - the following instances:
********* 1a. Outright copyright infringement - Don't just copy this and change the name.
********* 1b. Selling the unmodified original with no work done what-so-ever, that's REALLY being a dick.
********* 1c. Modifying the original work to contain hidden harmful content. That would make you a PROPER dick.
***** If you become rich through modifications, related works/services, or supporting the original work, share the love. Only a dick would make loads off this work and not buy the original works creator(s) a pint.
***** Code is provided with no warranty. 
*********** Using somebody else's code and bitching when it goes wrong makes you a DONKEY dick. 
*********** Fix the problem yourself. A non-dick would submit the fix back.
 * 
 * 
 * filename: Matches.php
 * This project started from a great idea posted by @veedeoo [veedeoo@gmail.com] on http://www.daniweb.com/web-development/php/code/477847/codeigniter-cli-trainer-script-creates-simple-application
 * License info: http://www.dbad-license.org/
 */




/* first we make sure this isn't called from a web browser */
if (isset($_SERVER['REMOTE_ADDR'])) die('Permission denied.');
/* raise or eliminate limits we would otherwise put on http requests */
set_time_limit(0);
ini_set('memory_limit', '256M');

/* here we go */
class Matches extends CI_Controller {
	private $_c_extends;
	private $_mo_extends;
	private $_mi_extends;
	private $_templates_loc;
	private $_tab = "\t";
	private $_tab2 = "\t\t";
	private $_tab3 = "\t\t\t";
	
	private $_ret = "\n";
	private $_ret2 = "\n\n";
	private $_rettab = "\n\t";
    private $_tabret= "\t\n";
	private $_find_replace = array();
	public function __construct()
	{
		parent::__construct();
		
		$this->config->load('matches',TRUE);
		$this->_templates_loc = $this->config->item('templates', 'matches');
		$this->_c_extends = $this->config->item('c_extends', 'matches');
		$this->_mo_extends = $this->config->item('mo_extends', 'matches');
		$this->_mi_extends = $this->config->item('mi_extends', 'matches');
		
		if (ENVIRONMENT === 'production')
		{
			echo "\n";
			echo "======== WARNING ========".$this->_ret;
			echo "===== IN PRODUCTION =====".$this->_ret;
			echo "=========================".$this->_ret;
			echo "Are you sure you want to work with CLI on a production app? (y/n)";
			$line = fgets(STDIN);
			if(trim($line) != 'y')
			{
				echo "Aborting!".$this->_ret;
				exit;
			}
			echo "\n";
			echo "Thank you, continuing...".$this->_ret2;
		}
		$this->load->helper('file');
	}
	/*
	* return string
	*/
	public function index()
	{
		echo 'Hello. Need help to ignite somethin\'?'.$this->_ret;
	}
	
	/*
	* CLI tester
	* returns string 
	*/
	public function hello($name)
	{
		echo 'Hello '. $name;
	}
	
	
	/*
	* list the available commands
	* 
	*/
	public function help()
	{
		echo $this->_ret.'Available commands:';
		echo $this->_ret2.' create';
		echo $this->_ret.'  app name_of_app';
		echo $this->_ret.'  controller name_of_controller';
		echo $this->_ret.'  migration name_of_migration name_of_table-(OPTIONAL)';
		echo $this->_ret.'  model name_of_model';
		echo $this->_ret.'  view name_of_view';
		echo $this->_ret2.' encryption_key string_to_hash-(OPTIONAL)';
		echo $this->_ret2.$this->_ret2;
	}
	
	
	
	/*
	* create application's controller file, model file, view file and migration file
	*/
	
	public function create($what,$name='')
	{
		$what = filter_var($what, FILTER_SANITIZE_STRING);
		$name = filter_var($name, FILTER_SANITIZE_STRING);
		$can_create = array('app','controller','model','migration');
		if(in_array($what, $can_create))
		{
			switch($what)
			{
				case 'app':
					$this->create_app($name);
				break;
				case 'controller':
					$this->create_controller($name);
				break;
				case 'model':
					$this->create_model($name);
				break;
				case 'view':
					$this->create_view($name);
				break;
				case 'migration':
					$this->create_migration($name);
				break;
			}
				
		}
		else
		{
			echo  $this->_ret.'I can only create: app, controller, model, migration';
		}
	}
	
	public function create_app($app)
	{
		if(isset($app))
		{	
			if(file_exists('application/controllers/'.$this->_filename($app).'.php') OR (class_exists(''.$app.'')) OR (class_exists(''.$app.'_model')))
			{
				echo $app.' Controller or Model already exists in the application/controllers directory.';
			}
			else
			{
				$this->create_controller($app);
				$this->create_model($app);
				$this->create_view($app);
				
			}
		}
		else
		{
			echo $this->_ret.'You need to provide a name for the app';
		}
	}
	/*
	* create controller
	* returns boolean true
	*/
	public function create_controller($controller)
	{
		if(isset($controller))
		{
			$class_name = ucfirst($controller);
			$file_name = $this->_filename($class_name);
			if(file_exists('application/controllers/'.$file_name.'.php') OR (class_exists($class_name)))
			{
				echo $this->_ret.$class_name.' Controller already exists in the application/controllers directory.';
			}
			else
			{
				if(file_exists($this->_templates_loc.'controller_template.txt'))
				{
					$f = read_file($this->_templates_loc.'controller_template.txt');
				}
				else
				{
					echo $this->_ret.'Couldn\'t find Controller template.';
					return FALSE;
				}
				$this->_find_replace['{{CONTROLLER}}'] = $class_name;
				$this->_find_replace['{{CONTROLLER_FILE}}'] = $file_name;
				$this->_find_replace['{{MV}}'] = strtolower($class_name);
				$this->_find_replace['{{C_EXTENDS}}'] = $this->_c_extends;
				$f = strtr($f,$this->_find_replace);
				if(write_file('application/controllers/'.$file_name.'.php',$f))
				{
					echo $this->_ret.'Controller '.$class_name.' has been created.';
					return TRUE;
				}
				else
				{
					echo $this->_ret.'Couldn\'t write Controller.';
					return FALSE;
				}
			}
		}
		else
		{
			echo $this->_ret.'You need to provide a name for the controller.';
		}
	}
	/*
	* create model
	* returns boolean true
	*/
	public function create_model($model)
	{
		if(isset($model))
		{
			$class_name = ucfirst($model).'_model';
			$file_name = $this->_filename($class_name);
			if(file_exists('application/models/'.$file_name.'.php') OR (class_exists($class_name)))
			{
				echo $this->_ret.$class_name.' Model already exists in the application/models directory.';
			}
			else
			{
				if(file_exists($this->_templates_loc.'model_template.txt'))
				{
					$f = read_file($this->_templates_loc.'model_template.txt');
				}
				else
				{
					echo $this->_ret.'Couldn\'t find Model template.';
					return FALSE;
				}
				$this->_find_replace['{{MODEL}}'] = $class_name;
				$this->_find_replace['{{MODEL_FILE}}'] = $file_name;
				$this->_find_replace['{{MO_EXTENDS}}'] = $this->_mo_extends;
				$f = strtr($f,$this->_find_replace);
				if(write_file('application/models/'.$file_name.'.php',$f))
				{
					echo $this->_ret.'Model '.$class_name.' has been created.';
					return TRUE;
				}
				else
				{
					echo $this->_ret.'Couldn\'t write Model.';
					return FALSE;
				}
			}
		}
		else
		{
			echo $this->_ret.'You need to provide a name for the model.';
		}
	}

	/*
	* create view 
	* returns string
	*/
	public function create_view($view)
	{
		if(isset($view))
		{
			$file_name = $view;
			if(file_exists('application/views/'.$file_name.'_view.php'))
			{
				echo $this->_ret.$file_name.' View already exists in the application/views directory.';
			}
			else
			{
				if(file_exists($this->_templates_loc.'view_template.txt'))
				{
					$f = read_file($this->_templates_loc.'view_template.txt');
				}
				else
				{
					echo $this->_ret.'Couldn\'t find View template.';
					return FALSE;
				}
				$this->_find_replace['{{VIEW}}'] = $file_name;
				$f = strtr($f,$this->_find_replace);
				$writeThisFile = fopen('application/views/'.$file_name.'_view.php',"w");
				if(write_file('application/views/'.$file_name.'_view.php',$f))
				{
					echo $this->_ret.'View '.$file_name.' has been created.';
					return TRUE;
				}
				else
				{
					echo $this->_ret.'Couldn\'t write View.';
					return FALSE;
				}
			}
		}
		else
		{
			echo $this->_ret.'You need to provide a name for the view file.';
		}
	}

	

	public function do_migration()
	{
		echo 'test';
	}

	public function create_migration($action, $table = NULL)
	{
		if(isset($action))
		{
			$class_name = 'Migration_'.ucfirst($action);
			$this->config->load('migration',TRUE);
			$migration_path = $this->config->item('migration_path','migration');
			if(!file_exists($migration_path))
			{
				if(mkdir($migration_path,0755))
				{
					echo $this->_ret.'Folder migrations created.';
				}
				else
				{
					echo $this->_ret.'Couldn\'t create folder migrations.';
					return FALSE;
				}
			}
			$migration_type = $this->config->item('migration_type','migration');
			if(empty($migration_type))
			{
				$migration_type = 'sequential';
			}
			if($migration_type == 'timestamp')
			{
				$file_name = date('YmdHis').'_'.strtolower($action);
			}
			else
			{
				$latest_migration = 0;
				foreach (glob($migration_path.'*.php') as $migration)
				{
					$pattern = '/[0-9]{3}/';
					if(preg_match($pattern, $migration,$matches))
					{
						$migration_version = intval($matches[0]);
						$latest_migration = ($migration_version>$latest_version) ? $migration_version : $latest_version;
					}
				}
				$latest_migration = (string)++$latest_migration;
				$file_name = str_pad($latest_migration, 3, '0', STR_PAD_LEFT).'_'.strtolower($action);
			}
			if(file_exists($migration_path.$file_name) OR (class_exists($class_name)))
			{
				echo $this->_ret.$class_name.' Migration already exists.';
				return FALSE;
			}
			else
			{
				if(file_exists($this->_templates_loc.'migration_template.txt'))
				{
					$f = read_file($this->_templates_loc.'migration_template.txt');
				}
				else
				{
					echo $this->_ret.'Couldn\'t find Migration template.';
					return FALSE;
				}
				$this->_find_replace['{{MIGRATION}}'] = $class_name;
				$this->_find_replace['{{MIGRATION_FILE}}'] = $file_name;
				$this->_find_replace['{{MIGRATION_PATH}}'] = $migration_path;
				$this->_find_replace['{{MI_EXTENDS}}'] = $this->_mi_extends;
				if(empty($table))
				{
					$table = $action;
				}
				$this->_find_replace['{{TABLE}}'] = $table;
				$f = strtr($f,$this->_find_replace);
				if(write_file($migration_path.$file_name.'.php',$f))
				{
					echo $this->_ret.'Migration '.$class_name.' has been created.';
					return TRUE;
				}
				else
				{
					echo $this->_ret.'Couldn\'t write Migration.';
					return FALSE;
				}
			}
		}
		else
		{
			echo $this->_ret.'You need to provide a name for the migration.';
		}
	}

	public function encryption_key($string = NULL)
	{
		if(is_null($string))
		{
			$string = microtime();
		}
		$key = hash('ripemd128', $string);
		$files = $this->_search_files('application/config/','config.php');
		if(!empty($files))
		{
			$search = '$config[\'encryption_key\'] = \'\';';
			$replace = '$config[\'encryption_key\'] = \''.$key.'\';';
			foreach($files as $file)
			{
				$file = trim($file);
				// is weird, but it seems that the file cannot be found unless I do some trimming
				$f = read_file($file);
				if(strpos($f, $search)>=0)
				{
					$f = str_replace($search, $replace, $f);
					if(write_file($file,$f))
					{
						echo $this->_ret.'Encryption key '.$key.' added to '.$file.'.';
					}
					else
					{
						echo $this->_ret.'Couldn\'t write encryption key '.$key.' to '.$file.'.';
					}
				}
				else
				{
					echo $this->_ret.'Couldn\t find encryption_key or encryption_key already exists in '.$file.'.';
				}
			}
		}
		else
		{
			echo $this->_ret.'Couldn\'t find config.php';
		}
	}
	
	private function _search_files($path,$file)
	{
		$dir = new RecursiveDirectoryIterator($path);
	    $ite = new RecursiveIteratorIterator($dir);
		$files = array();
	    foreach($ite as $oFile)
	    {
	    	if($oFile->getFilename()=='config.php')
	    	{
	    		$found = str_replace('\\', '/', $this->_ret.$oFile->getPath().'/'.$file);
				$files[] = $found;
			}
		}
		return $files;
    }

	private function _filename($str)
	{
		$file_name = strtolower($str);
		if (substr(CI_VERSION, 0, 1) != '2')
		{
			$file_name = ucfirst($file_name);
		}
		return $file_name;
	}
	
	
}

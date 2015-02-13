<?php
/*
 * Project:	Xtemplate
 * File:	Xtemplate.php
 * Author:	Emiliyan Stoenchev <emo@xgroup.bg
 * Copyright: 2008 Emiliyan Stoenchev
 *
 */
if (!defined('XTEMPLATE_DIR')) {
	define('XTEMPLATE_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

class Xtemplate {
	// public configuration variables
	public $left_delimiter			= "{";		// the left delimiter for template tags
	public $right_delimiter			= "}";		// the right delimiter for template tags
	public $cache			= false;	// whether or not to allow caching of files
	public $force_compile		= false;	// force a compile regardless of saved state
	public $template_dir		= "templates";	// where the templates are to be found
	public $plugins_dir			= array("plugins");	// where the plugins are to be found
	public $compile_dir		= "compiled";	// the directory to store the compiled files in
	public $config_dir			= "templates";	// where the config files are
	public $cache_dir			= "cached";	// where cache files are stored
	public $config_overwrite		= false;
	public $config_booleanize		= true;
	public $config_fix_new_lines	= true;
	public $config_read_hidden		= true;
	public $cache_lifetime		= 0;		// how long the file in cache should be considered "fresh"
	public $encode_file_name		=	true;	// Set this to false if you do not want the name of the compiled/cached file to be md5 encoded.
	public $php_extract_vars		=	false;	// Set this to true if you want the $this->_tpl variables to be extracted for use by PHP code inside the template.
	public $reserved_template_varname = "Xtemplate";
	public $default_modifiers		= array();
	public $debugging	   =  false;
	public $developer_ips  = array();

	public $compiler_file        =    'Xcompiler.php';
	public $compiler_class        =   'Xtemplate_Compiler';
	public $config_class          =   'Xconfig';

	// gzip output configuration
	public $send_now			=  1;
	public $force_compression	=  0;
	public $compression_level	=  9;
	public $enable_gzip		=  1;

	// private internal variables
	public $_vars		= array();	// stores all internal assigned variables
	public $_confs		= array();	// stores all internal config variables
	public $_plugins		= array(	   'modifier'	  => array(),
									   'function'	  => array(),
									   'block'		 => array(),
									   'compiler'	  => array(),
									   'resource'	  => array(),
									   'prefilter'	 => array(),
									   'postfilter'	=> array(),
									   'outputfilter'  => array());
	public $_linenum		= 0;		// the current line number in the file we are processing
	public $_file		= "";		// the current file we are processing
	public $_config_obj	= null;
	public $_compile_obj	= null;
	public $_cache_id		= null;
	public $_cache_dir		= "";		// stores where this specific file is going to be cached
	public $_cache_info	= array('config' => array(), 'template' => array());
	public $_sl_md5		= '44fc71570b8b60cbc1b85839bf242aff';
	public $_version		= '1.0';
	public $_version_date	= "2008-02-04 23:44:32";
	public $_config_module_loaded = false;
	public $_xtemplate_debug_info	= array();
	public $_xtemplate_debug_loop	= false;
	public $_xtemplate_debug_dir	= "";
	public $_inclusion_depth	  = 0;
	public $_null = null;
	public $_resource_type = 1;
	public $_resource_time;
	public $_sections = array();
	public $_foreach = array();


	public function __construct()
	{
		$this->_version_date = strtotime($this->_version_date);
	}

	public function load_filter($type, $name)
	{
		switch ($type)
		{
			case 'output':
				include_once( $this->_get_plugin_dir($type . "filter." . $name . ".php") . $type . "filter." . $name . ".php");
				$this->_plugins['outputfilter'][$name] = "template_" . $type . "filter_" . $name;
			   break;
			case 'pre':
			case 'post':
				if (!isset($this->_plugins[$type . 'filter'][$name]))
				{
					$this->_plugins[$type . 'filter'][$name] = "template_" . $type . "filter_" . $name;
				}
				break;
		}
	}

	public function assign($key, $value = null) {
		if (is_array($key)) {
			foreach($key as $var => $val)
				if ($var != "" && !is_numeric($var))
					$this->_vars[$var] = $val;
		} else {
			if ($key != "" && !is_numeric($key))
				$this->_vars[$key] = $value;
		}
	}

	public function assign_by_ref($key, $value = null)
	{
		if ($key != '')
		{
			$this->_vars[$key] = &$value;
		}
	}

	public function assign_config($key, $value = null)
	{
		if (is_array($key))
		{
			foreach($key as $var => $val)
			{
				if ($var != "")
				{
					$this->_confs[$var] = $val;
				}
			}
		}
		else
		{
			if ($key != "")
			{
				$this->_confs[$key] = $value;
			}
		}
	}

	public function append($key, $value=null, $merge=false)
	{
		if (is_array($key))
		{
			foreach ($key as $_key => $_value)
			{
				if ($_key != '')
				{
					if(!@is_array($this->_vars[$_key]))
					{
						settype($this->_vars[$_key],'array');
					}
					if($merge && is_array($_value))
					{
						foreach($_value as $_mergekey => $_mergevalue)
						{
							$this->_vars[$_key][$_mergekey] = $_mergevalue;
						}
					}
					else
					{
						$this->_vars[$_key][] = $_value;
					}
				}
			}
		}
		else
		{
			if ($key != '' && isset($value))
			{
				if(!@is_array($this->_vars[$key]))
				{
					settype($this->_vars[$key],'array');
				}
				if($merge && is_array($value))
				{
					foreach($value as $_mergekey => $_mergevalue)
					{
						$this->_vars[$key][$_mergekey] = $_mergevalue;
					}
				}
				else
				{
					$this->_vars[$key][] = $value;
				}
			}
		}
	}

	public function append_by_ref($key, &$value, $merge=false)
	{
		if ($key != '' && isset($value))
		{
			if(!@is_array($this->_vars[$key]))
			{
				settype($this->_vars[$key],'array');
			}
			if ($merge && is_array($value))
			{
				foreach($value as $_key => $_val)
				{
					$this->_vars[$key][$_key] = &$value[$_key];
				}
			}
			else
			{
				$this->_vars[$key][] = &$value;
			}
		}
	}

	public function clear_assign($key = null)
	{
		if ($key == null)
		{
			$this->_vars = array();
		}
		else
		{
			if (is_array($key))
			{
				foreach($key as $index => $value)
				{
					if (in_array($value, $this->_vars))
					{
						unset($this->_vars[$index]);
					}
				}
			}
			else
			{
				if (in_array($key, $this->_vars))
				{
					unset($this->_vars[$index]);
				}
			}
		}
	}

	public function clear_all_assign()
	{
		$this->_vars = array();
	}

	public function clear_config($key = null)
	{
		if ($key == null)
		{
			$this->_conf = array();
		}
		else
		{
			if (is_array($key))
			{
				foreach($key as $index => $value)
				{
					if (in_array($value, $this->_conf))
					{
						unset($this->_conf[$index]);
					}
				}
			}
			else
			{
				if (in_array($key, $this->_conf))
				{
					unset($this->_conf[$key]);
				}
			}
		}
	}

	function &get_template_vars($key = null)
	{
		if ($key == null)
		{
			return $this->_vars;
		}
		else
		{
			if (isset($this->_vars[$key]))
			{
				return $this->_vars[$key];
			}
			else
			{
				return $this->_null;
			}
		}
	}

	function &get_config_vars($key = null)
	{
		if ($key == null)
		{
			return $this->_confs;
		}
		else
		{
			if (isset($this->_confs[$key]))
			{
				return $this->_confs[$key];
			}
			else
			{
				return $this->_null;
			}
		}
	}

	public function clear_compiled_tpl($file = null)
	{
		$this->_destroy_dir($file, null, $this->_get_dir($this->compile_dir));
	}

	public function clear_cache($file = null, $cache_id = null, $compile_id = null, $exp_time = null)
	{
		if (!$this->cache)
		{
			return;
		}
		$this->_destroy_dir($file, $cache_id, $this->_get_dir($this->cache_dir));
	}

	public function clear_all_cache($exp_time = null)
	{
		$this->clear_cache();
	}

	public function is_cached($file, $cache_id = null)
	{
		if (!$this->force_compile && $this->cache && $this->_is_cached($file, $cache_id))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function register_modifier($modifier, $implementation)
	{
		$this->_plugins['modifier'][$modifier] = $implementation;
	}

	function unregister_modifier($modifier)
	{
		unset($this->_plugins['modifier'][$modifier]);
	}

	function register_function($function, $implementation)
	{
		$this->_plugins['function'][$function] = $implementation;
	}

	function unregister_function($function)
	{
		unset($this->_plugins['function'][$function]);
	}

	function register_block($function, $implementation)
	{
		$this->_plugins['block'][$function] = $implementation;
	}

	function unregister_block($function)
	{
		unset($this->_plugins['block'][$function]);
	}

	function register_compiler($function, $implementation)
	{
		$this->_plugins['compiler'][$function] = $implementation;
	}

	function unregister_compiler($function)
	{
		unset($this->_plugins['compiler'][$function]);
	}

	function register_prefilter($function)
	{
		$_name = (is_array($function)) ? $function[1] : $function;
		$this->_plugins['prefilter'][$_name] = $_name;
	}

	function unregister_prefilter($function)
	{
		unset($this->_plugins['prefilter'][$function]);
	}

	function register_postfilter($function)
	{
		$_name = (is_array($function)) ? $function[1] : $function;
		$this->_plugins['postfilter'][$_name] = $_name;
	}

	function unregister_postfilter($function)
	{
		unset($this->_plugins['postfilter'][$function]);
	}

	function register_outputfilter($function)
	{
		$_name = (is_array($function)) ? $function[1] : $function;
		$this->_plugins['outputfilter'][$_name] = $_name;
	}

	function unregister_outputfilter($function)
	{
		unset($this->_plugins['outputfilter'][$function]);
	}

	function register_resource($type, $functions)
	{
		if (count($functions) == 4)
		{
			$this->_plugins['resource'][$type] = $functions;
		}
		else
		{
			$this->trigger_error("malformed function-list for '$type' in register_resource");
		}
	}

	function unregister_resource($type)
	{
		unset($this->_plugins['resource'][$type]);
	}

	function template_exists($file) {
		if (file_exists($this->_get_dir($this->template_dir).$file)) {
			return true;
		} else {
			return false;
		}
	}

	function _get_resource($file)
	{
		$_resource_name = explode(':', trim($file));

		if (count($_resource_name) == 1 || $_resource_name[0] == "file")
        {
			if($_resource_name[0] == "file")
			{
				$file = substr($file, 5);
			}

			$exists = $this->template_exists($file);

			if (!$exists)
			{
				$this->trigger_error("file '$file' does not exist", E_USER_ERROR);
			}
		}
		else
		{
			$this->_resource_type = $_resource_name[0];
			$file = substr($file, strlen($this->_resource_type) + 1);
			$exists = isset($this->_plugins['resource'][$this->_resource_type]) && call_user_func_array($this->_plugins['resource'][$this->_resource_type][1], array($file, &$resource_timestamp, &$this));

			if (!$exists)
			{
				$this->trigger_error("file '$file' does not exist", E_USER_ERROR);
			}
			$this->_resource_time = $resource_timestamp;
		}
		return $file;
	}

	function display($file, $cache_id = null)
	{
		$this->fetch($file, $cache_id, true);
	}

	function fetch($file, $cache_id = null, $display = false) {
		$this->_cache_id = $cache_id;
		$this->template_dir = $this->_get_dir($this->template_dir);
		$this->compile_dir = $this->_get_dir($this->compile_dir);
		if ($this->cache)
			$this->_cache_dir = $this->_build_dir($this->cache_dir, $this->_cache_id);
		$name = md5($this->template_dir.$file).'.php';

		// don't display any errors
		$this->_error_level = error_reporting(error_reporting() & ~E_NOTICE);

		if (!$this->force_compile && $this->cache && $this->_is_cached($file, $cache_id)) {
			ob_start();
			include($this->_cache_dir.$name);
			$output = ob_get_contents();
			ob_end_clean();
			$output = substr($output, strpos($output, "\n") + 1);
		} else {
			$output = $this->_fetch_compile($file, $cache_id);
			if ($this->cache) {
				$f = fopen($this->_cache_dir.$name, "w");
				fwrite($f, serialize($this->_cache_info) . "\n$output");
				fclose($f);
			}
		}

		if (strpos($output, $this->_sl_md5) !== false) {
			preg_match_all('!' . $this->_sl_md5 . '{_run_insert (.*)}' . $this->_sl_md5 . '!U',$output,$_match);
			foreach($_match[1] as $value) {
				$arguments = unserialize($value);
				$output = str_replace($this->_sl_md5 . '{_run_insert ' . $value . '}' . $this->_sl_md5, call_user_func_array('insert_' . $arguments['name'], array((array)$arguments, $this)), $output);
			}
		}

		// return error reporting to normal
		error_reporting($this->_error_level);

		if ($display) {
			echo $output;
		} else {
			return $output;
		}
	}

	function config_load($file, $section_name = null, $var_name = null)
	{
		require_once(XTEMPLATE_DIR . "internal/template.config_loader.php");
	}

	function _is_cached($file, $cache_id) {
		$this->_cache_dir = $this->_get_dir($this->cache_dir, $cache_id);
		$this->config_dir = $this->_get_dir($this->config_dir);
		$this->template_dir = $this->_get_dir($this->template_dir);
		$name = md5($this->template_dir.$file).'.php';

		if (file_exists($this->_cache_dir.$name) && (((time() - filemtime($this->_cache_dir.$name)) < $this->cache_lifetime) || $this->cache_lifetime == -1) && (filemtime($this->_cache_dir.$name) > filemtime($this->template_dir.$file))) {
			$fh = fopen($this->_cache_dir.$name, "r");
			if (!feof($fh) && ($line = fgets($fh, filesize($this->_cache_dir.$name)))) {
				$includes = unserialize($line);
				if (isset($includes['template']))
					foreach($includes['template'] as $value)
						if (!(file_exists($this->template_dir.$value) && (filemtime($this->_cache_dir.$name) > filemtime($this->template_dir.$value))))
							return false;
				if (isset($includes['config']))
					foreach($includes['config'] as $value)
						if (!(file_exists($this->config_dir.$value) && (filemtime($this->_cache_dir.$name) > filemtime($this->config_dir.$value))))
							return false;
			}
			fclose($fh);
		} else {
			return false;
		}
		return true;
	}

	function _fetch_compile_include($_xtemplate_include_file, $_xtemplate_include_vars)
	{
		if(!function_exists("template_fetch_compile_include"))
		{
			require_once(XTEMPLATE_DIR . "internal/template.fetch_compile_include.php");
		}
		return template_fetch_compile_include($_xtemplate_include_file, $_xtemplate_include_vars, $this);
	}
	
	function compress($buffer){
		$search = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
		$replace = array('>','<','\\1');
		$buffer = preg_replace($search, $replace, $buffer);
		return $buffer;
	}

	function _fetch_compile($file)
	{
		$this->template_dir = $this->_get_dir($this->template_dir);
		$name = md5($this->template_dir.$file).'.php';

		if(!in_array($_SERVER['REMOTE_ADDR'], $this->developer_ips)){
			if (file_exists($this->compile_dir.'c_'.$name)) {
				flush();
				ob_start();
				include_once($this->compile_dir.'c_'.$name);
				$output = ob_get_contents();
				ob_end_clean();
				ob_end_flush();
				return $output;
				exit(0);
			} 
		}

		$file_contents = "";
		if($this->_resource_type == 1)
		{
			$f = fopen($this->template_dir . $file, "r");
			$size = filesize($this->template_dir . $file);
			if ($size > 0)
			{
				$file_contents = fread($f, $size);
			}
		}
		else
		if($this->_resource_type == "file")
		{
			$f = fopen($file, "r");
			$size = filesize($file);
			if ($size > 0)
			{
				$file_contents = fread($f, $size);
			}
		}
		else
		{
			call_user_func_array($this->_plugins['resource'][$this->_resource_type][0], array($file, &$file_contents, &$this));
		}

		$this->_file = $file;
		fclose($f);

		if (!is_object($this->_compile_obj))
		{
			if (file_exists(XTEMPLATE_DIR . $this->compiler_file)) {
				require_once(XTEMPLATE_DIR . $this->compiler_file);
			} else {
				require_once($this->compiler_file);
			}
			$this->_compile_obj = new $this->compiler_class;
		}
		$this->_compile_obj->left_delimiter = $this->left_delimiter;
		$this->_compile_obj->right_delimiter = $this->right_delimiter;
		$this->_compile_obj->plugins_dir = &$this->plugins_dir;
		$this->_compile_obj->template_dir = &$this->template_dir;
		$this->_compile_obj->_vars = &$this->_vars;
		$this->_compile_obj->_confs = &$this->_confs;
		$this->_compile_obj->_plugins = &$this->_plugins;
		$this->_compile_obj->_linenum = &$this->_linenum;
		$this->_compile_obj->_file = &$this->_file;
		$this->_compile_obj->php_extract_vars = &$this->php_extract_vars;
		$this->_compile_obj->reserved_template_varname = &$this->reserved_template_varname;
		$this->_compile_obj->default_modifiers = $this->default_modifiers;
		$output = $this->_compile_obj->_compile_file($file_contents);
    	#$output = $this->compress($output); 
		$f = fopen($this->compile_dir.'c_'.$name, "w");
		fwrite($f, $output);
		fclose($f);

		ob_start();
		eval(' ?>' . $output . '<?php ');
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}


	function _run_modifier()
	{
		$arguments = func_get_args();
		list($variable, $modifier, $php_function, $_map_array) = array_splice($arguments, 0, 4);
		array_unshift($arguments, $variable);
		if ($_map_array && is_array($variable))
		{
			foreach($variable as $key => $value)
			{
				if($php_function == "PHP")
				{
					$variable[$key] = call_user_func_array($modifier, $arguments);
				}
				else
				{
					$variable[$key] = call_user_func_array($this->_plugins["modifier"][$modifier], $arguments);
				}
			}
		}
		else
		{
			if($php_function == "PHP")
			{
				$variable = call_user_func_array($modifier, $arguments);
			}
			else
			{
				$variable = call_user_func_array($this->_plugins["modifier"][$modifier], $arguments);
			}
		}
		return $variable;
	}

	function _run_insert($arguments)
	{
			if (!function_exists('insert_' . $arguments['name']))
			{
				$this->trigger_error("function 'insert_" . $arguments['name'] . "' does not exist in 'insert'", E_USER_ERROR);
			}

			if (isset($arguments['assign']))
			{
				$this->assign($arguments['assign'], call_user_func_array('insert_' . $arguments['name'], array((array)$arguments, $this)));
			}
			else
			{
				return call_user_func_array('insert_' . $arguments['name'], array((array)$arguments, $this));
			}
	}

	function _get_dir($dir, $id = null)
	{
		if (empty($dir))
		{
			$dir = '.';
		}
		if (substr($dir, -1) != DIRECTORY_SEPARATOR)
		{
			$dir .= DIRECTORY_SEPARATOR;
		}
		if (!empty($id))
		{
			$_args = explode('|', $id);
			if (count($_args) == 1 && empty($_args[0]))
			{
				return $dir;
			}
			foreach($_args as $value)
			{
				$dir .= $value.DIRECTORY_SEPARATOR;
			}
		}
		return $dir;
	}

	function _get_plugin_dir($plugin_name)
	{
		static $_path_array = null;

		$plugin_dir_path = "";
		$_plugin_dir_list = is_array($this->plugins_dir) ? $this->plugins_dir : (array)$this->plugins_dir;
		foreach ($_plugin_dir_list as $_plugin_dir)
		{
			if (!preg_match("/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/", $_plugin_dir))
			{
				// path is relative
				if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . $_plugin_dir . DIRECTORY_SEPARATOR . $plugin_name))
				{
					$plugin_dir_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $_plugin_dir . DIRECTORY_SEPARATOR;
					break;
				}
			}
			else
			{
				// path is absolute
				if(!isset($_path_array))
				{
					$_ini_include_path = ini_get('include_path');

					if(strstr($_ini_include_path,';'))
					{
						// windows pathnames
						$_path_array = explode(';',$_ini_include_path);
					}
					else
					{
						$_path_array = explode(':',$_ini_include_path);
					}
				}

				if(!in_array($_plugin_dir,$_path_array))
				{
					array_unshift($_path_array,$_plugin_dir);
				}

				foreach ($_path_array as $_include_path)
				{
					if (file_exists($_include_path . DIRECTORY_SEPARATOR . $plugin_name))
					{
						$plugin_dir_path = $_include_path . DIRECTORY_SEPARATOR;
						break 2;
					}
				}
			}
		}
		return $plugin_dir_path;
	}

	function _build_dir($dir, $id)
	{
		if(!function_exists("template_build_dir"))
		{
			require_once(XTEMPLATE_DIR . "internal/template.build_dir.php");
		}
		return template_build_dir($dir, $id, $this);
	}

	function _destroy_dir($file, $id, $dir)
	{
		if(!function_exists("template_destroy_dir"))
		{
			require_once(XTEMPLATE_DIR . "internal/template.destroy_dir.php");
		}
		return template_destroy_dir($file, $id, $dir, $this);
	}

	function trigger_error($error_msg, $error_type = E_USER_ERROR, $file = null, $line = null)
	{
		if(isset($file) && isset($line))
		{
			$info = ' ('.basename($file).", line $line)";
		}
		else
		{
			$info = null;
		}
		trigger_error('TPL: [in ' . $this->_file . ' line ' . $this->_linenum . "]: syntax error: $error_msg$info", $error_type);
	}
}
?>
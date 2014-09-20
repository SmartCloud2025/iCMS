<?php
/*
 * Project:	template_lite, a smarter template engine
 * Author:	Paul Lockaby <paul@paullockaby.com>, Mark Dickenson <akapanamajack@sourceforge.net>
 * Copyright:	2003,2004,2005 by Paul Lockaby, 2005,2006 Mark Dickenson
 */
define('iTEMPLATE_PATH',dirname(strtr(__FILE__,'\\','/'))."/");
define('iTEMPLATE_DIR', iTEMPLATE_PATH.'template/');

class iTemplate {
	// public configuration variables
	public $left_delimiter            = "<!--{";		// the left delimiter for template tags
	public $right_delimiter           = "}-->";		// the right delimiter for template tags
	public $template_dir              = "template";	// where the templates are to be found
	public $plugins_dir               = "plugins";	// where the plugins are to be found
	public $compile_dir               = "cache";	// the directory to store the compiled files in
	public $tpl_tags                  = array();	// the directory to store the compiled files in

	public $php_extract_vars          = false;	// Set this to true if you want the $this->_tpl variables to be extracted for use by PHP code inside the template.
	public $php_handling              = "PHP_QUOTE";//2007-7-23 0:01 quote php tags
	public $default_modifiers         = array();
	public $debugging                 = false;

	public $_error_reporting          = "<?php defined('iPHP') OR exit('What are you doing?');error_reporting(iPHP_TPL_DEBUG?E_ALL & ~E_NOTICE:0);?>\n";

	public $reserved_template_varname = iPHP_TPL_VAR;

	// private internal variables
	public $_vars                     = array();	// stores all internal assigned variables
	public $_plugins                  = array('modifier'=> array(),'function' => array(),'block' => array(),'compiler' => array());
	public $_linenum                  = 0;		// the current line number in the file we are processing
	public $_file                     = "";		// the current file we are processing
	public $_include_file             = false;		// the current file we are processing
	public $_version                  = 'V2.10 Template Lite 4 January 2007  (c) 2005-2007 Mark Dickenson. All rights reserved. Released LGPL.';

	public $_templatelite_debug_info  = array();
	public $_templatelite_debug_loop  = false;
	public $_templatelite_debug_dir   = "";
	public $_inclusion_depth          = 0;
	public $_null                     = null;
	public $_sections                 = array();
	public $_foreach                  = array();
	public $_iTPL_VARS                =	array();

	function __construct() {
		$this->def_template_dir = $this->template_dir;
	}

	function assign($key, $value = null){
		if (is_array($key)){
			foreach($key as $var => $val)
				$var && $this->_vars[$var] = $val;
		}else{
			$key && $this->_vars[$key] = $value;
		}
	}

	function assign_by_ref($key, $value = null){
		$key && $this->_vars[$key] = &$value;
	}

	function append($key, $value=null, $merge=false){
		if (is_array($key)){
			foreach ($key as $_key => $_value){
				if ($_key != ''){
					if(!@is_array($this->_vars[$_key])){
						settype($this->_vars[$_key],'array');
					}
					if($merge && is_array($_value)){
						foreach($_value as $_mergekey => $_mergevalue){
							$this->_vars[$_key][$_mergekey] = $_mergevalue;
						}
					}else{
						$this->_vars[$_key][] = $_value;
					}
				}
			}
		}else{
			if ($key != '' && isset($value)){
				if(!@is_array($this->_vars[$key])){
					settype($this->_vars[$key],'array');
				}
				if($merge && is_array($value)){
					foreach($value as $_mergekey => $_mergevalue){
						$this->_vars[$key][$_mergekey] = $_mergevalue;
					}
				}else{
					$this->_vars[$key][] = $value;
				}
			}
		}
	}

	function append_by_ref($key, &$value, $merge=false){
		if ($key != '' && isset($value)){
			if(!@is_array($this->_vars[$key])){
				settype($this->_vars[$key],'array');
			}
			if ($merge && is_array($value)){
				foreach($value as $_key => $_val){
					$this->_vars[$key][$_key] = &$value[$_key];
				}
			}else{
				$this->_vars[$key][] = &$value;
			}
		}
	}

	function clear_assign($key = null){
		if ($key == null){
			$this->_vars = array();
		}else{
			if (is_array($key)){
				foreach($key as $index => $value){
					if (in_array($value, $this->_vars)){
						unset($this->_vars[$index]);
					}
				}
			}else{
				if (in_array($key, $this->_vars)){
					unset($this->_vars[$index]);
				}
			}
		}
	}

	function clear_all_assign(){
		$this->_vars = array();
	}

	function &get_template_vars($key = null){
		if ($key == null){
			return $this->_vars;
		}else{
			if (isset($this->_vars[$key])){
				return $this->_vars[$key];
			}else{
				return $this->_null;
			}
		}
	}

	function clear_compiled_tpl($file = null){
		$this->_destroy_dir($file);
	}

	function register_modifier($modifier, $implementation){
		$this->_plugins['modifier'][$modifier] = $implementation;
	}

	function unregister_modifier($modifier){
		unset($this->_plugins['modifier'][$modifier]);
	}

	function register_function($function, $implementation){
		$this->_plugins['function'][$function] = $implementation;
	}

	function unregister_function($function){
		unset($this->_plugins['function'][$function]);
	}

	function register_compiler($function, $implementation){
		$this->_plugins['compiler'][$function] = $implementation;
	}

	function unregister_compiler($function){
		unset($this->_plugins['compiler'][$function]);
	}
	function _get_resource($file){
//		if(strstr($file, 'debug.tpl')){
//			return $file;
//		}

		$file = ltrim($file,'/');
		strpos($file,'..') && $this->trigger_error("resource file has '..'", E_USER_ERROR);
		if(strpos($file, 'file::')!==false){
			list($_dir,$file)   = explode('||',str_replace('file::','',$file));
			$this->template_dir = $_dir;
		}else{
			strpos($file,'./') !==false && $file = str_replace('./',dirname($this->_file).'/',$file);
	        if(strpos($file,iPHP_APP.':/') !==false){
				$file = str_replace(iPHP_APP.':/',iPHP_APP,$file);
				if(!is_file(iPHP_TPL_DIR."/".$file)) {
					$file = str_replace(iPHP_APP.':/',iPHP_TPL_DEFAULT,$file);
				}
			}elseif(strpos($file,'{iTPL}') !==false){
				$file = str_replace('{iTPL}',iPHP_TPL_DEFAULT,$file);
			}
		}

		$this->template_dir = $this->_get_dir($this->template_dir);
		$RootPath           = $this->template_dir.$file;
		@is_file($RootPath) OR $this->trigger_error("file '$RootPath' does not exist", E_USER_ERROR);
		return $RootPath;
	}
	function _get_compile_file($file){
		$this->compile_dir = $this->_get_dir($this->compile_dir);
		$compile_file      = str_replace(array($this->template_dir,'/','.'),array('','_','_'),$file).'.php';
		$compile_file      = $this->compile_dir.$this->reserved_template_varname.'.'.$compile_file;
		return $compile_file;
	}
	function internal($fn){
		if(!function_exists($fn)){
			require iTEMPLATE_DIR . "internal/{$fn}.php";
		}
	}
	function display($file){
		$this->fetch($file,true);
	}

	function fetch($file,$display = false){
		if ($this->debugging){
			$this->_templatelite_debug_info[] = array('type'=> 'template',
												'filename'  => $file,
												'depth'     => 0,
												'exec_time' => array_sum(explode(' ', microtime())) );
			$included_tpls_idx = count($this->_templatelite_debug_info) - 1;
		}

		if ($display){

			$this->_fetch_compile($file);

			$this->debugging && $this->_templatelite_debug_info[$included_tpls_idx]['exec_time'] = array_sum(explode(' ', microtime())) - $this->_templatelite_debug_info[$included_tpls_idx]['exec_time'];
			if($this->debugging && !$this->_templatelite_debug_loop){
				$this->debugging = false;
				$this->internal('template_generate_debug_output');
				template_generate_debug_output($this);
				$this->debugging = true;
			}
		}else{
			return $this->_fetch_compile($file, true);
		}
	}

	function _fetch_compile_include($_templatelite_include_file, $_templatelite_include_vars){
		$this->internal('template_fetch_compile_include');
		return template_fetch_compile_include($_templatelite_include_file, $_templatelite_include_vars, $this);
	}

	function _fetch_compile($file, $ret = false){
		$template_file 		= $this->_get_resource($file);
		$compile_file       = $this->_get_compile_file($template_file);
		$this->_include_file OR $this->_file = $file;

		if (!@is_file($compile_file)){
			$iTC                            = new iTemplate_Compiler();
			$iTC->left_delimiter            = $this->left_delimiter;
			$iTC->right_delimiter           = $this->right_delimiter;
			$iTC->plugins_dir               = &$this->plugins_dir;
			$iTC->template_dir              = &$this->template_dir;
			$iTC->compile_dir               = &$this->compile_dir;
			$iTC->_vars                     = &$this->_vars;
			$iTC->_plugins                  = &$this->_plugins;
			$iTC->_linenum                  = &$this->_linenum;
			$iTC->_file                     = &$this->_file;
			$iTC->php_extract_vars          = &$this->php_extract_vars;
			$iTC->reserved_template_varname = &$this->reserved_template_varname;
			$iTC->_iTPL_VARS                = &$this->_iTPL_VARS;
			$iTC->default_modifiers         = &$this->default_modifiers;
			$output                         = $iTC->_compile_file($template_file);
			file_put_contents($compile_file,$output);
		}
		if($ret==='file') return $compile_file;

		$ret && ob_start();
		include $compile_file;
		if ($ret) {
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
	}

	function _run_modifier(){
		$arguments = func_get_args();
		list($variable, $modifier, $php_function, $_map_array) = array_splice($arguments, 0, 4);
		array_unshift($arguments, $variable);
		if(in_array($modifier,array("eval","include","system","exec","shell_exec","passthru","set_time_limit","ini_alter","dl","openlog","syslog","readlink","symlink","link","leak","popen","escapeshellcmd","apache_child_terminate","apache_get_modules","apache_get_version","apache_getenv","apache_note","apache_setenv","virtual"))){
			return false;
		}
		if ($_map_array){
			if($php_function == "PHP"){
				$variable = call_user_func_array($modifier, $arguments);
			}else{
				$variable = call_user_func_array($this->_plugins["modifier"][$modifier], $arguments);
			}
		}
		return $variable;
	}

	function _run_iPHP($a){
		$keys			= isset($a['as'])?$a['as']:$a['app'];
		if($a['method']){
			$callback	= $a['app'].'_'.$a['method'];
			function_exists($callback) OR $this->require_one(iPHP_APP_DIR."/".$a['app']."/".$a['app'].".func.php");
			isset($a['as']) OR $keys.= '_'.$a['method'];
		}else{
			$callback	= iPHP_TPL_VAR.'_' . $a['app'];
			function_exists($callback) OR $this->require_one(iPHP_TPL_FUN."/".iPHP_TPL_VAR.".".$a['app'].".php");
		}
		if(isset($a['vars'])){
			$_a = $a['vars'];
			unset($a['vars']);
		 	$a = array_merge($a,$_a);
		}
		$this->assign($keys,$callback($a));
	}

	function _get_dir($dir){
		return rtrim($dir,'/').'/';
	}

	function _get_plugin_dir($name){
		return iTEMPLATE_DIR.$this->plugins_dir.'/';
	}

	function _destroy_dir($file){
		if ($file == null){
			foreach ((array)glob($this->compile_dir.'/'.$this->reserved_template_varname.'.*.php') as $fpath) {
				@chmod($fpath, 0777);
		    	@unlink($fpath);
			}
		}else{
			$fpath = $this->_get_compile_file($file);
			@chmod($fpath, 0777);
		    @unlink($fpath);
		}
	}

	function trigger_error($error_msg, $error_type = E_USER_ERROR, $file = null, $line = null){
		$info = null;
		if(isset($file) && isset($line)){
			$info = ' ('.basename($file).", line $line)";
		}
		trigger_error('TPL: [in ' . $this->_file . ' line ' . ($this->_linenum-1) . "]: syntax error: $error_msg$info", $error_type);
	}
    function require_one($path){
		is_file($path) OR $this->trigger_error($path. " does not exist", E_USER_ERROR);
		$key	= str_replace(iPATH,'iPHP://',$path);
		//$key	= str_replace(array('/','.'),'_',$key);
      	if(isset($GLOBALS['_iPHP_REQ'][$key])) return;

		$GLOBALS['_iPHP_REQ'][$key] = true;
		require $path;
    }
}
class iTemplate_Compiler extends iTemplate {
	// public configuration variables
	public $left_delimiter            = "";
	public $right_delimiter           = "";
	public $plugins_dir               = "";
	public $template_dir              = "";
	public $reserved_template_varname = "";
	public $default_modifiers         = array();

	public $php_extract_vars          = true;	// Set this to false if you do not want the $this->_tpl variables to be extracted for use by PHP code inside the template.
	public $error                     = true;
	// private internal variables
	public $_vars                     = array();	// stores all internal assigned variables
	public $_plugins                  = array();	// stores all internal plugins
	public $_linenum                  = 0;		// the current line number in the file we are processing
	public $_file                     = "";		// the current file we are processing
	public $_literal                  = array();	// stores all literal blocks
	public $_foreachelse_stack        = array();
	public $_for_stack                = 0;
	public $_sectionelse_stack        = array();	// keeps track of whether section had 'else' part
	public $_iPHP_else_stack          = false;	// keeps track of whether section had 'else' part
	public $_iPHP_stack               = array();
	public $_switch_stack             = array();
	public $_tag_stack                = array();
	public $_require_stack            = array();	// stores all files that are "required" inside of the template
	public $_php_blocks               = array();	// stores all of the php blocks
	public $_error_level              = null;

	public $_db_qstr_regexp           = null;		// regexps are setup in the constructor
	public $_si_qstr_regexp           = null;
	public $_qstr_regexp              = null;
	public $_func_regexp              = null;
	public $_var_bracket_regexp       =	null;
	public $_dvar_regexp              =	null;
	public $_svar_regexp              =	null;
	public $_mod_regexp               =	null;
	public $_var_regexp               =	null;
	public $_obj_params_regexp        = null;
	public $_iTPL_VARS                =	array();

	function iTemplate_Compiler(){
		// matches double quoted strings:
		// "foobar"
		// "foo\"bar"
		// "foobar" . "foo\"bar"
		$this->_db_qstr_regexp = '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"';

		// matches single quoted strings:
		// 'foobar'
		// 'foo\'bar'
		$this->_si_qstr_regexp = '\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'';

		// matches single or double quoted strings
		$this->_qstr_regexp = '(?:' . $this->_db_qstr_regexp . '|' . $this->_si_qstr_regexp . ')';

		// matches bracket portion of vars
		// [0]
		// [foo]
		// [$bar]
		// [#bar#]
//		$this->_var_bracket_regexp = '\[[\$|\#]?\w+\#?\]';
		$this->_var_bracket_regexp = '\[\$?[\w\.]+\]';

		// matches section vars:
		// %foo.bar%
		$this->_svar_regexp = '\%\w+\.\w+\%';

		// matches $ vars (not objects):
		// $foo
		// $foo[0]
		// $foo[$bar]
		// $foo[5][blah]
//		$this->_dvar_regexp = '\$[a-zA-Z0-9_]{1,}(?:' . $this->_var_bracket_regexp . ')*(?:' . $this->_var_bracket_regexp . ')*';
		$this->_dvar_regexp = '\$\w{1,}(?:' . $this->_var_bracket_regexp . ')*(?:\.\$?\w+(?:' . $this->_var_bracket_regexp . ')*)*';

		// matches valid variable syntax:
		// $foo
		// 'text'
		// "text"
		$this->_var_regexp = '(?:(?:' . $this->_dvar_regexp . ')|' . $this->_qstr_regexp . ')';
		// matches valid modifier syntax:
		// |foo
		// |@foo
		// |foo:"bar"
		// |foo:$bar
		// |foo:"bar":$foobar
		// |foo|bar
		$this->_mod_regexp = '(?:\|@?\w+(?::(?>-?\w+|' . $this->_dvar_regexp . '|' . $this->_qstr_regexp .'))*)';

		// matches valid function name:
		// foo123
		// _foo_bar
		$this->_func_regexp = '[a-zA-Z_:]+';
//		$this->_func_regexp = '[a-zA-Z_]\w*';

	}

	function _compile_file($template_file){
		$file_contents = file_get_contents($template_file);
		$ldq           = preg_quote($this->left_delimiter);
		$rdq           = preg_quote($this->right_delimiter);
		$_match        = array();		// a temp variable for the current regex match
		$tags          = array();		// all original tags
		$text          = array();		// all original text
		$compiled_text = '';
		$compiled_tags = array();		// all tags and stuff

		$this->_require_stack = array();

		// remove all comments
		$file_contents = preg_replace ("!{$ldq}\*.*?\*{$rdq}!s","",$file_contents);

		// replace all php start and end tags
//		$file_contents = preg_replace('%(<\?(?!php|=|$))%i', '<?php echo \'\\1\'? >'."\n", $file_contents);

//2007-7-22 23:41 coolmoo add
        /* match anything resembling php tags */
        if (preg_match_all('~(<\?(?:\w+|=)?|\?>|language\s*=\s*[\"\']?\s*php\s*[\"\']?)~is', $file_contents, $sp_match)) {
             /* replace tags with placeholders to prevent recursive replacements */
             $sp_match[1] = array_unique($sp_match[1]);
             /* process each one */
             for ($curr_sp = 0, $for_max2 = count($sp_match[1]); $curr_sp < $for_max2; $curr_sp++) {
                 if ($this->php_handling == "PHP_PASSTHRU") {
                        /* echo php contents */
                        $file_contents = str_replace($sp_match[1][$curr_sp], '<?php echo \''.str_replace("'", "\'", $sp_match[1][$curr_sp]).'\'; ?>', $file_contents);
                    } else if ($this->php_handling == "PHP_QUOTE") {
                        /* quote php tags */
                        $file_contents = str_replace($sp_match[1][$curr_sp], htmlspecialchars($sp_match[1][$curr_sp]), $file_contents);
                    } else if ($this->php_handling == "PHP_REMOVE") {
                        /* remove php tags */
                        $file_contents = str_replace($sp_match[1][$curr_sp], '', $file_contents);
                    } else {
                        /* PHP_ALLOW, but echo non php starting tags */
                        $sp_match[1][$curr_sp] = preg_replace('~(<\?(?!php|=|$))~i', '<?php echo \'\\1\'?>', $sp_match[1][$curr_sp]);
                        $file_contents = str_replace($sp_match[1][$curr_sp], $sp_match[1][$curr_sp], $file_contents);
                    }
                }
            }
//2007-7-22 23:46
		// remove literal blocks

		preg_match_all("!{$ldq}\s*literal\s*{$rdq}(.*?){$ldq}\s*/literal\s*{$rdq}!s", $file_contents, $_match);
		$this->_literal = $_match[1];
		$file_contents = preg_replace("!{$ldq}\s*literal\s*{$rdq}(.*?){$ldq}\s*/literal\s*{$rdq}!s", stripslashes($ldq . "literal" . $rdq), $file_contents);

		// remove php blocks
		preg_match_all("!{$ldq}\s*php\s*{$rdq}(.*?){$ldq}\s*/php\s*{$rdq}!s", $file_contents, $_match);
		$this->_php_blocks = $_match[1];
		$file_contents = preg_replace("!{$ldq}\s*php\s*{$rdq}(.*?){$ldq}\s*/php\s*{$rdq}!s", stripslashes($ldq . "php" . $rdq), $file_contents);

		// gather all template tags
		preg_match_all("!{$ldq}\s*(.*?)\s*{$rdq}!s", $file_contents, $_match);
		$tags = $_match[1];

		// put all of the non-template tag text blocks into an array, using the template tags as delimiters
		$text = preg_split("!{$ldq}.*?{$rdq}!s", $file_contents);

		// compile template tags
		$count_tags = count($tags);
		for ($i = 0, $for_max = $count_tags; $i < $for_max; $i++){
			$this->_linenum += substr_count($text[$i], "\n");
			$compiled_tags[] = $this->_compile_tag($tags[$i]);
			$this->_linenum += substr_count($tags[$i], "\n");
		}

		// build the compiled template by replacing and interleaving text blocks and compiled tags
		$count_compiled_tags = count($compiled_tags);
		for ($i = 0, $for_max = $count_compiled_tags; $i < $for_max; $i++){
			if ($compiled_tags[$i] == '') {
				$text[$i+1] = preg_replace('~^(\r\n|\r|\n)~', '', $text[$i+1]);
			}
			$compiled_text .= $text[$i].$compiled_tags[$i];
		}
		$compiled_text .= $text[$i];

		foreach ($this->_require_stack as $key => $value){
			$compiled_text = '<?php require_once \''. $this->_get_plugin_dir($key) . $key . '\'; $this->register_' . $value[0] . '("' . $value[1] . '", "' . $value[2] . '"); ?>' . $compiled_text;
		}
		unset($this->_require_stack);

		// remove unnecessary close/open tags
		$compiled_text = preg_replace('!\?>\n?<\?php!', '', $compiled_text);

//2007-7-29 21:15 error_reporting/function_exists
		$compiled_text = $this->_error_reporting.$compiled_text;
		return $compiled_text;
	}

	function _compile_tag($tag){
		$_match		= array();		// stores the tags
		$_result	= "";			// the compiled tag
		$_variable	= "";			// the compiled variable

		// extract the tag command, modifier and arguments
		preg_match_all('/(?:(' . $this->_var_regexp . '|' . $this->_svar_regexp . '|\/?' . $this->_func_regexp . ')(' . $this->_mod_regexp . '*)(?:\s*[,\.]\s*)?)(?:\s+(.*))?/xs', $tag, $_match);

		if ($_match[1][0]{0} == '$' || $_match[1][0]{0} == "'" || $_match[1][0]{0} == '"' || $_match[1][0]{0} == '%'){
			$_result = $this->_parse_variables($_match[1], $_match[2]);
			return "<?php echo $_result; ?>";
		}
		// process a function
		$tag_command   = $_match[1][0];
		$tag_modifiers = empty($_match[2][0]) ? null : $_match[2][0];
		$tag_arguments = empty($_match[3][0]) ? null : $_match[3][0];
		$_result       = $this->_parse_function($tag_command, $tag_modifiers, $tag_arguments);
		return $_result;
	}
	function _parse_function($function, $modifiers, $arguments){
		if(strpos($function,iPHP_TPL_VAR.':')!==false){
			list($function,$app,$method)=explode(':',$function);
		}
		//var_dump($function,$app,$method);
		switch ($function) {
			case 'include':
				$this->internal('compile_include');
				return $include_file = str_replace($this->_error_reporting, '', compile_include($arguments, $this));
				break;
			case iPHP_TPL_VAR:
				$arguments.=' app="'.$app.'"';
				$method && $arguments.=' method="'.$method.'"';
				$_args = $this->_parse_arguments($arguments);
				if($app && isset($_args['loop'])){
					$this->_iPHP_stack[count($this->_iPHP_stack)-1] = true;
					$_app	= $this->_dequote($_args['app']);
					$_args['method'] && $_app.='_'.$this->_dequote($_args['method']);
					$_args['as'] 	 && $_app = $this->_dequote($_args['as']);
					$arguments = 'app=$'.$_app;
					isset($_args['start'])	&& $arguments.=" start={$_args['start']} ";
					isset($_args['step'])	&& $arguments.=" step={$_args['step']} ";
					isset($_args['max'])	&& $arguments.=" max={$_args['max']} ";
					$this->internal('compile_iPHP');
					$compile_iPHP	= compile_iPHP($arguments, $this);
				}
				isset($_args['app']) OR $this->trigger_error("missing 'app' attribute in '".iPHP_TPL_VAR."'", E_USER_ERROR, __FILE__, __LINE__);

				foreach ($_args as $key => $value){
					$arg_list[] = "'$key' => $value";
				}

				return '<?php $this->_run_iPHP(array('.implode(',', (array)$arg_list).')); ?>'.$compile_iPHP;
				break;
			case iPHP_TPL_VAR.'else':
				$this->_iPHP_else_stack = true;
				return "<?php }}else{ ?>";
				break;
			case '/'.iPHP_TPL_VAR:
				array_pop($this->_iPHP_stack) OR $this->trigger_error("missing 'loop' attribute in '".iPHP_TPL_VAR."'", E_USER_ERROR, __FILE__, __LINE__);
				if($this->_iPHP_else_stack){
					$this->_iPHP_else_stack = false;
					return "<?php } ?>";
				}
				return "<?php }} ?>";
				break;
			case 'ldelim':
				return $this->left_delimiter;
				break;
			case 'rdelim':
				return $this->right_delimiter;
				break;
			case 'literal':
				list (,$literal) = each($this->_literal);
				$this->_linenum += substr_count($literal, "\n");
				return "<?php echo '" . str_replace("'", "\'", str_replace("\\", "\\\\", $literal)) . "'; ?>";
				break;
			case 'php':
				list (,$php_block) = each($this->_php_blocks);
				//¹ýÂËPHP 2007-7-23 0:13
				if($this->php_handling != "PHP_ALLOW"){
					return htmlspecialchars($php_extract . '<?php '.$php_block.' ?>');
					break;
				}
				//¹ýÂËPHP 2007-7-23 0:13
				$this->_linenum += substr_count($php_block, "\n");
				$php_extract = '';
				$this->php_extract_vars && $php_extract = '<?php extract($this->_vars, EXTR_REFS); ?>' . "\n";
				return $php_extract . '<?php '.$php_block.' ?>';
				break;
			case 'foreach':
				array_push($this->_foreachelse_stack, false);
				$_args = $this->_parse_arguments($arguments);
				isset($_args['from']) OR $this->trigger_error("missing 'from' attribute in 'foreach'", E_USER_ERROR, __FILE__, __LINE__);
				if (!isset($_args['value']) && !isset($_args['item'])){
					$this->trigger_error("missing 'value' attribute in 'foreach'", E_USER_ERROR, __FILE__, __LINE__);
				}
				if (isset($_args['value'])){
					$_args['value'] = $this->_dequote($_args['value']);
				}elseif (isset($_args['item'])){
					$_args['value'] = $this->_dequote($_args['item']);
				}
//				isset($_args['key']) ? $_args['key'] = "\$this->_vars['".$this->_dequote($_args['key'])."'] => " : $_args['key'] = '';
				isset($_args['key']) OR $_args['key'] ='f'.rand(100,999);
				$keystr = "\$this->_vars['".$this->_dequote($_args['key'])."'] => ";
				$_result = '<?php $_count=count((array)' . $_args['from'] . ');
				$this->_vars[\'' . $_args['value'] . '\'][\'last\']=false;
				$this->_vars[\'' . $_args['value'] . '\'][\'first\']=false;
				$fec=1;
				if ($_count){ foreach ((array)' . $_args['from'] . ' as ' . $keystr . '$this->_vars[\'' . $_args['value'] . '\']){
					$fec==1 && $this->_vars[\'' . $_args['value'] . '\'][\'first\']=true;
					$fec==$_count && $this->_vars[\'' . $_args['value'] . '\'][\'last\']=true;
					$fec++;
				?>';
				return $_result;
				break;
			case 'foreachelse':
				$this->_foreachelse_stack[count($this->_foreachelse_stack)-1] = true;
				return "<?php }}else{ ?>";
				break;
			case '/foreach':
				if (array_pop($this->_foreachelse_stack)){
					return "<?php } ?>";
				}else{
					return "<?php }} ?>";
				}
				break;
			case 'for':
				$this->_for_stack++;
				$_args = $this->_parse_arguments($arguments);
				isset($_args['start'])	OR $this->trigger_error("missing 'start' attribute in 'for'", E_USER_ERROR, __FILE__, __LINE__);
				isset($_args['stop'])	OR $this->trigger_error("missing 'stop' attribute in 'for'", E_USER_ERROR, __FILE__, __LINE__);
				isset($_args['step'])	OR $_args['step'] = 1;
				$_result = '<?php for($for' . $this->_for_stack . ' = ' . $_args['start'] . '; ((' . $_args['start'] . ' < ' . $_args['stop'] . ') ? ($for' . $this->_for_stack . ' < ' . $_args['stop'] . ') : ($for' . $this->_for_stack . ' > ' . $_args['stop'] . ')); $for' . $this->_for_stack . ' += ((' . $_args['start'] . ' < ' . $_args['stop'] . ') ? ' . $_args['step'] . ' : -' . $_args['step'] . ')){ ?>';
				isset($_args['value']) &&$_result .= '<?php $this->assign(\'' . $this->_dequote($_args['value']) . '\', $for' . $this->_for_stack . '); ?>';
				return $_result;
				break;
			case '/for':
				$this->_for_stack--;
				return "<?php } ?>";
				break;
			case 'section':
				array_push($this->_sectionelse_stack, false);
				$this->internal('compile_section_start');
				return compile_section_start($arguments, $this);
				break;
			case 'sectionelse':
				$this->_sectionelse_stack[count($this->_sectionelse_stack)-1] = true;
				return "<?php }}else{ ?>";
				break;
			case '/section':
				if (array_pop($this->_sectionelse_stack)){
					return "<?php } ?>";
				}else{
					return "<?php }} ?>";
				}
				break;
			case 'while':
				$_args = $this->_compile_if($arguments, false, true);
				return '<?php while(' . $_args . '){ ?>';
				break;
			case '/while':
				return "<?php } ?>";
				break;
			case 'if':
				return $this->_compile_if($arguments);
				break;
			case 'else':
				return "<?php }else{ ?>";
				break;
			case 'elseif':
				return $this->_compile_if($arguments, true);
				break;
			case '/if':
				return "<?php }; ?>";
				break;
			case 'assign':
				$_args = $this->_parse_arguments($arguments);
				if (!isset($_args['var'])){
					$this->trigger_error("missing 'var' attribute in 'assign'", E_USER_ERROR, __FILE__, __LINE__);
				}
				if (!isset($_args['value'])){
					$this->trigger_error("missing 'value' attribute in 'assign'", E_USER_ERROR, __FILE__, __LINE__);
				}
				return '<?php $this->assign(\'' . $this->_dequote($_args['var']) . '\', ' . $_args['value'] . '); ?>';
				break;
			case 'switch':
				$_args = $this->_parse_arguments($arguments);
				isset($_args['from']) OR $this->trigger_error("missing 'from' attribute in 'switch'", E_USER_ERROR, __FILE__, __LINE__);
				array_push($this->_switch_stack, array("matched" => false, "var" => $this->_dequote($_args['from'])));
				return;
				break;
			case '/switch':
				array_pop($this->_switch_stack);
				return '<?php break; }; ?>';
				break;
			case 'case':
				if (count($this->_switch_stack) > 0){
					$_result = "<?php ";
					$_args = $this->_parse_arguments($arguments);
					$_index = count($this->_switch_stack) - 1;
					if (!$this->_switch_stack[$_index]["matched"]){
						$_result .= 'switch(' . $this->_switch_stack[$_index]["var"] . '){';
						$this->_switch_stack[$_index]["matched"] = true;
					}else{
						$_result .= 'break; ';
					}
					if (!empty($_args['value'])){
						$_result .= 'case '.$_args['value'].': ';
					}else{
						$_result .= 'default: ';
					}
					return $_result . ' ?>';
				}else{
					$this->trigger_error("unexpected 'case', 'case' can only be in a 'switch'", E_USER_ERROR, __FILE__, __LINE__);
				}
				break;
			default:
				$_result = "";
				if ($this->_compile_compiler_function($function, $arguments, $_result)){
					return $_result;
				}else if ($this->_compile_custom_block($function, $modifiers, $arguments, $_result)){
					return $_result;
				}elseif ($this->_compile_custom_function($function, $modifiers, $arguments, $_result)){
					return $_result;
				}else{
					$this->trigger_error($function." function does not exist", E_USER_ERROR, __FILE__, __LINE__);
				}
				break;
		}
	}

	function _compile_compiler_function($function, $arguments, &$_result){
		if ($function = $this->_plugin_exists($function, "compiler")){
			$_args   = $this->_parse_arguments($arguments);
			$_result = '<?php ' . $function($_args, $this) . ' ?>';
			return true;
		}else{
			return false;
		}
	}

	function _compile_custom_function($function, $modifiers, $arguments, &$_result){
		$this->internal('compile_custom_function');
		return compile_custom_function($function, $modifiers, $arguments, $_result, $this);
	}

	function _compile_custom_block($function, $modifiers, $arguments, &$_result){
		$this->internal('compile_custom_block');
		return compile_custom_block($function, $modifiers, $arguments, $_result, $this);
	}

	function _compile_if($arguments, $elseif = false, $while = false){
		$this->internal('compile_if');
		return compile_if($arguments, $elseif, $while, $this);
	}

	function _parse_is_expr($is_arg, $_arg){
		$this->internal('compile_parse_is_expr');
		return compile_parse_is_expr($is_arg, $_arg, $this);
	}

	function _dequote($string){
		if (($string{0} == "'" || $string{0} == '"') && $string{strlen($string)-1} == $string{0}){
			return substr($string, 1, -1);
		}else{
			return $string;
		}
	}

	function _parse_arguments($arguments){
		$_match		= array();
		$_result	= array();
		$_variables	= array();
		preg_match_all('/(?:' . $this->_qstr_regexp . ' | (?>[^"\'=\s]+))+|[=]/x', $arguments, $_match);
		/*
		   Parse state:
			 0 - expecting attribute name
			 1 - expecting '='
			 2 - expecting attribute value (not '=')
		*/
		$state = 0;
		foreach($_match[0] as $value){
			switch($state){
				case 0:
					// valid attribute name
					if (is_string($value)){
						$a_name = $value;
						$state  = 1;
					}else{
						$this->trigger_error("invalid attribute name: '$token'", E_USER_ERROR, __FILE__, __LINE__);
					}
					break;
				case 1:
					if ($value == '='){
						$state = 2;
					}else{
						$this->trigger_error("expecting '=' after '$last_value'", E_USER_ERROR, __FILE__, __LINE__);
					}
					break;
				case 2:
					$value = $this->_dequote($value);
					if ($value != '='){
						if ($value == 'yes' || $value == 'on' || $value == 'true'){
							$value = true;
						}elseif ($value == 'no' || $value == 'off' || $value == 'false'){
							$value = false;
						}elseif ($value == 'null'){
							$value = null;
						}
						if(preg_match_all('/(?:(' . $this->_var_regexp . '|' . $this->_svar_regexp . ')(' . $this->_mod_regexp . '*))(?:\s+(.*))?/xs', $value, $_variables)){
							$_result[$a_name] = $this->_parse_variables($_variables[1], $_variables[2]);
						}else{
							$_result[$a_name] = '"'.$value.'"';
							if (is_bool($value)){
								$_result[$a_name] = $value ? 'true' : 'false';
							}
						}
						$state = 0;
					}else{
						$this->trigger_error("'=' cannot be an attribute value", E_USER_ERROR, __FILE__, __LINE__);
					}
					break;
			}
			$last_value = $value;
		}
		if($state != 0){
			if($state == 1){
				$this->trigger_error("expecting '=' after attribute name '$last_value'", E_USER_ERROR, __FILE__, __LINE__);
			}else{
				$this->trigger_error("missing attribute value", E_USER_ERROR, __FILE__, __LINE__);
			}
		}
		return $_result;
	}

	function _parse_variables($variables, $modifiers){
		$_result = "";
		foreach($variables as $key => $value){
			$tag_variable = trim($variables[$key]);
			if(!empty($this->default_modifiers) && !preg_match('!(^|\|)templatelite:nodefaults($|\|)!',$modifiers[$key])){
				$_default_mod_string = implode('|',(array)$this->default_modifiers);
				$modifiers[$key] = empty($modifiers[$key]) ? $_default_mod_string : $_default_mod_string . '|' . $modifiers[$key];
			}
			if (empty($modifiers[$key])){
				$_result .= $this->_parse_variable($tag_variable).'.';
			}else{
				$_result .= $this->_parse_modifier($this->_parse_variable($tag_variable), $modifiers[$key]).'.';
			}
		}
		return substr($_result, 0, -1);
	}

	function _parse_variable($variable){
		// replace variable with value
		if ($variable{0} == '$'){
			// replace the variable
			return $this->_compile_variable($variable);
		}elseif ($variable{0} == '"'){
			// expand the quotes to pull any variables out of it
			// fortunately variables inside of a quote aren't fancy, no modifiers, no quotes
			//   just get everything from the $ to the ending space and parse it
			// if the $ is escaped, then we won't expand it
			$_result = "";
//			preg_match_all('/(?:[^\\\]' . $this->_dvar_regexp . ')/', substr($variable, 1, -1), $_expand);  // old match
// 21:57 2008-4-27 math
			preg_match_all('/(?:[^\\\]' . $this->_dvar_regexp . ')/', $variable, $_expand);  // old match
//			preg_match_all('/(?:[^\\\]' . $this->_dvar_regexp . '[^\\\])/', $variable, $_expand);
			$_expand = array_unique($_expand[0]);
			foreach($_expand as $key => $value){
				$_expand[$key] = trim($value);
				if (strpos($_expand[$key], '$') > 0){
					$_expand[$key] = substr($_expand[$key], strpos($_expand[$key], '$'));
				}
			}
			$_result = $variable;
			foreach($_expand as $value){
				$value = trim($value);
//				$_result = str_replace($value, '" . ' . $this->_parse_variable($value) . ' . "', $_result);
//mod 21:56 2008-4-27 math
				$_result = str_replace($value,$this->_parse_variable($value), $this->_dequote($_result));
//				echo $_result;
			}
			$_result = str_replace("`", "", $_result);
			return $_result;
		}elseif ($variable{0} == "'"){
			// return the value just as it is
			return $variable;
		}elseif ($variable{0} == "%"){
			return $this->_parse_section_prop($variable);
		}else{
			// return it as is; i believe that there was a reason before that i did not just return it as is,
			// but i forgot what that reason is ...
			// the reason i return the variable 'as is' right now is so that unquoted literals are allowed
			return $variable;
		}
	}

	function _parse_section_prop($section_prop_expr){
		$parts     = explode('|', $section_prop_expr, 2);
		$var_ref   = $parts[0];
		$modifiers = isset($parts[1]) ? $parts[1] : '';

		preg_match('!%(\w+)\.(\w+)%!', $var_ref, $match);
		$section_name = $match[1];
		$prop_name    = $match[2];
		$output       = "\$this->_sections['$section_name']['$prop_name']";

		$this->_parse_modifier($output, $modifiers);

		return $output;
	}

	function _compile_variable($variable){
		$_result  = "";
		// remove the $
		$variable = substr($variable, 1);

		// get [foo] and .foo and (...) pieces
		preg_match_all('!(?:^\w+)|(?:' . $this->_var_bracket_regexp . ')|\.\$?\w+|\S+!', $variable, $_match);
		$variable = $_match[0];
		$var_name = array_shift($variable);

		if ($var_name == $this->reserved_template_varname){
			if ($variable[0]{0} == '[' || $variable[0]{0} == '.'){
				$find = array("[", "]", ".");
				switch(strtoupper(str_replace($find, "", $variable[0]))){
					case 'SELF':		$_result = "\$_SERVER['PHP_SELF']";break;
					case 'REQUEST_URI':	$_result = "\$_SERVER['REQUEST_URI']";break;
					case 'SERVER_NAME':	$_result = "\$_SERVER['SERVER_NAME']";break;
					case 'SERVER_PORT':	$_result = "\$_SERVER['SERVER_PORT']";break;
					case 'NOW':			$_result = "time()";break;
					case 'SECTION':		$_result = "\$this->_sections";break;
					case 'LDELIM':		$_result = "\$this->left_delimiter";break;
					case 'RDELIM':		$_result = "\$this->right_delimiter";break;
					case 'TPLVERSION':	$_result = "\$this->_version";break;
					case 'VERSION':		$_result = "'iPHP '.iPHP_VER";break;
					case 'TEMPLATE':	$_result = "\$this->_file";break;
					case 'CONST':
						$constant = str_replace($find, "", $_match[0][2]);
						$_result = "constant('$constant')";
						$variable = array();
						break;
					default:
						$_var_name = str_replace($find, "", $variable[0]);
						$_result = "\$this->_iTPL_VARS['$_var_name']";
						break;
				}
				array_shift($variable);
			}else{
				$this->trigger_error('$' . $var_name.implode('', $variable) . ' is an invalid $templatelite reference', E_USER_ERROR, __FILE__, __LINE__);
			}
		}else{
			$_result = "\$this->_vars['$var_name']";
		}

		foreach ($variable as $var){
			if ($var{0} == '['){
				$var = substr($var, 1, -1);
				if (is_numeric($var)){
					$_result .= "[$var]";
				}elseif ($var{0} == '$'){
					$_result .= "[" . $this->_compile_variable($var) . "]";
				}else{
//					$_result .= "['$var']";
					$parts        = explode('.', $var);
					$section      = $parts[0];
					$section_prop = isset($parts[1]) ? $parts[1] : 'index';
					$_result      .= "[\$this->_sections['$section']['$section_prop']]";
				}
			}else if ($var{0} == '.'){
   				if ($var{1} == '$'){
	   				$_result .= "[\$this->_TPL['" . substr($var, 2) . "']]";
				}else{
			   		$_result .= "['" . substr($var, 1) . "']";
				}
			}else if (substr($var,0,2) == '->'){
				if(substr($var,2,2) == '__'){
					$this->trigger_error('call to internal object members is not allowed', E_USER_ERROR, __FILE__, __LINE__);
				}
				else if (substr($var, 2, 1) == '$'){
					$_output .= '->{(($var=$this->_TPL[\''.substr($var,3).'\']) && substr($var,0,2)!=\'__\') ? $_var : $this->trigger_error("cannot access property \\"$var\\"")}';
				}
			}else{
				$this->trigger_error('$' . $var_name.implode('', $variable) . ' is an invalid reference', E_USER_ERROR, __FILE__, __LINE__);
			}
		}
		return $_result;
	}

	function _parse_modifier($variable, $modifiers){
		$_match = array();
		$_mods  = array();		// stores all modifiers
		$_args  = array();		// modifier arguments

		preg_match_all('!\|(@?\w+)((?>:(?:'. $this->_qstr_regexp . '|[^|]+))*)!', '|' . $modifiers, $_match);
		list(, $_mods, $_args) = $_match;
		$count_mods            = count($_mods);
		for ($i = 0, $for_max = $count_mods; $i < $for_max; $i++){
			preg_match_all('!:(' . $this->_qstr_regexp . '|[^:]+)!', $_args[$i], $_match);
			$_arg       = $_match[1];
			$_map_array = 1;
			if ($_mods[$i]{0} == '@'){
				$_mods[$i]  = substr($_mods[$i], 1);
				$_map_array = 0;
			}

			foreach($_arg as $key => $value){
				$_arg[$key] = $this->_parse_variable($value);
			}
			$_plugin_exists = $this->_plugin_exists($_mods[$i], "modifier");
			if ($_plugin_exists||function_exists($_mods[$i])){
				$_arg         = (count($_arg) > 0)?', '.implode(', ', $_arg):'';
				$php_function = $_plugin_exists?"plugin":"PHP";
				$variable     = "\$this->_run_modifier($variable, '$_mods[$i]', '$php_function', $_map_array$_arg)";
			}else{
				$variable = "\$this->trigger_error(\"'" . $_mods[$i] . "' modifier does not exist\", E_USER_NOTICE, __FILE__, __LINE__);";
			}
		}
		return $variable;
	}

	function _plugin_exists($function, $type){
		// check for object functions
		//var_dump($this->_plugins);
		$_plugins_fun = $this->_plugins[$type][$function];
		if (isset($_plugins_fun) && is_array($_plugins_fun) && is_object($_plugins_fun[0]) && method_exists($_plugins_fun[0], $_plugins_fun[1])){
			return '$this->_plugins[\'' . $type . '\'][\'' . $function . '\'][0]->' . $_plugins_fun[1];
		}
		// check for standard functions
		if (isset($_plugins_fun) && function_exists($_plugins_fun)){
			return $_plugins_fun;
		}
		// check for a plugin in the plugin directory
		$_plugins_file_name = $type . '.' . $function . '.php';
		$pluginfile         = $this->_get_plugin_dir($_plugins_file_name).$_plugins_file_name;
		if(@is_file($pluginfile)){
			require_once $pluginfile;
			$_plugins_fun_name = 'tpl_' . $type . '_' . $function;
			if (function_exists($_plugins_fun_name)){
				$this->register_modifier($function,$_plugins_fun_name);
				$this->_require_stack[$_plugins_file_name] = array($type, $function, $_plugins_fun_name);
				return $_plugins_fun_name;
			}
		}
		return false;
	}
}

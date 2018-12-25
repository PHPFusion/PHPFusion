<?php

/**
 @author Nguyen Quoc Bao <quocbao.coder@gmail.com>
 @version 1.3
 @desc A simple object for processing download operation , support section downloading
 Please send me an email if you find some bug or it doesn't work with download manager.
 I've tested it with
 	- Reget
 	- FDM
 	- FlashGet
 	- GetRight
 	- DAP

 @copyright It's free as long as you keep this header .
 @example

 1: File Download
 	$object = new downloader;
 	$object->set_byfile($filename); //Download from a file
 	$object->use_resume = true; //Enable Resume Mode
 	$object->download(); //Download File

 2: Data Download
  $object = new downloader;
 	$object->set_bydata($data); //Download from php data
 	$object->use_resume = true; //Enable Resume Mode
 	$object->set_filename($filename); //Set download name
 	$object->set_mime($mime); //File MIME (Default: application/otect-stream)
 	$object->download(); //Download File

 	3: Manual Download
 	$object = new downloader;
 	$object->set_filename($filename);
	$object->download_ex($size);
	//output your data here , remember to use $this->seek_start and $this->seek_end value :)

**/

class httpdownload {

	var $data = null;
	var $data_len = 0;
	var $data_mod = 0;
	var $data_type = 0;
	var $data_section = 0; //section download
	/**
	 * @var ObjectHandler
	 **/
	var $handler = array('auth' => null);
	var $use_resume = true;
	var $use_autoexit = false;
	var $use_auth = false;
	var $filename = null;
	var $mime = null;
	var $bufsize = 2048;
	var $seek_start = 0;
	var $seek_end = -1;

	/**
	 * Total bandwidth has been used for this download
	 * @var int
	 */
	var $bandwidth = 0;
	/**
	 * Speed limit
	 * @var float
	 */
	var $speed = 0;

	/*-------------------
	| Download Function |
	-------------------*/
	/**
	 * Check authentication and get seek position
	 * @return bool
	 **/
	function initialize() {
		global $HTTP_SERVER_VARS;

		if ($this->use_auth) //use authentication
		{
			if (!$this->_auth()) //no authentication
			{
				header('WWW-Authenticate: Basic realm="Please enter your username and password"');
    		header('HTTP/1.0 401 Unauthorized');
    		header('status: 401 Unauthorized');
    		if ($this->use_autoexit) exit();
				return false;
			}
		}
		if ($this->mime == null) $this->mime = "application/octet-stream"; //default mime

		if (isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE']))
		{

			if (isset($HTTP_SERVER_VARS['HTTP_RANGE'])) $seek_range = substr($HTTP_SERVER_VARS['HTTP_RANGE'] , strlen('bytes='));
			else $seek_range = substr($_SERVER['HTTP_RANGE'] , strlen('bytes='));

			$range = explode('-',$seek_range);

			if ($range[0] > 0)
			{
				$this->seek_start = intval($range[0]);
			}

			if ($range[1] > 0) $this->seek_end = intval($range[1]);
			else $this->seek_end = -1;

			if (!$this->use_resume)
			{
				$this->seek_start = 0;

				//header("HTTP/1.0 404 Bad Request");
				//header("Status: 400 Bad Request");

				//exit;

				//return false;
			}
			else
			{
				$this->data_section = 1;
			}

		}
		else
		{
			$this->seek_start = 0;
			$this->seek_end = -1;
		}

		return true;
	}
	/**
	 * Send download information header
	 **/
	function header($size,$seek_start=null,$seek_end=null) {
		header('Content-type: ' . $this->mime);
		header('Content-Disposition: attachment; filename="' . $this->filename . '"');
		header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T' , $this->data_mod));

		if ($this->data_section && $this->use_resume)
		{
			header("HTTP/1.0 206 Partial Content");
			header("Status: 206 Partial Content");
			header('Accept-Ranges: bytes');
			header("Content-Range: bytes $seek_start-$seek_end/$size");
			header("Content-Length: " . ($seek_end - $seek_start + 1));
		}
		else
		{
			header("Content-Length: $size");
		}
	}

	function download_ex($size)
	{
		if (!$this->initialize()) return false;
		ignore_user_abort(true);
		//Use seek end here
		if ($this->seek_start > ($size - 1)) $this->seek_start = 0;
		if ($this->seek_end <= 0) $this->seek_end = $size - 1;
		$this->header($size,$seek,$this->seek_end);
		$this->data_mod = time();
		return true;
	}

	/**
	 * Start download
	 * @return bool
	 **/
	function download() {
		if (!$this->initialize()) return false;

		$seek = $this->seek_start;
		$speed = $this->speed;
		$bufsize = $this->bufsize;
		$packet = 1;

		//do some clean up
		if (ob_get_length() !== FALSE){
			@ob_end_clean();
		}
		$old_status = ignore_user_abort(true);
		if (!ini_get('safe_mode')) {
			@set_time_limit(0);
		}
		$this->bandwidth = 0;

		$size = $this->data_len;

		if ($this->data_type == 0) //download from a file
		{

			$size = filesize($this->data);
			if ($seek > ($size - 1)) $seek = 0;
			if ($this->filename == null) $this->filename = basename($this->data);

			$res = fopen($this->data,'rb');
			if ($seek) fseek($res , $seek);
			if ($this->seek_end < $seek) $this->seek_end = $size - 1;

			$this->header($size,$seek,$this->seek_end); //always use the last seek
			$size = $this->seek_end - $seek + 1;

			while (!(connection_aborted() || connection_status() == 1) && $size > 0)
			{
				if ($size < $bufsize)
				{
					echo fread($res , $size);
					$this->bandwidth += $size;
				}
				else
				{
					echo fread($res , $bufsize);
					$this->bandwidth += $bufsize;
				}

				$size -= $bufsize;
				flush();

				if ($speed > 0 && ($this->bandwidth > $speed*$packet*1024))
				{
					sleep(1);
					$packet++;
				}
			}
			fclose($res);

		}

		elseif ($this->data_type == 1) //download from a string
		{
			if ($seek > ($size - 1)) $seek = 0;
			if ($this->seek_end < $seek) $this->seek_end = $this->data_len - 1;
			$this->data = substr($this->data , $seek , $this->seek_end - $seek + 1);
			if ($this->filename == null) $this->filename = time();
			$size = strlen($this->data);
			$this->header($this->data_len,$seek,$this->seek_end);
			while (!connection_aborted() && $size > 0) {
				if ($size < $bufsize)
				{
					$this->bandwidth += $size;
				}
				else
				{
					$this->bandwidth += $bufsize;
				}

				echo substr($this->data , 0 , $bufsize);
				$this->data = substr($this->data , $bufsize);

				$size -= $bufsize;
				flush();

				if ($speed > 0 && ($this->bandwidth > $speed*$packet*1024))
				{
					sleep(1);
					$packet++;
				}
			}
		} else if ($this->data_type == 2) {
			//just send a redirect header
			header('location: ' . $this->data);
		}

		if ($this->use_autoexit) exit();

		//restore old status
		ignore_user_abort($old_status);

		if (!ini_get('safe_mode')) {
			@set_time_limit(ini_get("max_execution_time"));
		}

		return true;
	}

	function set_byfile($dir) {
		if (is_readable($dir) && is_file($dir)) {
			$this->data_len = 0;
			$this->data = $dir;
			$this->data_type = 0;
			$this->data_mod = filemtime($dir);
			return true;
		} else return false;
	}

	function set_bydata($data) {
		if ($data == '') return false;
		$this->data = $data;
		$this->data_len = strlen($data);
		$this->data_type = 1;
		$this->data_mod = time();
		return true;
	}

	function set_byurl($data) {
		$this->data = $data;
		$this->data_len = 0;
		$this->data_type = 2;
		return true;
	}

  function set_filename($filename) {
    $this->filename = $filename;
  }

  function set_mime($mime) {
    $this->mime = $mime;
  }

	function set_lastmodtime($time) {
		$time = intval($time);
		if ($time <= 0) $time = time();
		$this->data_mod = $time;
	}

	/**
	 * Check authentication
	 * @return bool
	 **/
	function _auth() {
		if (!isset($_SERVER['PHP_AUTH_USER'])) return false;
		if (isset($this->handler['auth']) && function_exists($this->handler['auth']))
		{
			return $this->handler['auth']('auth' , $_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
		}
		else return true; //you must use a handler
	}

}

?>
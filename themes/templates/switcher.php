<?php
/*.Universal Property Switcher for PHP-Fusion v7.*|
|*.Author: Max "Matonor" Toball..................*|
|*.Last Change: 03/20/07............Version: 1.3.*|
|*.Released under the AGPLv3.....................*/

class Switcher{
	var $args;
	var $buttons;
	var $class;
	var $cookies;
	var $dir;
	var $enabled;
	var $error;
	var $ext;
	var $mode;
	var $name;
	var $post;
	var $props;
	var $selected;
	var $separator;
	
	function Switcher($mode, $dir, $ext, $default, $class="", $separator=" ", $auto=true, $args=""){
		$this->args = $args;
		$this->buttons = array();
		$this->changed = false;
		$this->class = $class;
		$this->cookie = $_COOKIE;
		$this->default = $default;
		$this->dir = THEME.$dir;
		$this->enabled = true;
		$this->error = false;
		$this->ext = $ext;
		$this->mode = $mode;
		$this->name = $dir;
		$this->post = $_POST;
		$this->props = array();
		$this->selected = "";
		$this->separator = $separator;
		
		if($auto){
			$this->props = $this->getProps();
			$this->selected = $this->getSelected();
			if($this->changed){
				$this->writeSelected();
			}
		}
	}
	
	function disable(){
		$this->enabled = false;
		$this->selected = $this->default;
	}
	
	function getProps(){
		$mode = $this->mode;
		if($mode == "select"){
			$dir = $this->dir;
			$ext = $this->ext;
			
			$dirHandle = opendir($dir);
			$props = array();
			if($dirHandle){
				while(false !==($file = readdir($dirHandle))){
					if(!is_dir($dir."/".$file) && preg_match("/[A-z0-9]+\.".$ext."\z/", $file)){
						$props[] = str_replace(".".$ext, "", $file);
					}
				}
			}
		}elseif($mode == "increment"){
			$props = array("less", "reset", "more");
		}
		return $props;
	}
	
	function getSelected(){
		$args = $this->args;
		$cookie = $this->cookie;
		$cookie_val = isset($cookie["theme_".$this->name]) ? $cookie["theme_".$this->name] : "";
		$mode = $this->mode;
		$name = $this->name;
		$post = $this->post;
		$props = $this->props;
		$value = "";
		if($mode == "select"){
			if(isset($post['change_'.$name])){
				foreach($props as $prop){
					if(isset($post[$prop.'_x'])){
						$this->changed = true;
						return $prop;
					}
				}
			}elseif(!empty($cookie_val)){
				if(in_array($cookie_val, $props)){
					return $cookie_val;
				}
			}
			return $this->default;
		}elseif($mode == "increment"){
			if(is_numeric($cookie_val) && !isset($post['reset_x'])){
				$value = $cookie_val;
			}else{
				$value = $this->default;
			}
			if(isset($post['change_'.$name])){
				$this->changed = true;
				if(isset($post['less_x'])){
					if(!isset($args['min']) || $value+$args['step'] >= $args['min']){
						$value = $value-$args['step'];
					}
				}elseif(isset($post['more_x'])){
					if(!isset($args['max']) || $value+$args['step'] <= $args['max']){
						$value = $value+$args['step'];
					}
				}
			}
			return $value;
		}
	}
	
	function writeSelected(){
		if($this->selected == $this->default){
			setcookie("theme_".$this->name, $this->selected, time()-3600*24*14, "/");
		}else{
			setcookie("theme_".$this->name, $this->selected, time()+3600*24*14, "/");
		}
	}
	
	function getButtons(){
		$props = $this->props;
		$dir = $this->dir;
		$ext = $this->ext;
		$class = $this->class;
		$buttons = array();
		
		foreach($props as $prop){
			if($prop != $this->selected){
				$buttons[] = "<input type='image' name='$prop' src='$dir/$prop.$ext' class='$class' alt='$prop' />";
			}
		}
		
		return $buttons;
	}
	
	function makeForm($class=""){
		$separator = $this->separator;
		if($this->enabled){
			$this->buttons = $this->getButtons();
			return "<form id='theme_".$this->name."' class='$class' method='post' action='".FUSION_REQUEST."'>\n<div>\n<input type='hidden' name='change_".$this->name."' value='1'/>\n".implode($separator."\n", $this->buttons)."</div>\n</form>";
		}
	}
	
	function makeHeadTag(){
		return "<link rel='stylesheet' href='".$this->dir."/".$this->selected.".css' type='text/css' />\n";
	}
}

?>
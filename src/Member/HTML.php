<?php
namespace MOFG_form\Member;

/**
 * HTML class
 *
 * @package MOFG_form
 * @author Hiroyuki Suzuki
 * @copyright Copyright (c) 2016 Hiroyuki Suzuki mofg.net
 */
class HTML{
	private $Form = null;

	/**
	 * @param object $Form
	 */
	function __construct($Form){
		$this->Form = $Form;
	}

	/**
	 * @param string $name
	 * @param array $items
	 * @param array $attrs (optional)
	 */
	public function checkbox($name, $items, $attrs = array()){
		if( is_string($items) ) $items = array($items);
		if( !is_array($items) ) return;
		$out = "";
		$data = $this->Form->get_value(str_replace("[]", "", $name));
		if( !is_array($data) ) $data = array($data);
		$attr_txt = $this->get_attr_text($attrs);
		foreach($items as $i){
			if( !is_string($i) ) continue;
			$checked = ( in_array($i, $data, true) ) ? " checked=\"checked\"" : "";
			$brackets = ( count($items) > 1 ) ? "[]" : "";
			$i = htmlspecialchars($i);
			$out .= "<label{$attr_txt}><input type=\"checkbox\" name=\"{$name}{$brackets}\" value=\"{$i}\"{$checked} /> {$i}</label>";
		}
		echo $out;
	}

	/**
	 * @param string $name
	 * @param array $items
	 * @param array $attrs (optional)
	 */
	public function radio($name, $items, $attrs = array()){
		if( is_string($items) ) $items = array($items);
		if( !is_array($items) ) return;
		$out = "";
		$data = $this->Form->get_value($name);
		$attr_txt = $this->get_attr_text($attrs);
		foreach($items as $i){
			if( !is_string($i) ) continue;
			$checked = ( $i === $data ) ? " checked=\"checked\"" : "";
			$i = htmlspecialchars($i);
			$out .= "<label{$attr_txt}><input type=\"radio\" name=\"{$name}\" value=\"{$i}\"{$checked} /> {$i}</label>";
		}
		echo $out;
	}

	/**
	 * @param string $name
	 * @param array $options
	 * @param string $empty (optional)
	 * @param array $attrs (optional)
	 */
	public function select($name, $options, $empty = "", $attrs = array()){
		if( is_string($options) ) $options = array($options);
		if( !is_array($options) ) return;
		$attr_txt = $this->get_attr_text($attrs);
		$out = "<select name=\"".htmlspecialchars($name)."\"{$attr_txt}>";
		if( is_string($empty) && $empty !== "" ){
			$out .= "<option value=\"\">".htmlspecialchars($empty)."</option>";
		}
		foreach($options as $i){
			if( !is_string($i) ) continue;
			$selected = ( $this->Form->get_value($name) === $i ) ? " selected=\"selected\"" : "";
			$i = htmlspecialchars($i);
			$out .= "<option value=\"{$i}\"{$selected}>{$i}</option>";
		}
		$out .= "</select>";
		echo $out;
	}

	/**
	 * @param string $name
	 * @param array $attrs (optional)
	 */
	public function text($name, $attrs = array()){
		$attr_txt = $this->get_attr_text($attrs);
		$data = $this->Form->get_value($name);
		$data = ( is_string($data) ) ? htmlspecialchars($data) : "";
		echo "<input type=\"text\" name=\"{$name}\" value=\"{$data}\"{$attr_txt} />";
	}

	/**
	 * @param string $name
	 * @param array $attrs (optional)
	 */
	public function textarea($name, $attrs = array()){
		$attr_txt = $this->get_attr_text($attrs);
		$data = $this->Form->get_value($name);
		$data = ( is_string($data) ) ? htmlspecialchars($data) : "";
		echo "<textarea name=\"{$name}\"{$attr_txt}>{$data}</textarea>";
	}

	/**
	 * @param array $attrs (optional)
	 * @return string
	 */
	public function get_attr_text($attrs = array()){
		$result = "";
		if( !empty($attrs) && is_array($attrs) ){
			foreach($attrs as $k => $v){
				if( !is_string($k) || !is_string($v) ) continue;
				$v = htmlspecialchars($v);
				$result .= " {$k}=\"{$v}\"";
			}
		}
		return $result;
	}
}

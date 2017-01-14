<?php
namespace MofgForm;

/**
 * MofgForm
 *
 * @package MofgForm
 * @author Hiroyuki Suzuki
 * @copyright Copyright (c) 2016 Hiroyuki Suzuki mofg.net
 * @license http://opensource.org/licenses/MIT The MIT License
 * @version 2.2.0
 */
class MofgForm{
	protected $space = "MofgForm";
	protected $data = [];
	protected $values = [];
	protected $errors = [];
	protected $flags = [];

	public $Html = null;
	public $Mail = null;

	const FMT_NONE = 0;
	const FMT_INT = 1;
	const FMT_ALP = 2;
	const FMT_NUM = 3;
	const FMT_ALPNUM = 4;
	const FMT_HIRA = 5;
	const FMT_KATA = 6;
	const FMT_TEL = 7;
	const FMT_EMAIL = 8;
	const FMT_URL = 9;

	const FLT_TO_ZENKAKU_KANA = 1;
	const FLT_TO_HANKAKU_ALPNUM = 2;
	const FLT_TO_UPPER_CASE = 3;
	const FLT_TO_LOWER_CASE = 4;
	const FLT_EOL_TO_N = 5;
	const FLT_EOL_TO_SPACE = 6;
	const FLT_TRIM = 7;
	const FLT_RTRIM = 8;
	const FLT_LTRIM = 9;

	const E_DEFAULT = "_-1";
	const E_NONE = "_0";
	const E_REQUIRED = "_1";
	const E_MINLEN = "_2";
	const E_MAXLEN = "_3";
	const E_PATTERN = "_4";
	const E_FMT_INT = "_5";
	const E_FMT_ALP = "_6";
	const E_FMT_NUM = "_7";
	const E_FMT_ALPNUM = "_8";
	const E_FMT_HIRA = "_9";
	const E_FMT_KATA = "_10";
	const E_FMT_TEL = "_11";
	const E_FMT_EMAIL = "_12";
	const E_FMT_URL = "_13";

	const CTL_ENTER = "enter";
	const CTL_BACK = "back";
	const CTL_RESET = "reset";

	/**
	 * ##### $items
	 * - "in_page": (integer) (optional)
	 * - "title": (string) (optional)
	 * - "required": (boolean) (optional)
	 * - "rule": (array) (optional)
	 * - "add": (array) (optional)
	 * - "filter": (integer|array) (optional)
	 *
	 * ##### $items["rule"]
 	 * - "format": (integer) (optional)
 	 * - "minlen": (integer) (optional)
 	 * - "maxlen": (integer) (optional)
 	 * - "pattern": (string) (optional)
	 *
	 * ##### $items["add"]
	 * - "before": (string) (optional)
	 * - "after": (string) (optional)
	 *
	 * @param string $session_space (optional)
	 * @param array $items (optional)
	 * @param array $POST (optional)
	 */
	function __construct($session_space = "", $items = [], $POST = []){
		if( is_string($session_space) && $session_space !== "" ) $this->space = $session_space;

		$this->Html = new Html($this);
		$this->Mail = new Mail();

		$this->init();
		$this->pull_data();

		if( empty($_SESSION[$this->space]["items"]) ){
			while( list($k, $v) = each($items) ) $this->register_item($k, $v);
		}

		$this->import_posted_data($POST);

		if( $this->flags["reset"] ){
			$this->end_clean();
			$this->init();
		}
	}

	/**
	 * @access protected
	 */
	protected function init(){
		$this->data["page"] = 1;
		$this->data["error_format"] = "<p style=\"color:#f00;\">%s</p>";
		$this->data["error_message"] = [self::E_DEFAULT => "Invalid value"];
		$this->data["array_glue"] = ", ";
		$this->data["name_for_enter"] = "_enter";
		$this->data["name_for_back"] = "_back";
		$this->data["name_for_reset"] = "_reset";
		$this->flags["enter"] = false;
		$this->flags["back"] = false;
		$this->flags["reset"] = false;
		$this->flags["settled"] = false;
		$this->flags["updated_data"] = false;
	}

	/**
	 * @access protected
	 */
	protected function pull_data(){
		if( isset($_SESSION[$this->space]["data"]) ) $this->data = $_SESSION[$this->space]["data"];
	}

	/**
	 * @access protected
	 */
	protected function push_data(){
		$_SESSION[$this->space]["data"] = $this->data;
	}

	/**
	 * @access protected
	 * @param string $id
	 * @param array $options
	 */
	protected function register_item($id, $options){
		$_SESSION[$this->space]["items"][$id]["in_page"] = ( isset($options["in_page"]) && is_numeric($options["in_page"]) ) ? intval($options["in_page"]) : 1;
		$_SESSION[$this->space]["items"][$id]["title"] = ( isset($options["title"]) && is_string($options["title"]) ) ? $options["title"] : "";
		$_SESSION[$this->space]["items"][$id]["required"] = ( !empty($options["required"]) ) ? true : false;
		$_SESSION[$this->space]["items"][$id]["rule"] = ( isset($options["rule"]) && is_array($options["rule"]) ) ? $options["rule"] : [];
		$_SESSION[$this->space]["items"][$id]["add"] = ( isset($options["add"]) && is_array($options["add"]) ) ? $options["add"] : [];
		$_SESSION[$this->space]["items"][$id]["filter"] = ( isset($options["filter"]) && ( is_numeric($options["filter"]) || is_array($options["filter"]) ) ) ? $options["filter"] : null;
	}

	/**
	 * @access protected
	 * @param array $POST (optional)
	 * @return boolean
	 */
	protected function import_posted_data($POST = []){
		if( empty($POST) || empty($_SESSION[$this->space]["items"]) ) return false;
		if( !empty($POST[$this->data["name_for_enter"]]) || !empty($POST[$this->data["name_for_enter"]."_x"]) ) $this->flags["enter"] = true;
		if( !empty($POST[$this->data["name_for_back"]]) || !empty($POST[$this->data["name_for_back"]."_x"]) ) $this->flags["back"] = true;
		if( !empty($POST[$this->data["name_for_reset"]]) || !empty($POST[$this->data["name_for_reset"]."_x"]) ) $this->flags["reset"] = true;
		if( !$this->flags["enter"] ) return true;

		foreach($_SESSION[$this->space]["items"] as $k => $v){
			if( $v["in_page"] !== $this->data["page"] || !isset($POST[$k]) || (!is_string($POST[$k]) && !is_array($POST[$k])) ) continue;
			$value = $POST[$k];
			if( isset($v["filter"]) ) $value = $this->apply_filter($value, $v["filter"]);
			$this->values[$k] = $value;
		}
		return true;
	}

	public function end_clean(){
		$_SESSION[$this->space] = [];
		$this->values = [];
		$this->errors = [];
	}

	/**
	 * @return integer
	 */
	public function settle(){
		if( $this->flags["settled"] || $this->flags["reset"] ) return $this->data["page"];
		$this->flags["settled"] = true;

		if( $this->flags["enter"] && $this->count_errors() === 0 ){
			$this->push_values();
			$this->set_page($this->data["page"] + 1);
		}else if( $this->flags["back"] ){
			$this->set_page($this->data["page"] - 1);
		}

		if( $this->flags["updated_data"] ) $this->push_data();
		return $this->data["page"];
	}

	/**
	 * @access protected
	 * @return integer
	 */
	protected function count_errors(){
		if( empty($_SESSION[$this->space]["items"]) ) return false;
		$result = 0;
		foreach($_SESSION[$this->space]["items"] as $k => $v){
			if( $v["in_page"] !== $this->data["page"] ) continue;
			if( !isset($this->errors[$k]) ){
				if( ($e = $this->validate($k)) === self::E_NONE ) continue;
				$this->errors[$k] = ( isset($this->data["error_message"][$e]) ) ? $this->data["error_message"][$e] : $this->data["error_message"][self::E_DEFAULT];
			}
			$result++;
		}
		return $result;
	}

	/**
	 * @param string $id
	 * @return string
	 */
	public function validate($id){
		if( !isset($_SESSION[$this->space]["items"][$id]) ) return false;
		$i = $_SESSION[$this->space]["items"][$id];

		if( $i["in_page"] !== $this->data["page"] ) return self::E_NONE;

		if( !isset($this->values[$id]) || $this->values[$id] === "" || $this->values[$id] === [] ){
			if( $i["required"] ) return self::E_REQUIRED;
			return self::E_NONE;
		}

		if( is_array($this->values[$id]) ) return self::E_NONE;

		$v = strval($this->values[$id]);
		if( isset($i["rule"]["format"]) ){
			switch($i["rule"]["format"]){
				case self::FMT_INT:
					if( !preg_match('/\A-?[1-9][0-9]*\z/', $v) && $v !== "0" ) return self::E_FMT_INT;
					break;
				case self::FMT_ALP:
					if( !preg_match('/\A[a-zA-Z]+\z/', $v) ) return self::E_FMT_ALP;
					break;
				case self::FMT_NUM:
					if( !preg_match('/\A-?[0-9]+\z/', $v) ) return self::E_FMT_NUM;
					break;
				case self::FMT_ALPNUM:
					if( !preg_match('/\A[a-zA-Z0-9]+\z/', $v) ) return self::E_FMT_ALPNUM;
					break;
				case self::FMT_HIRA:
					if( !preg_match('/\A[ぁ-んー　 ]+\z/u', $v) ) return self::E_FMT_HIRA;
					break;
				case self::FMT_KATA:
					if( !preg_match('/\A[ァ-ヶー　 ]+\z/u', $v) ) return self::E_FMT_KATA;
					break;
				case self::FMT_TEL:
					if( !preg_match('/\A[0-9]+(-[0-9]+)*\z/', $v) ) return self::E_FMT_TEL;
					break;
				case self::FMT_EMAIL:
					if( !filter_var($v, FILTER_VALIDATE_EMAIL) ) return self::E_FMT_EMAIL;
					break;
				case self::FMT_URL:
					if( !filter_var($v, FILTER_VALIDATE_URL) ) return self::E_FMT_URL;
					break;
			}
		}
		if( isset($i["rule"]["minlen"]) && mb_strlen($v) < $i["rule"]["minlen"] ) return self::E_MINLEN;
		if( isset($i["rule"]["maxlen"]) && mb_strlen($v) > $i["rule"]["maxlen"] ) return self::E_MAXLEN;
		if( isset($i["rule"]["pattern"]) && !preg_match($i["rule"]["pattern"], $v) ) return self::E_PATTERN;
		return self::E_NONE;
	}

	/**
	 * @access protected
	 * @return boolean
	 */
	protected function push_values(){
		if( empty($_SESSION[$this->space]["items"]) ) return false;
		foreach($_SESSION[$this->space]["items"] as $k => $v){
			if( $v["in_page"] !== $this->data["page"] ) continue;
			$_SESSION[$this->space]["items"][$k]["value"] = ( isset($this->values[$k]) ) ? $this->values[$k] : null;
		}
		return true;
	}

	/**
	 * @param string $id
	 */
	public function remove_item($id){
		if( array_key_exists($id, $this->values) ) unset($this->values[$id]);
		if( array_key_exists($id, $_SESSION[$this->space]["items"]) ) unset($_SESSION[$this->space]["items"][$id]);
		if( array_key_exists($id, $this->errors) ) unset($this->errors[$id]);
	}

	/**
	 * @param string $id
	 * @param string $title
	 * @param array $items
	 * @param string $separator (optional)
	 */
	public function register_group($id, $title, $items, $separator = ""){
		if( is_string($items) || is_numeric($items) ) $items = [$items];
		if( !is_array($items) ) $items = [];
		$_SESSION[$this->space]["groups"][$id] = [
			"title" => $title,
			"items" => $items,
			"separator" => $separator
		];
	}

	/**
	 * @param string $id
	 */
	public function remove_group($id){
		if( isset($_SESSION[$this->space]["groups"][$id]) ) unset($_SESSION[$this->space]["groups"][$id]);
	}

	/**
	 * @return integer
	 */
	public function get_page(){
		return $this->data["page"];
	}

	/**
	 * @param integer $page
	 */
	public function set_page($page){
		$page = intval($page);
		$this->data["page"] = ( $page > 0 ) ? $page : 1;
		$this->flags["updated_data"] = true;
	}

	/**
	 * @param string $format
	 */
	public function set_error_format($format){
		$this->data["error_format"] = $format;
		$this->flags["updated_data"] = true;
	}

	/**
	 * @param string $glue
	 */
	public function set_array_glue($glue){
		$this->data["array_glue"] = $glue;
		$this->flags["updated_data"] = true;
	}

	/**
	 * @param string $control
	 * @return string
	 */
	public function get_name_for($control){
		if( !in_array($control, [self::CTL_ENTER, self::CTL_BACK, self::CTL_RESET], true) ) return "";
		return $this->data["name_for_".$control];
	}

	/**
	 * @param string $control
	 * @param string $name
	 * @return boolean
	 */
	public function set_name_for($control, $name){
		if( !in_array($control, [self::CTL_ENTER, self::CTL_BACK, self::CTL_RESET], true) ) return false;
		$this->data["name_for_".$control] = $name;
		$this->flags["updated_data"] = true;
		return true;
	}

	/**
	 * @param array $message
	 * @return boolean
	 */
	public function set_error_message($message){
		if( !is_array($message) ) return false;
		$this->data["error_message"] = array_merge($this->data["error_message"], $message);
		$this->flags["updated_data"] = true;
		return true;
	}

	/**
	 * @param string $id
	 * @param mixed $value (string|array)
	 * @return boolean
	 */
	public function set_value($id, $value){
		if( !is_string($value) && !is_array($value) ) return false;
		$this->values[$id] = $value;
		return true;
	}

	/**
	 * @param string $id
	 * @return mixed (string|array)
	 */
	public function get_value($id){
		if( isset($this->values[$id]) ){
			$value = $this->values[$id];
		}else if( isset($_SESSION[$this->space]["items"][$id]["value"]) ){
			$value = $_SESSION[$this->space]["items"][$id]["value"];
		}
		return ( isset($value) ) ? $value : false;
	}

	/**
	 * @param string $id
	 * @param string $message
	 * @return boolean
	 */
	public function set_error($id, $message){
		if( !is_string($message) ) return false;
		$this->errors[$id] = $message;
		return true;
	}

	/**
	 * @param string $id
	 * @return boolean
	 */
	public function has_error($id){
		return ( $this->validate($id) === self::E_NONE && !isset($this->errors[$id]) ) ? false : true;
	}

	/**
	 * @param string $id
	 * @param boolean $add (optional)
	 */
	public function v($id, $add = true){
		if( !$this->flags["settled"] || !is_string($id) ) return;
		$value = $this->get_value($id);
		if( is_array($value) ) $value = implode($this->data["array_glue"], $value);
		if( $value === false ) $value = "";
		if( $add && $value !== "" ){
			if( isset($_SESSION[$this->space]["items"][$id]["add"]["before"]) ) $value = $_SESSION[$this->space]["items"][$id]["add"]["before"].$value;
			if( isset($_SESSION[$this->space]["items"][$id]["add"]["after"]) ) $value = $value.$_SESSION[$this->space]["items"][$id]["add"]["after"];
		}
		echo nl2br(htmlspecialchars($value));
	}

	/**
	 * @param string $id
	 */
	public function e($id){
		if( !$this->flags["settled"] || !is_string($id) || !isset($this->errors[$id]) ) return;
		echo sprintf($this->data["error_format"], htmlspecialchars($this->errors[$id]));
	}

	/**
	 * @param string $title_open (optional)
	 * @param string $title_close (optional)
	 * @param string $separator (optional)
	 * @return string
	 */
	public function construct_text($title_open = "[", $title_close = "]\n", $separator = "\n\n"){
		$items = ( !empty($_SESSION[$this->space]["items"]) ) ? $_SESSION[$this->space]["items"] : [];
		$groups = ( !empty($_SESSION[$this->space]["groups"]) ) ? $_SESSION[$this->space]["groups"] : [];

		$result = "";
		while( list($k, $v) = each($items) ){
			if( empty($v) ) continue;

			reset($groups);
			while( list($gk, $gv) = each($groups) ){
				if( !in_array($k, $gv["items"]) ) continue;
				$result .= $title_open.$gv["title"].$title_close;
				for($i = 0; $i < count($gv["items"]); $i++){
					$item = $gv["items"][$i];
					if( isset($items[$item]["value"]) && $items[$item]["value"] !== "" && $items[$item]["value"] !== [] ){
						if( is_array($items[$item]["value"]) ) $items[$item]["value"] = implode($this->data["array_glue"], $items[$item]["value"]);
						if( isset($items[$item]["add"]["before"]) ) $result .= $items[$item]["add"]["before"];
						$result .= $items[$item]["value"];
						if( isset($items[$item]["add"]["after"]) ) $result .= $items[$item]["add"]["after"];
					}
					$result .= $gv["separator"];
					if( isset($items[$item]) ) $items[$item] = [];
				}
				unset($groups[$gk]);
				if( ($seplen = mb_strlen($gv["separator"])) > 0 ) $result = mb_substr($result, 0, -$seplen);
				$result .= $separator;
				continue 2;
			}

			$result .= $title_open.$v["title"].$title_close;
			if( isset($v["value"]) && $v["value"] !== "" && $v["value"] !== [] ){
				if( is_array($v["value"]) ) $v["value"] = implode($this->data["array_glue"], $v["value"]);
				if( isset($v["add"]["before"]) ) $result .= $v["add"]["before"];
				$result .= $v["value"];
				if( isset($v["add"]["after"]) ) $result .= $v["add"]["after"];
			}
			$result .= $separator;
		}

		if( ($seplen = mb_strlen($separator)) > 0 ) $result = mb_substr($result, 0, -$seplen);
		return $result;
	}

	/**
	 * @param string $data
	 * @param mixed $filter (integer|array)
	 * @return string
	 */
	public function apply_filter($data, $filter){
		if( !is_string($data) ) return $data;
		$result = $data;
		if( is_array($filter) ){
			foreach($filter as $i) $result = $this->apply_filter($result, $i);
		}else{
			$filter = intval($filter);
			switch($filter){
				case self::FLT_TO_ZENKAKU_KANA:
					$result = mb_convert_kana($result, "KV");
					break;
				case self::FLT_TO_HANKAKU_ALPNUM:
					$result = mb_convert_kana($result, "a");
					break;
				case self::FLT_TO_UPPER_CASE:
					$result = strtoupper($result);
					break;
				case self::FLT_TO_LOWER_CASE:
					$result = strtolower($result);
					break;
				case self::FLT_EOL_TO_N:
					$result = str_replace(["\r\n", "\r"], "\n", $result);
					break;
				case self::FLT_EOL_TO_SPACE:
					$result = str_replace(["\r\n", "\r", "\n"], " ", $result);
					break;
				case self::FLT_TRIM:
					$result = trim($result);
					break;
				case self::FLT_RTRIM:
					$result = rtrim($result);
					break;
				case self::FLT_LTRIM:
					$result = ltrim($result);
					break;
			}
		}
		return $result;
	}
}

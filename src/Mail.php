<?php

namespace MofgForm;

/**
 * Mail class
 *
 * @package MofgForm
 * @author Hiroyuki Suzuki
 * @copyright Copyright (c) 2021 Hiroyuki Suzuki mofg.net
 */
class Mail
{
    use MultiMethodNameTrait;

    /**
     * @var string
     */
    private $to = "";

    /**
     * @var string
     */
    private $subject = "";

    /**
     * @var string
     */
    private $body = "";

    /**
     * @var string
     */
    private $header = "";

    public const FORMAT_ADDRESS = '#\A[a-zA-Z0-9.!\#$%&\'*+/=?^_`{|}~-]+@[a-zA-Z0-9_\-]+(\.[a-zA-Z0-9_\-]+)+\z#';
    public const FORMAT_HEADER = '#\A[a-zA-Z0-9_\-]+ *: *[^:]+\z#';

    /**
     * @param string $to (optional)
     * @param string $subject (optional)
     * @param string $body (optional)
     * @param string $header (optional)
     * @return boolean
     */
    public function send($to = null, $subject = null, $body = null, $header = null)
    {
        if(!isset($to)) {
            $to = $this->to;
            $subject = $this->subject;
            $body = $this->body;
            $header = $this->header;
        } elseif(!isset($subject) && !isset($body)) {
            return false;
        }

        $result = false;
        $to = $this->construct_data($to, ",", self::FORMAT_ADDRESS);
        if(!empty($to)) {
            $header = $this->construct_data($header, "\n", self::FORMAT_HEADER);
            if(empty($header)) {
                $result = mb_send_mail($to, $subject, $body);
            } else {
                $header = $this->group_header($header);
                $result = mb_send_mail($to, $subject, $body, $header);
            }
        }
        return $result;
    }

    /**
     * @param string|array $data
     * @param string $separator (optional)
     * @param string $pattern (optional)
     * @return string
     */
    public function construct_data($data, $separator = ",", $pattern = '/.*/')
    {
        if(is_string($data)) {
            $data = trim($data, " \t\n\r\0\x0B");
            $data = explode($separator, $data);
        }
        if(!is_array($data)) {
            return false;
        }

        $count = count($data);
        for($i = 0; $i < $count; $i++) {
            if(is_string($data[$i])) {
                $data[$i] = trim($data[$i], " \t\n\r\0\x0B");
                if(!preg_match($pattern, $data[$i])) {
                    unset($data[$i]);
                }
            } else {
                unset($data[$i]);
            }
        }
        return implode($separator, $data);
    }

    /**
     * @param string $header
     * @return string
     */
    public function group_header($header)
    {
        if(!is_string($header)) {
            return false;
        }
        $result = "";
        $groups = [];

        $items = explode("\n", $header);
        foreach($items as $i) {
            list($k, $v) = explode(":", $i);
            $k = trim($k, " \t\n\r\0\x0B");
            $v = trim($v, " \t\n\r\0\x0B");
            if(!isset($groups[$k])) {
                $groups[$k] = [];
            }
            $groups[$k][] = $v;
        }

        foreach($groups as $k => $v) {
            if($result !== "") {
                $result .= "\n";
            }
            $result .= $k.": ".implode(", ", $v);
        }
        return $result;
    }

    /**
     * @param string|array $to
     */
    public function add_to($to)
    {
        $add = $this->construct_data($to, ",", self::FORMAT_ADDRESS);
        if($add === false) {
            return;
        }
        if(!empty($this->to) && $add !== "") {
            $this->to .= ",";
        }
        $this->to .= $add;
    }

    /**
     * @param string $subject
     */
    public function set_subject($subject)
    {
        if(!is_string($subject)) {
            return;
        }
        $this->subject = $subject;
    }

    /**
     * @param string $body
     */
    public function set_body($body)
    {
        if(!is_string($body)) {
            return;
        }
        $this->body = $body;
    }

    /**
     * @param string|array $header
     */
    public function add_header($header)
    {
        $add = $this->construct_data($header, "\n", self::FORMAT_HEADER);
        if($add === false) {
            return;
        }
        if(!empty($this->header) && $add !== "") {
            $this->header .= "\n";
        }
        $this->header .= $add;
    }

    /**
     * @param boolean $array (optional)
     * @return mixed
     */
    public function get_to($array = false)
    {
        return ($array) ? explode(",", $this->to) : $this->to;
    }

    /**
     * @return string
     */
    public function get_subject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function get_body()
    {
        return $this->body;
    }

    /**
     * @param boolean $array (optional)
     * @return mixed
     */
    public function get_header($array = false)
    {
        return ($array) ? explode("\n", $this->header) : $this->header;
    }
}

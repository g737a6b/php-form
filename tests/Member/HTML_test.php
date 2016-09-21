<?php
require(__DIR__."/../../autoload.php");

use PHPUnit\Framework\TestCase;
use MOFG_form\Member\HTML;

class HTML_test extends TestCase{
	protected $Form;

	protected function setUp(){
		$this->Form = $this->createMock(MOFG_form\MOFG_form::class);
		$this->Form->method("get_value")->willReturn("foo");
	}

	/**
	 * @dataProvider checkbox_provider
	 */
	public function test_checkbox($expected, $name, $items, $attrs){
		$HTML = new MOFG_form\Member\HTML($this->Form);
		$this->expectOutputString($expected);
		$HTML->checkbox($name, $items, $attrs);
	}

	public function checkbox_provider(){
		return array(
			array(
				"",
				"test",
				array(),
				array()
			),
			array(
				"<label><input type=\"checkbox\" name=\"test\" value=\"bar\" /> bar</label>",
				"test",
				array("bar"),
				array()
			),
			array(
				"<label><input type=\"checkbox\" name=\"test\" value=\"foo\" checked=\"checked\" /> foo</label>",
				"test",
				array("foo"),
				array()
			),
			array(
				"<label><input type=\"checkbox\" name=\"test[]\" value=\"bar\" /> bar</label><label><input type=\"checkbox\" name=\"test[]\" value=\"baz\" /> baz</label>",
				"test",
				array("bar", "baz"),
				array()
			),
			array(
				"<label><input type=\"checkbox\" name=\"test[]\" value=\"foo\" checked=\"checked\" /> foo</label><label><input type=\"checkbox\" name=\"test[]\" value=\"bar\" /> bar</label>",
				"test",
				array("foo", "bar"),
				array()
			),
			array(
				"<label class=\"colored\"><input type=\"checkbox\" name=\"test\" value=\"bar\" /> bar</label>",
				"test",
				array("bar"),
				array("class" => "colored")
			)
		);
	}

	/**
	 * @dataProvider radio_provider
	 */
	public function test_radio($expected, $name, $items, $attrs){
		$HTML = new MOFG_form\Member\HTML($this->Form);
		$this->expectOutputString($expected);
		$HTML->radio($name, $items, $attrs);
	}

	public function radio_provider(){
		return array(
			array(
				"",
				"test",
				array(),
				array()
			),
			array(
				"<label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
				"test",
				array("bar"),
				array()
			),
			array(
				"<label><input type=\"radio\" name=\"test\" value=\"foo\" checked=\"checked\" /> foo</label>",
				"test",
				array("foo"),
				array()
			),
			array(
				"<label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label><label><input type=\"radio\" name=\"test\" value=\"baz\" /> baz</label>",
				"test",
				array("bar", "baz"),
				array()
			),
			array(
				"<label><input type=\"radio\" name=\"test\" value=\"foo\" checked=\"checked\" /> foo</label><label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
				"test",
				array("foo", "bar"),
				array()
			),
			array(
				"<label class=\"colored\"><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
				"test",
				array("bar"),
				array("class" => "colored")
			)
		);
	}

	/**
	 * @dataProvider select_provider
	 */
	public function test_select($expected, $name, $options, $empty, $attrs){
		$HTML = new MOFG_form\Member\HTML($this->Form);
		$this->expectOutputString($expected);
		$HTML->select($name, $options, $empty, $attrs);
	}

	public function select_provider(){
		return array(
			array(
				"<select name=\"test\"></select>",
				"test",
				array(),
				"",
				array()
			),
			array(
				"<select name=\"test\"><option value=\"bar\">bar</option></select>",
				"test",
				array("bar"),
				"",
				array()
			),
			array(
				"<select name=\"test\"><option value=\"foo\" selected=\"selected\">foo</option></select>",
				"test",
				array("foo"),
				"",
				array()
			),
			array(
				"<select name=\"test\"><option value=\"bar\">bar</option><option value=\"baz\">baz</option></select>",
				"test",
				array("bar", "baz"),
				"",
				array()
			),
			array(
				"<select name=\"test\"><option value=\"foo\" selected=\"selected\">foo</option><option value=\"bar\">bar</option></select>",
				"test",
				array("foo", "bar"),
				"",
				array()
			),
			array(
				"<select name=\"test\"><option value=\"\">----</option></select>",
				"test",
				array(),
				"----",
				array()
			),
			array(
				"<select name=\"test\"><option value=\"\">----</option><option value=\"bar\">bar</option></select>",
				"test",
				array("bar"),
				"----",
				array()
			),
			array(
				"<select name=\"test\" class=\"colored\"></select>",
				"test",
				array(),
				"",
				array("class" => "colored")
			)
		);
	}

	/**
	 * @dataProvider text_provider
	 */
	public function test_text($expected, $name, $attrs){
		$HTML = new MOFG_form\Member\HTML($this->Form);
		$this->expectOutputString($expected);
		$HTML->text($name, $attrs);
	}

	public function text_provider(){
		return array(
			array(
				"<input type=\"text\" name=\"test\" value=\"foo\" />",
				"test",
				array()
			),
			array(
				"<input type=\"text\" name=\"test\" value=\"foo\" class=\"colored\" />",
				"test",
				array("class" => "colored")
			)
		);
	}

	/**
	 * @dataProvider password_provider
	 */
	public function test_password($expected, $name, $attrs){
		$HTML = new MOFG_form\Member\HTML($this->Form);
		$this->expectOutputString($expected);
		$HTML->password($name, $attrs);
	}

	public function password_provider(){
		return array(
			array(
				"<input type=\"password\" name=\"test\" value=\"foo\" />",
				"test",
				array()
			),
			array(
				"<input type=\"password\" name=\"test\" value=\"foo\" class=\"colored\" />",
				"test",
				array("class" => "colored")
			)
		);
	}

	/**
	 * @dataProvider textarea_provider
	 */
	public function test_textarea($expected, $name, $attrs){
		$HTML = new MOFG_form\Member\HTML($this->Form);
		$this->expectOutputString($expected);
		$HTML->textarea($name, $attrs);
	}

	public function textarea_provider(){
		return array(
			array(
				"<textarea name=\"test\">foo</textarea>",
				"test",
				array()
			),
			array(
				"<textarea name=\"test\" class=\"colored\">foo</textarea>",
				"test",
				array("class" => "colored")
			)
		);
	}

	public function test_get_attr_text(){
		$HTML = new MOFG_form\Member\HTML($this->Form);
		$this->assertSame("", $HTML->get_attr_text(array()));
		$this->assertSame(" class=\"colored\"", $HTML->get_attr_text(array("class" => "colored")));
		$this->assertSame(" class=\"colored\" data-id=\"100\"", $HTML->get_attr_text(array("class" => "colored", "data-id" => "100")));
	}
}

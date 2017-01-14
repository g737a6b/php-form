<?php
require(__DIR__."/../autoload.php");

use PHPUnit\Framework\TestCase;
use MofgForm\Html;

class HtmlTest extends TestCase{
	protected $Form;

	protected function setUp(){
		$this->Form = $this->createMock(MofgForm\MofgForm::class);
		$this->Form->method("get_value")->willReturn("foo");
	}

	/**
	 * @dataProvider checkbox_provider
	 */
	public function test_checkbox($expected, $name, $items, $attrs){
		$Html = new MofgForm\Html($this->Form);
		$this->expectOutputString($expected);
		$Html->checkbox($name, $items, $attrs);
	}

	public function checkbox_provider(){
		return array(
			array(
				"",
				"test",
				[],
				[]
			),
			array(
				"<label><input type=\"checkbox\" name=\"test\" value=\"bar\" /> bar</label>",
				"test",
				["bar"],
				[]
			),
			array(
				"<label><input type=\"checkbox\" name=\"test\" value=\"foo\" checked=\"checked\" /> foo</label>",
				"test",
				["foo"],
				[]
			),
			array(
				"<label><input type=\"checkbox\" name=\"test[]\" value=\"bar\" /> bar</label><label><input type=\"checkbox\" name=\"test[]\" value=\"baz\" /> baz</label>",
				"test",
				["bar", "baz"],
				[]
			),
			array(
				"<label><input type=\"checkbox\" name=\"test[]\" value=\"foo\" checked=\"checked\" /> foo</label><label><input type=\"checkbox\" name=\"test[]\" value=\"bar\" /> bar</label>",
				"test",
				["foo", "bar"],
				[]
			),
			array(
				"<label class=\"colored\"><input type=\"checkbox\" name=\"test\" value=\"bar\" /> bar</label>",
				"test",
				["bar"],
				["class" => "colored"]
			)
		);
	}

	/**
	 * @dataProvider radio_provider
	 */
	public function test_radio($expected, $name, $items, $attrs){
		$Html = new MofgForm\Html($this->Form);
		$this->expectOutputString($expected);
		$Html->radio($name, $items, $attrs);
	}

	public function radio_provider(){
		return array(
			array(
				"",
				"test",
				[],
				[]
			),
			array(
				"<label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
				"test",
				["bar"],
				[]
			),
			array(
				"<label><input type=\"radio\" name=\"test\" value=\"foo\" checked=\"checked\" /> foo</label>",
				"test",
				["foo"],
				[]
			),
			array(
				"<label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label><label><input type=\"radio\" name=\"test\" value=\"baz\" /> baz</label>",
				"test",
				["bar", "baz"],
				[]
			),
			array(
				"<label><input type=\"radio\" name=\"test\" value=\"foo\" checked=\"checked\" /> foo</label><label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
				"test",
				["foo", "bar"],
				[]
			),
			array(
				"<label class=\"colored\"><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
				"test",
				["bar"],
				["class" => "colored"]
			)
		);
	}

	/**
	 * @dataProvider select_provider
	 */
	public function test_select($expected, $name, $options, $empty, $attrs){
		$Html = new MofgForm\Html($this->Form);
		$this->expectOutputString($expected);
		$Html->select($name, $options, $empty, $attrs);
	}

	public function select_provider(){
		return array(
			array(
				"<select name=\"test\"></select>",
				"test",
				[],
				"",
				[]
			),
			array(
				"<select name=\"test\"><option value=\"bar\">bar</option></select>",
				"test",
				["bar"],
				"",
				[]
			),
			array(
				"<select name=\"test\"><option value=\"foo\" selected=\"selected\">foo</option></select>",
				"test",
				["foo"],
				"",
				[]
			),
			array(
				"<select name=\"test\"><option value=\"bar\">bar</option><option value=\"baz\">baz</option></select>",
				"test",
				["bar", "baz"],
				"",
				[]
			),
			array(
				"<select name=\"test\"><option value=\"foo\" selected=\"selected\">foo</option><option value=\"bar\">bar</option></select>",
				"test",
				["foo", "bar"],
				"",
				[]
			),
			array(
				"<select name=\"test\"><option value=\"\">----</option></select>",
				"test",
				[],
				"----",
				[]
			),
			array(
				"<select name=\"test\"><option value=\"\">----</option><option value=\"bar\">bar</option></select>",
				"test",
				["bar"],
				"----",
				[]
			),
			array(
				"<select name=\"test\" class=\"colored\"></select>",
				"test",
				[],
				"",
				["class" => "colored"]
			)
		);
	}

	/**
	 * @dataProvider text_provider
	 */
	public function test_text($expected, $name, $attrs){
		$Html = new MofgForm\Html($this->Form);
		$this->expectOutputString($expected);
		$Html->text($name, $attrs);
	}

	public function text_provider(){
		return array(
			array(
				"<input type=\"text\" name=\"test\" value=\"foo\" />",
				"test",
				[]
			),
			array(
				"<input type=\"text\" name=\"test\" value=\"foo\" class=\"colored\" />",
				"test",
				["class" => "colored"]
			)
		);
	}

	/**
	 * @dataProvider password_provider
	 */
	public function test_password($expected, $name, $attrs){
		$Html = new MofgForm\Html($this->Form);
		$this->expectOutputString($expected);
		$Html->password($name, $attrs);
	}

	public function password_provider(){
		return array(
			array(
				"<input type=\"password\" name=\"test\" value=\"foo\" />",
				"test",
				[]
			),
			array(
				"<input type=\"password\" name=\"test\" value=\"foo\" class=\"colored\" />",
				"test",
				["class" => "colored"]
			)
		);
	}

	/**
	 * @dataProvider textarea_provider
	 */
	public function test_textarea($expected, $name, $attrs){
		$Html = new MofgForm\Html($this->Form);
		$this->expectOutputString($expected);
		$Html->textarea($name, $attrs);
	}

	public function textarea_provider(){
		return array(
			array(
				"<textarea name=\"test\">foo</textarea>",
				"test",
				[]
			),
			array(
				"<textarea name=\"test\" class=\"colored\">foo</textarea>",
				"test",
				["class" => "colored"]
			)
		);
	}

	public function test_get_attr_text(){
		$Html = new MofgForm\Html($this->Form);
		$this->assertSame("", $Html->get_attr_text([]));
		$this->assertSame(" class=\"colored\"", $Html->get_attr_text(["class" => "colored"]));
		$this->assertSame(" class=\"colored\" data-id=\"100\"", $Html->get_attr_text(["class" => "colored", "data-id" => "100"]));
	}
}

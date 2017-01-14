<?php
require(__DIR__."/../autoload.php");

use PHPUnit\Framework\TestCase;
use MofgForm\MofgForm;

class MofgForm_test extends TestCase{
	public function test_construct(){
		$_SESSION = [];
		$Form = new MofgForm();
		$this->assertInstanceOf("\MofgForm\Member\HTML", $Form->HTML);
		$this->assertInstanceOf("\MofgForm\Member\Mail", $Form->Mail);
	}

	public function test_register_items(){
		$_SESSION = [];
		$session_space = "test";
		$items = $this->get_sample_definition();
		$Form = new MofgForm($session_space, $items);

		$this->assertSame($_SESSION[$session_space]["items"]["name"]["in_page"], 1);
		$this->assertSame($_SESSION[$session_space]["items"]["name"]["title"], "Name");
		$this->assertSame($_SESSION[$session_space]["items"]["name"]["required"], true);
		$this->assertSame($_SESSION[$session_space]["items"]["name"]["rule"], []);
		$this->assertSame($_SESSION[$session_space]["items"]["name"]["add"], []);
		$this->assertSame($_SESSION[$session_space]["items"]["name"]["filter"], MofgForm::FLT_TRIM);

		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["in_page"], 1);
		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["title"], "Tel");
		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["required"], false);
		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["rule"], ["format" => MofgForm::FMT_TEL]);
		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["add"], array("before" => "(", "after" => ")"));
		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["filter"], [MofgForm::FLT_TRIM, MofgForm::FLT_TO_HANKAKU_ALPNUM]);

		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["in_page"], 2);
		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["title"], "Zip code");
		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["required"], false);
		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["rule"], ["pattern" => '/^[0-9]{3}-[0-9]{4}$/']);
		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["add"], ["before" => "〒"]);
		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["filter"], [MofgForm::FLT_TRIM, MofgForm::FLT_TO_HANKAKU_ALPNUM]);

		$this->assertSame($_SESSION[$session_space]["items"]["location"]["in_page"], 2);
		$this->assertSame($_SESSION[$session_space]["items"]["location"]["title"], "Address");
		$this->assertSame($_SESSION[$session_space]["items"]["location"]["required"], false);
		$this->assertSame($_SESSION[$session_space]["items"]["location"]["rule"], []);
		$this->assertSame($_SESSION[$session_space]["items"]["location"]["add"], []);
		$this->assertSame($_SESSION[$session_space]["items"]["location"]["filter"], null);
	}

	public function test_import_posted_data(){
		$_SESSION = [];
		$items = $this->get_sample_definition();

		$POST = [
			"name" => "Suzuki",
			"tel" => "000-0000-0000",
			"zip" => "000-0000",
			"location" => "Tokyo, Japan"
		];
		$Form = new MofgForm("", $items, $POST);
		$this->assertEmpty($Form->get_value("name"));
		$this->assertEmpty($Form->get_value("tel"));
		$this->assertEmpty($Form->get_value("zip"));
		$this->assertEmpty($Form->get_value("location"));

		$_SESSION = [];

		$POST = array_merge($POST, [
			"_enter" => "1"
		]);
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame($Form->get_value("name"), "Suzuki");
		$this->assertSame($Form->get_value("tel"), "000-0000-0000");
		$this->assertEmpty($Form->get_value("zip"));
		$this->assertEmpty($Form->get_value("location"));

		$Form->settle();

		$POST = array_merge($POST, [
			"tel" => "111-1111-1111",
			"_enter" => "1"
		]);
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame($Form->get_value("name"), "Suzuki");
		$this->assertSame($Form->get_value("tel"), "000-0000-0000");
		$this->assertSame($Form->get_value("zip"), "000-0000");
		$this->assertSame($Form->get_value("location"), "Tokyo, Japan");
	}

	public function test_page_transition(){
		$_SESSION = [];
		$items = $this->get_sample_definition();

		$POST = ["name" => "Suzuki", "_enter" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame(2, $Form->settle());
		$this->assertSame(2, $Form->settle());
		$this->assertSame(2, $Form->get_page());

		$POST = ["zip" => "000-0000", "_enter" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame(3, $Form->settle());

		$POST = ["_enter_x" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame(4, $Form->settle());

		$POST = ["_back" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame(3, $Form->settle());

		$POST = ["_back_x" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame(2, $Form->settle());

		$_SESSION = [];

		$POST = ["name" => "", "_enter" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame(1, $Form->settle());

		$POST = ["name" => "Suzuki", "_enter" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame(2, $Form->settle());

		$POST = ["zip" => "ERROR", "_enter" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame(2, $Form->settle());

		$POST = ["zip" => "000-0000", "_enter" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame(3, $Form->settle());

		$POST = ["_reset" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$this->assertSame(1, $Form->settle());

		$_SESSION = [];

		$POST = ["name" => "Suzuki", "_enter" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$Form->set_error("name", "Invalid");
		$this->assertSame(1, $Form->settle());
	}

	public function test_has_error(){
		$_SESSION = [];
		$Form = new MofgForm("", ["item" => []], ["item" => "", "_enter" => "1"]);
		$this->assertFalse($Form->has_error("item"));
		$Form->set_error("item", "Invalid");
		$this->assertTrue($Form->has_error("item"));
	}

	/**
	 * @dataProvider validate_provider
	 */
	public function test_validate($options, $value, $expected){
		$_SESSION = [];
		$Form = new MofgForm("", [
			"ID" => $options
		], [
			"ID" => $value,
			"_enter" => "1"
		]);
		$this->assertSame($expected, $Form->validate("ID"));
	}

	public function validate_provider(){
		return array(
			[["required" => false], "", MofgForm::E_NONE],
			[["required" => false], [], MofgForm::E_NONE],
			[["required" => true], "0", MofgForm::E_NONE],
			[["required" => true], "", MofgForm::E_REQUIRED],
			[["required" => true], [], MofgForm::E_REQUIRED],

			[["rule" => ["minlen" => 5]], "12345", MofgForm::E_NONE],
			[["rule" => ["minlen" => 5]], "123456", MofgForm::E_NONE],
			[["rule" => ["minlen" => 5]], "1234", MofgForm::E_MINLEN],

			[["rule" => ["maxlen" => 5]], "12345", MofgForm::E_NONE],
			[["rule" => ["maxlen" => 5]], "1234", MofgForm::E_NONE],
			[["rule" => ["maxlen" => 5]], "123456", MofgForm::E_MAXLEN],

			[["rule" => ["pattern" => '/^[0-9]{4}-[A-Z]{4}$/']], "1234-ABCD", MofgForm::E_NONE],
			[["rule" => ["pattern" => '/^[0-9]{4}-[A-Z]{4}$/']], "1234-!!!!", MofgForm::E_PATTERN],

			[["rule" => ["format" => MofgForm::FMT_INT]], "1", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_INT]], "123", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_INT]], "0", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_INT]], "-1", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_INT]], "01", MofgForm::E_FMT_INT],
			[["rule" => ["format" => MofgForm::FMT_INT]], "1-", MofgForm::E_FMT_INT],
			[["rule" => ["format" => MofgForm::FMT_INT]], "a", MofgForm::E_FMT_INT],

			[["rule" => ["format" => MofgForm::FMT_ALP]], "abc", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_ALP]], "abc_", MofgForm::E_FMT_ALP],
			[["rule" => ["format" => MofgForm::FMT_ALP]], "123", MofgForm::E_FMT_ALP],

			[["rule" => ["format" => MofgForm::FMT_NUM]], "1", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_NUM]], "123", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_NUM]], "0", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_NUM]], "-1", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_NUM]], "01", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_NUM]], "1-", MofgForm::E_FMT_NUM],
			[["rule" => ["format" => MofgForm::FMT_NUM]], "a", MofgForm::E_FMT_NUM],

			[["rule" => ["format" => MofgForm::FMT_ALPNUM]], "abc", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_ALPNUM]], "012", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_ALPNUM]], "abc012", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_ALPNUM]], "abc012-", MofgForm::E_FMT_ALPNUM],
			[["rule" => ["format" => MofgForm::FMT_ALPNUM]], "-012abc", MofgForm::E_FMT_ALPNUM],

			[["rule" => ["format" => MofgForm::FMT_HIRA]], "あいうえおわーを ん　ぁぃぅぇぉゃゅょゎ", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_HIRA]], "あ_", MofgForm::E_FMT_HIRA],
			[["rule" => ["format" => MofgForm::FMT_HIRA]], "あ、", MofgForm::E_FMT_HIRA],
			[["rule" => ["format" => MofgForm::FMT_HIRA]], "あ。", MofgForm::E_FMT_HIRA],
			[["rule" => ["format" => MofgForm::FMT_HIRA]], "アイウエオ", MofgForm::E_FMT_HIRA],
			[["rule" => ["format" => MofgForm::FMT_HIRA]], "abc", MofgForm::E_FMT_HIRA],
			[["rule" => ["format" => MofgForm::FMT_HIRA]], "123", MofgForm::E_FMT_HIRA],

			[["rule" => ["format" => MofgForm::FMT_KATA]], "アイウエオワーヲ ン　ァィゥェォャュョヮ", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_KATA]], "ア_", MofgForm::E_FMT_KATA],
			[["rule" => ["format" => MofgForm::FMT_KATA]], "ア、", MofgForm::E_FMT_KATA],
			[["rule" => ["format" => MofgForm::FMT_KATA]], "ア。", MofgForm::E_FMT_KATA],
			[["rule" => ["format" => MofgForm::FMT_KATA]], "あいうえお", MofgForm::E_FMT_KATA],
			[["rule" => ["format" => MofgForm::FMT_KATA]], "abc", MofgForm::E_FMT_KATA],
			[["rule" => ["format" => MofgForm::FMT_KATA]], "123", MofgForm::E_FMT_KATA],

			[["rule" => ["format" => MofgForm::FMT_TEL]], "000-0000-0000", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_TEL]], "111-1111-1111", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_TEL]], "000", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_TEL]], "111", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_TEL]], "0-00", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_TEL]], "1-11", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_TEL]], "0_00", MofgForm::E_FMT_TEL],
			[["rule" => ["format" => MofgForm::FMT_TEL]], "0--00", MofgForm::E_FMT_TEL],
			[["rule" => ["format" => MofgForm::FMT_TEL]], "0-00_", MofgForm::E_FMT_TEL],
			[["rule" => ["format" => MofgForm::FMT_TEL]], "-000", MofgForm::E_FMT_TEL],
			[["rule" => ["format" => MofgForm::FMT_TEL]], "abc", MofgForm::E_FMT_TEL],

			[["rule" => ["format" => MofgForm::FMT_EMAIL]], "abc@example.com", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_EMAIL]], "a.b.c@example.com", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_EMAIL]], "abc@a.b.c.example.com", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_EMAIL]], "abc+1-2_3++4--5__6@example.com", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_EMAIL]], "a b c@example.com", MofgForm::E_FMT_EMAIL],
			[["rule" => ["format" => MofgForm::FMT_EMAIL]], "abcexample.com", MofgForm::E_FMT_EMAIL],
			[["rule" => ["format" => MofgForm::FMT_EMAIL]], "abc@example", MofgForm::E_FMT_EMAIL],

			[["rule" => ["format" => MofgForm::FMT_URL]], "http://example.com", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_URL]], "http://example.com/", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_URL]], "https://example.com", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_URL]], "http://a.b.c.example.com", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_URL]], "ftp://example.com", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_URL]], "http://example", MofgForm::E_NONE],
			[["rule" => ["format" => MofgForm::FMT_URL]], "example.com", MofgForm::E_FMT_URL],

			[["rule" => ["minlen" => 2, "maxlen" => 4, "format" => MofgForm::FMT_INT]], "123", MofgForm::E_NONE],
			[["rule" => ["minlen" => 2, "maxlen" => 4, "format" => MofgForm::FMT_INT]], "1", MofgForm::E_MINLEN],
			[["rule" => ["minlen" => 2, "maxlen" => 4, "format" => MofgForm::FMT_INT]], "12345", MofgForm::E_MAXLEN],
			[["rule" => ["minlen" => 2, "maxlen" => 4, "format" => MofgForm::FMT_INT]], "abc", MofgForm::E_FMT_INT]
		);
	}

	/**
	 * @dataProvider test_output_values_provider
	 */
	public function test_output_values($value, $expected){
		$this->expectOutputString($expected);
		$_SESSION = [];
		$Form = new MofgForm("", [
			"item" => []
		], [
			"item" => $value,
			"_enter" => "1"
		]);
		$Form->settle();
		$Form->v("item");
	}

	public function test_output_values_provider(){
		$str = "<a href=\"javascript:void(0)\">&nbsp;</a>";
		return array(
			["foo", "foo"],
			array($str, htmlspecialchars($str))
		);
	}

	/**
	 * @dataProvider test_output_custom_errors_provider
	 */
	public function test_output_custom_errors($error_format, $error_message, $expected){
		$this->expectOutputString($expected);
		$_SESSION = [];
		$Form = new MofgForm("", [
			"item" => []
		], [
			"item" => "",
			"_enter" => "1"
		]);
		$Form->set_error_format($error_format);
		$Form->set_error("item", $error_message);
		$Form->settle();
		$Form->e("item");
	}

	public function test_output_custom_errors_provider(){
		$str = "<a href=\"javascript:void(0)\">&nbsp;</a>";
		return array(
			["%s", "foo", "foo"],
			array("%s", $str, htmlspecialchars($str)),
			["<p>%s</p>", "foo", "<p>foo</p>"]
		);
	}

	public function test_construct_text(){
		$_SESSION = [];
		$items = ["item_1" => ["title" => "Item 1"]];
		$POST = ["item_1" => "100", "_enter" => "1"];
		$Form = new MofgForm("", $items, $POST);
		$Form->settle();

		$text = $Form->construct_text("[", "] ", "\n");
		$expected = "[Item 1] 100";
		$this->assertSame($expected, $text);

		$text = $Form->construct_text("'", "' => ", "");
		$expected = "'Item 1' => 100";
		$this->assertSame($expected, $text);

		$text = $Form->construct_text("* ", "\n", "\n");
		$expected = "* Item 1\n100";
		$this->assertSame($expected, $text);

		$_SESSION = [];
		$items = array(
			"item_1" => array(
				"title" => "Item 1",
				"add" => array(
					"before" => "(",
					"after" => ")"
				),
				"filter" => MofgForm::FLT_TRIM
			),
			"item_2" => [
				"title" => "Item 2",
				"add" => [
					"before" => "#"
				]
			],
			"item_3-1" => [
				"title" => "Item 3-1"
			],
			"item_3-2" => [
				"title" => "Item 3-2"
			]
		);
		$POST = [
			"item_1" => "    foo    ",
			"item_2" => "100",
			"item_3-1" => "bar",
			"item_3-2" => "baz",
			"_enter" => "1"
		];
		$Form = new MofgForm("", $items, $POST);
		$Form->settle();

		$text = $Form->construct_text("* ", ": ", "\n");
		$expected = <<< EOD
* Item 1: (foo)
* Item 2: #100
* Item 3-1: bar
* Item 3-2: baz
EOD;
		$this->assertSame($expected, $text);

		$Form->register_group("item3", "Item 3", ["item_3-1", "item_3-2"], " & ");
		$text = $Form->construct_text("* ", ": ", "\n");
		$expected = <<< EOD
* Item 1: (foo)
* Item 2: #100
* Item 3: bar & baz
EOD;
		$this->assertSame($expected, $text);
	}

	protected function get_sample_definition(){
		return array(
			"name" => [
				"in_page" => 1,
				"title" => "Name",
				"required" => true,
				"filter" => MofgForm::FLT_TRIM
			],
			"tel" => array(
				"title" => "Tel",
				"required" => 0,
				"rule" => [
					"format" => MofgForm::FMT_TEL
				],
				"add" => array(
					"before" => "(",
					"after" => ")"
				),
				"filter" => [
					MofgForm::FLT_TRIM,
					MofgForm::FLT_TO_HANKAKU_ALPNUM
				]
			),
			"zip" => [
				"in_page" => 2,
				"title" => "Zip code",
				"rule" => [
					"pattern" => "/^[0-9]{3}-[0-9]{4}$/"
				],
				"add" => [
					"before" => "〒"
				],
				"filter" => [
					MofgForm::FLT_TRIM,
					MofgForm::FLT_TO_HANKAKU_ALPNUM
				]
			],
			"location" => [
				"in_page" => 2,
				"title" => "Address"
			]
		);
	}
}

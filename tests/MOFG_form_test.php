<?php
require(__DIR__."/../autoload.php");

use PHPUnit\Framework\TestCase;
use MOFG_form\MOFG_form;

class MOFG_form_test extends TestCase{
	public function test_construct(){
		$_SESSION = array();
		$Form = new MOFG_form();
		$this->assertInstanceOf("\MOFG_form\Member\HTML", $Form->HTML);
		$this->assertInstanceOf("\MOFG_form\Member\Mail", $Form->Mail);
	}

	public function test_register_items(){
		$_SESSION = array();
		$session_space = "test";
		$items = $this->get_sample_definition();
		$Form = new MOFG_form($session_space, $items);

		$this->assertSame($_SESSION[$session_space]["items"]["name"]["in_page"], 1);
		$this->assertSame($_SESSION[$session_space]["items"]["name"]["title"], "Name");
		$this->assertSame($_SESSION[$session_space]["items"]["name"]["required"], true);
		$this->assertSame($_SESSION[$session_space]["items"]["name"]["rule"], array());
		$this->assertSame($_SESSION[$session_space]["items"]["name"]["add"], array());
		$this->assertSame($_SESSION[$session_space]["items"]["name"]["filter"], MOFG_form::FLT_TRIM);

		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["in_page"], 1);
		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["title"], "Tel");
		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["required"], false);
		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["rule"], array("format" => MOFG_form::FMT_TEL));
		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["add"], array("before" => "(", "after" => ")"));
		$this->assertSame($_SESSION[$session_space]["items"]["tel"]["filter"], array(MOFG_form::FLT_TRIM, MOFG_form::FLT_TO_HANKAKU_ALPNUM));

		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["in_page"], 2);
		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["title"], "Zip code");
		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["required"], false);
		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["rule"], array("pattern" => '/^[0-9]{3}-[0-9]{4}$/'));
		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["add"], array("before" => "〒"));
		$this->assertSame($_SESSION[$session_space]["items"]["zip"]["filter"], array(MOFG_form::FLT_TRIM, MOFG_form::FLT_TO_HANKAKU_ALPNUM));

		$this->assertSame($_SESSION[$session_space]["items"]["location"]["in_page"], 2);
		$this->assertSame($_SESSION[$session_space]["items"]["location"]["title"], "Address");
		$this->assertSame($_SESSION[$session_space]["items"]["location"]["required"], false);
		$this->assertSame($_SESSION[$session_space]["items"]["location"]["rule"], array());
		$this->assertSame($_SESSION[$session_space]["items"]["location"]["add"], array());
		$this->assertSame($_SESSION[$session_space]["items"]["location"]["filter"], null);
	}

	public function test_import_posted_data(){
		$_SESSION = array();
		$items = $this->get_sample_definition();

		$POST = array(
			"name" => "Suzuki",
			"tel" => "000-0000-0000",
			"zip" => "000-0000",
			"location" => "Tokyo, Japan"
		);
		$Form = new MOFG_form("", $items, $POST);
		$this->assertEmpty($Form->get_value("name"));
		$this->assertEmpty($Form->get_value("tel"));
		$this->assertEmpty($Form->get_value("zip"));
		$this->assertEmpty($Form->get_value("location"));

		$_SESSION = array();

		$POST = array_merge($POST, array(
			"_enter" => "1"
		));
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame($Form->get_value("name"), "Suzuki");
		$this->assertSame($Form->get_value("tel"), "000-0000-0000");
		$this->assertEmpty($Form->get_value("zip"));
		$this->assertEmpty($Form->get_value("location"));

		$Form->settle();

		$POST = array_merge($POST, array(
			"tel" => "111-1111-1111",
			"_enter" => "1"
		));
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame($Form->get_value("name"), "Suzuki");
		$this->assertSame($Form->get_value("tel"), "000-0000-0000");
		$this->assertSame($Form->get_value("zip"), "000-0000");
		$this->assertSame($Form->get_value("location"), "Tokyo, Japan");
	}

	public function test_page_transition(){
		$_SESSION = array();
		$items = $this->get_sample_definition();

		$POST = array("name" => "Suzuki", "_enter" => "1");
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame(2, $Form->settle());
		$this->assertSame(2, $Form->settle());
		$this->assertSame(2, $Form->get_page());

		$POST = array("zip" => "000-0000", "_enter" => "1");
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame(3, $Form->settle());

		$POST = array("_enter_x" => "1");
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame(4, $Form->settle());

		$POST = array("_back" => "1");
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame(3, $Form->settle());

		$POST = array("_back_x" => "1");
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame(2, $Form->settle());

		$_SESSION = array();

		$POST = array("name" => "", "_enter" => "1");
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame(1, $Form->settle());

		$POST = array("name" => "Suzuki", "_enter" => "1");
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame(2, $Form->settle());

		$POST = array("zip" => "ERROR", "_enter" => "1");
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame(2, $Form->settle());

		$POST = array("zip" => "000-0000", "_enter" => "1");
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame(3, $Form->settle());

		$POST = array("_reset" => "1");
		$Form = new MOFG_form("", $items, $POST);
		$this->assertSame(1, $Form->settle());
	}

	/**
	 * @dataProvider validate_provider
	 */
	public function test_validate($options, $value, $expected){
		$_SESSION = array();
		$Form = new MOFG_form("", array(
			"ID" => $options
		), array(
			"ID" => $value,
			"_enter" => "1"
		));
		$this->assertSame($expected, $Form->validate("ID"));
	}

	public function validate_provider(){
		return array(
			array(array("required" => false), "", MOFG_form::E_NONE),
			array(array("required" => false), array(), MOFG_form::E_NONE),
			array(array("required" => true), "0", MOFG_form::E_NONE),
			array(array("required" => true), "", MOFG_form::E_REQUIRED),
			array(array("required" => true), array(), MOFG_form::E_REQUIRED),

			array(array("rule" => array("minlen" => 5)), "12345", MOFG_form::E_NONE),
			array(array("rule" => array("minlen" => 5)), "123456", MOFG_form::E_NONE),
			array(array("rule" => array("minlen" => 5)), "1234", MOFG_form::E_MINLEN),

			array(array("rule" => array("maxlen" => 5)), "12345", MOFG_form::E_NONE),
			array(array("rule" => array("maxlen" => 5)), "1234", MOFG_form::E_NONE),
			array(array("rule" => array("maxlen" => 5)), "123456", MOFG_form::E_MAXLEN),

			array(array("rule" => array("pattern" => '/^[0-9]{4}-[A-Z]{4}$/')), "1234-ABCD", MOFG_form::E_NONE),
			array(array("rule" => array("pattern" => '/^[0-9]{4}-[A-Z]{4}$/')), "1234-!!!!", MOFG_form::E_PATTERN),

			array(array("rule" => array("format" => MOFG_form::FMT_INT)), "1", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_INT)), "123", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_INT)), "0", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_INT)), "-1", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_INT)), "01", MOFG_form::E_FMT_INT),
			array(array("rule" => array("format" => MOFG_form::FMT_INT)), "1-", MOFG_form::E_FMT_INT),
			array(array("rule" => array("format" => MOFG_form::FMT_INT)), "a", MOFG_form::E_FMT_INT),

			array(array("rule" => array("format" => MOFG_form::FMT_ALP)), "abc", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_ALP)), "abc_", MOFG_form::E_FMT_ALP),
			array(array("rule" => array("format" => MOFG_form::FMT_ALP)), "123", MOFG_form::E_FMT_ALP),

			array(array("rule" => array("format" => MOFG_form::FMT_NUM)), "1", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_NUM)), "123", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_NUM)), "0", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_NUM)), "-1", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_NUM)), "01", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_NUM)), "1-", MOFG_form::E_FMT_NUM),
			array(array("rule" => array("format" => MOFG_form::FMT_NUM)), "a", MOFG_form::E_FMT_NUM),

			array(array("rule" => array("format" => MOFG_form::FMT_ALPNUM)), "abc", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_ALPNUM)), "012", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_ALPNUM)), "abc012", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_ALPNUM)), "abc012-", MOFG_form::E_FMT_ALPNUM),
			array(array("rule" => array("format" => MOFG_form::FMT_ALPNUM)), "-012abc", MOFG_form::E_FMT_ALPNUM),

			array(array("rule" => array("format" => MOFG_form::FMT_HIRA)), "あいうえおわーを ん　ぁぃぅぇぉゃゅょゎ", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_HIRA)), "あ_", MOFG_form::E_FMT_HIRA),
			array(array("rule" => array("format" => MOFG_form::FMT_HIRA)), "あ、", MOFG_form::E_FMT_HIRA),
			array(array("rule" => array("format" => MOFG_form::FMT_HIRA)), "あ。", MOFG_form::E_FMT_HIRA),
			array(array("rule" => array("format" => MOFG_form::FMT_HIRA)), "アイウエオ", MOFG_form::E_FMT_HIRA),
			array(array("rule" => array("format" => MOFG_form::FMT_HIRA)), "abc", MOFG_form::E_FMT_HIRA),
			array(array("rule" => array("format" => MOFG_form::FMT_HIRA)), "123", MOFG_form::E_FMT_HIRA),

			array(array("rule" => array("format" => MOFG_form::FMT_KATA)), "アイウエオワーヲ ン　ァィゥェォャュョヮ", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_KATA)), "ア_", MOFG_form::E_FMT_KATA),
			array(array("rule" => array("format" => MOFG_form::FMT_KATA)), "ア、", MOFG_form::E_FMT_KATA),
			array(array("rule" => array("format" => MOFG_form::FMT_KATA)), "ア。", MOFG_form::E_FMT_KATA),
			array(array("rule" => array("format" => MOFG_form::FMT_KATA)), "あいうえお", MOFG_form::E_FMT_KATA),
			array(array("rule" => array("format" => MOFG_form::FMT_KATA)), "abc", MOFG_form::E_FMT_KATA),
			array(array("rule" => array("format" => MOFG_form::FMT_KATA)), "123", MOFG_form::E_FMT_KATA),

			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "000-0000-0000", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "111-1111-1111", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "000", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "111", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "0-00", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "1-11", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "0_00", MOFG_form::E_FMT_TEL),
			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "0--00", MOFG_form::E_FMT_TEL),
			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "0-00_", MOFG_form::E_FMT_TEL),
			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "-000", MOFG_form::E_FMT_TEL),
			array(array("rule" => array("format" => MOFG_form::FMT_TEL)), "abc", MOFG_form::E_FMT_TEL),

			array(array("rule" => array("format" => MOFG_form::FMT_EMAIL)), "abc@example.com", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_EMAIL)), "a.b.c@example.com", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_EMAIL)), "abc@a.b.c.example.com", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_EMAIL)), "abc+1-2_3++4--5__6@example.com", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_EMAIL)), "a b c@example.com", MOFG_form::E_FMT_EMAIL),
			array(array("rule" => array("format" => MOFG_form::FMT_EMAIL)), "abcexample.com", MOFG_form::E_FMT_EMAIL),
			array(array("rule" => array("format" => MOFG_form::FMT_EMAIL)), "abc@example", MOFG_form::E_FMT_EMAIL),

			array(array("rule" => array("format" => MOFG_form::FMT_URL)), "http://example.com", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_URL)), "http://example.com/", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_URL)), "https://example.com", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_URL)), "http://a.b.c.example.com", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_URL)), "ftp://example.com", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_URL)), "http://example", MOFG_form::E_NONE),
			array(array("rule" => array("format" => MOFG_form::FMT_URL)), "example.com", MOFG_form::E_FMT_URL),

			array(array("rule" => array("minlen" => 2, "maxlen" => 4, "format" => MOFG_form::FMT_INT)), "123", MOFG_form::E_NONE),
			array(array("rule" => array("minlen" => 2, "maxlen" => 4, "format" => MOFG_form::FMT_INT)), "1", MOFG_form::E_MINLEN),
			array(array("rule" => array("minlen" => 2, "maxlen" => 4, "format" => MOFG_form::FMT_INT)), "12345", MOFG_form::E_MAXLEN),
			array(array("rule" => array("minlen" => 2, "maxlen" => 4, "format" => MOFG_form::FMT_INT)), "abc", MOFG_form::E_FMT_INT)
		);
	}

	public function test_construct_text(){
		$_SESSION = array();
		$items = array("item_1" => array("title" => "Item 1"));
		$POST = array("item_1" => "100", "_enter" => "1");
		$Form = new MOFG_form("", $items, $POST);
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

		$_SESSION = array();
		$items = array(
			"item_1" => array(
				"title" => "Item 1",
				"add" => array(
					"before" => "(",
					"after" => ")"
				),
				"filter" => MOFG_form::FLT_TRIM
			),
			"item_2" => array(
				"title" => "Item 2",
				"add" => array(
					"before" => "#"
				)
			),
			"item_3-1" => array(
				"title" => "Item 3-1"
			),
			"item_3-2" => array(
				"title" => "Item 3-2"
			)
		);
		$POST = array(
			"item_1" => "    foo    ",
			"item_2" => "100",
			"item_3-1" => "bar",
			"item_3-2" => "baz",
			"_enter" => "1"
		);
		$Form = new MOFG_form("", $items, $POST);
		$Form->settle();

		$text = $Form->construct_text("* ", ": ", "\n");
		$expected = <<< EOD
* Item 1: (foo)
* Item 2: #100
* Item 3-1: bar
* Item 3-2: baz
EOD;
		$this->assertSame($expected, $text);

		$Form->register_group("item3", "Item 3", array("item_3-1", "item_3-2"), " & ");
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
			"name" => array(
				"in_page" => 1,
				"title" => "Name",
				"required" => true,
				"filter" => MOFG_form::FLT_TRIM
			),
			"tel" => array(
				"title" => "Tel",
				"required" => 0,
				"rule" => array(
					"format" => MOFG_form::FMT_TEL
				),
				"add" => array(
					"before" => "(",
					"after" => ")"
				),
				"filter" => array(
					MOFG_form::FLT_TRIM,
					MOFG_form::FLT_TO_HANKAKU_ALPNUM
				)
			),
			"zip" => array(
				"in_page" => 2,
				"title" => "Zip code",
				"rule" => array(
					"pattern" => "/^[0-9]{3}-[0-9]{4}$/"
				),
				"add" => array(
					"before" => "〒"
				),
				"filter" => array(
					MOFG_form::FLT_TRIM,
					MOFG_form::FLT_TO_HANKAKU_ALPNUM
				)
			),
			"location" => array(
				"in_page" => 2,
				"title" => "Address"
			)
		);
	}
}

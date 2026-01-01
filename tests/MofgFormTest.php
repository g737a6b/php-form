<?php

require(__DIR__."/../autoload.php");

use PHPUnit\Framework\TestCase;
use MofgForm\MofgForm;

class MofgFormTest extends TestCase
{
    public function test_construct()
    {
        $_SESSION = [];
        $Form = new MofgForm();
        $this->assertInstanceOf("\MofgForm\Html", $Form->Html);
        $this->assertInstanceOf("\MofgForm\Mail", $Form->Mail);
    }

    public function test_register_items()
    {
        $_SESSION = [];
        $sessionSpace = "test";
        $items = $this->get_sample_definition();
        $Form = new MofgForm($sessionSpace, $items);

        $this->assertSame($_SESSION[$sessionSpace]["items"]["name"]["in_page"], 1);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["name"]["title"], "Name");
        $this->assertSame($_SESSION[$sessionSpace]["items"]["name"]["required"], true);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["name"]["rule"], []);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["name"]["add"], []);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["name"]["filter"], MofgForm::FLT_TRIM);

        $this->assertSame($_SESSION[$sessionSpace]["items"]["tel"]["in_page"], 1);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["tel"]["title"], "Tel");
        $this->assertSame($_SESSION[$sessionSpace]["items"]["tel"]["required"], false);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["tel"]["rule"], ["format" => MofgForm::FMT_TEL]);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["tel"]["add"], ["before" => "(", "after" => ")"]);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["tel"]["filter"], [MofgForm::FLT_TRIM, MofgForm::FLT_TO_HANKAKU_ALPNUM]);

        $this->assertSame($_SESSION[$sessionSpace]["items"]["zip"]["in_page"], 2);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["zip"]["title"], "Zip code");
        $this->assertSame($_SESSION[$sessionSpace]["items"]["zip"]["required"], false);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["zip"]["rule"], ["pattern" => '/^[0-9]{3}-[0-9]{4}$/']);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["zip"]["add"], ["before" => "〒"]);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["zip"]["filter"], [MofgForm::FLT_TRIM, MofgForm::FLT_TO_HANKAKU_ALPNUM]);

        $this->assertSame($_SESSION[$sessionSpace]["items"]["location"]["in_page"], 2);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["location"]["title"], "Address");
        $this->assertSame($_SESSION[$sessionSpace]["items"]["location"]["required"], false);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["location"]["rule"], []);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["location"]["add"], []);
        $this->assertSame($_SESSION[$sessionSpace]["items"]["location"]["filter"], null);
    }

    public function test_import_posted_data()
    {
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

    public function test_page_transition()
    {
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

    public function test_has_error()
    {
        $_SESSION = [];
        $Form = new MofgForm("", ["item" => []], ["item" => "", "_enter" => "1"]);
        $this->assertFalse($Form->has_error("item"));
        $Form->set_error("item", "Invalid");
        $this->assertTrue($Form->has_error("item"));
    }

    /**
     * @dataProvider provider_for_test_validate
     */
    public function test_validate($options, $value, $expected)
    {
        $_SESSION = [];
        $Form = new MofgForm("", [
            "ID" => $options
        ], [
            "ID" => $value,
            "_enter" => "1"
        ]);
        $this->assertSame($expected, $Form->validate("ID"));
    }

    public static function provider_for_test_validate()
    {
        return [
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
        ];
    }

    /**
     * @dataProvider provider_for_test_output_values
     */
    public function test_output_values($value, $expected)
    {
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

    public static function provider_for_test_output_values()
    {
        $str = "<a href=\"javascript:void(0)\">&nbsp;</a>";
        return [
            ["foo", "foo"],
            [$str, htmlspecialchars($str)]
        ];
    }

    /**
     * @dataProvider provider_for_test_output_custom_errors
     */
    public function test_output_custom_errors($errorFormat, $errorMessage, $expected)
    {
        $this->expectOutputString($expected);
        $_SESSION = [];
        $Form = new MofgForm("", [
            "item" => []
        ], [
            "item" => "",
            "_enter" => "1"
        ]);
        $Form->set_error_format($errorFormat);
        $Form->set_error("item", $errorMessage);
        $Form->settle();
        $Form->e("item");
    }

    public static function provider_for_test_output_custom_errors()
    {
        $str = "<a href=\"javascript:void(0)\">&nbsp;</a>";
        return [
            ["%s", "foo", "foo"],
            ["%s", $str, htmlspecialchars($str)],
            ["<p>%s</p>", "foo", "<p>foo</p>"]
        ];
    }

    public function test_construct_text()
    {
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
        $items = [
            "item_1" => [
                "title" => "Item 1",
                "add" => [
                    "before" => "(",
                    "after" => ")"
                ],
                "filter" => MofgForm::FLT_TRIM
            ],
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
        ];
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

    /**
     * @dataProvider provider_for_test_apply_filter
     */
    public function test_apply_filter($input, $filter, $expected)
    {
        $_SESSION = [];
        $Form = new MofgForm();
        $result = $Form->apply_filter($input, $filter);
        $this->assertSame($expected, $result);
    }

    public static function provider_for_test_apply_filter()
    {
        return [
            ["  test  ", MofgForm::FLT_TRIM, "test"],
            ["  test  ", MofgForm::FLT_LTRIM, "test  "],
            ["  test  ", MofgForm::FLT_RTRIM, "  test"],
            ["ｱｲｳ", MofgForm::FLT_TO_ZENKAKU_KANA, "アイウ"],
            ["ＡＢＣ１２３", MofgForm::FLT_TO_HANKAKU_ALPNUM, "ABC123"],
            ["abc", MofgForm::FLT_TO_UPPER_CASE, "ABC"],
            ["ABC", MofgForm::FLT_TO_LOWER_CASE, "abc"],
            ["line1\r\nline2\rline3", MofgForm::FLT_EOL_TO_N, "line1\nline2\nline3"],
            ["line1\r\nline2\rline3", MofgForm::FLT_EOL_TO_SPACE, "line1 line2 line3"],
            ["  ＡＢＣ  ", [MofgForm::FLT_TRIM, MofgForm::FLT_TO_HANKAKU_ALPNUM], "ABC"],
        ];
    }

    public function test_remove_item()
    {
        $_SESSION = [];
        $items = ["item1" => ["title" => "Item 1"], "item2" => ["title" => "Item 2"]];
        $POST = ["item1" => "value1", "item2" => "value2", "_enter" => "1"];
        $Form = new MofgForm("", $items, $POST);

        $this->assertSame("value1", $Form->get_value("item1"));
        $this->assertSame("value2", $Form->get_value("item2"));

        $Form->remove_item("item1");
        $this->assertFalse($Form->get_value("item1"));
        $this->assertSame("value2", $Form->get_value("item2"));
    }

    public function test_remove_group()
    {
        $_SESSION = [];
        $items = ["item1" => ["title" => "Item 1"], "item2" => ["title" => "Item 2"]];
        $POST = ["item1" => "value1", "item2" => "value2", "_enter" => "1"];
        $Form = new MofgForm("", $items, $POST);
        $Form->settle();

        $Form->register_group("group1", "Group 1", ["item1", "item2"], " - ");
        $text = $Form->construct_text("[", "] ", "\n");
        $this->assertStringContainsString("Group 1", $text);

        $Form->remove_group("group1");
        $text = $Form->construct_text("[", "] ", "\n");
        $this->assertStringNotContainsString("Group 1", $text);
    }

    public function test_end_clean()
    {
        $_SESSION = [];
        $items = ["item" => ["title" => "Item"]];
        $POST = ["item" => "value", "_enter" => "1"];
        $Form = new MofgForm("session_space", $items, $POST);
        $Form->settle();

        $this->assertNotEmpty($_SESSION["session_space"]);
        $this->assertSame("value", $Form->get_value("item"));

        $Form->end_clean();
        $this->assertEmpty($_SESSION["session_space"]);
        $this->assertFalse($Form->get_value("item"));
    }

    public function test_output_values_with_add_options()
    {
        $this->expectOutputString("(before)test(after)");
        $_SESSION = [];
        $Form = new MofgForm("", [
            "item" => [
                "add" => [
                    "before" => "(before)",
                    "after" => "(after)"
                ]
            ]
        ], [
            "item" => "test",
            "_enter" => "1"
        ]);
        $Form->settle();
        $Form->v("item");
    }

    public function test_array_value_validation()
    {
        $_SESSION = [];
        $Form = new MofgForm("", [
            "items" => ["rule" => ["format" => MofgForm::FMT_INT]]
        ], [
            "items" => ["value1", "value2"],
            "_enter" => "1"
        ]);
        $this->assertSame(MofgForm::E_NONE, $Form->validate("items"));
    }

    public function test_array_value_output()
    {
        $this->expectOutputString("val1, val2, val3");
        $_SESSION = [];
        $Form = new MofgForm("", [
            "items" => []
        ], [
            "items" => ["val1", "val2", "val3"],
            "_enter" => "1"
        ]);
        $Form->settle();
        $Form->v("items");
    }

    public function test_array_value_with_custom_glue()
    {
        $this->expectOutputString("val1 | val2 | val3");
        $_SESSION = [];
        $Form = new MofgForm("", [
            "items" => []
        ], [
            "items" => ["val1", "val2", "val3"],
            "_enter" => "1"
        ]);
        $Form->set_array_glue(" | ");
        $Form->settle();
        $Form->v("items");
    }

    public function test_getter_setter_methods()
    {
        $_SESSION = [];
        $Form = new MofgForm();

        $this->assertSame(1, $Form->get_page());
        $Form->set_page(3);
        $this->assertSame(3, $Form->get_page());
        $Form->set_page(0);
        $this->assertSame(1, $Form->get_page());
        $Form->set_page(-1);
        $this->assertSame(1, $Form->get_page());

        $this->assertSame("_enter", $Form->get_name_for(MofgForm::CTL_ENTER));
        $this->assertSame("_back", $Form->get_name_for(MofgForm::CTL_BACK));
        $this->assertSame("_reset", $Form->get_name_for(MofgForm::CTL_RESET));

        $this->assertTrue($Form->set_name_for(MofgForm::CTL_ENTER, "submit"));
        $this->assertSame("submit", $Form->get_name_for(MofgForm::CTL_ENTER));

        $this->assertSame("", $Form->get_name_for("invalid"));
        $this->assertFalse($Form->set_name_for("invalid", "test"));

        $this->assertTrue($Form->set_error_message([
            MofgForm::E_REQUIRED => "Required field",
            MofgForm::E_MINLEN => "Too short"
        ]));
    }

    public function test_set_value()
    {
        $_SESSION = [];
        $Form = new MofgForm("", ["item" => []]);

        $this->assertTrue($Form->set_value("item", "test"));
        $this->assertSame("test", $Form->get_value("item"));

        $this->assertTrue($Form->set_value("item", ["val1", "val2"]));
        $this->assertSame(["val1", "val2"], $Form->get_value("item"));

        $this->assertFalse($Form->set_value("item", 123));
        $this->assertFalse($Form->set_value("item", null));
    }

    protected function get_sample_definition()
    {
        return [
            "name" => [
                "in_page" => 1,
                "title" => "Name",
                "required" => true,
                "filter" => MofgForm::FLT_TRIM
            ],
            "tel" => [
                "title" => "Tel",
                "required" => 0,
                "rule" => [
                    "format" => MofgForm::FMT_TEL
                ],
                "add" => [
                    "before" => "(",
                    "after" => ")"
                ],
                "filter" => [
                    MofgForm::FLT_TRIM,
                    MofgForm::FLT_TO_HANKAKU_ALPNUM
                ]
            ],
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
        ];
    }
}

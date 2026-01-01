<?php

require(__DIR__."/../autoload.php");

use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{
    protected function createFormMock($returnValue = "foo")
    {
        $mock = $this->createMock(MofgForm\MofgForm::class);
        $mock->method("get_value")->willReturn($returnValue);
        return $mock;
    }

    /**
     * @dataProvider provider_for_test_checkbox
     */
    public function test_checkbox($expected, $name, $items, $attrs, $formValue = "foo")
    {
        $Form = $this->createFormMock($formValue);
        $Html = new MofgForm\Html($Form);
        $this->expectOutputString($expected);
        $Html->checkbox($name, $items, $attrs);
    }

    public static function provider_for_test_checkbox()
    {
        return [
            [
                "",
                "test",
                [],
                []
            ],
            [
                "<label><input type=\"checkbox\" name=\"test\" value=\"bar\" /> bar</label>",
                "test",
                ["bar"],
                []
            ],
            [
                "<label><input type=\"checkbox\" name=\"test\" value=\"foo\" checked=\"checked\" /> foo</label>",
                "test",
                ["foo"],
                []
            ],
            [
                "<label><input type=\"checkbox\" name=\"test[]\" value=\"bar\" /> bar</label><label><input type=\"checkbox\" name=\"test[]\" value=\"baz\" /> baz</label>",
                "test",
                ["bar", "baz"],
                []
            ],
            [
                "<label><input type=\"checkbox\" name=\"test[]\" value=\"foo\" checked=\"checked\" /> foo</label><label><input type=\"checkbox\" name=\"test[]\" value=\"bar\" /> bar</label>",
                "test",
                ["foo", "bar"],
                []
            ],
            [
                "<label><input type=\"checkbox\" name=\"test[]\" value=\"foo\" checked=\"checked\" /> Apple</label><label><input type=\"checkbox\" name=\"test[]\" value=\"bar\" /> Orange</label>",
                "test",
                ["foo" => "Apple", "bar" => "Orange"],
                []
            ],
            [
                "<label class=\"colored\"><input type=\"checkbox\" name=\"test\" value=\"bar\" /> bar</label>",
                "test",
                ["bar"],
                ["class" => "colored"]
            ],
            // Test with special characters in values
            [
                "<label><input type=\"checkbox\" name=\"test\" value=\"&lt;script&gt;\" /> &lt;script&gt;</label>",
                "test",
                ["<script>"],
                []
            ],
            [
                "<label><input type=\"checkbox\" name=\"test\" value=\"&quot;foo&quot;\" /> &quot;foo&quot;</label>",
                "test",
                ['"foo"'],
                []
            ],
            // Test with HTML entities in attributes
            [
                "<label class=\"&lt;script&gt;\"><input type=\"checkbox\" name=\"test\" value=\"bar\" /> bar</label>",
                "test",
                ["bar"],
                ["class" => "<script>"]
            ],
            // Test with items as string (converted to array)
            [
                "<label><input type=\"checkbox\" name=\"test\" value=\"bar\" /> bar</label>",
                "test",
                "bar",
                []
            ],
            // Test with non-array items (returns void, outputs nothing)
            [
                "",
                "test",
                null,
                []
            ],
            // Test with array containing non-string values (skipped)
            [
                "<label><input type=\"checkbox\" name=\"test[]\" value=\"bar\" /> bar</label>",
                "test",
                ["bar", 123],
                []
            ],
            // Test with name containing [] (stripped for value lookup)
            [
                "<label><input type=\"checkbox\" name=\"test[][]\" value=\"foo\" checked=\"checked\" /> foo</label><label><input type=\"checkbox\" name=\"test[][]\" value=\"bar\" /> bar</label>",
                "test[]",
                ["foo", "bar"],
                []
            ],
            // Test with form returning array of values
            [
                "<label><input type=\"checkbox\" name=\"test[]\" value=\"foo\" checked=\"checked\" /> foo</label><label><input type=\"checkbox\" name=\"test[]\" value=\"bar\" checked=\"checked\" /> bar</label>",
                "test",
                ["foo", "bar"],
                [],
                ["foo", "bar"]
            ],
            // Test with non-array form value (converted to array)
            [
                "<label><input type=\"checkbox\" name=\"test[]\" value=\"foo\" checked=\"checked\" /> foo</label><label><input type=\"checkbox\" name=\"test[]\" value=\"bar\" /> bar</label>",
                "test",
                ["foo", "bar"],
                [],
                "foo"
            ]
        ];
    }

    /**
     * @dataProvider provider_for_test_radio
     */
    public function test_radio($expected, $name, $items, $attrs, $formValue = "foo")
    {
        $Form = $this->createFormMock($formValue);
        $Html = new MofgForm\Html($Form);
        $this->expectOutputString($expected);
        $Html->radio($name, $items, $attrs);
    }

    public static function provider_for_test_radio()
    {
        return [
            [
                "",
                "test",
                [],
                []
            ],
            [
                "<label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
                "test",
                ["bar"],
                []
            ],
            [
                "<label><input type=\"radio\" name=\"test\" value=\"foo\" checked=\"checked\" /> foo</label>",
                "test",
                ["foo"],
                []
            ],
            [
                "<label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label><label><input type=\"radio\" name=\"test\" value=\"baz\" /> baz</label>",
                "test",
                ["bar", "baz"],
                []
            ],
            [
                "<label><input type=\"radio\" name=\"test\" value=\"foo\" checked=\"checked\" /> foo</label><label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
                "test",
                ["foo", "bar"],
                []
            ],
            [
                "<label><input type=\"radio\" name=\"test\" value=\"foo\" checked=\"checked\" /> Apple</label><label><input type=\"radio\" name=\"test\" value=\"bar\" /> Orange</label>",
                "test",
                ["foo" => "Apple", "bar" => "Orange"],
                []
            ],
            [
                "<label class=\"colored\"><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
                "test",
                ["bar"],
                ["class" => "colored"]
            ],
            // Test with special characters in values
            [
                "<label><input type=\"radio\" name=\"test\" value=\"&lt;script&gt;\" /> &lt;script&gt;</label>",
                "test",
                ["<script>"],
                []
            ],
            [
                "<label><input type=\"radio\" name=\"test\" value=\"&quot;foo&quot;\" /> &quot;foo&quot;</label>",
                "test",
                ['"foo"'],
                []
            ],
            // Test with HTML entities in attributes
            [
                "<label class=\"&lt;script&gt;\"><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
                "test",
                ["bar"],
                ["class" => "<script>"]
            ],
            // Test with items as string (converted to array)
            [
                "<label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
                "test",
                "bar",
                []
            ],
            // Test with non-array items (returns void, outputs nothing)
            [
                "",
                "test",
                null,
                []
            ],
            // Test with array containing non-string values (skipped)
            [
                "<label><input type=\"radio\" name=\"test\" value=\"bar\" /> bar</label>",
                "test",
                ["bar", 123],
                []
            ]
        ];
    }

    /**
     * @dataProvider provider_for_test_select
     */
    public function test_select($expected, $name, $options, $empty, $attrs, $formValue = "foo")
    {
        $Form = $this->createFormMock($formValue);
        $Html = new MofgForm\Html($Form);
        $this->expectOutputString($expected);
        $Html->select($name, $options, $empty, $attrs);
    }

    public static function provider_for_test_select()
    {
        return [
            [
                "<select name=\"test\"></select>",
                "test",
                [],
                "",
                []
            ],
            [
                "<select name=\"test\"><option value=\"bar\">bar</option></select>",
                "test",
                ["bar"],
                "",
                []
            ],
            [
                "<select name=\"test\"><option value=\"foo\" selected=\"selected\">foo</option></select>",
                "test",
                ["foo"],
                "",
                []
            ],
            [
                "<select name=\"test\"><option value=\"bar\">bar</option><option value=\"baz\">baz</option></select>",
                "test",
                ["bar", "baz"],
                "",
                []
            ],
            [
                "<select name=\"test\"><option value=\"foo\" selected=\"selected\">foo</option><option value=\"bar\">bar</option></select>",
                "test",
                ["foo", "bar"],
                "",
                []
            ],
            [
                "<select name=\"test\"><option value=\"\">----</option></select>",
                "test",
                [],
                "----",
                []
            ],
            [
                "<select name=\"test\"><option value=\"\">----</option><option value=\"bar\">bar</option></select>",
                "test",
                ["bar"],
                "----",
                []
            ],
            [
                "<select name=\"test\" class=\"colored\"></select>",
                "test",
                [],
                "",
                ["class" => "colored"]
            ],
            // Test with special characters in values
            [
                "<select name=\"test\"><option value=\"&lt;script&gt;\" selected=\"selected\">&lt;script&gt;</option></select>",
                "test",
                ["<script>"],
                "",
                [],
                "<script>"
            ],
            [
                "<select name=\"test\"><option value=\"\">&lt;script&gt;</option><option value=\"bar\">bar</option></select>",
                "test",
                ["bar"],
                "<script>",
                []
            ],
            // Test with special characters in name attribute
            [
                "<select name=\"&lt;test&gt;\"></select>",
                "<test>",
                [],
                "",
                []
            ],
            // Test with HTML entities in attributes
            [
                "<select name=\"test\" class=\"&lt;script&gt;\"></select>",
                "test",
                [],
                "",
                ["class" => "<script>"]
            ],
            // Test with associative array (keys are ignored)
            [
                "<select name=\"test\"><option value=\"Apple\">Apple</option><option value=\"Orange\">Orange</option></select>",
                "test",
                ["foo" => "Apple", "bar" => "Orange"],
                "",
                []
            ],
            // Test with options as string (converted to array)
            [
                "<select name=\"test\"><option value=\"bar\">bar</option></select>",
                "test",
                "bar",
                "",
                []
            ],
            // Test with non-array options (returns void, outputs nothing)
            [
                "",
                "test",
                null,
                "",
                []
            ],
            // Test with array containing non-string values (skipped)
            [
                "<select name=\"test\"><option value=\"bar\">bar</option></select>",
                "test",
                ["bar", 123],
                "",
                []
            ]
        ];
    }

    /**
     * @dataProvider provider_for_test_text
     */
    public function test_text($expected, $name, $attrs, $formValue = "foo")
    {
        $Form = $this->createFormMock($formValue);
        $Html = new MofgForm\Html($Form);
        $this->expectOutputString($expected);
        $Html->text($name, $attrs);
    }

    public static function provider_for_test_text()
    {
        return [
            [
                "<input type=\"text\" name=\"test\" value=\"foo\" />",
                "test",
                []
            ],
            [
                "<input type=\"text\" name=\"test\" value=\"foo\" class=\"colored\" />",
                "test",
                ["class" => "colored"]
            ],
            // Test with special characters in values
            [
                "<input type=\"text\" name=\"test\" value=\"&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;\" />",
                "test",
                [],
                "<script>alert('xss')</script>"
            ],
            [
                "<input type=\"text\" name=\"test\" value=\"&quot;foo&quot;\" />",
                "test",
                [],
                '"foo"'
            ],
            // Test with HTML entities in attributes
            [
                "<input type=\"text\" name=\"test\" value=\"foo\" class=\"&lt;script&gt;\" />",
                "test",
                ["class" => "<script>"],
                "foo"
            ],
            // Test with non-string value (empty string)
            [
                "<input type=\"text\" name=\"test\" value=\"\" />",
                "test",
                [],
                123
            ],
            [
                "<input type=\"text\" name=\"test\" value=\"\" />",
                "test",
                [],
                null
            ],
            [
                "<input type=\"text\" name=\"test\" value=\"\" />",
                "test",
                [],
                []
            ]
        ];
    }

    /**
     * @dataProvider provider_for_test_password
     */
    public function test_password($expected, $name, $attrs, $formValue = "foo")
    {
        $Form = $this->createFormMock($formValue);
        $Html = new MofgForm\Html($Form);
        $this->expectOutputString($expected);
        $Html->password($name, $attrs);
    }

    public static function provider_for_test_password()
    {
        return [
            [
                "<input type=\"password\" name=\"test\" value=\"foo\" />",
                "test",
                []
            ],
            [
                "<input type=\"password\" name=\"test\" value=\"foo\" class=\"colored\" />",
                "test",
                ["class" => "colored"]
            ],
            // Test with special characters in values
            [
                "<input type=\"password\" name=\"test\" value=\"&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;\" />",
                "test",
                [],
                "<script>alert('xss')</script>"
            ],
            [
                "<input type=\"password\" name=\"test\" value=\"&quot;foo&quot;\" />",
                "test",
                [],
                '"foo"'
            ],
            // Test with HTML entities in attributes
            [
                "<input type=\"password\" name=\"test\" value=\"foo\" class=\"&lt;script&gt;\" />",
                "test",
                ["class" => "<script>"],
                "foo"
            ],
            // Test with non-string value (empty string)
            [
                "<input type=\"password\" name=\"test\" value=\"\" />",
                "test",
                [],
                123
            ],
            [
                "<input type=\"password\" name=\"test\" value=\"\" />",
                "test",
                [],
                null
            ],
            [
                "<input type=\"password\" name=\"test\" value=\"\" />",
                "test",
                [],
                []
            ]
        ];
    }

    /**
     * @dataProvider provider_for_test_textarea
     */
    public function test_textarea($expected, $name, $attrs, $formValue = "foo")
    {
        $Form = $this->createFormMock($formValue);
        $Html = new MofgForm\Html($Form);
        $this->expectOutputString($expected);
        $Html->textarea($name, $attrs);
    }

    public static function provider_for_test_textarea()
    {
        return [
            [
                "<textarea name=\"test\">foo</textarea>",
                "test",
                []
            ],
            [
                "<textarea name=\"test\" class=\"colored\">foo</textarea>",
                "test",
                ["class" => "colored"]
            ],
            // Test with special characters in values
            [
                "<textarea name=\"test\">&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;</textarea>",
                "test",
                [],
                "<script>alert('xss')</script>"
            ],
            [
                "<textarea name=\"test\">&quot;foo&quot;</textarea>",
                "test",
                [],
                '"foo"'
            ],
            // Test with HTML entities in attributes
            [
                "<textarea name=\"test\" class=\"&lt;script&gt;\">foo</textarea>",
                "test",
                ["class" => "<script>"],
                "foo"
            ],
            // Test with non-string value (empty string)
            [
                "<textarea name=\"test\"></textarea>",
                "test",
                [],
                123
            ],
            [
                "<textarea name=\"test\"></textarea>",
                "test",
                [],
                null
            ],
            [
                "<textarea name=\"test\"></textarea>",
                "test",
                [],
                []
            ]
        ];
    }

    public function test_get_attr_text()
    {
        $Form = $this->createFormMock();
        $Html = new MofgForm\Html($Form);

        // Basic tests
        $this->assertSame("", $Html->get_attr_text([]));
        $this->assertSame(" class=\"colored\"", $Html->get_attr_text(["class" => "colored"]));
        $this->assertSame(" class=\"colored\" data-id=\"100\"", $Html->get_attr_text(["class" => "colored", "data-id" => "100"]));

        // Test with special characters in attribute values
        $this->assertSame(" class=\"&lt;script&gt;\"", $Html->get_attr_text(["class" => "<script>"]));
        $this->assertSame(" data-value=\"&quot;foo&quot;\"", $Html->get_attr_text(["data-value" => '"foo"']));

        // Test with non-string keys or values (skipped)
        $this->assertSame("", $Html->get_attr_text([123 => "value"]));
        $this->assertSame("", $Html->get_attr_text(["key" => 123]));
        $this->assertSame(" class=\"colored\"", $Html->get_attr_text(["class" => "colored", 123 => "value", "key" => 456]));

        // Test with null, empty string, and other non-array types
        $this->assertSame("", $Html->get_attr_text(null));
        $this->assertSame("", $Html->get_attr_text(""));
        $this->assertSame("", $Html->get_attr_text(123));
        $this->assertSame("", $Html->get_attr_text(false));
    }
}

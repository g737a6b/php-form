<?php

require(__DIR__."/../autoload.php");

use PHPUnit\Framework\TestCase;
use MofgForm\Mail;

class MailTest extends TestCase
{
    public function test_construct_data()
    {
        $Mail = new MofgForm\Mail();

        $email = "abc@example.com";
        $data = $Mail->construct_data($email, ", ", Mail::FORMAT_ADDRESS);
        $this->assertSame($email, $data);

        $valid = [
            "abc@example.com",
            "a.b.c@example.com",
            "abc@a.b.c.example.com",
            "abc+1-2_3++4--5__6@example.com"
        ];
        $invalid = [
            "a b c@example.com",
            "abcexample.com",
            "abc@example"
        ];
        $emails = array_merge($valid, $invalid);
        $data = $Mail->construct_data($emails, ", ", Mail::FORMAT_ADDRESS);
        $this->assertSame(implode(", ", $valid), $data);
    }

    public function test_construct_data_rejects_header_injection()
    {
        $Mail = new MofgForm\Mail();

        // Test that individual header values with \r or \n are rejected
        $malicious_header_with_r = "From: test@example.com\rBcc: attacker@example.com";
        $result = $Mail->construct_data($malicious_header_with_r, "\n", Mail::FORMAT_HEADER);
        $this->assertSame("", $result, "Header with \\r should be rejected");

        // Test that headers with multiple colons are rejected
        $invalid_header = "From: test@example.com: extra";
        $result = $Mail->construct_data($invalid_header, "\n", Mail::FORMAT_HEADER);
        $this->assertSame("", $result, "Header with multiple colons should be rejected");

        // Test valid headers are accepted
        $valid_headers = ["From: test@example.com", "Reply-To: reply@example.com"];
        $result = $Mail->construct_data($valid_headers, "\n", Mail::FORMAT_HEADER);
        $this->assertSame("From: test@example.com\nReply-To: reply@example.com", $result);
    }

    public function test_construct_data_edge_cases()
    {
        $Mail = new MofgForm\Mail();

        // Empty string
        $result = $Mail->construct_data("", ",", Mail::FORMAT_ADDRESS);
        $this->assertSame("", $result);

        // Empty array
        $result = $Mail->construct_data([], ",", Mail::FORMAT_ADDRESS);
        $this->assertSame("", $result);

        // Non-string, non-array input
        $result = $Mail->construct_data(123, ",", Mail::FORMAT_ADDRESS);
        $this->assertSame(false, $result);

        // Array with non-string elements
        $result = $Mail->construct_data([123, null, "test@example.com"], ",", Mail::FORMAT_ADDRESS);
        $this->assertSame("test@example.com", $result);
    }

    /**
     * @dataProvider provider_for_test_group_header
     */
    public function test_group_header($expected, $header)
    {
        $Mail = new MofgForm\Mail();
        $this->assertSame($expected, $Mail->group_header($header));
    }

    public static function provider_for_test_group_header()
    {
        return [
            [
                "From: test@example.com",
                "From: test@example.com"
            ],
            [
                "From: foo@example.com, bar@example.com",
                "From: foo@example.com\nFrom: bar@example.com"
            ],
            [
                "From: foo@example.com\nReply-To: bar@example.com",
                "From: foo@example.com\nReply-To: bar@example.com"
            ],
            [
                "From: foo@example.com, bar@example.com\nReply-To: baz@example.com, qux@example.com",
                "From: foo@example.com\nFrom: bar@example.com\nReply-To: baz@example.com\nReply-To: qux@example.com"
            ]
        ];
    }

    public function test_group_header_edge_cases()
    {
        $Mail = new MofgForm\Mail();

        // Non-string input
        $result = $Mail->group_header(123);
        $this->assertSame(false, $result);
    }

    public function test_add_to()
    {
        $Mail = new MofgForm\Mail();

        $Mail->add_to("test1@example.com");
        $this->assertSame("test1@example.com", $Mail->get_to());

        $Mail->add_to("test2@example.com");
        $this->assertSame("test1@example.com,test2@example.com", $Mail->get_to());

        $Mail->add_to(["test3@example.com", "test4@example.com"]);
        $this->assertSame("test1@example.com,test2@example.com,test3@example.com,test4@example.com", $Mail->get_to());

        // Test invalid email is filtered out
        $Mail->add_to("invalid-email");
        $this->assertSame("test1@example.com,test2@example.com,test3@example.com,test4@example.com", $Mail->get_to());
    }

    public function test_set_subject()
    {
        $Mail = new MofgForm\Mail();

        $Mail->set_subject("Test Subject");
        $this->assertSame("Test Subject", $Mail->get_subject());

        // Test with non-string input (should be ignored)
        $Mail->set_subject(123);
        $this->assertSame("Test Subject", $Mail->get_subject());
    }

    public function test_set_body()
    {
        $Mail = new MofgForm\Mail();

        $Mail->set_body("Test Body");
        $this->assertSame("Test Body", $Mail->get_body());

        // Test with non-string input (should be ignored)
        $Mail->set_body(null);
        $this->assertSame("Test Body", $Mail->get_body());
    }

    public function test_add_header()
    {
        $Mail = new MofgForm\Mail();

        $Mail->add_header("From: test@example.com");
        $this->assertSame("From: test@example.com", $Mail->get_header());

        $Mail->add_header("Reply-To: reply@example.com");
        $this->assertSame("From: test@example.com\nReply-To: reply@example.com", $Mail->get_header());

        $Mail->add_header(["Cc: cc@example.com", "Bcc: bcc@example.com"]);
        $this->assertSame("From: test@example.com\nReply-To: reply@example.com\nCc: cc@example.com\nBcc: bcc@example.com", $Mail->get_header());

        // Test invalid header is filtered out
        $Mail->add_header("InvalidHeader");
        $this->assertSame("From: test@example.com\nReply-To: reply@example.com\nCc: cc@example.com\nBcc: bcc@example.com", $Mail->get_header());
    }

    public function test_get_to_as_array()
    {
        $Mail = new MofgForm\Mail();

        $Mail->add_to(["test1@example.com", "test2@example.com"]);
        $result = $Mail->get_to(true);

        $this->assertIsArray($result);
        $this->assertSame(["test1@example.com", "test2@example.com"], $result);
    }

    public function test_get_header_as_array()
    {
        $Mail = new MofgForm\Mail();

        $Mail->add_header(["From: test@example.com", "Reply-To: reply@example.com"]);
        $result = $Mail->get_header(true);

        $this->assertIsArray($result);
        $this->assertSame(["From: test@example.com", "Reply-To: reply@example.com"], $result);
    }
}

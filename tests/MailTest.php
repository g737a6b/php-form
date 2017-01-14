<?php
require(__DIR__."/../autoload.php");

use PHPUnit\Framework\TestCase;
use MofgForm\Mail;

class MailTest extends TestCase{
	public function test_construct_data(){
		$Mail = new MofgForm\Mail();

		$email = "abc@example.com";
		$data = $Mail->construct_data($email, ", ", Mail::FORMAT_EMAIL_ADDRESS);
		$this->assertSame($email, $data);

		$valid = array(
			"abc@example.com",
			"a.b.c@example.com",
			"abc@a.b.c.example.com",
			"abc+1-2_3++4--5__6@example.com"
		);
		$invalid = array(
			"a b c@example.com",
			"abcexample.com",
			"abc@example"
		);
		$emails = array_merge($valid, $invalid);
		$data = $Mail->construct_data($emails, ", ", Mail::FORMAT_EMAIL_ADDRESS);
		$this->assertSame(implode(", ", $valid), $data);
	}

	/**
	 * @dataProvider group_header_provider
	 */
	public function test_group_header($expected, $header){
		$Mail = new MofgForm\Mail();
		$this->assertSame($expected, rtrim($Mail->group_header($header), "\n"));
	}

	public function group_header_provider(){
		return array(
			array(
				"From: test@example.com",
				"From: test@example.com"
			),
			array(
				"From: foo@example.com, bar@example.com",
				"From: foo@example.com\nFrom: bar@example.com"
			),
			array(
				"From: foo@example.com\nReply-To: bar@example.com",
				"From: foo@example.com\nReply-To: bar@example.com"
			),
			array(
				"From: foo@example.com, bar@example.com\nReply-To: baz@example.com, qux@example.com",
				"From: foo@example.com\nFrom: bar@example.com\nReply-To: baz@example.com\nReply-To: qux@example.com"
			)
		);
	}
}

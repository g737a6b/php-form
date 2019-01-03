<?php
require(__DIR__."/../autoload.php");

use PHPUnit\Framework\TestCase;
use MofgForm\Mail;

class MailTest extends TestCase{
	public function test_construct_data(){
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

	/**
	 * @dataProvider provider_for_test_group_header
	 */
	public function test_group_header($expected, $header){
		$Mail = new MofgForm\Mail();
		$this->assertSame($expected, $Mail->group_header($header));
	}

	public function provider_for_test_group_header(){
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
}

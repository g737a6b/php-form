<?php
require(__DIR__."/../../autoload.php");

use PHPUnit\Framework\TestCase;
use MOFG_form\Member\Mail;

class Mail_test extends TestCase{
	public function test_construct_data(){
		$Mail = new MOFG_form\Member\Mail();

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
}

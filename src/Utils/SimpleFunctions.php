<?php
// src/Utils/SimpleFunctions.php
namespace App\Utils;

class SimpleFunctions {
	public static function hashPw(string $pw) {
		return password_hash($pw, PASSWORD_BCRYPT, ['cost' => 6, 'salt' => '65432Test_Salt12354cba']);
	}

	public static function checkPw(string $pw,string $hash) {
		return password_verify($pw, $hash);
	}
}
?>
<?php
session_start();
require_once('config.php');
require_once('twitteroauth/Encrypt.php');
header('Content-Type: application/json');

	$key = base64_decode(ENCRYPTION_KEY);
	$iv = base64_decode(IV);
	echo "Key: " . $key;
	echo "\n\nIV: ". $iv;
	$test = new Encrypt($key,$iv,DEFAULT_TIME_ZONE);
	$temp = $test->encrypt("hello world");
	echo "\n\nEncrypted Value: " . $temp;
	echo "\n\nDecrypted Value: " . $test->decrypt($temp);
	echo "\n\n---------\n\nGenerate Key (Base64): " . $test->get_RandomKey();
?>
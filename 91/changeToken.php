<?php
	function getNewToken($token) {
		$replacements = "0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
		$len = strlen($token) - 1;

		$ran_token = rand(0,$len);
		$ran_str = rand(0, strlen($replacements)-1);
		$token[$ran_token] = $replacements[$ran_str];

		return $token;
	}
?>

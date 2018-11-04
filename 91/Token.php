<?php
	function getNewToken($token) {
		$replacements = "0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
		$ran_token = rand(0,44);
		$ran_str = rand(0, 61);
		$token[$ran_token] = $replacements[$ran_str];

		return $token;
	}

	function generateToken() {
		$new_token = "";
		$replacements = "0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
		
		for ($i = 0; $i < 45; $i++) {
			$ran_str = rand(0, 61);
			$new_token .= $replacements[$ran_str];
		}

		return $new_token;
	}
?>

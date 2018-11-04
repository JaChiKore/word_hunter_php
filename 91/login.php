<?php
<<<<<<< HEAD
	include 'Token.php';
=======
	include 'changeToken.php';
>>>>>>> 1fea1ede4c6765e6aa2c7362451b7a4fd6d0b840
	$params = parse_ini_file("../config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

<<<<<<< HEAD
	$username = $_POST['username'];
	$password = $_POST['password'];
	$token = $_POST['token'];
	
	$send_data->debug = "debug info<br>\n";
	
	if (strlen($token) == 45) {
		$result = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '$username';");

		while ($row = mysqli_fetch_assoc($result)) {
			$output[] = $row[id_user];
		}

		$count = count($output);

		if ($count == 1) {
			$result = mysqli_query($conn, "SELECT u.password FROM user u WHERE u.username = '$username';");
			$result = mysqli_fetch_object($result);
			if (sha1($password) == $result->password) {
				$token = getNewToken($token);
				mysqli_query($conn, "UPDATE user SET token = '$token' WHERE username = '$username';");
				$send_data->token = $token;
				$send_data->res = "true";
				$json = json_encode($send_data);
				print($json);
			} else {
				$send_data->token = -1;
				$send_data->res = "false";
				$json = json_encode($send_data);
				print($json);
			}
=======
	$username = $_REQUEST['username'];
	$password = $_REQUEST['password'];
	$token = $_REQUEST['token'];

	$result = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '$username';");

	while ($row = mysqli_fetch_assoc($result)) {
		$output[] = $row[id_user];
	}

	$count = count($output);

	if ($count == 1) {
		$result = mysqli_query($conn, "SELECT u.password FROM user u WHERE u.username = '$username';");
		$result = mysqli_fetch_assoc($result);
		if (sha1($password) == $result[password]) {
			$token = getNewToken($token);
			mysqli_query($conn, "UPDATE user SET token = '$token' WHERE username = '$username';");
			$send_data->token = $token;
			$send_data->res = "true";
			$json = json_encode($send_data);
			print($json);
>>>>>>> 1fea1ede4c6765e6aa2c7362451b7a4fd6d0b840
		} else {
			$send_data->token = -1;
			$send_data->res = "false";
			$json = json_encode($send_data);
			print($json);
		}
	} else {
<<<<<<< HEAD
		$send_data->debug = "404 Not Found<br>\n";
=======
		$send_data->token = -1;
>>>>>>> 1fea1ede4c6765e6aa2c7362451b7a4fd6d0b840
		$send_data->res = "false";
		$json = json_encode($send_data);
		print($json);
	}

	mysqli_close();

?>

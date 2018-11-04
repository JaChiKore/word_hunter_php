<?php
	include 'Token.php';
	$params = parse_ini_file("../config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

<<<<<<< HEAD
	$username = $_POST['username'];
	$password = $_POST['password'];
	$token = $_POST['token'];
=======
	$username = $_REQUEST['username'];
	$password = $_REQUEST['password'];
	$token = $_REQUEST['token'];

	$result = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '$username';");
>>>>>>> 1fea1ede4c6765e6aa2c7362451b7a4fd6d0b840
	
	$send_data->debug = "debug info<br>\n";
	
	if (strlen($token) == 45) {
		$result = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '$username';");
		
		while ($row = mysqli_fetch_assoc($result)) {
			$output[] = $row[id_user];
		}

		$count = count($output);

		if ($count == 0) {
			$password = sha1($password);
			$token = getNewToken($token);
			mysqli_query($conn, "INSERT INTO user(username, password, token) VALUES ('$username', '$password','$token')");
			$result = mysqli_query($conn, "SELECT MAX(id_user) AS id_user FROM user WHERE username = '$username';");
			$row = mysqli_fetch_object($result);
			mysqli_query($conn, "INSERT INTO user_task(id_user, id_task, max_score, level) VALUES ('$row->id_user', 1, 0, 1)");
			mysqli_query($conn, "INSERT INTO user_task(id_user, id_task, max_score, level) VALUES ('$row->id_user', 2, 0, 1)");

<<<<<<< HEAD
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
	if ($count == 0) {
		$password = sha1($password);
		mysqli_query($conn, "INSERT INTO user(username, password, token) VALUES ('$username', '$password','$token')");
		$result = mysqli_query($conn, "SELECT MAX(id_user) AS id_user FROM user WHERE username = '$username';");
		$row = mysqli_fetch_object($result);
		mysqli_query($conn, "INSERT INTO user_task(id_user, id_task, max_score, level) VALUES ('$row->id_user', 1, 0, 1)");
		mysqli_query($conn, "INSERT INTO user_task(id_user, id_task, max_score, level) VALUES ('$row->id_user', 2, 0, 1)");
		print('true');
>>>>>>> 1fea1ede4c6765e6aa2c7362451b7a4fd6d0b840
	} else {
		$send_data->debug = "404 Not Found<br>\n";
		$send_data->res = "false";
		$json = json_encode($send_data);
		print($json);
	}

	mysqli_close();
?>

<?php
	$params = parse_ini_file("../config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$username = $_REQUEST['username'];
	$password = $_REQUEST['password'];

	$result = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '$username';");

	while ($row = mysqli_fetch_assoc($result)) {
		$output[] = $row[id_user];
	}

	$count = count($output);

	if ($count == 1) {
		$result = mysqli_query($conn, "SELECT u.password FROM user u WHERE u.username = '$username';");
		$result = mysqli_fetch_assoc($result);
		if (sha1($password) == $result[password]) {
			print('true');
		} else {
			print('false');
		}
	} else {
		print('false');
	}

	mysqli_close();

?>

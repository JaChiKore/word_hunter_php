<?php
	$params = parse_ini_file("./config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$filename = $_REQUEST['filename'];
	$trans = $_REQUEST['trans'];
	if ($trans == 0) {
		$trans = "none_of_these";
	}
	$user = $_REQUEST['user'];
	$level = $_REQUEST['level'];
	$startDate = $_REQUEST['startDate'];
	$endDate = $_REQUEST['endDate'];
	$usedTime = $_REQUEST['usedTime'];
	$scoreInici = $_REQUEST['scoreInici'];
	$scoreFinal = $_REQUEST['scoreFinal'];

	$res = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '$user';");
	$res = mysqli_fetch_object($res);
	$id_user = $res->id_user;

	$res = mysqli_query($conn, "SELECT id_batch, id_image FROM batch b INNER JOIN batch_image bi INNER JOIN image i ON b.id_batch = bi.id_batch AND bi.id_image = i.id_image WHERE b.id_task = '1' AND i.name_croppped_image = '$filename';");
	$res = mysqli_fetch_object($res);
	$id_image = $res->id_image;
	$id_batch = $res->id_batch;

	mysqli_query($conn, "INSERT INTO round(initial_score, final_score, used_time, id_batch, id_user) VALUES ('$scoreFinal', '$scoreFinal', '$usedTime', '$id_batch', '$id_user')");

	$res = mysqli_query($conn, "SELECT LAST_INSERT_ID() as last;");
	$res = mysqli_fetch_object($res);
	$id_round = $res->last;

	$result = mysqli_query($conn, "INSERT INTO answer_user(id_user, id_round, id_image, answer) VALUES ('$id_user', '$id_round', '$id_image', '$trans');");

	if ($result != False) {
		print('True');
	} else {
		print('False');	
	}
	
	mysqli_close();
?>

<?php
	$params = parse_ini_file("../config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$filename = $_POST['filename'];
	$trans = $_POST['trans'];
	if ($trans == "0") {
		$trans = "none_of_these";
	}
	$user = $_POST['user'];
	$level = $_POST['level'];
	$startDate = $_POST['startDate'];
	$endDate = $_POST['endDate'];
	$usedTime = $_POST['usedTime'];
	$scoreInici = $_POST['scoreInici'];
	$scoreFinal = $_POST['scoreFinal'];
	$token = $_POST['token'];

	$send_data->debug = "debug info<br>\n";

	if (strlen($token) == 45) {
		$res = mysqli_query($conn, "SELECT token FROM user WHERE username = '$user';");
		$res = mysqli_fetch_object($res);
		$db_token = $res->token;
		
		if ($db_token == $token) {
			$res = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '$user';");
			$res = mysqli_fetch_object($res);
			$id_user = $res->id_user;

			$res = mysqli_query($conn, "SELECT bi.id_batch AS id_batch, bi.id_image AS id_image FROM batch b INNER JOIN batch_image bi INNER JOIN image i ON b.id_batch = bi.id_batch AND bi.id_image = i.id_image WHERE b.id_task = '1' AND i.name_cropped_image = '$filename';");
			$res = mysqli_fetch_object($res);
			$id_image = $res->id_image;
			$id_batch = $res->id_batch;

			$res = mysqli_query($conn, "SELECT id_round FROM round WHERE start_date = STR_TO_DATE('$startDate','%Y%m%d %H%i%s') AND end_date = STR_TO_DATE('$endDate','%Y%m%d %H%i%s') AND id_user = '$id_user' AND id_batch = '$id_batch' AND initial_score = '$scoreInici' AND final_score = '$scoreFinal' LIMIT 1;");
			$res = mysqli_fetch_object($res);
			if (!$res) {
				mysqli_query($conn, "INSERT INTO round(initial_score, final_score, used_time, id_batch, id_user, start_date, end_date) VALUES ('$scoreInici', '$scoreFinal', '$usedTime', '$id_batch', '$id_user', STR_TO_DATE('$startDate','%Y%m%d %H%i%s'), STR_TO_DATE('$endDate','%Y%m%d %H%i%s'))");

				$res = mysqli_query($conn, "SELECT LAST_INSERT_ID() as last;");
				$res = mysqli_fetch_object($res);
				$id_round = $res->last;
			} else {
				$id_round = $res->id_round;
			}

			$result = mysqli_query($conn, "INSERT INTO answer_user(id_user, id_round, id_image, answer) VALUES ('$id_user', '$id_round', '$id_image', '$trans');");

			if ($result != False) {
				$send_data->res = "true";
				$json = json_encode($send_data);
				print($json);
			} else {
				$send_data->res = "false";
				$json = json_encode($send_data);
				print($json);
			}
		} else {
			$send_data->debug = "404 Not Found<br>\n";
			$send_data->res = "false";
			$json = json_encode($send_data);
			print($json);
		}
	} else {
		$send_data->debug = "404 Not Found<br>\n";
		$send_data->res = "false";
		$json = json_encode($send_data);
		print($json);
	}
	
	mysqli_close();
?>

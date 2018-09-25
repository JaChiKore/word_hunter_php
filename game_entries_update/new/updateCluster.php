<?php
	$params = parse_ini_file("./config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$filename = $_REQUEST['filename'];
	$cluster = $_REQUEST['cluster'];
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

	$result = mysqli_query($conn, "SELECT id_image FROM image_cluster WHERE id_cluster = '$cluster';");
	foreach ($result as $key => $res) {
		$id_images[] = $res['id_image'];
	}

	$res = mysqli_query($conn, "SELECT id_batch FROM batch_image WHERE id_validation = '$cluster' LIMIT 1;");
	$res = mysqli_fetch_object($res);
	$id_batch = $res->id_batch;

	mysqli_query($conn, "INSERT INTO round(initial_score, final_score, used_time, id_batch, id_user) VALUES ('$scoreFinal', '$scoreFinal', '$usedTime', '$id_batch', '$id_user')");

	$res = mysqli_query($conn, "SELECT LAST_INSERT_ID() as last;");
	$res = mysqli_fetch_object($res);
	$id_round = $res->last;

	$count = count($id_images);

	if ($filename == "eq") {
		#insert 'eq' in answer foreach image
		$answer = "eq";
		for($i = 0; $i < $count; $i++) {
			$id_image = $id_images[$count];
			mysqli_query($conn, "INSERT INTO answer_user(id_user, id_round, id_image, answer) VALUES ('$id_user', '$id_round', '$id_image', '$answer');");
		}
	} else if ($filename == "diff") {
		#insert 'diff' in answer foreach image
		$answer = "diff";
		for($i = 0; $i < $count; $i++) {
			$id_image = $id_images[$count];
			mysqli_query($conn, "INSERT INTO answer_user(id_user, id_round, id_image, answer) VALUES ('$id_user', '$id_round', '$id_image', '$answer');");
		}
	} else {
		#insert '0/1' in answer where 1 is the different one
		$res = mysqli_query($conn, "SELECT id_image FROM image WHERE name_cropped_image = '$filename';");
		$res = mysqli_fetch_object($res);
		$id = $res->id_image;

		for($i = 0; $i < $count; $i++) {
			$id_image = $id_images[$count];
			$answer = 0;
			if ($id_image == $id) {
				$answer = 1;
			}
			$result = mysqli_query($conn, "INSERT INTO answer_user(id_user, id_round, id_image, answer) VALUES ('$id_user', '$id_round', '$id_image', '$answer');");
		}
	}

	if ($result != False) {
		print('True');
	} else {
		print('False');	
	}
	
	mysqli_close();
?>

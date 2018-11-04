<?php
	$params = parse_ini_file("../config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$username = $_POST['username'];

	if ($username == NULL) {
		$result = mysqli_query($conn, "SELECT u.username AS username, ut.max_score AS max_score FROM user u INNER JOIN user_task ut ON u.id_user = ut.id_user WHERE ut.id_task = 1 ORDER BY ut.max_score DESC LIMIT 10;");

		while ($row = mysqli_fetch_assoc($result)) {
			$output1[] = $row[username].','.$row[max_score];
		}

		$result = mysqli_query($conn, "SELECT u.username AS username, ut.max_score AS max_score FROM user u INNER JOIN user_task ut ON u.id_user = ut.id_user WHERE ut.id_task = 2 ORDER BY ut.max_score DESC LIMIT 10;");
		while ($row = mysqli_fetch_assoc($result)) {
			$output2[] = $row[username].','.$row[max_score];
		}

		$out1 = join('<br>',$output1);
		$out2 = join('<br>',$output2);
		$out = $out1.'separator<br>'.$out2;

		$send_data->res = $out;
		$json = json_encode($send_data);
		print($json);
	} else {
		$result = mysqli_query($conn, "SELECT ut.max_score AS max_score, ut.level AS level FROM user u INNER JOIN user_task ut ON u.id_user = ut.id_user WHERE u.username = '$username' AND ut.id_task = 1;");
		$match = mysqli_fetch_object($result);
		$result = mysqli_query($conn, "SELECT ut.max_score AS max_score, ut.level AS level FROM user u INNER JOIN user_task ut ON u.id_user = ut.id_user WHERE u.username = '$username' AND ut.id_task = 2;");
		$diff = mysqli_fetch_object($result);
		$out = $match->max_score.','.$diff->max_score.','.$match->level.','.$diff->level;
		
		$send_data->res = $out;
		$json = json_encode($send_data);
		print($json);
	}

	mysqli_close();

?>

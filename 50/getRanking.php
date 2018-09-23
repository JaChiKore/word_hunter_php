<?php
	$params = parse_ini_file("./config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$username = $_REQUEST['username'];

	if ($username == NULL) {
		$result = mysqli_query($conn, "SELECT u.username AS username, ut.max_score AS max_score FROM user u INNER JOIN user_task ut ON u.id_user = ut.id_user WHERE ut.id_task = 1 ORDER BY ut.max_score DESC LIMIT 10;");

		while ($row = mysqli_fetch_assoc($result)) {
			$output1[] = $row[username].','.$row[max_score];
		}

		$result = mysqli_query($conn, "SELECT u.username AS username, ut.max_score AS max_score FROM user u INNER JOIN user_task ut ON u.id_user = ut.id_user WHERE ut.id_task = 2 ORDER BY ut.max_score DESC LIMIT 10;");
		while ($row = mysqli_fetch_assoc($result)) {
			$output2[] = $row[username].','.$row[max_score];
		}

		$count = count($output1);

		for ($i = 0; $i < $count; $i++) {
			print($output1[$i].'<br>');
		}
		print('separator<br>');
		for ($i = 0; $i < $count; $i++) {
			print($output2[$i].'<br>');
		}
	} else {
		$result = mysqli_query($conn, "SELECT ut.max_score AS max_score, ut.level AS level FROM user u INNER JOIN user_task ut ON u.id_user = ut.id_user WHERE u.username = '$username' AND ut.id_task = 1;");
		$match = mysqli_fetch_object($result);
		$result = mysqli_query($conn, "SELECT ut.max_score AS max_score, ut.level AS level FROM user u INNER JOIN user_task ut ON u.id_user = ut.id_user WHERE u.username = '$username' AND ut.id_task = 2;");
		$diff = mysqli_fetch_object($result);
		print($match->max_score.','.$diff->max_score.','.$match->level.','.$diff->level);
	}

	mysqli_close();

?>

<?php
	$params = parse_ini_file("./config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$username = $_REQUEST['username'];

	if ($username == NULL) {
		$result = mysqli_query($conn, "SELECT username, max_score FROM user_task WHERE id_task = 1 ORDER BY max_score DESC LIMIT 10;");

		while ($row = mysqli_fetch_assoc($result)) {
			$output1[] = $row[username].','.$row[max_score];
		}

		$result = mysqli_query($conn, "SELECT username, max_score FROM user_task WHERE id_task = 2 ORDER BY max_score DESC LIMIT 10;");
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
		$result = mysqli_query($conn, "SELECT ut.max_score FROM user_task ut WHERE ut.username = '".$username."' AND ut.id_task = 1;");
		$match = mysqli_fetch_assoc($result);
		$result = mysqli_query($conn, "SELECT ut.max_score FROM user_task ut WHERE ut.username = '".$username."' AND ut.id_task = 2;");
		$diff = mysqli_fetch_assoc($result);
		print($match[max_score].','.$diff[max_score]);
	}

	mysqli_close();

?>

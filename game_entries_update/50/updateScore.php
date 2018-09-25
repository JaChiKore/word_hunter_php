<?php
	$params = parse_ini_file("./config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$username = $_REQUEST['username'];
	$score1 = $_REQUEST['match_game'];
	$score2 = $_REQUEST['difference_game'];
	$level1 = $_REQUEST['match_level'];
	$level2 = $_REQUEST['difference_level'];

	$result = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '".$username."';");
	
	while ($row = mysqli_fetch_assoc($result)) {
		$output[] = $row[id];
	}

	$count = count($output);

	if ($count == 0) {
		print('false');
	} else {
		$result = mysqli_query($conn, "UPDATE user_task SET max_score = '".$score1."' AND level = '".$level1."' WHERE id_task = 1 AND id_user = '".$output[0]."';");
		$result = mysqli_query($conn, "UPDATE user_task SET max_score = '".$score2."' AND level = '".$level2."' WHERE id_task = 2 AND id_user = '".$output[0]."';");
		print('true');
	}
	mysqli_close();

?>

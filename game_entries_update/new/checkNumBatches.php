<?php
	$params = parse_ini_file("./config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	
	$game = $_REQUEST['game'];
	
	if ($game == '1') { //Transcription game check
		$result = mysqli_query($conn, "SELECT COUNT(id_batch) num FROM batch WHERE active = '1' AND id_task = '1';");
	} else { // Cluster game check
		$result = mysqli_query($conn, "SELECT COUNT(id_batch) num FROM batch WHERE active = '1' AND id_task = '2';");
	}
	
	$row = mysqli_fetch_assoc($result);

	print(($row['num']));

	mysqli_close();
?>

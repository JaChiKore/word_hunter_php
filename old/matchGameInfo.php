<?php

	function make_seed()
	{
		  list($usec, $sec) = explode(' ', microtime());
	    	return $sec + $usec * 1000000;
	}

	$params = parse_ini_file("./config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	mt_srand(make_seed());
	$randval = mt_rand();

	$username = $_REQUEST['username'];

	$result = mysqli_query($conn, "SELECT level FROM user u INNER JOIN user_task ut ON u.id_user = ut.id_user WHERE ut.id_task = 1 AND u.username = '$username';");
	$result = mysqli_fetch_object($result);
	if ($result->level >= 3) {
		if ($result->level <= 4) {
			for ($i = $result->level; $i < 5; $i++) {
				$goldens = 10 - $i*2;
				$result = mysqli_query($conn, "SELECT id_batch FROM batch WHERE active = '1' AND id_task = '1' AND golden_tasks = '$goldens' ORDER BY used_times, RAND(".$randval.") LIMIT 1;");
				$result = mysqli_fetch_object($result);
				$id_batches[] = $result->id_batch;
			}
			$limit = 3 - count($id_batches);
		} else {
			$limit = 3;
		}
		$result = mysqli_query($conn, "SELECT id_batch FROM batch WHERE active = '1' AND id_task = '1' AND golden_tasks = 0 ORDER BY used_times, RAND(".$randval.") LIMIT '$limit';");
		foreach ($result as $key => $batch) {
			$id_batches[] = $batch;
		}
		$count = count($id_batches);
		for ($i = 0; $i < $count; $i++) {
			$id_batch = $id_batches[$i];
			mysqli_query($conn, "UPDATE batch SET used_times = used_times + 1 WHERE id_batch = '$id_batch';");
			$out = mysqli_query($conn, "SELECT id_image FROM batch_image WHERE id_batch = '$id_batch' ORDER BY RAND(".$randval.");");
			foreach ($out as $key => $im) {
				$image_ids[] = $im[id_image];
			}
		}

	} else {
		for ($i = $result->level; $i < $result->level + 3; $i++) {
			$goldens = 10 - $i*2;
			$result = mysqli_query($conn, "SELECT id_batch FROM batch WHERE active = '1' AND id_task = '1' AND golden_tasks = '$goldens' ORDER BY used_times, RAND(".$randval.") LIMIT 1;");
			$result = mysqli_fetch_object($result);
			$id_batches[] = $result->id_batch;
		}
		$count = count($id_batches);
		for ($i = 0; $i < $count; $i++) {
			$id_batch = $id_batches[$i];
			mysqli_query($conn, "UPDATE batch SET used_times = used_times + 1 WHERE id_batch = '$id_batch';");
			$out = mysqli_query($conn, "SELECT id_image FROM batch_image WHERE id_batch = '$id_batch' ORDER BY RAND(".$randval.");");
			foreach ($out as $key => $im) {
				$image_ids[] = $im[id_image];
			}
		}
	}

	$count = count($image_ids);

	for ($i = 0; $i < $count; $i++) {
		$result = mysqli_query($conn, "SELECT i.name_cropped_image, t.transcription, ti.state FROM image i INNER JOIN transcription t INNER JOIN task_image ti ON i.id_image = t.id_image AND i.id_image = ti.id_image AND t.id_transcription = ti.id_validation WHERE id_image = ".$image_ids[$i]." AND id_task = 1");

		while ($row = mysqli_fetch_assoc($result)) {
			if ($row[state] == 6) {
				$output = $row[name_cropped_image].";".$row[transcription].";1;1";
			} else if ($row[state] == 11) {
				$output = $row[name_cropped_image].";".$row[transcription].";1;0";
			}else {
				$output = $row[name_cropped_image].";".$row[transcription].";0;0";
			}
		}
	}

	$count = count($output);

	for($i = 0; $i < $count; $i++) {
		print($output[$i].'<br>');
	}

	mysqli_close($conn);
?>

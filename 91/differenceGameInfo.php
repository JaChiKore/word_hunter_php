<?php

	function make_seed()
	{
		  list($usec, $sec) = explode(' ', microtime());
	    	return $sec + $usec * 1000000;
	}

	$params = parse_ini_file("../config.ini");
	$conn = mysqli_connect($params['hostname'],$params['username'],$params['password'],$params['db_name']);

	if (mysqli_connect_errno($conn)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	mt_srand(make_seed());
	$randval = mt_rand();

	$username = $_REQUEST['username'];

	$result = mysqli_query($conn, "SELECT ut.level FROM user u INNER JOIN user_task ut ON u.id_user = ut.id_user WHERE ut.id_task = 2 AND u.username = '$username';");
	$result = mysqli_fetch_object($result);
	if ($result->level >= 4) {
		if ($result->level == 4) {
			$result = mysqli_query($conn, "SELECT id_batch FROM batch WHERE active = '1' AND id_task = '2' AND golden_tasks = 2 ORDER BY used_times, RAND(".$randval.") LIMIT 1;");
			$result = mysqli_fetch_object($result);
			$id_batches[] = $result->id_batch;
			$limit = 1;
		} else {
			$limit = 2;
		}
		$result = mysqli_query($conn, "SELECT id_batch FROM batch WHERE active = '1' AND id_task = '2' AND golden_tasks = 0 ORDER BY used_times, RAND(".$randval.") LIMIT 2;");
		for ($i = 0; $i < $limit; $i++) {
			$row = mysqli_fetch_row($result);
			$id_batches[] = $row[0];
		}
		for ($i = 0; $i < 2; $i++) {
			$id_batch = $id_batches[$i];
			mysqli_query($conn, "UPDATE batch SET used_times = used_times + 1 WHERE id_batch = '$id_batch';");
			$out2 = mysqli_query($conn, "SELECT id_image FROM batch_image WHERE id_batch = '$id_batch' ORDER BY id_validation, RAND(".$randval.");");
			foreach ($out2 as $key => $im) {
				$image_ids[] = $im[id_image];
			}
		}
	} else {
		for ($i = $result->level; $i < $result->level + 3; $i++) {
			$goldens = 10 - $i*2;
			$result = mysqli_query($conn, "SELECT id_batch FROM batch WHERE active = '1' AND id_task = '2' AND golden_tasks = '$goldens' ORDER BY used_times, RAND(".$randval.") LIMIT 1;");
			$result = mysqli_fetch_object($result);
			$id_batches[] = $result->id_batch;
		}
		$count = count($id_batches);
		for ($i = 0; $i < $count; $i++) {
			$id_batch = $id_batches[$i];
			mysqli_query($conn, "UPDATE batch SET used_times = used_times + 1 WHERE id_batch = '$id_batch';");
			$out2 = mysqli_query($conn, "SELECT id_image FROM batch_image WHERE id_batch = '$id_batch' ORDER BY id_validation, RAND(".$randval.");");
			foreach ($out2 as $key => $im) {
				$image_ids[] = $im[id_image];
			}
		}
	}
	foreach ($image_ids as $key => $id_image) {
		$result = mysqli_query($conn, "SELECT i.name_cropped_image, c.id_cluster, ti.state, bi.id_batch FROM image i INNER JOIN image_cluster c INNER JOIN task_image ti INNER JOIN batch_image bi ON i.id_image = c.id_image AND i.id_image = ti.id_image AND c.id_cluster = ti.id_validation AND bi.id_image = i.id_image AND bi.id_validation = c.id_cluster WHERE i.id_image = '$id_image';");
		$row = mysqli_fetch_assoc($result);
		if ($row[state] != 7 && $row[state] != 8) {
			$output[] = $row[name_cropped_image].";".$row[id_cluster].";0;0";
		} else if ($row[state] == 7) {
			$output[] = $row[name_cropped_image].";".$row[id_cluster].";1;1";
		} else {
			$output[] = $row[name_cropped_image].";".$row[id_cluster].";0;1";
		}
	}

	$count = count($output);
	for ($i = 0; $i < $count; $i++) {
		print($output[$i].'<br>');
	}
	mysqli_close($conn);
?>

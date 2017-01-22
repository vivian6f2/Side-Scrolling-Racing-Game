<?php
	require_once("connect.php");
	$db->exec('SET CHARACTER SET utf8');
	$db->query("SET NAMES utf8");
	$map_data = $_POST["map_data"];
	$object_data = $_POST["object_data"];
	$map_name = $_POST["map_name"];
	//save map_name into project_MAP first
	//datetime = 'YYYY-MM-DD HH:MM:SS
	date_default_timezone_set('Asia/Taipei');
	$time = date('Y-m-d H:i:s');
	$sql = "INSERT INTO project_MAP(id, name, rate, rate_time, time)
			VALUES(NULL, ?, ?, ?, ?)";
	$sth = $db->prepare($sql);
	$sth->execute(array($map_name, 3.0, 1, $time));
	//get map id
	$sql = "SELECT * FROM project_MAP WHERE name = ? AND time = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($map_name, $time));
	$row = $sth->fetchObject();
	$mid = $row->id;
	
	//save map data into project_MAP_DATA
	$decode_map = json_decode($map_data);
	foreach ($decode_map as $mp){
		$sql = "INSERT INTO project_MAP_DATA(id, mid, x, y, item)
				VALUES(NULL, ?, ?, ?, ?)";
		$sth = $db->prepare($sql);
		$sth->execute(array($mid, $mp->x, $mp->y, $mp->item));
	}
	//save map data into project_MAP_OBJECT
	$decode_map = json_decode($object_data);
	foreach ($decode_map as $mp){
		$sql = "INSERT INTO project_MAP_OBJECT(id, mid, x, y, item)
				VALUES(Null, ?, ?, ?, ?)";
		$sth = $db->prepare($sql);
		$sth->execute(array($mid, $mp->x, $mp->y, $mp->item));
	}
	echo $mid;
	$db=null;
?>
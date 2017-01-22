<?php
	require_once("connect.php");
	$db->exec('SET CHARACTER SET utf8');
	$db->query("SET NAMES utf8");
	$mid = $_POST["mid"];
	$add_type = $_POST["add_type"];
	$sql = "SELECT * FROM project_MAP WHERE id = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	$row = $sth->fetchObject();
	if($add_type == 'die'){
		$die_time = $row->die_time + 1;
		$win_time = $row->win_time;
		$die_rate = $die_time / ($win_time + $die_time);
		$sql = "UPDATE project_MAP SET die_time = ?, die_rate = ? WHERE id = ?";
		$sth = $db->prepare($sql);
		$sth->execute(array($die_time,$die_rate,$mid));
		echo $die_time;
	}
	else if($add_type == 'win'){
		$win_time = $row->win_time + 1;
		$die_time = $row->die_time;
		$die_rate = $die_time / ($win_time + $die_time);
		$sql = "UPDATE project_MAP SET win_time = ?, die_rate = ? WHERE id = ?";
		$sth = $db->prepare($sql);
		$sth->execute(array($win_time,$die_rate,$mid));
		echo $win_time;
	}
	$db=null;
?>
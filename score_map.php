<?php
	require_once("connect.php");
	$db->exec('SET CHARACTER SET utf8');
	$db->query("SET NAMES utf8");
	$mid = $_POST["mid"];
	$score = $_POST["score"];
	$sql = "SELECT * FROM project_MAP WHERE id = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	$row = $sth->fetchObject();
	$rate = $row->rate;
	$rate_time = $row->rate_time + 1;
	
	$rate = ($rate * $row->rate_time + $score) / $rate_time;
	
	$sql = "UPDATE project_MAP SET rate = ?, rate_time = ? WHERE id = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($rate,$rate_time,$mid));
	
	echo $rate;
	$db=null;
?>
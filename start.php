<!DOCTYPE html>
<!--
	author: Lin Ting Hsuan
	date: 2016/3/22
	student ID: 0116328
-->
<html>
	<head>
		<meta charset="utf-8">
		<title>Project</title>
		<link href="gamestyle.css" rel="stylesheet" type="text/css"/>
		<script type="text/javascript" src="click_function.js"></script>
	</head>
	<body>
		<div id="detailPage" class="detailPage">
			<div class="closeButton" onclick="closeDetail()"> </div>
			<h4>Javascript & PHP Project</h4>
			<h3 style="margin-left:40px;">
			Student ID : 0116328<br>
			How to play :<br>
			<img src="image/wasd.png" style="width:150px;">
			<img src="image/uldr.png" style="width:150px;">
			<img src="image/spacebar.png" style="width:300px;"></h3>
			<a href="start.php" target="_self" class="button">Back to Start</a>
			<a href="http://people.cs.nctu.edu.tw/~tihlin/hw1-0116328-林亭萱.html" target="_self" class="button">Home Page</a>
		</div>
		<header><!-- header area -->
			<div style="height:40px; width:40px; float:left; margin-left: 10px; margin-top: 10px;"> </div>
			<h1>CATVENTURE</h1>
			<div class="option" onclick="clickOption()"> </div>
		</header>
		<div id="content">
			<a href="menu.php" target="_self" class="bigButton" style="margin-top:200px;">START</a>
		</div>
		<?php
			require_once("header_footer.class.php");
			$project = new project();
			$project->output_footer();
		?>
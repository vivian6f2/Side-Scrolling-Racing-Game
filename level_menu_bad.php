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
		<script type="text/javascript">
			function changeOrderMethod(){
				var method = document.getElementById("order_type").value;
				var new_html = 'http://people.cs.nctu.edu.tw/~tihlin/project/level_menu_bad.php?order='+method;
				location.replace(new_html);
			}
		</script>
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
		<header>
			<a href="menu.php" target="_self" class="back">B</a>
			<h1>LEVEL</h1>
			<div class="option" onclick="clickOption()"> </div>
		</header>
		<div id="content">
		<?php
			echo '<br>';
			$page = $_GET['page'];
			$order_type = "time";
			if (isset($_GET["order"])) $order_type = $_GET['order'];
			if($page==null) $page=1;
			require_once("connect.php");
			//kill the bad game that is exist too long
			$sql = "DELETE FROM project_MAP WHERE time < date(now()-interval 30 day) AND rate < 1.5";
			$sth = $db->prepare($sql);
			$sth->execute();
			
			if($order_type == "time")
				$sql = "SELECT * FROM project_MAP WHERE rate < 1.5 ORDER BY time DESC";
			else if($order_type == "rate")
				$sql = "SELECT * FROM project_MAP WHERE rate < 1.5 ORDER BY rate DESC";
			else if($order_type == "pnum")
				$sql = "SELECT * FROM project_MAP WHERE rate < 1.5 ORDER BY rate_time DESC";
			else if($order_type == "die_rate")
				$sql = "SELECT * FROM project_MAP WHERE rate < 1.5 ORDER BY die_rate DESC";
			$sth = $db->prepare($sql);
			$sth->execute();
			$count = $sth->rowCount();
			$column_count = 0;
			$row_count = 0;
			echo '<div>排序方式 : <select name="order_type" id="order_type" size="1" onChange="changeOrderMethod()">';
			if($order_type=="time") echo '<option value="time" selected>建立時間(新->舊)</option>';
			else echo '<option value="time">建立時間(新->舊)</option>';
			if($order_type=="rate") echo '<option value="rate" selected>平均分數(高->低)</option>';
			else echo '<option value="rate">平均分數(高->低)</option>';
			if($order_type=="pnum") echo '<option value="pnum" selected>評分人數(高->低)</option>';
			else echo '<option value="pnum">評分人數(高->低)</option>';
			if($order_type=="die_rate") echo '<option value="die_rate" selected>死亡率(高->低)</option>';
			else echo '<option value="die_rate">死亡率(高->低)</option>';
			echo '</select>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;<a href="level_menu.php" target="_self" class="button">正常關卡</a>';
			echo '</div>';
			echo '<div><div id="makeleft">';
			if ($page>1) echo '<a href="level_menu_bad.php?page='.($page-1).'&order='.$order_type.'" target="_self" class="left" style="margin-top:190px; margin-left:30px;">l</a>';
			else echo '&nbsp;';
			echo '</div>';
			
			echo '<div id="makecenter">';
			echo '<table id="level_menu">';
			if($page>1){
				for($i=0;$i<($page-1)*18;$i++){
					$row = $sth->fetchObject();
				}
			}
			
			while($row = $sth->fetchObject()){
				if($column_count == 0) echo '<tr>';
				echo '<td id="level_menu_td">'.$row->name.'<br><a href="play_game.php?mid='.$row->id.'" class="button">Play</a>'.'<br>Rate: '.number_format($row->rate, 1).'<br>Die: '.number_format($row->die_rate*100, 0).'%</td>';
				$column_count++;
				if($column_count == 6){
					$column_count = 0;
					$row_count++;
					echo '</tr>';
					if($row_count == 3) break;
				}
			}
            if($column_count==0) $row_count--;
            if($column_count!=0){
				for($i=6;$i-$column_count>0;$i--){
					echo '<td id="level_menu_null_td"></td>';
				}
				echo '</tr>';
			}
			if($row_count==0){ //echo '<tr></tr><tr></tr>';
				echo '<tr>';
				for($i=0;$i<6;$i++){
					echo '<td id="level_menu_null_td"></td>';
				}
				echo '</tr>';
				echo '<tr>';
				for($i=0;$i<6;$i++){
					echo '<td id="level_menu_null_td"></td>';
				}
				echo '</tr>';
			}
			else if($row_count==1){ //echo '<tr></tr>';
				echo '<tr>';
				for($i=0;$i<6;$i++){
					echo '<td id="level_menu_null_td"></td>';
				}
				echo '</tr>';
            }
			echo '</table></div>';
			echo '<div id="makeright">';
			if ($count > ($page*18)) echo '<a href="level_menu_bad.php?page='.($page+1).'&order='.$order_type.'" target="_self" class="right" style="margin-top:190px; margin-left:30px;">r</a>';
			else echo '&nbsp;';
			echo '</div></div>';
			echo '<div style="clear:both;"></div>';
			echo $page;
			//echo '<div style="clear:both;"><h2 style="line-height:20px;">'.$page.'</h2></div>';
			$db=null;
		?>
		</div>
		<?php
			require_once("header_footer.class.php");
			$project = new project();
			$project->output_footer();
		?>
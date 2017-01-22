<?php
	class project{
		private $header= <<<EOF
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
				<script type="text/javascript">
					function clickOption(){
						document.all.detailPage.style.display="inline";
					}
					function closeDetail(){
						document.all.detailPage.style.display="none";
					}
				</script>
			</head>
			<body>
				<div id="detailPage" class="detailPage">
					<div class="closeButton" onclick="closeDetail()"> </div>
					<br>
					<h3>Javascript & PHP Project<br>
					學號:0116328<br>
					操作模式:wasd or ↑←↓→<br></h3>
					<a href="start.php" target="_self" class="button">回開始畫面</a>
				</div>
EOF
;
		private $footer= <<<EOF
		
		<footer>
			<h2>javascript & php project&nbsp;&nbsp;&nbsp;by 林亭萱(0116328)</h2>
		</footer>
	</body>
</html>
EOF
;
		function output_header(){
			echo $this->header;
		}
		function output_footer(){
			echo $this->footer;
		}
	}
?>
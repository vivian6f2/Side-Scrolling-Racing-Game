<!DOCTYPE html>
<!--
	author: Lin Ting Hsuan
	date: 2016/2/23
	student ID: 0116328
-->
<?php
	//get map data
	$mid = $_GET['mid'];
	$map_name = "";
	$map_data = array();
	$object_data = array();
	require_once("connect.php");
	$sql = "SELECT * FROM project_TRY_MAP WHERE id = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	$row = $sth->fetchObject();
	$map_name = $row->name;
	$sql = "SELECT * FROM project_TRY_MAP_DATA WHERE mid = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	while($row = $sth->fetchObject()){
		array_push($map_data,$row);
	}
	$sql = "SELECT * FROM project_TRY_MAP_OBJECT WHERE mid = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	while($row = $sth->fetchObject()){
		array_push($object_data,$row);
	}
	//echo $mid;
	//echo json_encode($map_data);
	//echo json_encode($object_data);
	$sql = "DELETE FROM project_TRY_MAP WHERE id=?";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	//if($map_data==null) echo 'balala';
	
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>Project</title>
		<link href="gamestyle.css" rel="stylesheet" type="text/css"/>
		<script type="text/javascript" src="click_function.js"></script>
		<script type="text/javascript">
			grass_image = new Image();
			stair_image = new Image();
			slab_image = new Image();
			erase_image = new Image();
			flag_image = new Image();
			trap_image = new Image();
			thorn_image = new Image();
			smoke_image = new Image();
			mushroom_image = new Image();
			sticky_image = new Image();
			glass_image = new Image();
			change_button_image = new Image();
			change_button_press_image = new Image(); //12 not use here
			character_image = new Image();
			character_image.src = "image/char_stop_right.png";
			grass_image.src = "image/grass.jpg"; //1
			stair_image.src = "image/stairs.png"; //2
			slab_image.src = "image/slab.png"; //3
			erase_image.src = "image/erase.png"; //0
			flag_image.src = "image/flag.png"; //4
			trap_image.src = "image/trap.png"; //5
			thorn_image.src = "image/thorn.png"; //6
			smoke_image.src = "image/smoke.png"; //7
			mushroom_image.src = "image/mushroom.png"; //8
			sticky_image.src = "image/sticky.jpg"; //9
			glass_image.src = "image/glass.png"; //10
			change_button_image.src = "image/change_button.png"; //11
			change_button_press_image.src = "image/change_button_press.png"; //12 not use here
			function init(){
				position_show = 0;
				m=document.getElementById("background_canvas");
				c=m.getContext("2d");
				reset();
			}
			function reset(){
				//地圖大小
				max_rows = 6;
				max_columns = 100;
				c.clearRect(0,0,max_columns*60,max_rows*60);
				c.translate(position_show,0);
				position_show = 0; //地圖顯示到哪
				map_left = 0; //是否往左
				map_right = 0; //是否往右
				canvas_top = realPosY(m); //畫布位子
				canvas_left = realPosX(m);
				speed = 60;
				item_select = -1; //是否有選擇物件
				explain_element_id = ""; //是否有要顯示解釋欄
				flag_set = <?php if($object_data!=null) echo 1; else echo 0; ?>; //是否放旗子了
				if(flag_set==1) document.all.flag.src="image/flag_no.png";
				item = [grass_image,stair_image,slab_image,flag_image,trap_image,thorn_image,smoke_image,mushroom_image,sticky_image,glass_image,change_button_image];
				item_move = [document.getElementById("erase_move"),document.getElementById("grass_move"),document.getElementById("stairs_move"),document.getElementById("slab_move"),document.getElementById("flag_move"),document.getElementById("trap_move"),document.getElementById("thorn_move"),document.getElementById("smoke_move"),document.getElementById("mushroom_move"),document.getElementById("sticky_move"),document.getElementById("glass_move"),document.getElementById("change_button_move")];
				background_map = Create2DArray(max_rows,max_columns);
				item_map = Create2DArray(max_rows,max_columns);
				map_data = <?php echo json_encode($map_data);?>;
				object_data = <?php echo json_encode($object_data);?>;
				getmap(); //以後要改 會跟php取資料
				background_map[5][0] = 1;
				background_map[5][1] = 1;
				background_map[5][2] = 1;
				drawLine(); //畫方格
				drawBackground();
				moveBackground();
			}
			function getmap(){ //把地圖資訊放到array
				for(i=0;i<map_data.length;i++){
					background_map[map_data[i].y][map_data[i].x] = map_data[i].item;
					//exist_map[map_data[i].y][map_data[i].x] = 8;
				}
				for(i=0;i<object_data.length;i++){
					item_map[object_data[i].y][object_data[i].x] = object_data[i].item;
					//exist_map[object_data[i].y][object_data[i].x] = 8;
				}
			}
			function realPosX (oTarget) {  //取得canvas座標
				try {  
					var realX = oTarget.offsetLeft;  
					if (oTarget.offsetParent.tagName != "BODY") {  
					realX += realPosX(oTarget.offsetParent);   
					}   
					return realX;  
				}  
				catch (e) {  
					alert("realPosX: "+e);  
				} 
			}   
			function realPosY (oTarget) {  
				try {  
					var realY = oTarget.offsetTop;  
					if (oTarget.offsetParent.tagName != "BODY") {  
						realY += realPosY(oTarget.offsetParent);  
					}  
					return realY;  
				}  
				catch (e) {  
					alert("realPosY: "+e);  
				} 
			}  
			function clearMap(){
				if(confirm("要清除重做嗎？")==true){
					c.clearRect(0,0,max_columns*60,max_rows*60);//清乾淨
					c.translate(position_show,0);
					position_show = 0;
					background_map = Create2DArray(max_rows,max_columns);
					item_map = Create2DArray(max_rows,max_columns);
					background_map[5][0] = 1;
					background_map[5][1] = 1;
					background_map[5][2] = 1;
					flag_set = 0; //是否放旗子了
					document.all.flag.src="image/flag.png";
					drawLine(); //畫方格
					drawBackground();
					document.all.saveSuccessPage.style.display="none";
				}
			}
			function drawLine(){
				//橫線
				for(i=0;i<60*max_rows;i=i+60){
					c.beginPath();
					c.moveTo(0,i);
					c.lineTo(60*max_columns,i);
					c.stroke();
				}
				//直線
				for(i=0;i<60*max_columns;i=i+60){
					c.beginPath();
					c.moveTo(i,0);
					c.lineTo(i,60*max_rows);
					c.stroke();
				}
			}
			function saveMap(){ //存檔
				if(flag_set==1){
					//create json array
					var map_name = document.getElementById("input_map_name").value;
					if(map_name==null||map_name=='') map_name="New Game";
					//document.getElementById("demo3").innerHTML = 'save';
					var map_data = [];
					for(i=0;i<max_rows;i++){
						for(j=0;j<max_columns;j++){
							if(background_map[i][j]!=null){
								var jsonArg = {};
								jsonArg.x = j;
								jsonArg.y = i;
								jsonArg.item = background_map[i][j];
								map_data.push(jsonArg);
							}
						}
					}
					var object_data = [];
					for(i=0;i<max_rows;i++){
						for(j=0;j<max_columns;j++){
							if(item_map[i][j]!=null){
								var jsonArg = {};
								jsonArg.x = j;
								jsonArg.y = i;
								jsonArg.item = item_map[i][j];
								object_data.push(jsonArg);
							}
						}
					}
					var xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
						if(xmlhttp.readyState == 4 && xmlhttp.status == 200){
							//document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
							mid = xmlhttp.responseText;
							document.all.play_new_game.href="play_game.php?mid="+mid;
							//alert(mid);
						}
					}
					var map_data_to_send = JSON.stringify(map_data);
					var object_data_to_send = JSON.stringify(object_data);
					xmlhttp.open("POST", "save_map.php", true);
					xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
					xmlhttp.send("map_data="+map_data_to_send+"&object_data="+object_data_to_send+"&map_name="+map_name);
					clearMap();
					document.all.savePage.style.display="none";
					document.all.saveSuccessPage.style.display="inline";
					
				}
				else{ //flag還沒設過
					alert("還沒設終點歐!");
				}
			}
			function clickTry(){ //存檔
				if(flag_set==1){
					//create json array
					//create json array
					var map_name;
					if(map_name==null||map_name=='') map_name="Try Your Game";
					//document.getElementById("demo3").innerHTML = 'save';
					var map_data = [];
					for(i=0;i<max_rows;i++){
						for(j=0;j<max_columns;j++){
							if(background_map[i][j]!=null){
								var jsonArg = {};
								jsonArg.x = j;
								jsonArg.y = i;
								jsonArg.item = background_map[i][j];
								map_data.push(jsonArg);
							}
						}
					}
					var object_data = [];
					for(i=0;i<max_rows;i++){
						for(j=0;j<max_columns;j++){
							if(item_map[i][j]!=null){
								var jsonArg = {};
								jsonArg.x = j;
								jsonArg.y = i;
								jsonArg.item = item_map[i][j];
								object_data.push(jsonArg);
							}
						}
					}
					var xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
						if(xmlhttp.readyState == 4 && xmlhttp.status == 200){
							//document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
							mid = xmlhttp.responseText;
							//document.all.play_new_game.href="play_game.php?mid="+mid;
							//alert(mid);
						}
					}
					var map_data_to_send = JSON.stringify(map_data);
					var object_data_to_send = JSON.stringify(object_data);
					xmlhttp.open("POST", "save_try_map.php", false);
					xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
					xmlhttp.send("map_data="+map_data_to_send+"&object_data="+object_data_to_send+"&map_name="+map_name);
					//alert("before replace");
					var new_html = 'http://people.cs.nctu.edu.tw/~tihlin/project/try_game.php?mid='+mid;
					//alert("after replace");
					location.replace(new_html);
					/*clearMap();
					document.all.savePage.style.display="none";
					document.all.saveSuccessPage.style.display="inline";*/
				}
				else{ //flag還沒設過
					alert("還沒設終點歐!");
				}
			}
			function selectItem(number){
				item_select = number;
				for(i=0;i<=11;i++){
					item_move[i].style.display="none";
				}
				item_move[number].style.display="inline";
				if(flag_set == 1 && number == 4){ //已經設過旗子不可再設
					item_move[number].style.display="none";
				}
			}
			function moveItem(e){
				if(item_select>=0){
					item_move[item_select].style.left=(e.clientX+1)+"px";
					item_move[item_select].style.top=(e.clientY+1)+"px";
					//document.getElementById("demo1").innerHTML = e.clientX;
					//document.getElementById("demo2").innerHTML = e.clientY;
				}
				if(explain_element_id!=""){
					ele=document.getElementById(explain_element_id);
					ele.style.left=(e.clientX+1)+"px";
					ele.style.top=(e.clientY+1)+"px";
				}
			}
			function drawItem(e){
				if(item_select>=0){
				//先求滑鼠在哪個格子
					c.clearRect(0,0,max_columns*60,max_rows*60);//清乾淨
					x = Math.floor((e.clientX-canvas_left+position_show)/60);
					y = Math.floor((e.clientY-canvas_top)/60);
					//document.getElementById("demo1").innerHTML = x;
					//document.getElementById("demo2").innerHTML = y;
					if(item_map[y][x]==4){ //原本是旗子
						flag_set = 0; //可以再放旗子
						document.all.flag.src="image/flag.png";
					}
					if(item_map[y][x]==null&&background_map[y][x]==null){ //原本兩個都沒東西
						if(item_select>=0&&item_select<4) background_map[y][x]=item_select;
						else if(item_select==8) background_map[y][x]=item_select;
						else if(item_select==9||item_select==10) background_map[y][x]=item_select;
						else if(flag_set==0&&item_select==4) item_map[y][x]=item_select;
						else if(item_select>=5&&item_select<=7) item_map[y][x]=item_select;
						else if(item_select==11) item_map[y][x]=item_select;
					}
					else if(background_map[y][x]!=null){ //background有東西
						background_map[y][x]=null;
						if(item_select>=0&&item_select<4) background_map[y][x]=item_select;
						else if(item_select==8) background_map[y][x]=item_select;
						else if(item_select==9||item_select==10) background_map[y][x]=item_select;
						else if(flag_set==0&&item_select==4) item_map[y][x]=item_select;
						else if(item_select>=5&&item_select<=7) item_map[y][x]=item_select;
						else if(item_select==11) item_map[y][x]=item_select;
					}
					else if(item_map[y][x]!=null){ //item有東西
						item_map[y][x]=null;
						if(item_select>=0&&item_select<4) background_map[y][x]=item_select;
						else if(item_select==8) background_map[y][x]=item_select;
						else if(item_select==9||item_select==10) background_map[y][x]=item_select;
						else if(flag_set==0&&item_select==4) item_map[y][x]=item_select;
						else if(item_select>=5&&item_select<=7) item_map[y][x]=item_select;
						else if(item_select==11) item_map[y][x]=item_select;
					}
					//if(flag_set!=1 || item_select!=4) background_map[y][x]=item_select;
					
					
					if(item_select==0){
						background_map[y][x]=null; //erase
						item_map[y][x]=null;
					}
					if((x>=0&&x<=2)&&y==5){
						background_map[y][x]=1; //ground
					}
					if(x==1&&y==4){
						background_map[y][x]=null; //character
						item_map[y][x]=null;
					}
					if(item_select==4){
						flag_set = 1; //已經放旗子了
						document.all.flag.src="image/flag_no.png";
						item_move[4].style.display="none";
					}
				
					drawLine();
					drawBackground();
				}
			}
			function drawBackground(){ //畫圖囉
				c.drawImage(character_image,1*60,4*60+10,50,50);
				for(i=0;i<max_rows;i++){
					for(j=0;j<max_columns;j++){
						if(background_map[i][j]==1) c.drawImage(grass_image,j*60,i*60,60,60);
						else if(background_map[i][j]==2) c.drawImage(stair_image,j*60,i*60,60,60);
						else if(background_map[i][j]==3) c.drawImage(slab_image,j*60,i*60+40,60,20);
						else if(item_map[i][j]==4) c.drawImage(flag_image,j*60,i*60,60,60);
						else if(item_map[i][j]==5) c.drawImage(trap_image,j*60,i*60,60,60);
						else if(item_map[i][j]==6) c.drawImage(thorn_image,j*60,i*60,60,60);
						else if(item_map[i][j]==7) c.drawImage(smoke_image,j*60,i*60,60,60);
						else if(background_map[i][j]==8) c.drawImage(mushroom_image,j*60,i*60,60,60);
						else if(background_map[i][j]==9) c.drawImage(sticky_image,j*60,i*60,60,60);
						else if(background_map[i][j]==10) c.drawImage(glass_image,j*60,i*60,60,60);
						else if(item_map[i][j]==11) c.drawImage(change_button_image,j*60,i*60,60,60);
					}
				}
			}
			function mousedownRight(){map_right=1;}
			function mousedownLeft(){map_left=1;}
			function mouseupLeft(){map_left=0;}
			function mouseupRight(){map_right=0;}
			function moveBackground(){
				//document.getElementById("demo").innerHTML = item_select;
				if(map_left==1) moveLeft();
				if(map_right==1) moveRight();
				setTimeout("moveBackground()",speed);
			}
			function moveLeft(){
				//地圖往左
				if((position_show-10)>=0){
					c.clearRect(0,0,max_columns*60,max_rows*60);//清乾淨
					c.translate(10,0);
					position_show -= 10;
					drawLine();
					drawBackground();
				}
			}
			function moveRight(){
				//地圖往右
				if((position_show+10)<=max_columns*60){
					c.clearRect(0,0,max_columns*60,max_rows*60);//清乾淨
					c.translate(-10,0);
					position_show += 10;
					drawLine();
					drawBackground();
				}
			}
			function Create2DArray(rows,columns) {
				x = [];
				x.length = rows;
				for (i = 0; i < rows; i++) {
					x[i] = [];
					x[i].length = columns;
				}
				return x;
			}
			function clickSave(){
				if (confirm("確定要儲存嗎？可以先試玩看看喔")==true){
					document.all.savePage.style.display="inline";
				}
			}
			function closeSave(){
				document.all.savePage.style.display="none";
			}
			function closeSaveSuccess(){
				document.all.saveSuccessPage.style.display="none";
			}
			function showExplain(eid){
				//alert(id);
				explain_element_id = eid;
				ele=document.getElementById(eid);
				ele.style.display="inline";
				//ele.style.left=(e.clientX+1)+"px";
				//ele.style.top=(e.clientY+1)+"px";
			}
			function closeExplain(eid){
				//alert('out');
				explain_element_id = "";
				ele=document.getElementById(eid);
				ele.style.display="none";
			}

            function overRetry(){
				document.getElementById("replay").src="image/retry_hover.png";
            }
            function outRetry(){
				document.getElementById("replay").src="image/retry.png";
            }
            function clickBack(){
                if (confirm("確定要放棄目前製作的地圖直接離開？若已儲存可忽略")==true){
                    var new_html = 'http://people.cs.nctu.edu.tw/~tihlin/project/menu.php';
                    location.replace(new_html);
                }
            }
		</script>
	</head>
	<body onload="init()" onmousemove="moveItem(event)">
		<div id="detailPage">
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
		<div id="savePage">
			<div class="closeButton" onclick="closeSave()"> </div>
			<br>
			<br>
			<h3>請輸入關卡名稱: <input type="text" name="input_map_name" id="input_map_name" value="New Game"><br></h3>
			<br>
			<div class="button" onclick="saveMap()">OK</div>
		</div>
		<div id="saveSuccessPage">
			<div class="closeButton" onclick="closeSaveSuccess()"> </div>
			<br>
			<h3>儲存成功</h3><br><h3>直接玩</h3>
			<a href="level_menu.php" class="button" id="play_new_game">Play</a>
			<br>
			<a href="menu.php" target="_self" class="backNoFloat"  style="position:absolute; top:200px; left:50%; margin-left:-60px;">.</a>
			<img src="image/retry.png" style="position:absolute; top:200px; left:50%; margin-left:20px; width:40px; height:40px;" id="replay" onclick="clearMap()" onmouseover="overRetry()" onmouseout="outRetry()">
		</div>
		<h4 class="explainPage" id="explain_grass">
			草地，可踩在上面
		</h4>
		<h4 class="explainPage" id="explain_stairs">
			樹叢，可踩在上面
		</h4>
		<h4 class="explainPage" id="explain_slab">
			木板，可踩在上面
		</h4>
		<h4 class="explainPage" id="explain_flag">
			過關旗，碰到過關
		</h4>
		<h4 class="explainPage" id="explain_trap">
			陷阱，碰到死亡
		</h4>
		<h4 class="explainPage" id="explain_thorn">
			雜草，碰到緩速
		</h4>
		<h4 class="explainPage" id="explain_smoke">
			煙霧，碰到影響視覺
		</h4>
		<h4 class="explainPage" id="explain_mushroom">
			彈跳香菇，可踩在上面，碰到彈起
		</h4>
		<h4 class="explainPage" id="explain_sticky">
			黏地板，可踩在上面，跳躍高度減少
		</h4>
		<h4 class="explainPage" id="explain_glass">
			冰塊，可踩在上面，會消失
		</h4>
		<h4 class="explainPage" id="explain_change_button">
			毒菇，吃到改變移動方向
		</h4>
		<h4 class="explainPage" id="explain_erase">
			橡皮擦
		</h4>
		<h4 class="explainPage" id="explain_redo">
			清除重做
		</h4>
		<h4 class="explainPage" id="explain_store">
			儲存地圖
		</h4>
		<h4 class="explainPage" id="explain_try">
			試玩地圖，可回此頁繼續創作
		</h4>
		<header>
            <div class="back" onclick="clickBack()"> </div>
			<h1>CREATE MAP</h1>
			<div class="option" onclick="clickOption()"> </div>
		</header>
		<div id="content">
			&nbsp;
			<div class="left" style="position:absolute;top:250px; left:50%; margin-left:-360px; width:40px; height:40px;" id="left" onmousedown="mousedownLeft()" onmouseup="mouseupLeft()"> </div>
			<div id="gameCanvas">
				<canvas id="background_canvas" width=600 height=360 onclick="drawItem(event)"></canvas>
			</div>
			<div class="right" style="position:absolute;top:250px; left:50%; margin-left:320px; width:40px; height:40px;" id="right" onmousedown="mousedownRight()" onmouseup="mouseupRight()"> </div>
			<img src="image/grass.jpg" style="position:absolute;top:500px; left:50%; margin-left:-300px; width:40px; height:40px;" id="grass" onclick="selectItem(1)" onmouseover="showExplain('explain_grass')" onmouseout="closeExplain('explain_grass')">
			<img src="image/stairs.png" style="position:absolute;top:500px; left:50%; margin-left:-250px; width:40px; height:40px;" id="stairs" onclick="selectItem(2)" onmouseover="showExplain('explain_stairs')" onmouseout="closeExplain('explain_stairs')">
			<img src="image/slab_big.png" style="position:absolute;top:500px; left:50%; margin-left:-200px; width:40px; height:40px;" id="slab" onclick="selectItem(3)" onmouseover="showExplain('explain_slab')" onmouseout="closeExplain('explain_slab')">
			<img src="image/flag.png" style="position:absolute;top:500px; left:50%; margin-left:-150px; width:40px; height:40px;" id="flag" onclick="selectItem(4)" onmouseover="showExplain('explain_flag')" onmouseout="closeExplain('explain_flag')">
			<img src="image/trap.png" style="position:absolute;top:500px; left:50%; margin-left:-100px; width:40px; height:40px;" id="trap" onclick="selectItem(5)" onmouseover="showExplain('explain_trap')" onmouseout="closeExplain('explain_trap')">
			<img src="image/thorn.png" style="position:absolute;top:500px; left:50%; margin-left:-50px; width:40px; height:40px;" id="thorn" onclick="selectItem(6)" onmouseover="showExplain('explain_thorn')" onmouseout="closeExplain('explain_thorn')">
			<img src="image/smoke.png" style="position:absolute;top:500px; left:50%; margin-left:0px; width:40px; height:40px;" id="smoke" onclick="selectItem(7)" onmouseover="showExplain('explain_smoke')" onmouseout="closeExplain('explain_smoke')">
			<img src="image/mushroom.png" style="position:absolute;top:500px; left:50%; margin-left:50px; width:40px; height:40px;" id="mushroom" onclick="selectItem(8)" onmouseover="showExplain('explain_mushroom')" onmouseout="closeExplain('explain_mushroom')">
			<img src="image/sticky.jpg" style="position:absolute;top:500px; left:50%; margin-left:100px; width:40px; height:40px;" id="sticky" onclick="selectItem(9)" onmouseover="showExplain('explain_sticky')" onmouseout="closeExplain('explain_sticky')">
			<img src="image/glass.png" style="position:absolute;top:500px; left:50%; margin-left:150px; width:40px; height:40px;" id="glass" onclick="selectItem(10)" onmouseover="showExplain('explain_glass')" onmouseout="closeExplain('explain_glass')">
			<img src="image/change_button.png" style="position:absolute;top:500px; left:50%; margin-left:200px; width:40px; height:40px;" id="change_button" onclick="selectItem(11)" onmouseover="showExplain('explain_change_button')" onmouseout="closeExplain('explain_change_button')">
			<img src="image/erase.png" style="position:absolute;top:70px; left:50%; margin-left:-300px; width:40px; height:40px;" id="erase" onclick="selectItem(0)" onmouseover="showExplain('explain_erase')" onmouseout="closeExplain('explain_erase')">
			<img src="image/redo.png" style="position:absolute;top:70px; left:50%; margin-left:-250px; width:40px; height:40px;" id="redo" onclick="clearMap()" onmouseover="showExplain('explain_redo')" onmouseout="closeExplain('explain_redo')">
			<img src="image/store.png" style="position:absolute;top:70px; left:50%; margin-left:-200px; width:40px; height:40px;" id="save" onclick="clickSave()" onmouseover="showExplain('explain_store')" onmouseout="closeExplain('explain_store')">
			<img src="image/try.png" style="position:absolute;top:70px; left:50%; margin-left:-150px; width:40px; height:40px;" id="try" onclick="clickTry()" onmouseover="showExplain('explain_try')" onmouseout="closeExplain('explain_try')">
			<img src="image/grass.jpg" style="position:absolute;top:700px; left:100px; width:40px; height:40px; display:none;" id="grass_move">
			<img src="image/stairs.png" style="position:absolute;top:700px; left:200px; width:40px; height:40px; display:none;" id="stairs_move">
			<img src="image/slab.png" style="position:absolute;top:700px; left:300px; width:40px;  display:none;" id="slab_move">
			<img src="image/erase.png" style="position:absolute;top:700px; left:400px; width:40px; height:40px; display:none;" id="erase_move">
			<img src="image/flag.png" style="position:absolute;top:700px; left:500px; width:40px; height:40px; display:none;" id="flag_move">
			<img src="image/trap.png" style="position:absolute;top:700px; left:500px; width:40px; height:40px; display:none;" id="trap_move">
			<img src="image/thorn.png" style="position:absolute;top:700px; left:500px; width:40px; height:40px; display:none;" id="thorn_move">
			<img src="image/smoke.png" style="position:absolute;top:700px; left:500px; width:40px; height:40px; display:none;" id="smoke_move">
			<img src="image/mushroom.png" style="position:absolute;top:700px; left:500px; width:40px; height:40px; display:none;" id="mushroom_move">
			<img src="image/sticky.jpg" style="position:absolute;top:700px; left:500px; width:40px; height:40px; display:none;" id="sticky_move">
			<img src="image/glass.png" style="position:absolute;top:700px; left:500px; width:40px; height:40px; display:none;" id="glass_move">
			<img src="image/change_button.png" style="position:absolute;top:700px; left:500px; width:40px; height:40px; display:none;" id="change_button_move">
			<p></p>
			<p><span id="txtHint"></span></p>
			<p id="demo3"></p>
			<p id="demo"></p>
			<p id="demo1"></p>
			<p id="demo2"></p>
		</div>
		<?php
			require_once("header_footer.class.php");
			$project = new project();
			$project->output_footer();
			$db=null;
		?>
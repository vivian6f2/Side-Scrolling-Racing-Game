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
	$sql = "SELECT * FROM project_MAP WHERE id = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	$row = $sth->fetchObject();
	$map_name = $row->name;
	$sql = "SELECT * FROM project_MAP_DATA WHERE mid = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	while($row = $sth->fetchObject()){
		array_push($map_data,$row);
	}
	$sql = "SELECT * FROM project_MAP_OBJECT WHERE mid = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	while($row = $sth->fetchObject()){
		array_push($object_data,$row);
	}
	//get max x in this map
	$sql = "SELECT * FROM project_MAP_DATA WHERE mid = ? ORDER BY x DESC";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	$row = $sth->fetchObject();
	$max_x = $row->x;
	
	
	$sql = "SELECT * FROM project_MAP_OBJECT WHERE mid = ? ORDER BY x DESC";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	$row = $sth->fetchObject();
	if($row->x > $max_x) $max_x = $row->x;
	//get flag x in this map
	
	$sql = "SELECT * FROM project_MAP_OBJECT WHERE mid = ? AND item = 4";
	$sth = $db->prepare($sql);
	$sth->execute(array($mid));
	$row = $sth->fetchObject();
	$flag_x = $row->x;
	//echo json_encode($map_data);
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>Project</title>
		<link href="gamestyle.css" rel="stylesheet" type="text/css"/>
		<script type="text/javascript" src="click_function.js"></script>
		<script type="text/javascript">
			//get image source
			grass_image = new Image(); //1
			stair_image = new Image(); //2
			slab_image = new Image(); //3
			flag_image = new Image(); //4
			trap_image = new Image(); //5
			thorn_image = new Image(); //6
			smoke_image = new Image(); //7
			mushroom_image = new Image(); //8
			sticky_image = new Image(); //9
			glass_image = new Image(); //10
			change_button_image = new Image(); //11
			change_button_press_image = new Image(); //12
			grass_image.src = "image/grass.jpg"; //1
			stair_image.src = "image/stairs.png"; //2
			slab_image.src = "image/slab.png"; //3
			flag_image.src = "image/flag.png"; //4
			trap_image.src = "image/trap.png"; //5
			thorn_image.src = "image/thorn.png"; //6
			smoke_image.src = "image/smoke.png"; //7
			mushroom_image.src = "image/mushroom.png"; //8
			sticky_image.src = "image/sticky.jpg"; //9
			glass_image.src = "image/glass.png"; //10
			change_button_image.src = "image/change_button.png"; //11
			change_button_press_image.src = "image/change_button_press.png"; //12
			stars = ["star1","star2","star3","star4","star5","star6","star7","star8","star9","star10"];
			function init(){
				position_show = 0;
				m=document.getElementById("background_canvas");
				m_front = document.getElementById("front_canvas");
				chara=document.getElementById("character");
				//document.getElementById("demo3").innerHTML = "left:" + realPosX(m);
				//document.getElementById("demo4").innerHTML = "top:" + realPosY(m);
				c=m.getContext("2d");
				c_front = m_front.getContext("2d");
				orig_character_top = realPosY(chara);
				orig_character_left = realPosX(chara)+240;
				reset();
				pictureChange();
				distanceBar();
			}
			function reset(){
				//地圖大小 (6*60 100*60) 之後取php資料決定
				max_rows = 6;
				max_columns = 100;
				//for reset clean the canvas ans move back to 0 position
				c.clearRect(0,0,max_columns*60,max_rows*60);
				c.translate(position_show,0);
				c_front.clearRect(0,0,max_columns*60,max_rows*60);
				c_front.translate(position_show,0);
				//if move the canvas & character, i have to change this
				canvas_top = realPosY(m);
				canvas_left = realPosX(m)+240;
				character_top = orig_character_top;
				character_left = orig_character_left;
				chara_width_px = document.all.character.style.width;
				chara_height_px = document.all.character.style.height;
				chara_width = Number(chara_width_px.replace("px",""));
				chara_height = Number(chara_height_px.replace("px",""));
				//alert(chara_width + " " + chara_height);
				move_left = 0;
				move_right = 0;
				change_move = 0;
				move_up = 0;
				speed = 30; //lower is faster
				position_show = 0; //記住顯示到地圖的哪裡了
				up_v = 0; //往上的速度
				last_up_v = 0;
				constant_up_v = 30;
				down_a = 4; //往下的加速度
				horizon_v = 5; //左右移動的速度
				jump = 0;
				last_up_v = 0;
				die = 0;
				win = 0;
				resize = 0;
				face_side = 1;
				small_face_side = 1;
				count = 0;
				move_left_right_change = -1; //左右移動相反與否
				background_map = Create2DArray(max_rows,max_columns);
				item_map = Create2DArray(max_rows,max_columns);
				exist_map = Create2DArray(max_rows,max_columns);
				//document.getElementById("demo").innerHTML = '123';
				map_data = <?php echo json_encode($map_data); ?>;
				object_data = <?php echo json_encode($object_data); ?>;
				map_max_x = <?php echo $max_x; ?>;
				map_min_x = 0;
				map_flag_x = <?php echo $flag_x; ?>;
				getmap(); //以後要改 會跟php取資料
				drawBackground(); //畫圖囉
				moveFunction(); //會一直跑
				document.all.character.style.left=character_left+"px"; //for reset
				document.all.character.style.top=character_top+"px"; //for reset
				document.all.character.src="image/char_stop_right.png"; //位子變回來
				document.all.scoreButton1.style.display="inline";
				document.all.scoreButton2.style.display="inline";
				for(i=1;i<=10;i++){
					document.getElementById(stars[i-1]).src="image/star.png";
				}
				for(i=1;i<=3;i++){
					document.getElementById(stars[i-1]).src="image/star_hover.png";
				}
				for(i=6;i<=8;i++){
					document.getElementById(stars[i-1]).src="image/star_hover.png";
				}
				document.all.winPage.style.display="none";
				document.all.losePage.style.display="none";
				star_save = 0;
				star_score = 3;
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
			function pictureChange(){ //改角色看的方向
				if(move_left==1&&move_right==1){//&&move_up==0){ //我爽
					change_move=0;
					if(count==5){
						count = 0;	
						face_side*=(-1);
					}
					else count++;
					if(face_side==1){
						document.all.character.src="image/char_right.png";
						document.getElementById("character").height = chara_height_px;
						document.getElementById("character").width = chara_width_px;
					}
					else{
						document.all.character.src="image/char_left.png";
						document.getElementById("character").height = chara_height_px;
						document.getElementById("character").width = chara_width_px;
					}
				}
				else if(move_left==1){
					if(change_move!=1){
						change_move=1;
						document.all.character.src="image/char_move_left.gif";
						document.getElementById("character").height = chara_height_px;
						document.getElementById("character").width = chara_width_px;
					}
				}
				else if(move_right==1){
					if(change_move!=2){
						change_move=2;
						document.all.character.src="image/char_move_right.gif";
						document.getElementById("character").height = chara_height_px;
						document.getElementById("character").width = chara_width_px;
					}
				}
				else if(move_right==0&&move_left==0){
					change_move=0;
					document.all.character.src="image/char_stop_right.png";
					document.getElementById("character").height = chara_height_px;
					document.getElementById("character").width = chara_width_px;
				}
				setTimeout("pictureChange()",speed);
			}
			function getmap(){ //把地圖資訊放到array
				for(i=0;i<map_data.length;i++){
					background_map[map_data[i].y][map_data[i].x] = map_data[i].item;
					exist_map[map_data[i].y][map_data[i].x] = 8;
				}
				for(i=0;i<object_data.length;i++){
					item_map[object_data[i].y][object_data[i].x] = object_data[i].item;
					exist_map[object_data[i].y][object_data[i].x] = 8;
				}
			}
			function drawBackground(){ //畫圖囉
				for(i=0;i<max_rows;i++){
					for(j=0;j<max_columns;j++){
						if(background_map[i][j]==1) c.drawImage(grass_image,j*60,i*60,60,60);
						else if(background_map[i][j]==2) c.drawImage(stair_image,j*60,i*60,60,60);
						else if(background_map[i][j]==3) c.drawImage(slab_image,j*60,i*60+40,60,20);
						else if(item_map[i][j]==4) c.drawImage(flag_image,j*60,i*60,60,60);
						else if(item_map[i][j]==5) c.drawImage(trap_image,j*60,i*60,60,60);
						else if(item_map[i][j]==6) {c.drawImage(thorn_image,j*60,i*60,60,60);c_front.drawImage(thorn_image,j*60,i*60,60,60);}
						else if(item_map[i][j]==7) {c.drawImage(smoke_image,j*60,i*60,60,60);c_front.drawImage(smoke_image,j*60,i*60,60,60);}
						else if(background_map[i][j]==8) c.drawImage(mushroom_image,j*60,i*60,60,60);
						else if(background_map[i][j]==9) c.drawImage(sticky_image,j*60,i*60,60,60);
						else if(background_map[i][j]==10) c.drawImage(glass_image,j*60,i*60,60,60);
						else if(item_map[i][j]==11) c.drawImage(change_button_image,j*60,i*60,60,60);
						else if(item_map[i][j]==12) c.drawImage(change_button_press_image,j*60,i*60,60,60);
					}
				}
			}
			function Create2DArray(rows,columns) { //2D
				x = [];
				x.length = rows;
				for (i = 0; i < rows; i++) {
					x[i] = [];
					x[i].length = columns;
				}
				return x;
			}
			function keydownFunction(e){
				if(window.event){ //ie opera chrome safari
					u_key = e.keyCode;
				}
				else if(e.which){ //firefox
					u_key = e.which;
				}
				if(u_key==65||u_key==37){ //a or ←
					if(move_left_right_change==1) move_right=1;
					else move_left=1;
				}
				if(u_key==68||u_key==39){ //d or →
					if(move_left_right_change==1) move_left=1;
					else move_right=1;
				}
				if(u_key==87||u_key==38||u_key==32){ //w or ↑ or spacebar
					move_up=1;
				}
			}
			function keyupFunction(e){
				if(window.event){ //IE
					u_key = e.keyCode;
				}
				else if(e.which){
					u_key = e.which;
				}
				if(u_key==65||u_key==37){ //a or ←
					if(move_left_right_change==1) move_right=0;
					else move_left=0;
				}
				if(u_key==68||u_key==39){ //d or →
					if(move_left_right_change==1) move_left=0;
					else move_right=0;
				}
				if(u_key==87||u_key==38||u_key==32){ //w or ↑ or spacebar
					move_up=0;
				}
			}
			function moveFunction(){
				//document.getElementById("demo3").innerHTML = "left:" + character_left;
				//document.getElementById("demo4").innerHTML = "top:" + character_top;
				//get character's position (60*60px=1格)
				char_x = Math.floor((character_left-canvas_left+position_show)/60); //最左邊在哪就算哪
				char_y = Math.floor(((character_top+chara_height-canvas_top)%60==0)? (character_top+chara_height-canvas_top)/60-1: (character_top+chara_height-canvas_top)/60); //最下面在哪就算哪 除了剛好在格子的
				
				//document.getElementById("demo5").innerHTML = position_show;
				//document.getElementById("demo6").innerHTML = "y:" + char_y;
				//document.getElementById("demo7").innerHTML = "left:" + canvas_left;
				//document.getElementById("demo8").innerHTML = "top:" + canvas_top;
				
				ground_y = 360;
				right_ground_y = 360;
				up_y = -1;
				right_up_y = -1;
				//-----找上面和下面最接近的格子-----//
				if(char_y >= 0){
					for(i=char_y;i<max_rows;i++){
						if(background_map[i][char_x]!=null){
							ground_y = i*60;
							if(background_map[i][char_x]==3) ground_y+=40;
							break;
						}
					}
					if(((character_left-canvas_left+position_show+chara_width)>(char_x+1)*60)){ //如果角色最右邊超過碰到右邊那格
						for(i=char_y;i<max_rows;i++){ 
							if((background_map[i][char_x+1]!=null)){
								right_ground_y = i*60;
								if(background_map[i][char_x+1]==3) right_ground_y+=40;
								break;
							}
						}
					}
					for(i=char_y-1;i>=0;i--){
						if(background_map[i][char_x]!=null){
							up_y = i*60+60;
							break;
						}
					}
					if((character_left-canvas_left+position_show+chara_width)>(char_x+1)*60){ //如果角色最右邊超過碰到右邊那格
						for(i=char_y-1;i>=0;i--){
							if(background_map[i][char_x+1]!=null){
								right_up_y = i*60+60;
								break;
							}
						}
					}
				}
				if(right_ground_y<ground_y) ground_y=right_ground_y; //選位置比較高(數字小)的那邊
				if(right_up_y>up_y) up_y=right_up_y; //選位置比較低(數字大)的那邊
				//-----找上面和下面最接近的格子-----//
				if(move_up==1&&jump==0&&up_v==0){ //給初始向上速度
					up_v=constant_up_v;
					jump=1;
				}
				if(up_v==0){ //沒有上下移動 只需判斷左右
					if(move_left==1&&move_right==1){//恐龍很慌張
						/*if(count==5){
							count = 0;	
							face_side*=(-1);
						}
						else count++;
						if(face_side==1){
							document.all.character.src="image/char_right.png";
							document.getElementById("character").height = "40";
							document.getElementById("character").width = "40";
						}
						else{
							document.all.character.src="image/char_left.png";
							document.getElementById("character").height = "40";
							document.getElementById("character").width = "40";
						}*/
					}
					else if(move_left==1&&move_right==0){ //往左	
						/*document.all.character.src="image/char_move_left.gif";
						document.getElementById("character").height = "40";
						document.getElementById("character").width = "40";*/
						if(char_y >= 0){ //在canvas框框內
								
							if(background_map[char_y][char_x-1]==null){ //左邊沒東西
								if((position_show-horizon_v)<0) moveCharacterH(); //已經到地圖底了
								else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
								else moveBackground();
							}
							else if(background_map[char_y][char_x-1]==3&&background_map[char_y][char_x]==3){ //左邊有小平台 ***且自己也是在平台上
								if((position_show-horizon_v)<0) moveCharacterH(); //已經到地圖底了
								else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
								else moveBackground();
							}
							else if(background_map[char_y][char_x-1]==3&&background_map[char_y][char_x]!=3){ //左邊有小平台 ***且自己不是在平台上
								//把y移到平台上
								if((character_top-canvas_top+chara_height)!=(char_y*60+40)){
									character_top=character_top-20;
									document.all.character.style.top=character_top+"px";
								}
							
								if((position_show-horizon_v)<0) moveCharacterH(); //已經到地圖底了
								else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
								else moveBackground();
							}
							else if((character_left-canvas_left+position_show)!=(char_x)*60){ //左邊有東西但沒碰到
								if((position_show-horizon_v)<0) moveCharacterH(); //已經到地圖底了
								else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
								else moveBackground();
							}
						}
						else{ //恐龍跳出去了
							if((position_show-horizon_v)<0) moveCharacterH(); //已經到地圖底了
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
							else moveBackground();
						}
					}
					else if(move_left==0&&move_right==1){ //往右
						/*document.all.character.src="image/char_move_right.gif";
						document.getElementById("character").height = "40";
						document.getElementById("character").width = "40";*/
						if(char_y >= 0){ //在canvas框框內
							if(background_map[char_y][char_x+1]==null){ //右邊沒東西
								//document.getElementById("demo").innerHTML = "右邊沒東西";
								if((position_show+horizon_v)>(max_columns*60-600)) moveCharacterH(); //地圖底
								else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
								else moveBackground();
							}
							else if(background_map[char_y][char_x]==3&&background_map[char_y][char_x+1]==3){ //右邊有小平台 且自己也是在平台上
								
								//document.getElementById("demo").innerHTML = "右邊有小平台也";
								if((position_show+horizon_v)>(max_columns*60-600)) moveCharacterH(); //地圖底
								else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
								else moveBackground();
							}
							else if(background_map[char_y][char_x+1]==3){ //右邊有小平台 且自己不是在平台上
								//document.getElementById("demo").innerHTML = "右邊有小平台不";
								//把y移到平台上
								if((character_top-canvas_top+chara_height)!=(char_y*60+40)){
									character_top=character_top-20;
									document.all.character.style.top=character_top+"px";
								}
								
								if((position_show+horizon_v)>(max_columns*60-600)) moveCharacterH(); //地圖底
								else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
								else moveBackground();
							}
							else if((character_left-canvas_left+position_show+chara_width)!=(char_x+1)*60){ //右邊有東西但沒碰到
								
								//document.getElementById("demo").innerHTML = "右邊有東西但沒碰到";
								//document.getElementById("demo2").innerHTML = (character_left-canvas_left+position_show+chara_width);
								//document.getElementById("demo3").innerHTML = (char_x+1)*60;
								if((position_show+horizon_v)>(max_columns*60-600)) moveCharacterH(); //地圖底
								else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
								else moveBackground();
							}
						}
						else{
							if((position_show+horizon_v)>(max_columns*60-600)) moveCharacterH(); //地圖底
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
							else moveBackground();
						}
						
					}
				}
				else if(up_v!=0){ //有上下移動
					if(up_v>0){ //up
						if(up_y==-1){ //上面沒東西
							future_char_y = Math.floor(((character_top+chara_height-canvas_top-up_v)%60==0)? (character_top+chara_height-canvas_top-up_v)/60-1: (character_top+chara_height-canvas_top-up_v)/60); //上下移動後的y
						}
						else if((character_top-canvas_top-up_v)>up_y){ //加上這次的還不會碰到天花板
							future_char_y = Math.floor(((character_top+chara_height-canvas_top-up_v)%60==0)? (character_top+chara_height-canvas_top-up_v)/60-1: (character_top+chara_height-canvas_top-up_v)/60); //上下移動後的y
						}
						else{ //碰到了
							future_char_y = Math.floor(((up_y+chara_height)%60==0)? (up_y+chara_height)/60-1: (up_y+chara_height)/60); //上下移動後的y
						}
					}
					else if(up_v<0){ //down
						if((character_top+chara_height-canvas_top-up_v)<ground_y){ //加上這次的還不會碰到地板
							future_char_y = Math.floor(((character_top+chara_height-canvas_top-up_v)%60==0)? (character_top+chara_height-canvas_top-up_v)/60-1: (character_top+chara_height-canvas_top-up_v)/60); //上下移動後的y
						}
						else{ //碰到地板
							future_char_y = Math.floor(((ground_y)%60==0)? (ground_y)/60-1: (ground_y)/60); //上下移動後的y
						}
					}
					if((move_left==0&&move_right==0)||(move_left==1&&move_right==1)){ //沒有往左或往右
						/*if(move_left==1&&move_right==1){ //恐龍很慌張
							if(count==5){
								count = 0;	
								face_side*=(-1);
							}
							else count++;
							if(face_side==1){
								document.all.character.src="image/char_right.png";
								document.getElementById("character").height = "40";
								document.getElementById("character").width = "40";
							}
							else{
								document.all.character.src="image/char_left.png";
								document.getElementById("character").height = "40";
								document.getElementById("character").width = "40";
							}
						}
						else{
								document.all.character.src="image/char_stop_right.png";
								document.getElementById("character").height = "40";
								document.getElementById("character").width = "40";
						}*/
						moveCharacterV(); //上下移動角色
					}
					else if(move_left==1&&move_right==0){ //往左
						/*document.all.character.src="image/char_move_left.gif";
						document.getElementById("character").height = "40";
						document.getElementById("character").width = "40";*/
						//-----先判斷往左移動-----//
						if(char_y >= 0 && future_char_y >= 0){
							if(background_map[char_y][char_x-1]==null){ //上下移動前左邊沒東西
								if(background_map[future_char_y][char_x-1]==null){ //上下移動後的左邊沒東西
									if((position_show-horizon_v)<0) moveCharacterH(); //已經到地圖底了
									else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
									else moveBackground();
								}
								else if((character_left-canvas_left+position_show)!=(char_x)*60){ //上下移動後的左邊有東西但沒碰到
									if((position_show-horizon_v)<0) moveCharacterH(); //已經到地圖底了
									else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
									else moveBackground();
								}
							}
							else if((character_left-canvas_left+position_show)!=(char_x)*60){ //上下移動前左邊有東西但沒碰到
								if(background_map[future_char_y][char_x-1]==null){ //上下移動後的左邊沒東西
									if((position_show-horizon_v)<0) moveCharacterH(); //已經到地圖底了
									else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
									else moveBackground();
								}
								else if((character_left-canvas_left+position_show)!=(char_x)*60){ //上下移動後的左邊有東西但沒碰到
									if((position_show-horizon_v)<0) moveCharacterH(); //已經到地圖底了
									else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
									else moveBackground();
								}
							}
						}
						else{
							if((position_show-horizon_v)<0) moveCharacterH(); //已經到地圖底了
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
							else moveBackground();
						}
						//-----先判斷往左移動-----//
						moveCharacterV(); //上下移動角色
					}
					else if(move_left==0&&move_right==1){ //往右
						/*document.all.character.src="image/char_move_right.gif";
						document.getElementById("character").height = "40";
						document.getElementById("character").width = "40";*/
						//-----先判斷往右移動-----//
						if(char_y >= 0 && future_char_y >= 0){
							if(background_map[char_y][char_x+1]==null){ //上下移動前右邊沒東西
								if(background_map[future_char_y][char_x+1]==null){ //上下移動後的右邊沒東西
									if((position_show+horizon_v)>(max_columns*60-600)) moveCharacterH(); //地圖底
									else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
									else moveBackground();
								}
								else if((character_left-canvas_left+position_show+chara_width)!=(char_x+1)*60){ //上下移動後的右邊有東西但沒碰到
									if((position_show+horizon_v)>(max_columns*60-600)) moveCharacterH(); //地圖底
									else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
									else moveBackground();
								}
							}
							else if((character_left-canvas_left+position_show+chara_width)!=(char_x+1)*60){ //上下移動前右邊有東西但沒碰到
								if(background_map[future_char_y][char_x+1]==null){ //上下移動後的右邊沒東西
									if((position_show+horizon_v)>(max_columns*60-600)) moveCharacterH(); //地圖底
									else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
									else moveBackground();
								}
								else if((character_left-canvas_left+position_show+chara_width)!=(char_x+1)*60){ //上下移動後的右邊有東西但沒碰到
									if((position_show+horizon_v)>(max_columns*60-600)) moveCharacterH(); //地圖底
									else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
									else moveBackground();
								}
							}
						}
						else{
							if((position_show+horizon_v)>(max_columns*60-600)) moveCharacterH(); //地圖底
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
							else moveBackground();
						}
						//-----先判斷往右移動-----//
						moveCharacterV(); //上下移動角色
					}
				}
								//document.getElementById("demo2").innerHTML = (character_left-canvas_left+position_show+chara_width);
								//document.getElementById("demo3").innerHTML = (char_x+1)*60;
				if((character_top+chara_height-canvas_top)<ground_y){ //還沒碰到地板給加速度
					up_v-=down_a;
				}
				checkState(); //檢查狀態
				if(die==0&&win==0&&resize==0){ //不這樣會跑出兩個一起跑 變成動超快 lol
					setTimeout("moveFunction()",speed);
				}
				else if(die==1){
					//alert("GG");
					var xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
						if(xmlhttp.readyState == 4 && xmlhttp.status == 200){
							//document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
						}
					}
					xmlhttp.open("POST", "add_die_win_time.php", true);
					xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
					type = 'die';
					xmlhttp.send("mid="+<?php echo $mid;?>+"&add_type=" + type);
					
					document.all.losePage.style.display="inline";
					//reset();
				}
				else if(win==1){
					//alert("You Win!~~~~");
					var xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
						if(xmlhttp.readyState == 4 && xmlhttp.status == 200){
							//document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
						}
					}
					xmlhttp.open("POST", "add_die_win_time.php", true);
					xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
					type = 'win';
					xmlhttp.send("mid="+<?php echo $mid;?>+"&add_type=" + type);
					
					document.all.winPage.style.display="inline";
					//reset();
				}
				else if(resize==1){
					old_orig_character_left = orig_character_left;
					orig_character_left = realPosX(m)+240+60;
					canvas_left = realPosX(m)+240;
					character_left = character_left + orig_character_left - old_orig_character_left;
					document.all.character.style.left=character_left+"px";
					resize=0;
					setTimeout("moveFunction()",speed); //繼續開始
				}
				/* 好亂喔QQ
				//get character's position (60*60px=1格)
				char_x = Math.floor((character_left-100+position_show)/60);
				char_y = Math.floor(((character_top+40-100)%60==0)? (character_top+40-100)/60-1: (character_top+40-100)/60);
				//horizontal
				if(move_left==1&&move_right==0){ //往左 
					if(up_v==0){ //沒有上下移動
						if(background_map[char_y][char_x-1]==null){ //左邊沒東西
							if((position_show-10)<0) moveCharacterH(); //已經到地圖底了
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
							else moveBackground();
						}
						else if((character_left-100+position_show)!=(char_x)*60){ //左邊有東西但沒碰到
							if((position_show-10)<0) moveCharacterH(); //已經到地圖底了
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
							else moveBackground();
						}
					}
					else{
						temp_char_y = Math.floor(((character_top+40-100-up_v)%60==0)? (character_top+40-100-up_v)/60-1: (character_top+40-100-up_v)/60);
						if(background_map[temp_char_y][char_x-1]==null){//上下移動後的格子沒東西
							if((position_show-10)<0) moveCharacterH(); //已經到地圖底了
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
							else moveBackground();
						}
						else if((character_left-100+position_show)!=(char_x)*60){ //上下移動後左邊有東西但沒碰到
							if((position_show-10)<0) moveCharacterH(); //已經到地圖底了
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //角色沒在中間
							else moveBackground();
						}
					}
				}
				if(move_left==0&&move_right==1){ //往右
					//document.getElementById("demo").innerHTML = (character_left-100+position_show+40);
					//document.getElementById("demo2").innerHTML = char_x+1;
					if(up_v==0){ //沒有上下移動
						if(background_map[char_y][char_x+1]==null){ //右邊沒東西
							if((position_show+10)>(max_columns*60-600)) moveCharacterH(); //地圖底
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
							else moveBackground();
						}
						else if((character_left-100+position_show+40)!=(char_x+1)*60){ //右邊有東西但是角色還沒碰到(40*40)
							if((position_show+10)>(max_columns*60-600)) moveCharacterH(); //地圖底
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
							else moveBackground();
						}
					}
					else{
						temp_char_y = Math.floor(((character_top+40-100-up_v)%60==0)? (character_top+40-100-up_v)/60-1: (character_top+40-100-up_v)/60);
						if(background_map[temp_char_y][char_x+1]==null){ //右邊沒東西
							if((position_show+10)>(max_columns*60-600)) moveCharacterH(); //地圖底
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
							else moveBackground();
						}
						else if((character_left-100+position_show+40)!=(char_x+1)*60){ //右邊有東西但是角色還沒碰到(40*40)
							if((position_show+10)>(max_columns*60-600)) moveCharacterH(); //地圖底
							else if(character_left!=(canvas_left+300)) moveCharacterH(); //沒在中間
							else moveBackground();
						}
					}
				}
				//get character's position 跑完水平再判斷一次
				char_x = Math.floor((character_left-100+position_show)/60); 
				char_y = Math.floor(((character_top+40-100)%60==0)? (character_top+40-100)/60-1: (character_top+40-100)/60);
				//vertical
				//找下面和上面最接近的格子 //////我大概要寫往左往右的判斷+上下判斷ˋˊ
				ground_y = -1;
				right_ground_y = -1;
				up_y = -1;
				right_up_y = -1;
				if(up_v==0) temp_char_y=char_y;
				else{
					temp_char_y = Math.floor(((character_top+40-100-up_v)%60==0)? (character_top+40-100-up_v)/60-1: (character_top+40-100-up_v)/60);
				}
				for(i=temp_char_y;i<max_rows;i++){
					if(background_map[i][char_x]!=null){
						ground_y = i*60;
						if(background_map[i][char_x]==3) ground_y+=40;
						break;
					}
				}
				if(((character_left-100+position_show+40)>(char_x+1)*60)&&up_v!=0){ //如果角色最右邊超過碰到右邊那格
					for(i=temp_char_y;i<max_rows;i++){ 
						if((background_map[i][char_x+1]!=null)){
							right_ground_y = i*60;
							if(background_map[i][char_x+1]==3) right_ground_y+=40;
							break;
						}
					}
				}
				for(i=temp_char_y-1;i>=0;i--){
					if(background_map[i][char_x]!=null){
						up_y = i*60+60;
						break;
					}
				}
				if(((character_left-100+position_show+40)>(char_x+1)*60)&&up_v!=0){ //如果角色最右邊超過碰到右邊那格
					for(i=temp_char_y-1;i>=0;i--){
						if(background_map[i][char_x]!=null){
							right_up_y = i*60+60;
							break;
						}
					}
				}
				document.getElementById("demo").innerHTML = ground_y;
				document.getElementById("demo2").innerHTML = right_ground_y;
				if(right_ground_y!=-1) ground_y=right_ground_y;
				if(right_up_y!=-1) up_y=right_up_y;
				if(move_up==1&&jump==0){ //給初始向上速度
					up_v=60;
					jump=1;
				}
				if(up_v!=0){ //上下動
					moveCharacterV();
				}
				if(ground_y==-1||((character_top+40-100)<ground_y)){ //還沒碰到地板給加速度
					up_v-=down_a;
				}
				checkState(); //檢查狀態
				if(die==0){ //不這樣會跑出兩個一起跑 變成動超快 lol
					setTimeout("moveFunction()",speed);
				}
				if(die==1){
					alert("GG");
					reset();
				}
				*/
			}
			function checkState(){ //以後增加死掉或勝利的判斷
				left_char_x = Math.floor((character_left-canvas_left+position_show)/60); //最左邊在哪就算哪
				right_char_x = (character_left-canvas_left+position_show)%60==0? left_char_x : (left_char_x+1); //若剛好在格子內則算同格 否則+1
				up_char_y = Math.floor((character_top-canvas_top)/60); //最上面在哪就算哪
				down_char_y = Math.floor(((character_top+chara_height-canvas_top)%60==0)? (character_top+chara_height-canvas_top)/60-1: (character_top+chara_height-canvas_top)/60); //最下面在哪就算哪 除了剛好在格子的		
				middle_char_x = Math.floor((character_left-canvas_left+position_show+20)/60);
				middle_char_y = Math.floor((character_top-canvas_top+20)/60); 
				//-------------------DIE--------------------//
				if((character_top+chara_height-canvas_top)>=360) die=1;
				if(up_char_y>=0&&middle_char_y>=0&&down_char_y>=0){
					if(item_map[middle_char_y][middle_char_x]==5||item_map[down_char_y][middle_char_x]==5||item_map[up_char_y][middle_char_x]==5){
						die=1;
					}
				}
				//-------------------DIE--------------------//
				//-------------------WIN--------------------//
				if(up_char_y>=0&&middle_char_y>=0&&down_char_y>=0){
					if(item_map[middle_char_y][middle_char_x]==4){
						win=1;
					}
				}
				//-------------------WIN--------------------//
				//-----------------SPECIAL------------------//
				if(up_char_y>=0&&middle_char_y>=0&&down_char_y>=0){
					if(item_map[middle_char_y][middle_char_x]==6){ //藤蔓減速
						horizon_v = 2;
					}
					else{
						horizon_v = 5;
						if((character_left-canvas_left+position_show+chara_width)%5!=0) horizon_v = 2;
					}
					if(item_map[middle_char_y][middle_char_x]==7){ //煙霧失明
						document.all.gameCanvas_front_front.style.backgroundColor= "rgba(0%,0%,0%,0.87)";
					}
					else{
						document.all.gameCanvas_front_front.style.backgroundColor= "rgba(100%,100%,100%,0)";
					}
					if(middle_char_y<5){
						if(((character_top+chara_height-canvas_top)%60==0)&&(background_map[middle_char_y+1][middle_char_x]==8)){ //香菇跳
							up_v=30;
							jump=1;
						}
						if(((character_top+chara_height-canvas_top)%60==0)&&(background_map[middle_char_y+1][middle_char_x]==9)){ //黏地板
							constant_up_v=15;
						}
						else{
							constant_up_v=30;
						}
					}
					if(middle_char_y>0&&middle_char_y<5){
						if(jump==1&&background_map[middle_char_y+1][middle_char_x]!=null&&background_map[middle_char_y-1][middle_char_x]!=null&&chara_height==60){
						
						jump=0;
						}//only when char size = 60
					}
					if(middle_char_y<5){
						if(((character_top+chara_height-canvas_top)%60==0)&&(background_map[middle_char_y+1][middle_char_x]==10)){ //消失地板
						
							exist_map[middle_char_y+1][middle_char_x]-=1;
							if(exist_map[middle_char_y+1][middle_char_x]==0){
								background_map[middle_char_y+1][middle_char_x]=null;
								c.clearRect(0,0,max_columns*60,max_rows*60);
								c_front.clearRect(0,0,max_columns*60,max_rows*60);
								drawBackground();
							}
						}
					}
					if(item_map[middle_char_y][middle_char_x]==11){ //button press
						item_map[middle_char_y][middle_char_x]=12;
						move_left_right_change = -move_left_right_change;
						if (move_left==1&&move_right==0){
							move_left=0;
							move_right=1;
						}
						else if(move_left==0&&move_right==1){
							move_right=0;
							move_left=1;
						}
						c.clearRect(0,0,max_columns*60,max_rows*60);
						c_front.clearRect(0,0,max_columns*60,max_rows*60);
						drawBackground();
					}
				}
				//-----------------SPECIAL------------------//
				if(last_up_v==0&&up_v==0) jump=0;
				last_up_v = up_v;
			}
			function moveCharacterV(){
				if(up_v>0){ //up
					if(up_y==-1){ //上面沒東西
						character_top-=up_v;
						document.all.character.style.top=character_top+"px";
					}
					else if((character_top-canvas_top-up_v)>up_y){ //加上這次的還不會碰到天花板
						character_top-=up_v;
						document.all.character.style.top=character_top+"px";
					}
					else{ //碰到了 速度=0
						character_top=up_y+canvas_top;
						document.all.character.style.top=character_top+"px";
						up_v=0;
					}
				}
				else if(up_v<0){ //down
					if((character_top+chara_height-canvas_top-up_v)<ground_y){ //加上這次的還不會碰到地板
						character_top-=up_v;
						document.all.character.style.top=character_top+"px";
					}
					else{ //碰到地板 跳的狀態結束 速度=0
						character_top=ground_y+canvas_top-chara_height;
						document.all.character.style.top=character_top+"px";
						jump=0;
						up_v=0;
					}
				}
			}
			function moveBackground(){ //移動背景 (移動畫布)
				if(move_left||move_right){
					c.clearRect(0,0,max_columns*60,max_rows*60); //記得清乾淨QQ
					c_front.clearRect(0,0,max_columns*60,max_rows*60); //記得清乾淨QQ
					if(move_left==1){
						c.translate(horizon_v,0);
						c_front.translate(horizon_v,0);
						position_show -= horizon_v;
					}
					if(move_right==1){
						c.translate(-horizon_v,0);
						c_front.translate(-horizon_v,0);
						position_show += horizon_v;
					}
					drawBackground();
				}
			}
			function moveCharacterH(){ //動角色
				if(move_left==1){
					if(character_left>=canvas_left+horizon_v){
						character_left-=horizon_v;
						document.all.character.style.left=character_left+"px";
					}
				}
				if(move_right==1){
					if(character_left<=canvas_left+550){
						character_left+=horizon_v;
						document.all.character.style.left=character_left+"px";
					}
				}
			}
			function clickStar(number){
				for(i=1;i<=10;i++){
					document.getElementById(stars[i-1]).src = "image/star.png";
				}
				for(i=1;i<=number;i++){
					document.getElementById(stars[i-1]).src ="image/star_hover.png";
				}
				if(number<=5) star_score = number;
				else star_score = number-5;
			}
			function overStar(number){
				for(i=1;i<=10;i++){
					document.getElementById(stars[i-1]).src="image/star.png";
				}
				for(i=1;i<=number;i++){
					document.getElementById(stars[i-1]).src="image/star_hover.png";
				}
			}
			function outStar(number){
				for(i=1;i<=10;i++){
					document.getElementById(stars[i-1]).src="image/star.png";
				}
				score = star_score;
				if(number>5) score+=5;
				for(i=1;i<=score;i++){
					document.getElementById(stars[i-1]).src="image/star_hover.png";
				}
			}
			function saveScore(){
				if(star_save==0){
					var xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
						if(xmlhttp.readyState == 4 && xmlhttp.status == 200){
							//document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
						}
					}
					xmlhttp.open("POST", "score_map.php", true);
					xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
					xmlhttp.send("mid="+<?php echo $mid;?>+"&score="+star_score);
				}
				document.all.scoreButton1.style.display="none";
				document.all.scoreButton2.style.display="none";
				star_save = 1;
			}
			function ifResize(){
				//alert("改變視窗大小遊戲會重新開始歐!");
				//window.location.reload();
				resize = 1;
			}
			function overRetry(){
				document.getElementById("replay1").src="image/retry_hover.png";
				document.getElementById("replay2").src="image/retry_hover.png";
			}
			function outRetry(){
				document.getElementById("replay1").src="image/retry.png";
				document.getElementById("replay2").src="image/retry.png";
			}
			function distanceBar(){
				middle_char_x = Math.floor((character_left-canvas_left+position_show+20)/60);
				if(middle_char_x==0) middle_char_x = 1;
				if(map_flag_x==0) map_flag_x = 1;
				//change image if need
				if(map_flag_x < middle_char_x && small_face_side!= -1){
					document.getElementById("small_char").src="image/char_move_left.gif";
					small_face_side = -small_face_side;
				}
				else if(map_flag_x > middle_char_x && small_face_side!=1){
					document.getElementById("small_char").src="image/char_move_right.gif";
					small_face_side = -small_face_side;
				}
				document.getElementById("char_path").style.width= (middle_char_x*600/map_max_x) + "px";
				document.getElementById("flag_path").style.width= (map_flag_x*600/map_max_x) + "px";
				setTimeout("distanceBar()",speed);
			}
		</script>
	</head>
	<body onkeydown="keydownFunction(event)" onkeyup="keyupFunction(event)" onload="init()" onresize="ifResize()" tabindex="0">
		<div id="winPage">
			<h3>You Win!</h3>
			<br>
			<img src="image/star_hover.png" style="width:40px; height:40px;" id="star1" onclick="clickStar(1)" onmouseover="overStar(1)" onmouseout="outStar(1)">
			<img src="image/star_hover.png" style="width:40px; height:40px;" id="star2" onclick="clickStar(2)" onmouseover="overStar(2)" onmouseout="outStar(2)">
			<img src="image/star_hover.png" style="width:40px; height:40px;" id="star3" onclick="clickStar(3)" onmouseover="overStar(3)" onmouseout="outStar(3)">
			<img src="image/star.png" style="width:40px; height:40px;" id="star4" onclick="clickStar(4)" onmouseover="overStar(4)" onmouseout="outStar(4)">
			<img src="image/star.png" style="width:40px; height:40px;" id="star5" onclick="clickStar(5)" onmouseover="overStar(5)" onmouseout="outStar(5)">
			<br><br>
			<div id="scoreButton1" class="button" onclick="saveScore()">Score</div>
			<br>
			<div>
			<a href="level_menu.php" target="_self" class="backNoFloat" style="position:absolute; top:200px; left:50%; margin-left:-60px;">.</a>
			<img src="image/retry.png" style="position:absolute; top:200px; left:50%; margin-left:20px; width:40px; height:40px;" id="replay1" onclick="reset()" onmouseover="overRetry()" onmouseout="outRetry()">
			</div>
		</div>
		<div id="losePage">
			<h3>You Die!</h3>
			<br>
			<img src="image/star_hover.png" style="width:40px; height:40px;" id="star6" onclick="clickStar(6)" onmouseover="overStar(6)" onmouseout="outStar(6)">
			<img src="image/star_hover.png" style="width:40px; height:40px;" id="star7" onclick="clickStar(7)" onmouseover="overStar(7)" onmouseout="outStar(7)">
			<img src="image/star_hover.png" style="width:40px; height:40px;" id="star8" onclick="clickStar(8)" onmouseover="overStar(8)" onmouseout="outStar(8)">
			<img src="image/star.png" style="width:40px; height:40px;" id="star9" onclick="clickStar(9)" onmouseover="overStar(9)" onmouseout="outStar(9)">
			<img src="image/star.png" style="width:40px; height:40px;" id="star10" onclick="clickStar(10)" onmouseover="overStar(10)" onmouseout="outStar(10)">
			<br><br>
			<div id="scoreButton2" class="button" onclick="saveScore()">Score</div>
			<br>
			<div>
			<a href="level_menu.php" target="_self" class="backNoFloat" style="position:absolute; top:200px; left:50%; margin-left:-60px;">.</a>
			<img src="image/retry.png" style="position:absolute; top:200px; left:50%; margin-left:20px; width:40px; height:40px;" id="replay2" onclick="reset()" onmouseover="overRetry()" onmouseout="outRetry()">
			</div>
		</div>
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
			<a href="level_menu.php" target="_self" class="back">B</a>
			<?php
				echo '<h1>'.$map_name.'</h1>';
			?>
			<div class="option" onclick="clickOption()"> </div>
		</header>
		<div id="content">
			&nbsp;
			<div id="gameCanvas">
				<canvas id="background_canvas" width=600 height=360></canvas>
			</div>
			<div id="gameCanvas_front">
				<canvas id="front_canvas" width=600 height=360></canvas>
			</div>
			<div id="gameCanvas_front_front">
				<canvas id="front_canvas" width=600 height=360></canvas>
			</div>
			<img src="image/char_stop_right.png" style="position:absolute; top:120px; left:50%; margin-left:-240px; width:50px; height:50px;" id="character">
			<div id="total_path"> </div>
			<div id="char_path">
				<img src="image/char_move_right.gif" style="width:20px; height:20px; float:right;" id="small_char">
			</div>
			<div id="flag_path">
				<img src="image/flag.png" style="width:20px; height:20px; float:right;" id="small_flag">
			</div>
			<p><span id="txtHint"></span></p>
			<p id="demo" style="text-align: left;"></p>
			<p id="demo2" style="text-align: left;"></p>
			<p id="demo3" style="text-align: left;"></p>
			<p id="demo4" style="text-align: left;"></p>
			<p id="demo5" style="text-align: left;"></p>
			<p id="demo6" style="text-align: left;"></p>
			<p id="demo7" style="text-align: left;"></p>
			<p id="demo8" style="text-align: left;"></p>
			<p id="demo9" style="text-align: left;"></p>
			<p id="demo10" style="text-align: left;"></p>
			<p id="demo11" style="text-align: left;"></p>
		</div>
		<?php
			require_once("header_footer.class.php");
			$project = new project();
			$project->output_footer();
			$db=null;
		?>
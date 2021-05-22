<?php

// 返回json格式的数据
header("Content-type:application/json");

// 开启session，判断登陆状态
session_start();
if(isset($_SESSION["huoma.admin"])){

	// 当前登录的用户
	$lguser= $_SESSION["huoma.admin"];

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获得表单POST过来的数据
	$active_title = trim($_POST["active_title"]);
	$active_yuming = trim($_POST["active_yuming"]);
	$active_qrcode = trim($_POST["active_qrcode"]);
	$active_url = trim($_POST["active_url"]);
	$active_content = trim($_POST["active_content"]);
	$active_shuoming = trim($_POST["active_shuoming"]);
	$active_type = trim($_POST["active_type"]);
	$active_endtime = trim($_POST["active_endtime"]);

	// 创建活码id和日期
	$active_id = rand(10000,99999);
	$active_update_time = date("Y-m-d");

	// 过滤表单
	if(empty($active_title)){
		$result = array(
			"code" => "101",
			"msg" => "标题不得为空"
		);
	}else if(empty($active_yuming)){
		$result = array(
			"code" => "102",
			"msg" => "请选择落地页域名"
		);
	}else if(empty($active_qrcode)){
		$result = array(
			"code" => "103",
			"msg" => "请上传微信二维码"
		);
	}else if(empty($active_shuoming)){
		$result = array(
			"code" => "104",
			"msg" => "请输入活动结束语"
		);
	}else if($active_type == 1 && empty($active_url)){
		$result = array(
			"code" => "105",
			"msg" => "请粘贴活动链接"
		);
	}else if($active_type == 2 && empty($active_content)){
		$result = array(
			"code" => "106",
			"msg" => "请编辑活动文案"
		);
	}else if(empty($active_type)){
		$result = array(
			"code" => "107",
			"msg" => "请选择活动展示形式"
		);
	}else{
		// 字符编码设为utf8
		mysqli_query($conn, "SET NAMES UTF-8"); 
		// 插入数据库
		$sql_creat_active = "INSERT INTO huoma_active (active_title,active_id,active_yuming,active_qrcode,active_update_time,active_url,active_shuoming,active_type,active_content,active_user,active_endtime) VALUES ('$active_title','$active_id','$active_yuming','$active_qrcode','$active_update_time','$active_url','$active_shuoming','$active_type','$active_content','$lguser','$active_endtime')";
		
		if ($conn->query($sql_creat_active) === TRUE) {
		    $result = array(
				"code" => "100",
				"msg" => "创建成功"
			);
		} else {
		    $result = array(
				"code" => "109",
				"msg" => "创建失败，数据库发生错误"
			);
		}
		
		// 断开数据库连接
		$conn->close();
	}
}else{
	$result = array(
		"code" => "108",
		"msg" => "未登录"
	);
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>
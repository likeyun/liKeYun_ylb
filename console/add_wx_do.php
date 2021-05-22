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
	$wx_title = trim($_POST["wx_title"]);
	$wx_yuming = trim($_POST["wx_yuming"]);
	$wx_qrcode = trim($_POST["wx_qrcode"]);
	$wx_num = trim($_POST["wx_num"]);
	$wx_shuoming = trim($_POST["wx_shuoming"]);

	// 创建活码id和日期
	$wx_id = rand(10000,99999);
	$wx_update_time = date("Y-m-d");

	// 过滤表单
	if(empty($wx_title)){
		$result = array(
			"code" => "101",
			"msg" => "标题不得为空"
		);
	}else if(empty($wx_yuming)){
		$result = array(
			"code" => "102",
			"msg" => "请选择落地页域名"
		);
	}else if(empty($wx_qrcode)){
		$result = array(
			"code" => "103",
			"msg" => "请上传微信二维码"
		);
	}else if(empty($wx_num)){
		$result = array(
			"code" => "104",
			"msg" => "请输入微信号"
		);
	}else{
		// 字符编码设为utf8
		mysqli_query($conn, "SET NAMES UTF-8"); 
		// 插入数据库
		$sql_creat_wx = "INSERT INTO huoma_wx (wx_title,wx_id,yuming,wx_qrcode,wx_update_time,wx_num,wx_shuoming,wx_user) VALUES ('$wx_title','$wx_id','$wx_yuming','$wx_qrcode','$wx_update_time','$wx_num','$wx_shuoming','$lguser')";
		
		if ($conn->query($sql_creat_wx) === TRUE) {
		    $result = array(
				"code" => "100",
				"msg" => "创建成功"
			);
		} else {
		    $result = array(
				"code" => "105",
				"msg" => "创建失败，数据库发生错误"
			);
		}
		
		// 断开数据库连接
		$conn->close();
	}
}else{
	$result = array(
		"code" => "106",
		"msg" => "未登录"
	);
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>
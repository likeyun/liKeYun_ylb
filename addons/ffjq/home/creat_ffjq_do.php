<?php

// 返回json格式的数据
header("Content-type:application/json");

// 开启session，判断登陆状态
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 当前登录的用户
	$lguser= $_SESSION["huoma.dashboard"];

	// 数据库配置
	include '../../../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获得表单POST过来的数据
	$ffjq_title = trim($_POST["ffjq_title"]);
	$ffjq_rkym = trim($_POST["ffjq_rkym"]);
	$ffjq_ldym = trim($_POST["ffjq_ldym"]);
	$ffjq_price = trim($_POST["ffjq_price"]);
	$ffjq_qrcode = trim($_POST["ffjq_qrcode"]);

	// 创建id
	$ffjq_id = rand(10000,99999);

	// 过滤表单
	if(empty($ffjq_title)){
		$result = array(
			"code" => "101",
			"msg" => "标题不得为空"
		);
	}else if(empty($ffjq_rkym)){
		$result = array(
			"code" => "102",
			"msg" => "请选择入口域名"
		);
	}else if(empty($ffjq_ldym)){
		$result = array(
			"code" => "103",
			"msg" => "请选择落地域名"
		);
	}else if(empty($ffjq_price)){
		$result = array(
			"code" => "104",
			"msg" => "请设置进群需支付的金额"
		);
	}else if(empty($ffjq_qrcode)){
		$result = array(
			"code" => "106",
			"msg" => "请上传微信群二维码"
		);
	}else{

		// 字符编码设为utf8
		mysqli_query($conn, "SET NAMES UTF-8"); 

		// 插入数据库
		$sql_creat_qun = "INSERT INTO huoma_addons_ffjq (ffjq_id,ffjq_title,ffjq_rkym,ffjq_ldym,ffjq_price,ffjq_qrcode,ffjq_user) VALUES ('$ffjq_id','$ffjq_title','$ffjq_rkym','$ffjq_ldym','$ffjq_price','$ffjq_qrcode','$lguser')";
		
		if ($conn->query($sql_creat_qun) === TRUE) {
			
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
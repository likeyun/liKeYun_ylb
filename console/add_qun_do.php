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
	$qun_title = trim($_POST["qun_title"]);
	$qun_rkym = trim($_POST["qun_rkym"]);
	$qun_ldym = trim($_POST["qun_ldym"]);
	$wx_status = trim($_POST["wx_status"]);
	$wx_qrcode = trim($_POST["wx_qrcode"]);

	// 创建活码id和日期
	$qun_hmid = rand(10000,99999);
	$qun_creat_time = date("Y-m-d");

	// 过滤表单
	if(empty($qun_title)){
		$result = array(
			"code" => "101",
			"msg" => "标题不得为空"
		);
	}else if(empty($qun_rkym)){
		$result = array(
			"code" => "102",
			"msg" => "请选择入口域名"
		);
	}else if(empty($qun_ldym)){
		$result = array(
			"code" => "103",
			"msg" => "请选择落地域名"
		);
	}else if(empty($wx_status)){
		$result = array(
			"code" => "104",
			"msg" => "请选择客服微信开启状态"
		);
	}else if(empty($wx_qrcode) && $wx_status == 1){
		$result = array(
			"code" => "105",
			"msg" => "请上传微信二维码"
		);
	}else{

		// 字符编码设为utf8
		mysqli_query($conn, "SET NAMES UTF-8"); 

		// 插入数据库
		$sql_creat_qun = "INSERT INTO huoma_qun (qun_title,qun_hmid,qun_rkym,qun_ldym,qun_wx_status,qun_wx_qrcode,qun_creat_time,qun_user) VALUES ('$qun_title','$qun_hmid','$qun_rkym','$qun_ldym','$wx_status','$wx_qrcode','$qun_creat_time','$lguser')";
		
		if ($conn->query($sql_creat_qun) === TRUE) {
			
			// 创建7个子码（一个默认群，6个备用群）
			$update_time = date("Y-m-d");
			$conn->query("INSERT INTO huoma_qunzima (hmid, zmid, update_time, xuhao) VALUES ('$qun_hmid','".rand(10000,99999)."','$update_time','1'),('$qun_hmid','".rand(10000,99999)."','$update_time','2'),('$qun_hmid','".rand(10000,99999)."','$update_time','3'),('$qun_hmid','".rand(10000,99999)."','$update_time','4'),('$qun_hmid','".rand(10000,99999)."','$update_time','5'),('$qun_hmid','".rand(10000,99999)."','$update_time','6'),('$qun_hmid','".rand(10000,99999)."','$update_time','7')");

		    $result = array(
				"code" => "100",
				"msg" => "创建成功"
			);
		} else {
		    $result = array(
				"code" => "106",
				"msg" => "创建失败，数据库发生错误"
			);
		}
		
		// 断开数据库连接
		$conn->close();
	}
}else{
	$result = array(
		"code" => "107",
		"msg" => "未登录"
	);
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>
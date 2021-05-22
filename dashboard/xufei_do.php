<?php
// 设置页面返回的字符编码为json格式
header("Content-type:application/json");

// 开启session，验证登录状态
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获得表单POST过来的数据
	$xufei_days = $_POST["xufei_days"];
	$user_id = $_POST["user_id"];

	if(empty($xufei_days)){
		$result = array(
			"code" => "101",
			"msg" => "续期的天数不得为空"
		);
	}else if(empty($user_id)){
		$result = array(
			"code" => "102",
			"msg" => "非法请求"
		);
	}else{
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 到期日期=当前日期+续期天数
		$daoqi_date = date("Y-m-d",strtotime("+".$xufei_days." day"));
		// 更新数据库
		mysqli_query($conn,"UPDATE huoma_user SET expire_time='$daoqi_date' WHERE user_id=".$user_id);
		$result = array(
			"code" => "100",
			"msg" => "已续期"
		);
	}
}else{
	$result = array(
		"code" => "103",
		"msg" => "未登录"
	);
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>
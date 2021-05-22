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
	$tc_id = $_POST["tc_id"];
	$tc_title = $_POST["tc_title"];
	$tc_price = $_POST["tc_price"];
	$tc_days = $_POST["tc_days"];

	if(empty($tc_title)){
		$result = array(
			"code" => "101",
			"msg" => "套餐标题不得为空"
		);
	}else if(empty($tc_id)){
		$result = array(
			"code" => "102",
			"msg" => "非法请求"
		);
	}else if(empty($tc_price)){
		$result = array(
			"code" => "103",
			"msg" => "套餐价格不得为空"
		);
	}else if(empty($tc_days)){
		$result = array(
			"code" => "104",
			"msg" => "续期天数不得为空"
		);
	}else{
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 更新数据库
		mysqli_query($conn,"UPDATE huoma_taocan SET tc_title='$tc_title',tc_days='$tc_days',tc_price='$tc_price' WHERE tc_id=".$tc_id);
		$result = array(
			"code" => "100",
			"msg" => "更新成功"
		);
	}
}else{
	$result = array(
		"code" => "105",
		"msg" => "未登录"
	);
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>
<?php

// 返回json格式的数据
header("Content-type:application/json");

// 开启session，判断登陆状态
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获得表单POST过来的数据
	$tc_title = trim($_POST["tc_title"]);
	$tc_days = trim($_POST["tc_days"]);
	$tc_price = trim($_POST["tc_price"]);

	// 过滤表单
	if(empty($tc_title)){
		$result = array(
			"code" => "101",
			"msg" => "套餐标题不得为空"
		);
	}else if (empty($tc_days)) {
		$result = array(
			"code" => "102",
			"msg" => "套餐天数不得为空"
		);
	}else if (empty($tc_price)){
		$result = array(
			"code" => "103",
			"msg" => "套餐价格不得为空"
		);
	}else{
		// 字符编码设为utf8
		mysqli_query($conn, "SET NAMES UTF-8");
		$tc_id = rand(100000,999999);
		// 插入数据库
		$sql_creat_tc = "INSERT INTO huoma_taocan (tc_title,tc_id,tc_days,tc_price) VALUES ('$tc_title','$tc_id','$tc_days','$tc_price')";
		
		if ($conn->query($sql_creat_tc) === TRUE) {
		    $result = array(
				"code" => "100",
				"msg" => "添加成功"
			);
		} else {
		    $result = array(
				"code" => "105",
				"msg" => "添加失败，数据库发生错误"
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
<?php

// 返回json格式的数据
header("Content-type:application/json");

// 开启session，判断登陆状态
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	// 数据库配置
	include '../../../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获得表单POST过来的数据
	$status = trim($_GET["status"]);
	$ffjq_id = trim($_GET["ffqid"]);

	// 过滤表单
	if(empty($status)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else if(empty($ffjq_id)){
		$result = array(
			"code" => "102",
			"msg" => "非法请求"
		);
	}else{

		// 字符编码设为utf8
		mysqli_query($conn, "SET NAMES UTF-8");

		if ($status == '1') {
			$ffjq_status = '2';
		}else{
			$ffjq_status = '1';
		}

		// 更新数据库
		$sql_update = "UPDATE huoma_addons_ffjq SET ffjq_status='$ffjq_status' WHERE ffjq_id=".$ffjq_id;
		
		if ($conn->query($sql_update) === TRUE) {
			
		    $result = array(
				"code" => "100",
				"msg" => "更新成功"
			);
		} else {
		    $result = array(
				"code" => "109",
				"msg" => "更新失败，数据库发生错误"
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
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
	$yuming = trim($_POST["yuming"]);

	// 判断传过来的是不是域名
	$yuming_str="#(http|https)://(.*\.)?.*\..*#i";

	// 过滤表单
	if(empty($yuming)){
		$result = array(
			"code" => "101",
			"msg" => "域名不得为空"
		);
	}else if (!preg_match($yuming_str,$yuming)) {
		$result = array(
			"code" => "102",
			"msg" => "请填写符合格式的域名"
		);
	}else if (substr($yuming, -1) == '/'){
		$result = array(
			"code" => "103",
			"msg" => "不得以 / 结尾"
		);
	}else{
		// 字符编码设为utf8
		mysqli_query($conn, "SET NAMES UTF-8"); 
		// 插入数据库
		$sql_creat_wx = "INSERT INTO huoma_yuming (yuming) VALUES ('$yuming')";
		
		if ($conn->query($sql_creat_wx) === TRUE) {
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
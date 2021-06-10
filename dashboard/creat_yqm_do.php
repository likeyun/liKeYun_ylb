<?php
// 返回json格式的数据
header("Content-type:application/json");

// 开启session，判断登陆状态
session_start();
if(isset($_SESSION["huoma.dashboard"])){

	$yaoqingma = trim($_POST['yqm']);
	$tianshu = trim($_POST['tianshu']);

	if (empty($yaoqingma)) {
		$result = array(
	        "code" => 201,
	        "msg" => "邀请码为空"
	    );
	}else if(empty($tianshu)){
		$result = array(
	        "code" => 202,
	        "msg" => "可用天数为空"
	    );
	}else{
		// 数据库配置
		include '../db_config/db_config.php';

		// 创建连接
		$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

		// 插入数据库的字符编码设为utf8
		mysqli_query($conn, "SET NAMES UTF-8");
		$sql_insert_yqm = "INSERT INTO huoma_yqm (yqm,yqm_status,yqm_daynum) VALUES ('$yaoqingma','1','$tianshu')";
	    if ($conn->query($sql_insert_yqm) === TRUE) {

		    // 导入成功
		    $result = array(
		        "code" => 200,
		        "msg" => "已生成"
		    );

		} else {

		    // 导入失败
		    $result = array(
		        "code" => 203,
		        "msg" => "生成失败"
		    );

		}

		// 断开数据库连接
		$conn->close();
	}
}

echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>
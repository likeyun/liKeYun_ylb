<?php
// 设置页面返回的字符编码为json格式
header("Content-type:application/json");

// 开启session，验证登录状态
session_start();
if(isset($_SESSION["huoma.admin"])){

	// 数据库配置
	include '../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 获得表单POST过来的数据
	$wx_title = trim($_POST["wx_title"]);
	$wx_ldym = trim($_POST["wx_ldym"]);
	$wx_status = trim($_POST["wx_status"]);
	$wx_id = trim($_POST["wx_id"]);
	$wx_moshi = trim($_POST["wx_moshi"]);
	$wx_online = trim($_POST["wx_online"]);

	if(empty($wx_title)){
		$result = array(
			"code" => "101",
			"msg" => "标题不得为空"
		);
	}else if(empty($wx_ldym)){
		$result = array(
			"code" => "102",
			"msg" => "请选择落地域名"
		);
	}else if(empty($wx_id)){
		$result = array(
			"code" => "103",
			"msg" => "非法提交"
		);
	}else if(empty($wx_status)){
		$result = array(
			"code" => "104",
			"msg" => "状态未选择"
		);
	}else if(empty($wx_moshi)){
		$result = array(
			"code" => "105",
			"msg" => "展示模式未选择"
		);
	}else{
		// 当前时间
		$date = date('Y-m-d');
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 更新数据库
		mysqli_query($conn,"UPDATE huoma_wx SET wx_title='$wx_title',wx_ldym='$wx_ldym',wx_status='$wx_status',wx_update_time='$date',wx_moshi='$wx_moshi',wx_online='$wx_online' WHERE wx_id=".$wx_id);
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
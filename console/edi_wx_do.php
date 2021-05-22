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
	$wx_yuming = trim($_POST["wx_yuming"]);
	$wx_status = trim($_POST["wx_status"]);
	$wx_qrcode = trim($_POST["wx_qrcode"]);
	$wx_id = trim($_POST["wx_id"]);
	$wx_num = trim($_POST["wx_num"]);
	$wx_shuoming = trim($_POST["wx_shuoming"]);

	if(empty($wx_title)){
		$result = array(
			"code" => "101",
			"msg" => "标题不得为空"
		);
	}else if(empty($wx_qrcode)){
		$result = array(
			"code" => "102",
			"msg" => "请上传二维码"
		);
	}else if(empty($wx_num)){
		$result = array(
			"code" => "103",
			"msg" => "请输入微信号"
		);
	}else{
		// 当前时间
		$date = date('Y-m-d');
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 更新数据库
		mysqli_query($conn,"UPDATE huoma_wx SET wx_title='$wx_title',yuming='$wx_yuming',wx_qrcode='$wx_qrcode',wx_status='$wx_status',wx_update_time='$date',wx_num='$wx_num',wx_shuoming='$wx_shuoming' WHERE wx_id=".$wx_id);
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
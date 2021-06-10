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
	$zmid = $_POST["zmid"];
	$qrcode = $_POST["qrcode"];
	$zima_status = $_POST["zima_status"];
	$wx_num = $_POST["wx_num"];
	$wx_beizhu = $_POST["wx_beizhu"];
	$wx_yuzhi = $_POST["wx_yuzhi"];

	if(empty($zmid)){
		$result = array(
			"code" => "101",
			"msg" => "非法请求"
		);
	}else if(empty($qrcode)){
		$result = array(
			"code" => "102",
			"msg" => "群二维码还没上传"
		);
	}else if(empty($zima_status)){
		$result = array(
			"code" => "103",
			"msg" => "请设置状态"
		);
	}else if(empty($wx_num)){
		$result = array(
			"code" => "104",
			"msg" => "请填写微信号"
		);
	}else if(empty($wx_yuzhi) && $wx_yuzhi !== '0'){
		$result = array(
			"code" => "106",
			"msg" => "请设置阈值"
		);
	}else{
		// 当前时间
		$date = date('Y-m-d');
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 更新数据库
		mysqli_query($conn,"UPDATE huoma_wxzima SET qrcode='$qrcode',update_time='$date',zima_status='$zima_status',wx_num='$wx_num',wx_beizhu='$wx_beizhu',wx_yuzhi='$wx_yuzhi' WHERE zmid=".$zmid);
		$result = array(
			"code" => "100",
			"msg" => "已更新"
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
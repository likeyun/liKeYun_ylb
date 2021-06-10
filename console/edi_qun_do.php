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
	$qun_title = trim($_POST["qun_title"]);
	$qun_rkym = trim($_POST["qun_rkym"]);
	$qun_ldym = trim($_POST["qun_ldym"]);
	$wx_status = trim($_POST["wx_status"]);
	$qun_wx_qrcode = trim($_POST["qun_wx_qrcode"]);
	$qun_hmid = trim($_POST["qun_hmid"]);
	$qun_status = trim($_POST["qun_status"]);
	$qun_chongfu = trim($_POST["qun_chongfu"]);

	if(empty($qun_title)){
		$result = array(
			"code" => "101",
			"msg" => "标题不得为空"
		);
	}else if(empty($qun_rkym)){
		$result = array(
			"code" => "102",
			"msg" => "入口域名不得为空"
		);
	}else if(empty($qun_ldym)){
		$result = array(
			"code" => "103",
			"msg" => "落地域名不得为空"
		);
	}else if(empty($wx_status)){
		$result = array(
			"code" => "104",
			"msg" => "请设置个人微信开启状态"
		);
	}else if(empty($qun_status)){
		$result = array(
			"code" => "105",
			"msg" => "请设置群活码开启状态"
		);
	}else{
		// 当前时间
		$date = date('Y-m-d');
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 更新数据库
		mysqli_query($conn,"UPDATE huoma_qun SET qun_title='$qun_title',qun_rkym='$qun_rkym',qun_ldym='$qun_ldym',qun_wx_qrcode='$qun_wx_qrcode',qun_wx_status='$wx_status',qun_status='$qun_status',qun_creat_time='$date',qun_chongfu='$qun_chongfu' WHERE qun_hmid=".$qun_hmid);
		$result = array(
			"code" => "100",
			"msg" => "更新成功"
		);
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
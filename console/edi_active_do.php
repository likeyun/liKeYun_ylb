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
	$active_title = trim($_POST["active_title"]);
	$active_yuming = trim($_POST["active_yuming"]);
	$active_status = trim($_POST["active_status"]);
	$active_qrcode = trim($_POST["active_qrcode"]);
	$active_id = trim($_POST["active_id"]);
	$active_url = trim($_POST["active_url"]);
	$active_type = trim($_POST["active_type"]);
	$active_content = trim($_POST["active_content"]);
	$active_shuoming = trim($_POST["active_shuoming"]);
	$active_endtime = trim($_POST["active_endtime"]);

	if(empty($active_title)){
		$result = array(
			"code" => "101",
			"msg" => "标题不得为空"
		);
	}else if(empty($active_qrcode)){
		$result = array(
			"code" => "102",
			"msg" => "请上传二维码"
		);
	}else if(empty($active_shuoming)){
		$result = array(
			"code" => "103",
			"msg" => "请输入活动结束语"
		);
	}else if($active_type == 1 && empty($active_url)){
		$result = array(
			"code" => "104",
			"msg" => "请粘贴活动链接"
		);
	}else if($active_type == 2 && empty($active_content)){
		$result = array(
			"code" => "105",
			"msg" => "请编辑活动文案"
		);
	}else{
		// 当前时间
		$date = date('Y-m-d');
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");
		// 更新数据库
		mysqli_query($conn,"UPDATE huoma_active SET active_title='$active_title',active_yuming='$active_yuming',active_qrcode='$active_qrcode',active_status='$active_status',active_update_time='$date',active_url='$active_url',active_content='$active_content',active_type='$active_type',active_status='$active_status',active_shuoming='$active_shuoming',active_endtime='$active_endtime' WHERE active_id=".$active_id);
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
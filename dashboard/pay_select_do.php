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
	$wxpay = $_POST["wxpay"];
	$alipay = $_POST["alipay"];

	if(empty($wxpay)){
		$result = array(
			"code" => "101",
			"msg" => "未选择微信支付接口"
		);
	}else if(empty($alipay)){
		$result = array(
			"code" => "102",
			"msg" => "未选择支付宝接口"
		);
	}else{
		// 设置字符编码为utf-8
		mysqli_query($conn, "SET NAMES UTF-8");

		// 更新数据库
		// 先把所有选项设置为1，即未设置状态
		mysqli_query($conn,"UPDATE huoma_payselect SET payselect='1'");
		// 再把当前的id设置为2，即当前的设置为选择
		mysqli_query($conn,"UPDATE huoma_payselect SET payselect='2' WHERE id=".$wxpay);
		mysqli_query($conn,"UPDATE huoma_payselect SET payselect='2' WHERE id=".$alipay);
		$result = array(
			"code" => "100",
			"msg" => "设置成功"
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
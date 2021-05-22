<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="color-scheme" content="light dark">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
	<link rel="shortcut icon" type="image/x-icon" href="http://res.wx.qq.com/a/wx_fed/assets/res/NTI4MWU5.ico">
	<link rel="mask-icon" href="http://res.wx.qq.com/a/wx_fed/assets/res/MjliNWVm.svg" color="#4C4C4C">
	<link rel="apple-touch-icon-precomposed" href="http://res.wx.qq.com/a/wx_fed/assets/res/OTE0YTAw.png">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" type="text/css" href="../../css/chunk-vendors.common.css">
</head>
<body>

<?php

// 页面字符编码
header("Content-type:text/html;charset=utf-8");
// 获取参数
$wx_id = $_GET["wxid"];
// 验证是否有参数
if (trim(empty($wx_id))) {
	echo "参数错误";
	exit;
}else{

	// 数据库配置
	include '../../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	if ($conn->connect_error) {
	    die("数据库连接失败: " . $conn->connect_error);
	} 
	
	// 获取活码信息
	$sql_hminfo = "SELECT * FROM huoma_wx WHERE wx_id =".$wx_id;
	$result_hminfo = $conn->query($sql_hminfo);
	while($row_hminfo = $result_hminfo->fetch_assoc()) {
		$wx_status = $row_hminfo["wx_status"]; // 微信活码启用状态
		$wx_title = $row_hminfo["wx_title"]; // 微信活码标题
		$wx_qrcode = $row_hminfo["wx_qrcode"]; // 个人微信二维码
		$wx_num = $row_hminfo["wx_num"]; // 微信号
		$wx_shuoming = $row_hminfo["wx_shuoming"]; // 加微信说明
	}

	// 更新活码访问量
	mysqli_query($conn,"UPDATE huoma_wx SET wx_fwl=wx_fwl+1 WHERE wx_id =".$wx_id);
	
	// 验证该活码是否存在
	if ($result_hminfo->num_rows > 0) {
		/**
		 * 验证群活码的状态
		 * wx_status=1 开启
		 * wx_status=2 关闭
		 * wx_status=3 停用
		 */
	    if ($wx_status == '1') {

			// 设置活码标题
			echo '<title>'.$wx_title.'</title>';

			echo '
			<!-- 顶部提示 -->
			<div id="safety-tips">
			<div class="safety-icon">
			<img src="../../images/safety-icon.png" />
			</div>
			<div class="safety-title">此二维码已通过安全认证，可以放心扫码</div>
			</div>
			';

			// 扫码提示
			echo '<br/><div id="scan_tips">请再次识别下方二维码加微信</div><br/>';

			// 展示二维码
			echo '<div id="hm_wxewm"><img src="'.$wx_qrcode.'" /></div>';

			// 微信号
			echo '<div id="wx_num">微信号：'.$wx_num.'</div>';

			// 加微信说明
			if (!empty($wx_shuoming)) {
				echo '<div id="shuoming">'.$wx_shuoming.'</div>';
			}

	    }else if ($wx_status == '2') {

	    	// 设置群活码标题
			echo '<title>提醒</title>';
	    	echo '<br/><br/><br/>';
	       	echo '<div id="tips_icon"><img src="../../images/warning.png" /></div>';
	       	echo '<div id="tips_text">该二维码已被管理员暂停使用</div>';
	    }else if ($wx_status == '3') {
	    	// 设置群活码标题
			echo '<title>提醒</title>';
	    	echo '<br/><br/><br/>';
	       	echo '<div id="tips_icon"><img src="../../images/error.png" /></div>';
	       	echo '<div id="tips_text">该二维码因违规已被管理员停止使用</div>';
	    }
	} else {
		// 设置群活码标题
		echo '<title>提醒</title>';
    	echo '<br/><br/><br/>';
       	echo '<div id="tips_icon"><img src="../../images/error.png" /></div>';
       	echo '<div id="tips_text">该二维码不存在或已被管理员删除</div>';
	}// 验证该页面是否存在结束
	$conn->close();
}// 验证是否有参数结束
?>
</body>
</html>

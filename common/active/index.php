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
<body style="background: #fff;">

<?php

// 页面字符编码
header("Content-type:text/html;charset=utf-8");
// 获取参数
$active_id = $_GET["activeid"];
// 验证是否有参数
if (trim(empty($active_id))) {
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
	$sql_hminfo = "SELECT * FROM huoma_active WHERE active_id =".$active_id;
	$result_hminfo = $conn->query($sql_hminfo);
	while($row_hminfo = $result_hminfo->fetch_assoc()) {
		$active_status = $row_hminfo["active_status"]; // 活动码启用状态
		$active_title = $row_hminfo["active_title"]; // 活动码标题
		$active_qrcode = $row_hminfo["active_qrcode"]; // 个人微信二维码
		$active_shuoming = $row_hminfo["active_shuoming"]; // 活动结束说明
		$active_url = $row_hminfo["active_url"]; // 活动跳转链接
		$active_content = $row_hminfo["active_content"]; // 活动内容
		$active_update_time = $row_hminfo["active_update_time"]; // 活动更新时间
		$active_pv = $row_hminfo["active_pv"]; // 活动访问量
		$active_type = $row_hminfo["active_type"]; // 活动类型
		$active_endtime = $row_hminfo["active_endtime"]; // 活动结束时间
	}

	// 更新活码访问量
	mysqli_query($conn,"UPDATE huoma_active SET active_pv=active_pv+1 WHERE active_id =".$active_id);
	
	// 验证该活码是否存在
	if ($result_hminfo->num_rows > 0) {
		/**
		 * 验证群活码的状态
		 * active_status=1 开启
		 * active_status=2 关闭
		 * active_status=3 停用
		 * active_status=4 结束
		 */
	    
	    // 首先验证活动是否已经达到结束的时间
	    // 如果当前时间>=活动结束的时间
		if (strtotime(date('Y-m-d')) >= strtotime($active_endtime) && $active_endtime !== '') {
			echo '<title>提醒</title>';
	    	echo '<br/><br/><br/>';
	       	echo '<div id="wxewm"><img src="'.$active_qrcode.'"/></div>';
	       	echo '<div id="tips_text">'.$active_shuoming.'</div>';
			exit;
		}

	    if ($active_status == '1') {

			// 设置活码标题
			echo '<title>'.$active_title.'</title>';

			// 判断活动形式
			if ($active_type == '1') {
				// $active_type=1，跳转链接
				header('Location:'.$active_url);
			}else{
				// 活动标题
				echo '<div id="active_title">'.$active_title.'</div>';

				// 发布信息
				echo '<div id="active_int">
				<span class="active_update_time">'.$active_update_time.'</span>
				<span class="active_pv">'.$active_pv.'次查看</span>
				</div>';
				
				// 活动内容
				echo '<div id="active_content">'.$active_content.'</div>';
			}
	    }else if ($active_status == '2') {

	    	// 设置群活码标题
			echo '<title>提醒</title>';
	    	echo '<br/><br/><br/>';
	       	echo '<div id="tips_icon"><img src="../../images/warning.png" /></div>';
	       	echo '<div id="tips_text">该页面已被管理员暂停使用</div>';
	    }else if ($active_status == '3') {
	    	// 设置群活码标题
			echo '<title>提醒</title>';
	    	echo '<br/><br/><br/>';
	       	echo '<div id="tips_icon"><img src="../../images/error.png" /></div>';
	       	echo '<div id="tips_text">该页面因违规已被管理员停止访问</div>';
	    }else if ($active_status == '4') {
	    	// 设置群活码标题
			echo '<title>提醒</title>';
	    	echo '<br/><br/><br/>';
	       	echo '<div id="wxewm"><img src="'.$active_qrcode.'"/></div>';
	       	echo '<div id="tips_text">'.$active_shuoming.'</div>';
	    }
	} else {
		// 设置群活码标题
		echo '<title>提醒</title>';
    	echo '<br/><br/><br/>';
       	echo '<div id="tips_icon"><img src="../../images/error.png" /></div>';
       	echo '<div id="tips_text">该页面不存在或已被管理员删除</div>';
	}// 验证该页面是否存在结束
	$conn->close();
}// 验证是否有参数结束
?>
</body>
</html>

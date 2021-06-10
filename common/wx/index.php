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
		$wx_user = $row_hminfo["wx_user"]; // 发布者
		$wx_moshi = $row_hminfo["wx_moshi"]; // 展示模式
	}
	
	// 获取客服子码信息
	$sql_zminfo = "SELECT * FROM huoma_wxzima WHERE wx_id = '$wx_id' AND zima_status = '1'";
	$result_zminfo = $conn->query($sql_zminfo);

	// 更新活码访问量
	mysqli_query($conn,"UPDATE huoma_wx SET wx_fwl=wx_fwl+1 WHERE wx_id =".$wx_id);

	// 获取用户账号信息
	// 判断用户账号到期
	$sql_userinfo = "SELECT * FROM huoma_user WHERE user = '$wx_user'";
	$result_userinfo = $conn->query($sql_userinfo);
	if ($result_userinfo->num_rows > 0) {
		while($row_userinfo = $result_userinfo->fetch_assoc()) {
			$user_status = $row_userinfo["user_status"]; // 账号状态
			$expire_time = $row_userinfo["expire_time"]; // 到期日期
		}
		if ($user_status !== '1') {
			echo '<title>提醒</title>
	    		  <br/><br/><br/>
	       		  <div id="tips_icon"><img src="../../images/warning.png" /></div>
	              <div id="tips_text">管理员账号异常</div>';
			exit;
		}
		if(strtotime(date("Y-m-d"))>=strtotime($expire_time)){
			echo '<title>提醒</title>
	    		  <br/><br/><br/>
	       		  <div id="tips_icon"><img src="../../images/warning.png" /></div>
	              <div id="tips_text">管理员账号已到期</div>';
			exit;
		}
	}else{
		echo '<title>提醒</title>
    		  <br/><br/><br/>
       		  <div id="tips_icon"><img src="../../images/warning.png" /></div>
              <div id="tips_text">管理员账号不存在</div>';
		exit;
	}
	
	// 验证该活码是否存在
	if ($result_hminfo->num_rows > 0) {
		/**
		 * 验证群活码的状态
		 * wx_status=1 开启
		 * wx_status=2 关闭
		 * wx_status=3 停用
		 */
	    if ($wx_status == '1') {

	    	// 定义一个数组，用来储存所有子码
	    	$kfzmlist = array();

	    	// 获取子码
	    	while($row_zminfo = $result_zminfo->fetch_assoc()) {
	    		
	    		// 将所有子码添加到数组
	    		$kfzmlist[] = $row_zminfo;

	    	}

	    	// 定义一个数组，用来储存经过条件筛选后的子码
		    $kfzm = [];

	    	// 展示模式
	    	if ($wx_moshi == '1') {

	    		// 阈值模式
		    	// 遍历所有符合以下条件的子码
		    	foreach ($kfzmlist as $k=>$v){
		    		if($kfzmlist[$k]['fwl'] < $kfzmlist[$k]['wx_yuzhi']){

		    			// 返回符合条件的数组
			       		$kfzm = $kfzmlist[$k];
			       		$zmid = $kfzmlist[$k]['zmid'];
			       		$qrcodeUrl = $kfzmlist[$k]['qrcode'];
			       		$wx_num = $kfzmlist[$k]['wx_num'];
			       		$wx_beizhu = trim($kfzmlist[$k]['wx_beizhu']);

			       		// 设置群活码标题
						echo '<title>'.$wx_title.'</title>';

						echo '<div id="safety-tips">
						<div class="safety-icon">
						<img src="../../images/safety-icon.png" />
						</div>
						<div class="safety-title">此二维码已通过安全认证，可以放心扫码</div>
						</div>';

			       		// 扫码提示
			       		echo '<div id="scan_tips" style="color:#999;">请再次识别下方二维码加微信</div>';

			       		// 展示二维码
			       		echo '<div id="ewm" style="width:280px;"><img src="'.$qrcodeUrl.'" width="280"/></div>';

			       		// 加微信
			       		echo '<div id="wxnum">微信号：'.$wx_num.'<div>';

			       		// 加微信备注
			       		if ($wx_beizhu !== '') {
			       			echo '<div id="wxbeizhu">'.$wx_beizhu.'<div>';
			       		}

			       		$exist = false;
			       		// 更新当前子码的访问量
			       		mysqli_query($conn,"UPDATE huoma_wxzima SET fwl=fwl+1 WHERE zmid='$zmid'");
			       		exit;

		    		}else{
						$exist = false;
			    	}
		    	}
		    	if(!$exist && count($kfzm) <= 0) {

					// 设置活码标题
					echo '<title>提醒</title>';
		       		echo '<br/><br/><br/>
		       			  <div id="tips_icon"><img src="../../images/warning.png" /></div>
		       			  <div id="tips_text">暂无微信可以添加</div>';
				}
	    	}else if($wx_moshi == '2'){
	    		// 将数组打乱
	    		shuffle($kfzmlist);
	    		// 遍历数组，取第一个对象
	    		foreach ($kfzmlist as $k=>$v){
	    			$kfzm = $kfzmlist[$k];
		       		$zmid = $kfzmlist[$k]['zmid'];
		       		$qrcodeUrl = $kfzmlist[$k]['qrcode'];
		       		$wx_num = $kfzmlist[$k]['wx_num'];
		       		$wx_beizhu = trim($kfzmlist[$k]['wx_beizhu']);
		       		
		       		// 设置群活码标题
					echo '<title>'.$wx_title.'</title>';

					echo '<div id="safety-tips">
					<div class="safety-icon">
					<img src="../../images/safety-icon.png" />
					</div>
					<div class="safety-title">此二维码已通过安全认证，可以放心扫码</div>
					</div>';

		       		// 扫码提示
		       		echo '<div id="scan_tips" style="color:#999;">请再次识别下方二维码加微信</div>';

		       		// 展示二维码
		       		echo '<div id="ewm" style="width:280px;"><img src="'.$qrcodeUrl.'" width="280"/></div>';

		       		// 加微信
		       		echo '<div id="wxnum">微信号：'.$wx_num.'<div>';

		       		// 加微信备注
		       		if ($wx_beizhu !== '') {
		       			echo '<div id="wxbeizhu">'.$wx_beizhu.'<div>';
		       		}

		       		$exist = false;
		       		// 更新当前子码的访问量
		       		mysqli_query($conn,"UPDATE huoma_wxzima SET fwl=fwl+1 WHERE zmid='$zmid'");
		       		exit;
	    		}
	    		if(!$exist && count($kfzm) <= 0) {

					// 设置活码标题
					echo '<title>提醒</title>';
		       		echo '<br/><br/><br/>
		       			  <div id="tips_icon"><img src="../../images/warning.png" /></div>
		       			  <div id="tips_text">暂无微信可以添加</div>';
				}
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

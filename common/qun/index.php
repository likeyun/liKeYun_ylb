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
$qun_hmid = $_GET["hmid"];

// 验证是否有参数
if (trim(empty($qun_hmid))) {
	echo "参数错误";
}else{
	
	// 数据库配置
	include '../../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	if ($conn->connect_error) {
	    die("数据库连接失败: " . $conn->connect_error);
	} 
	
	// 获取活码信息
	$sql_hminfo = "SELECT * FROM huoma_qun WHERE qun_hmid =".$qun_hmid;
	$result_hminfo = $conn->query($sql_hminfo);
	while($row_hminfo = $result_hminfo->fetch_assoc()) {
		$qun_status = $row_hminfo["qun_status"]; // 群活码启用状态
		$qun_title = $row_hminfo["qun_title"]; // 群活码标题
		$qun_wx_status = $row_hminfo["qun_wx_status"]; // 个人微信二维码展示状态
		$qun_wx_qrcode = $row_hminfo["qun_wx_qrcode"]; // 个人微信二维码
		$qun_chongfu = $row_hminfo["qun_chongfu"]; // 防止重复进群
		$qun_user = $row_hminfo["qun_user"]; // 发布者
	}

	// 获取子码信息
	$sql_zminfo = "SELECT * FROM huoma_qunzima WHERE hmid =".$qun_hmid;
	$result_zminfo = $conn->query($sql_zminfo);

	// 更新活码访问量
	mysqli_query($conn,"UPDATE huoma_qun SET qun_pv=qun_pv+1 WHERE qun_hmid =".$qun_hmid);

	// 获取用户账号信息
	// 判断用户账号到期
	$sql_userinfo = "SELECT * FROM huoma_user WHERE user = '$qun_user'";
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
		 * qun_status=1 开启
		 * qun_status=2 关闭
		 * qun_status=3 停用
		 */
	    if ($qun_status == '1') {

			// 防止重复进群，通过验证本地缓存的方式实现
			// 缓存有效期是1个月，只要用户浏览器不清理缓存，一个月内，都是看不到新的群二维码
			// 禁止重复进群的意思是当第一个群的阈值达到了，切换为第二个群，第一次访问的用户才可以看到
			// 第二个群二维码，重复访问页面，也只能看到第一次进入群活码页面的二维码，不会看到下一个阈值的二维码
			// $qun_chongfu == '1'代表不允许重复进群（禁止重复进群开启状态）
			if ($_COOKIE[$qun_hmid] == !null && $qun_chongfu == '1') {
				echo '<title>'.$qun_title.'</title>';
				echo '<div id="safety-tips">
						<div class="safety-icon">
							<img src="../../images/safety-icon.png" />
						</div>
						<div class="safety-title">此二维码已通过安全认证，可以放心扫码</div>
					 </div>
			         <div id="scan_tips">请再次识别下方二维码进群</div>
				     <div id="ewm"><img src="'.$_COOKIE[$qun_hmid].'" /></div>';
				// 展示个人微信
	       		if ($qun_wx_status == '1') {
	       			echo '<div id="scan_wx_tips">如无法扫码进群，可联系群主邀请</div>
	       			      <div id="wxewm"><img src="'.$qun_wx_qrcode.'" /></div>';
	       		}
				exit;
			}


	    	// 定义一个数组，用来储存所有子码
	    	$zmlist = array();
	    	// 获取子码
	    	while($row_zminfo = $result_zminfo->fetch_assoc()) {
	    		// 将所有子码添加到数组
	    		$zmlist[] = $row_zminfo;
	    	}

	    	// 定义一个数组，用来储存经过条件筛选后的子码
	    	$zm = [];
	    	// 遍历所有符合以下条件的子码
	    	foreach ($zmlist as $k=>$v){
		    	if($zmlist[$k]['fwl'] < $zmlist[$k]['yuzhi'] && $zmlist[$k]['zima_status'] == 1){

		    		// 返回符合条件的数组
		       		$zm = $zmlist[$k];
		       		$zmid = $zmlist[$k]['zmid'];
		       		$qrcodeUrl = $zmlist[$k]['qrcode'];

		       		// 设置群活码标题
					echo '<title>'.$qun_title.'</title>';

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
		       		echo '<div id="scan_tips">请再次识别下方二维码进群</div>';

		       		// 展示群二维码
		       		echo '<div id="ewm"><img src="'.$qrcodeUrl.'" /></div>';

		       		// 展示个人微信
		       		if ($qun_wx_status == '1') {
		       			echo '<div id="scan_wx_tips">如无法扫码进群，可联系群主邀请</div>
		       			      <div id="wxewm"><img src="'.$qun_wx_qrcode.'" /></div>';
		       		}
		       		$exist = false;
		       		// 更新当前子码的访问量
		       		mysqli_query($conn,"UPDATE huoma_qunzima SET fwl=fwl+1 WHERE zmid='$zmid'");

		       		// 将子码缓存到本地
		       		if ($_COOKIE[$qun_hmid] == null) {
		       			$expire_zima=time()+60*60*24*30;
						setcookie($qun_hmid, $qrcodeUrl, $expire_zima);
		       		}
		       		exit;
		    	}else{
					$exist = false;
		    	}
			}
			if(!$exist && count($zm) <= 0) {

				// 设置群活码标题
				echo '<title>提醒</title>';

			    // 没有符合条件的群二维码
	       		if ($qun_wx_status == '1') {
	       			echo '<br/><br/><br/>
	       			      <div id="wxewm"><img src="'.$qun_wx_qrcode.'" /></div>
	       			      <div id="tips_text">暂无群可以加入，如需进群可联系群主</div>';
	       		}else{
	       			echo '<br/><br/><br/>
	       			      <div id="tips_icon"><img src="../../images/warning.png" /></div>
	       			      <div id="tips_text">暂无群可以加入</div>';
	       		}
			}
	    }else if ($qun_status == '2') {

	    	// 设置群活码标题
			echo '<title>提醒</title>
	    		  <br/><br/><br/>
	       		  <div id="tips_icon"><img src="../../images/warning.png" /></div>
	              <div id="tips_text">该二维码已被管理员暂停使用</div>';
	    }else if ($qun_status == '3') {
	    	// 设置群活码标题
			echo '<title>提醒</title>
		    	  <br/><br/><br/>
		       	  <div id="tips_icon"><img src="../../images/error.png" /></div>
		       	  <div id="tips_text">该二维码因违规已被管理员停止使用</div>';
	    }
	} else {
		// 设置群活码标题
		echo '<title>提醒</title>
    	      <br/><br/><br/>
       	      <div id="tips_icon"><img src="../../images/error.png" /></div>
       	      <div id="tips_text">该二维码不存在或已被管理员删除</div>';
	}// 验证该页面是否存在结束
	$conn->close();
}// 验证是否有参数结束
?>
</body>
</html>

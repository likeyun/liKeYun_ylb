<?php
// 返回JSON格式的数据
header("Content-Type:application/json");

// 获取前端POST过来的数据
$user = trim($_POST["user"]);
$pwd = trim($_POST["pwd"]);

// 过滤表单
if (empty($user)) {
	// 请求结果数组
	$result = array(
		'code' => '101',
		'msg' => '账号未填'
	);
}else if (empty($pwd)) {
	// 请求结果数组
	$result = array(
		'code' => '102',
		'msg' => '密码未填'
	);
}else{
	// 连接数据库
	include '../../../db_config/db_config.php';

	// 创建连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
	 
	$check_user = "SELECT * FROM huoma_user WHERE user='$user' AND pwd='$pwd'";
	$result_user = $conn->query($check_user);
	
	// 验证结果
	if ($result_user->num_rows > 0) {
	    // 账号密码正确
	    // 获取账号的过期时间、使用状态
	    while($row_userinfo = $result_user->fetch_assoc()) {
			$user_status = $row_userinfo["user_status"];
			$expire_time = $row_userinfo["expire_time"]; // 到期日期
			$user_limit = $row_userinfo["user_limit"]; // 用户权限
		}

		// 计算是否已经到期
		date_default_timezone_set("Asia/Shanghai");
		$thisdate=date("Y-m-d");// 当前日期

		// 判断逻辑
		if ($user_status == 1) {
			// 判断账号是否已到期
			if(strtotime($thisdate)<strtotime($expire_time)){
				// 账号正常、未到期
				// 判断用户是否有权限登陆
				if ($user_limit == '1') {
					$result = array(
						'code' => '107',
						'msg' => '你的账号没有管理权限'
					);
				}else{
					$result = array(
						'code' => '100',
						'msg' => '登录成功'
					);
					session_start();
					$_SESSION['huoma.dashboard'] = $user;
				}
			}else{
				$result = array(
					'code' => '103',
					'msg' => '该账号已到期，请续期'
				);
			}
		}else if ($user_status == 2){
			$result = array(
				"result" => "104",
				"msg" => "该账号已被停止使用"
			);
		}else if ($user_status == 3){
			$result = array(
				"result" => "105",
				"msg" => "该账号已被永久封号"
			);
		}
	} else {
	    $result = array(
			'code' => '106',
			'msg' => '账号或密码错误'
		);
	}
	// 断开数据库连接
	$conn->close();
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>
<?php
header("Content-type:application/json");

$user = trim($_POST["user"]);
$pwd = trim($_POST["pwd"]);
$email = trim($_POST["email"]);
$yqm = trim($_POST["yqm"]);

//账号不能存在特殊符号
$tsfh = preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user);

//账号不能存在中文
$userczzw = preg_match('/[\x{4e00}-\x{9fa5}]/u', $user);
$pwdczzw = preg_match('/[\x{4e00}-\x{9fa5}]/u', $pwd);

if($user == "" && $pwd == "" && $email == "" && $yqm == ""){
	$result = array(
		"code" => "101",
		"msg" => "所有项不得为空"
	);
}else if($user == ""){
	$result = array(
		"code" => "102",
		"msg" => "账号不得为空"
	);
}else if ($pwd == "") {
	$result = array(
		"code" => "103",
		"msg" => "密码不得为空"
	);
}else if ($email == "") {
	$result = array(
		"code" => "104",
		"msg" => "邮箱不得为空"
	);
}else if ($yqm == "") {
	$result = array(
		"code" => "105",
		"msg" => "邀请码不得为空"
	);
}else if (strlen($user) < 6) {
	$result = array(
		"code" => "106",
		"msg" => "账号长度不得小于6个字符"
	);
}else if (strlen($pwd) < 8) {
	$result = array(
		"code" => "107",
		"msg" => "密码长度不得小于8个字符"
	);
}else if ($tsfh) {
	$result = array(
		"code" => "108",
		"msg" => "账号不能存在特殊字符"
	);
}else if($userczzw>0){
	$result = array(
		"code" => "109",
		"msg" => "账号不能存在中文"
	);
}else if($pwdczzw>0){
	$result = array(
		"code" => "110",
		"msg" => "密码不能存在中文"
	);
}else{

	// 连接数据库
	include '../../db_config/db_config.php';

	// 创建数据库连接
	$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

	// 验证邀请码
	$sql_checkyqm = "SELECT * FROM huoma_yqm WHERE yqm = '$yqm'";
	$result_yqm = $conn->query($sql_checkyqm);
	 
	if ($result_yqm->num_rows > 0) {
	    // 输出数据
	    while($row_yqm = $result_yqm->fetch_assoc()) {
	    	$yqm_status = $row_yqm["yqm_status"]; // 邀请码的使用状态1为未使用2为已使用
	    	$yqm_daynum = $row_yqm["yqm_daynum"]; // 可以使用的时间，单位：天
	    	$daoqidate = $yqm_daynum+1; // 到期时间需要在使用时间+1天
	    	if ($yqm_status == 2) {
	    		$result = array(
					"code" => "111",
					"msg" => "邀请码已被使用"
				);
	    	}else{
	    		// 验证是否存在该账号
				$sql_checkuser = "SELECT * FROM huoma_user WHERE user = '$user'";
				$result_checkuser=mysqli_query($conn,$sql_checkuser);
				$row_checkuser=mysqli_num_rows($result_checkuser);
				if ($row_checkuser) {
					// 如果存在，则代表该帐号已经被注册
					$result = array(
						"code" => "112",
						"msg" => "该帐号已被注册"
					);
				}else{
					$user_id = rand(10000,99999);// 生成uid
					$expire_time = date("Y-m-d",strtotime("+".$daoqidate." day")); // 过期时间
					$sql_creatuser = "INSERT INTO huoma_user (user_id, user, pwd, expire_time, email, user_status, user_limit) VALUES ('$user_id', '$user', '$pwd', '$expire_time', '$email', '1', '1')";
					// 验证是否成功
					if ($conn->query($sql_creatuser) === TRUE) {
					    $result = array(
							"code" => "100",
							"msg" => "注册成功"
						);

					// 注册成功后，邀请码的状态修改为已使用
					$usetime = date('Y-m-d H:i:s',time());
					mysqli_query($conn,"UPDATE huoma_yqm SET yqm_status='2',yqm_usetime='$usetime' WHERE yqm='$yqm'");

					$sendurl = dirname(dirname('http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"])).'/email/send_reg.php?email='.$email.'&user='.$user.'&pwd='.$pwd;
					file_get_contents($sendurl);

					} else {
					    $result = array(
							"code" => "113",
							"msg" => "注册失败，请检查数据库"
						);
					}
				}
	    	} 
	    }
	} else {
	    $result = array(
			"code" => "114",
			"msg" => "邀请码不正确"
		);
	}
}
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>
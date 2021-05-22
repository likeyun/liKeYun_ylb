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
	$yqmstr = $_POST["yqmstr"];
	$userid = $_POST["userid"];
	$expire_time = $_POST["expire_time"];

	if(empty($yqmstr)){
		$result = array(
			"code" => "101",
			"msg" => "邀请码不得为空"
		);
	}else if(empty($userid)){
		$result = array(
			"code" => "102",
			"msg" => "非法请求"
		);
	}else if(empty($userid)){
		$result = array(
			"code" => "103",
			"msg" => "非法请求"
		);
	}else{
		// 验证邀请码
		$sql_checkyqm = "SELECT * FROM huoma_yqm WHERE yqm = '$yqmstr'";
		$result_yqm = $conn->query($sql_checkyqm);
		if ($result_yqm->num_rows > 0) {
			// 获得当前邀请码的信息
			while($row_yqm = $result_yqm->fetch_assoc()) {
				$yqm_status = $row_yqm["yqm_status"]; // 邀请码的使用状态，1为未使用，2为已使用
				$yqm_daynum = $row_yqm["yqm_daynum"]; // 可以使用的时间，单位：天
				$daoqi_daynum = $yqm_daynum+1; // 到期天数，需要在使用邀请码当天+1天
			}
			if ($yqm_status == 2) {
				// 如果yqm_status=2就是邀请码被使用了
				$result = array(
					"code" => "105",
					"msg" => "邀请码已被使用"
				);
			}else{
				// 否则就可以进行续费了
				// 计算过期时间（在即将到期的日期基础上，增加续费的天数，得出新的到期日期）
				$new_daoqidate = date('Y-m-d',strtotime("{$expire_time} + ".$daoqi_daynum." day"));
				// 设置字符编码为utf-8
				mysqli_query($conn, "SET NAMES UTF-8");
				$xufei_sql = "UPDATE huoma_user SET expire_time='$new_daoqidate' WHERE user_id=".$userid;
				if ($conn->query($xufei_sql) === TRUE) {
					$result = array(
						"code" => "100",
						"msg" => "续费成功"
					);
					// 续费成功后，需要将邀请码状态修改为已使用
					$usetime = date('Y-m-d H:i:s',time());
					mysqli_query($conn,"UPDATE huoma_yqm SET yqm_status='2',yqm_usetime='$usetime' WHERE yqm='$yqmstr'");
				}else{
					$result = array(
						"code" => "106",
						"msg" => "续费失败"
					);
				}
				
			}
		}else{
			$result = array(
				"code" => "104",
				"msg" => "邀请码不正确"
			);
		}
	}
}else{
	$result = array(
		"code" => "107",
		"msg" => "未登录"
	);
}

// 输出JSON格式的数据
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>
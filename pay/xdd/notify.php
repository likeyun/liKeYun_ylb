<?php
	// 返回的页面编码
	header("Content-type:text/html;charset=utf-8");

	// 引入配置文件
	include '../../db_config/db_config.php';
	include './config.php';

	// 接收参数
	$order_no = $_POST["order_no"];
	$subject = $_POST["subject"];
	$pay_type = $_POST["pay_type"];
	$money = $_POST["money"];
	$realmoney = $_POST["realmoney"];
	$result = $_POST["result"];
	$xddpay_order = $_POST["xddpay_order"];
	$app_id = $_POST["app_id"];
	$extra = $_POST["extra"];
	$sign = $_POST["sign"];

	$tc_days_1 = substr($extra,strripos($extra,"-")+1);
	$tc_days = substr($tc_days_1, 0, strrpos($tc_days_1, "_"));
	$pay_type = substr($extra,strripos($extra,"_")+1);
	$user_id = substr($extra,0,strrpos($extra,"-"));

	if ($pay_type == 'wx') {
		$pay_type_str = '小叮当微信支付';
	}else{
		$pay_type_str = '小叮当支付宝';
	}

	// 验证签名，防止恶意攻击
	$sign_check = MD5('order_no='.$order_no.'&subject='.$subject.'&pay_type='.$pay_type.'&money='.$money.'&app_id='.$app_id.'&extra='.$extra.'&'.$app_key);
	$signStr = strtoupper($sign_check); // 签名转换为大写

	// 异步传过来的签名和当前生成的签名一致，就允许进行异步通知
	if ($sign = $sign_check) {
		// 签名验证通过，还得验证这个订单的异步通知result是否为success
		if ($result == "success") {
			// result为success就代表这是一个已支付的订单
			// 查询数据库是否已经收到这笔异步订单
			$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
			$sql_check_order = "SELECT * FROM huoma_order WHERE order_no = '$order_no'";
			$result_check_order = $conn->query($sql_check_order);
			if ($result_check_order->num_rows > 0) {
				// 已经收到异步通知，就不要再插入数据库
				echo "success";
			}else{
				// 设置字符编码为utf-8
				mysqli_query($conn, "SET NAMES UTF-8");

				// 获取当前用户的过期日期
		        $sql_checkuserinfo = "SELECT * FROM huoma_user WHERE user_id = '$user_id'";
		        $result_checkuserinfo = $conn->query($sql_checkuserinfo);
		        if ($result_checkuserinfo->num_rows > 0) {
		            while($row_checkuserinfo = $result_checkuserinfo->fetch_assoc()) {
		                $expire_time = $row_checkuserinfo['expire_time'];
		            }
		        }else{
		            // 获取失败，用当前时间作为默认时间
		            $expire_time = date("Y-m-d");
		        }

				// 还没收到异步通知，那就直接插入数据库
				$sql_notify = "INSERT INTO huoma_order (user_id, order_no, pay_money, xufei_daynum, pay_type) VALUES ('$user_id', '$xddpay_order', '$realmoney', '$tc_days', '$pay_type_str')";
				if ($conn->query($sql_notify) === TRUE) {
					// 插入数据库成功！
		            // 更新续费结果
		            // 计算过期时间（在即将到期的日期基础上，增加续费的天数，得出新的到期日期）
		            $daoqi_daynum = $tc_days+1;
		            $new_daoqidate = date('Y-m-d',strtotime("{$expire_time} + ".$daoqi_daynum." day"));
		            $xufei_sql = "UPDATE huoma_user SET expire_time='$new_daoqidate' WHERE user_id=".$user_id;
		            if ($conn->query($xufei_sql) === TRUE) {
		                echo "success";
		            }else{
		                echo "error";
		            }
				} else {
				    echo "error";
				}
			}
		}
	}else{
		echo "error";
	}

	// 断开数据库连接
	$conn->close();
?>

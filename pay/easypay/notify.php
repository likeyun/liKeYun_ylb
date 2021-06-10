<?php
	// 返回的页面编码
	header("Content-type:text/html;charset=utf-8");

	// 引入配置文件
	include '../../db_config/db_config.php';

	// 接收参数
	$out_trade_no = $_GET["out_trade_no"];
	$type = $_GET["type"];
	$name = $_GET["name"];
	$money = $_GET["money"];
	$trade_status = $_GET["trade_status"];

	// 将name分割
	$name_1 = substr($name,strripos($name,"huoma-")+6);
	$tc_days = substr($name_1,0,strrpos($name_1,"_")); // 套餐天数

	$name_2 = substr($name_1,strripos($name_1,"huoma-")+2);
	$tc_price = substr($name_2,0,strrpos($name_2,"|")); // 套餐价格

	$user_id = substr($name_2,strripos($name_2,"|")+1); // 用户id

	if ($type == 'wxpay') {
		$pay_type_str = '易支付微信支付';
	}else{
		$pay_type_str = '易支付支付宝';
	}

	if ($trade_status == "TRADE_SUCCESS") {

		// trade_status为TRADE_SUCCESS就代表这是一个已支付的订单
		// 查询数据库是否已经收到这笔异步订单
		$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
		$sql_check_order = "SELECT * FROM huoma_order WHERE out_trade_no = '$out_trade_no'";
		$result_check_order = $conn->query($sql_check_order);
		if ($result_check_order->num_rows > 0) {

			// 已经收到异步通知，就不要再插入数据库
			echo "SUCCESS";

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
			$sql_notify = "INSERT INTO huoma_order (user_id, order_no, pay_money, xufei_daynum, pay_type) VALUES ('$user_id', '$out_trade_no', '$money', '$tc_days', '$pay_type_str')";

			if ($conn->query($sql_notify) === TRUE) {

				// 插入数据库成功！
	            // 更新续费结果
	            // 计算过期时间（在即将到期的日期基础上，增加续费的天数，得出新的到期日期）
	            $daoqi_daynum = $tc_days+1;
	            $new_daoqidate = date('Y-m-d',strtotime("{$expire_time} + ".$daoqi_daynum." day"));
	            $xufei_sql = "UPDATE huoma_user SET expire_time='$new_daoqidate' WHERE user_id=".$user_id;

	            if ($conn->query($xufei_sql) === TRUE) {
	                echo "SUCCESS";
	            }else{
	                echo "ERROR";
	            }
			} else {
			    echo "ERROR";
			}
		}
	}

	// 断开数据库连接
	$conn->close();
?>

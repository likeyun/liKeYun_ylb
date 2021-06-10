<?php
// 页面字符编码
header("Content-type:text/html;charset=utf-8");

// 引入数据库配置
include '../../db_config/db_config.php';

// 获得异步传过来的参数
$out_trade_no =  $_POST['out_trade_no'];
$body =  $_POST['body'];
$total_amount = $_POST['total_amount'];

// body拆分，获得续费的天数
$tc_days = substr($body,strripos($body,"-")+1);
// body拆分，获得userid
$user_id = substr($body,0,strrpos($body,"-"));

// 连接数据库
$conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
mysqli_query($conn,"SET NAMES UTF8");
// 过滤重复通知
$sql_check = "SELECT * FROM huoma_order WHERE order_no = '$out_trade_no'";
$result = $conn->query($sql_check);
if ($result->num_rows > 0) {
	echo 'success';
}else{
	// 获取当前用户的过期日期
    $sql_checkuserinfo = "SELECT * FROM huoma_user WHERE user_id = '$user_id'";
    $result_checkuserinfo = $conn->query($sql_checkuserinfo);

    if ($result_checkuserinfo->num_rows > 0) {

        while($row_checkuserinfo = $result_checkuserinfo->fetch_assoc()) {

        	// 过期日期
            $expire_time = $row_checkuserinfo['expire_time'];
        }

    }else{

        // 获取失败，用当前时间作为默认时间
        $expire_time = date("Y-m-d");
    }

    // 插入数据库
    $sql_insert = "INSERT INTO huoma_order (user_id, order_no, pay_money, xufei_daynum, pay_type) VALUES ('$user_id', '$out_trade_no', '$total_amount', '$tc_days', '支付宝当面付')";

    if ($conn->query($sql_insert) === TRUE) {
        $daoqi_daynum = $tc_days+1;
        $new_daoqidate = date('Y-m-d',strtotime("{$expire_time} + ".$daoqi_daynum." day"));
        $xufei_sql = "UPDATE huoma_user SET expire_time='$new_daoqidate' WHERE user_id=".$user_id;
        $conn->query($xufei_sql);
        echo 'success';
    }else{
       $conn->error;
    }
}
?>
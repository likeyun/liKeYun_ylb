<?php
header("Content-type:application/json");

// 引入数据库配置
include './config.php';
include '../../db_config/db_config.php';

//获取POST过来的数据
$return_code = $_POST["return_code"];
$total_fee = $_POST["total_fee"];
$out_trade_no = $_POST["out_trade_no"];
$payjs_order_id = $_POST["payjs_order_id"];
$time_end = $_POST["time_end"];
$openid = $_POST["openid"];
$transaction_id = $_POST["transaction_id"];
$sign = $_POST["sign"];
$attach = $_POST["attach"];

// attach是一个json字符串，需要提取出来
$attach_arr = json_decode($attach);
$tc_days = $attach_arr->tc_days;
$user_id = $attach_arr->user_id;
$pay_type = $attach_arr->pay_type;

if ($pay_type == 'wx') {
    $pay_type_text = "PayJs微信支付";
}else if ($pay_type == 'ali'){
    $pay_type_text = "PayJs支付宝";
}

$data = [
    'mchid'           => $mchid,
    'return_code'     => $return_code,
    'payjs_order_id'  => $payjs_order_id,
    'total_fee'       => $total_fee,
    'time_end'        => $time_end,
    'out_trade_no'    => $out_trade_no,
    'openid'          => $openid,
    'transaction_id'  => $transaction_id,
    'sign'            => $sign,
    'attach'          => $attach
];

if($return_code == 1){

    // 1、验证签名
    function sign($data, $key)
    {
        array_filter($data);
        ksort($data);
        return strtoupper(md5(urldecode(http_build_query($data) . '&key=' . $key)));
    }

    // 2、连接数据库
    $conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    } 
    
    // 3、验证重复通知
    $sql_check = "SELECT * FROM huoma_order WHERE order_no = '$out_trade_no'";
    $result = $conn->query($sql_check);
     
    if ($result->num_rows > 0) {
        // 如果已经存在这个订单，就不要再插入数据库
        echo "该订单已经存在";
    } else {

        // 格式化金额
        if (strlen($total_fee) == '1') {
        // 1位
        $total_fee_num = '0.0'.$total_fee;
        }else if ($tofee_length == 2) {
        // 2位
        $total_fee_num = '0.'.$total_fee;
        }else if ($tofee_length == 3) {
        // 3位
        $total_fee_no_1 = substr($tofeeNum,0,1); // 获取第一位数
        $total_fee_no_23 = substr($tofeeNum,1);  // 获取第二、三位数
        $total_fee_num = $total_fee_no_1.".".$total_fee_no_23;
        }else if ($tofee_length == 4) {
        // 4位
        $total_fee_no_12 = substr($tofeeNum,0,2); // 获取第一第二位数
        $total_fee_no_34 = substr($tofeeNum,2,3); // 获取第三第四位数
        $total_fee_num = $total_fee_no_12.".".$total_fee_no_34;
        }else if ($tofee_length == 5) {
        // 5位
        $total_fee_no_123 = substr($tofeeNum,0,3); // 获取第一二三位数
        $total_fee_no_45 = substr($tofeeNum,3,4);  // 获取第四五位数
        $total_fee_num = $total_fee_no_123.".".$total_fee_no_45;
        }

       mysqli_query($conn,"SET NAMES UTF8");
       // 获取当前用户的过期日期
       $sql_checkuserinfo = "SELECT * FROM huoma_user WHERE user_id = '$user_id'";
       $result_checkuserinfo = $conn->query($sql_checkuserinfo);
       if ($result_checkuserinfo->num_rows > 0) {
        while($row_checkuserinfo = $result_checkuserinfo->fetch_assoc()) {
            $expire_time = $row_checkuserinfo['expire_time'];
        }
       }else{
        // 
       }

       // 否则需要插入数据库
       $sql_insert = "INSERT INTO huoma_order (user_id, order_no, pay_money, xufei_daynum, pay_type) VALUES ('$user_id', '$out_trade_no', '$total_fee_num', '$tc_days', '$pay_type_text')";
       if ($conn->query($sql_insert) === TRUE) {
            // 插入数据库成功！
            // 更新续费结果
            // 计算过期时间（在即将到期的日期基础上，增加续费的天数，得出新的到期日期）
            $daoqi_daynum = $tc_days+1;
            $new_daoqidate = date('Y-m-d',strtotime("{$expire_time} + ".$daoqi_daynum." day"));
            $xufei_sql = "UPDATE huoma_user SET expire_time='$new_daoqidate' WHERE user_id=".$user_id;
            if ($conn->query($xufei_sql) === TRUE) {
                echo "续费成功";
            }else{
                echo "续费失败";
            }
       }else{
            echo "插入失败！".$conn->error;
       }
    }

    // 断开数据库连接
    $conn->close();
    
    // 4、返回success
    echo 'success';
}else{
    echo "失败";
}
?>
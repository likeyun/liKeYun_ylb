<?php

// JSON编码
header("Content-Type:application/json");

// 引入配置文件
include './config.php';

// 获取前端POST过来的参数
$taocan = $_GET["taocan"]; // 选择的套餐

// 分割参数，获取套餐价格
$tc_price = substr($taocan,strripos($taocan,"-") + 1);
// 分割参数，获取套餐天数
$tc_days = substr($taocan,0,strrpos($taocan,"-"));
// 获取userid
$userid = $_GET["userid"];

// 订单参数
$out_trade_no = date('Ymd').time().rand(10,99);
$name = 'huoma-'.$tc_days.'_'.$tc_price.'|'.$userid;
$money = $tc_price;
$type = 'wxpay';
$sitename = 'likeyun';

// 签名算法
$sign = md5('money='.$money.'&name='.$name.'&notify_url='.$notify_url.'&out_trade_no='.$out_trade_no.'&pid='.$pid.'&return_url='.$return_url.'&sitename='.$sitename.'&type='.$type.$key);

// 构建json数据
$result = array(
	'code' => 200,
	'msg' => '请求成功',
	'url' => $api.'?pid='.$pid.'&type='.$type.'&out_trade_no='.$out_trade_no.'&notify_url='.$notify_url.'&return_url='.$return_url.'&name='.$name.'&money='.$money.'&sitename='.$sitename.'&sign='.$sign.'&sign_type=MD5',
	'order_no' => $out_trade_no
);

// 输出JSON
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>

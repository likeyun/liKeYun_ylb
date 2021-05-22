<?php
// 设置页面返回的字符编码为json格式
header("Content-type:application/json");

// 引入支付配置文件
include './config.php';

// 获取前端POST过来的参数
$taocan = $_GET["taocan"]; // 选择的套餐

// 分割参数，获取续费的金额
$total_fee = substr($taocan,strripos($taocan,"-")+1);
// 分割参数，获取续费的天数
$tc_days = substr($taocan,0,strrpos($taocan,"-"));

// 定义参数
$order_no = date('Ymd').time(); // 今天的日期+时间戳生成的字符串作为订单号
$subject = '活码续费'; // 商品名称
$pay_type = $_GET["paytype"]; //支付类型，43是支付宝、44是微信支付
$money = $total_fee; // 商品的金额

if ($pay_type == '43') {
	$paytype = 'ali';
}else{
	$paytype = 'wx';
}

$extra = $_GET["userid"].'-'.$tc_days.'_'.$paytype; // user_id和续费的天数

// 签名
$sign = MD5('order_no='.$order_no.'&subject='.$subject.'&pay_type='.$pay_type.'&money='.$money.'&app_id='.$app_id.'&extra='.$extra.'&'.$app_key);
$signStr = strtoupper($sign); // 签名转换为大写

// 发起支付
$pay = file_get_contents("http://gateway.xddpay.com/?order_no=".$order_no."&subject=".$subject."&app_id=".$app_id."&pay_type=".$pay_type."&money=".$money."&extra=".$extra."&sign=".$signStr."&format=json");

echo $pay;
?>
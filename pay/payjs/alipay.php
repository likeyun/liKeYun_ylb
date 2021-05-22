<?php
// 设置页面返回的字符编码为json格式
header("Content-type:application/json");

// 加载支付配置文件
include './config.php';

// 获取前端POST过来的参数
$taocan = $_GET["taocan"]; // 选择的套餐

// 分割参数，获取续费的金额
$total_fee = substr($taocan,strripos($taocan,"-") + 1);
// 分割参数，获取续费的天数
$tc_days = substr($taocan,0,strrpos($taocan,"-"));
// user_id和续费的天数
$attach = '{"user_id":"'.$_GET["userid"].'","tc_days":"'.$tc_days.'","pay_type":"ali"}';

// 获取当前文件所在服务器的URL和文件目录
$server_url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
$server_file_url = dirname($server_url);

// 引入金额转换类
include './tofee.class.php';

// 构造请求参数
$data = [
    'mchid'        => $mchid,
    'body'         => '活码续费',
    'total_fee'    => $tofee_val, // 这里用的是经过转换单位的金额，单位是分
    'attach'       => $attach,
    'type'         => 'alipay',
    'notify_url'   => $server_file_url.'/notify.php',
    'out_trade_no' => date('Ymd').time().rand(10,99), // 今天的日期+时间戳生成的字符串作为订单号+rand随机两位
];

// 签名算法
function sign($data, $key)
{
    array_filter($data);
    ksort($data);
    return strtoupper(md5(urldecode(http_build_query($data) . '&key=' . $key)));
}

// 把获取到的签名添加到请求参数数组中
$data['sign'] = sign($data, $key);

//发送请求
$url = 'https://payjs.cn/api/native?' . http_build_query($data);
function post($data, $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $rst = curl_exec($ch);
        curl_close($ch);
        return $rst;
}

//发送
$result = post($data, $url);
//转换json字符串
$parseJson = json_decode($result);
//输出二维码
$ewm = $parseJson->qrcode;
//输出金额
$tofeeNum = $parseJson->total_fee;

//格式化金额
$tofee_length = strlen($tofeeNum);

if ($tofee_length == 1) {
    $total_fee = "0.0".$tofeeNum;
}else if ($tofee_length == 2) {
    $total_fee = substr($tofeeNum,0);
    $total_fee = "0.".$total_fee;
}else if ($tofee_length == 3) {
    $total_fee_no_1 = substr($tofeeNum,0,1);
    $total_fee_no_23 = substr($tofeeNum,1);
    $total_fee = $total_fee_no_1.".".$total_fee_no_23;
}else if ($tofee_length == 4) {
    $total_fee_no_12 = substr($tofeeNum,0,2);
    $total_fee_no_34 = substr($tofeeNum,2,3);
    $total_fee = $total_fee_no_12.".".$total_fee_no_34;
}else if ($tofee_length == 5) {
    $total_fee_no_123 = substr($tofeeNum,0,3);
    $total_fee_no_45 = substr($tofeeNum,3,4);
    $total_fee = $total_fee_no_123.".".$total_fee_no_45;
}

//输出订单号
$payjs_order_id = $parseJson->payjs_order_id;

// 返回请求结果
echo $result;
?>
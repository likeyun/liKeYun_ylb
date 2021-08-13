<?php
header('Content-type:text/html; Charset=utf-8');

// 引入配置文件
include 'ffjq_config.php';
include '../../../db_config/db_config.php';

$wxPay = new WxpayService($mchid,$appid,$apiKey);
$result = $wxPay->notify();
if($result){

    $ffjq_order = $result['out_trade_no'];
    $ffjq_openid = $result['openid'];
    $ffjq_price = sprintf("%.2f", $result['total_fee'] / 100);
    $attach_jsonstr = $result['attach'];
    $ffjq_title = json_decode($attach_jsonstr)->ffjq_title;
    $ffjq_id = json_decode($attach_jsonstr)->ffjq_id;

    // 创建连接
    $conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
    mysqli_query($conn, "SET NAMES UTF-8"); 
    $ffjq_bzcode = rand(10000,99999);
    $notify_sql = "INSERT INTO huoma_addons_ffjq_order (ffjq_id,ffjq_title,ffjq_order,ffjq_price,ffjq_bzcode,ffjq_openid) VALUES ('$ffjq_id','$ffjq_title','$ffjq_order','$ffjq_price','$ffjq_bzcode','$ffjq_openid')";
     
    if (mysqli_query($conn, $notify_sql)) {
        echo 'SUCCESS';
    } else {
        echo "Error:";
    }
    
    // 断开数据库连接
    mysqli_close($conn);

}else{
    echo 'pay error';
}
class WxpayService
{
    protected $mchid;
    protected $appid;
    protected $apiKey;
    public function __construct($mchid, $appid, $key)
    {
        $this->mchid = $mchid;
        $this->appid = $appid;
        $this->apiKey = $key;
    }

    public function notify()
    {
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->apiKey,
        );
        $postStr = file_get_contents('php://input');
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);        
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($postObj === false) {
            die('parse xml error');
        }
        if ($postObj->return_code != 'SUCCESS') {
            die($postObj->return_msg);
        }
        if ($postObj->result_code != 'SUCCESS') {
            die($postObj->err_code);
        }
        $arr = (array)$postObj;
        unset($arr['sign']);
        if (self::getSign($arr, $config['key']) == $postObj->sign) {
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            return $arr;
        }
    }

    /**
     * 获取签名
     */
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}
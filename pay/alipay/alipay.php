<?php
header("Content-type:application/json");

// 引入支付配置
include './config.php';

// 获取前端GET过来的参数
$taocan = $_GET["taocan"]; // 选择的套餐
// 分割参数，获取续费的金额
$total_fee = substr($taocan,strripos($taocan,"-")+1);
// 分割参数，获取续费的天数
$tc_days = substr($taocan,0,strrpos($taocan,"-"));
$outTradeNo = date('Ymd').time().rand(10,99); // 今天的日期+时间戳生成的字符串作为订单号
$payAmount = $total_fee; // 商品的金额
$orderName = '活码续费'; // 商品名称
$beiZhu = $_GET["userid"].'-'.$tc_days; // user_id、续费天数
$signType = 'RSA2';			// 签名算法类型，支持RSA2和RSA，推荐使用RSA2
/*** 配置结束 ***/
$aliPay = new AlipayService();
$aliPay->setAppid($appid);
$aliPay->setNotifyUrl($notifyUrl);
$aliPay->setRsaPrivateKey($rsaPrivateKey);
$aliPay->setTotalFee($payAmount);
$aliPay->setOutTradeNo($outTradeNo);
$aliPay->setOrderName($orderName);
$aliPay->setBeiZhu($beiZhu);
$result = $aliPay->doPay();
$result = $result['alipay_trade_precreate_response'];
if($result['code'] && $result['code']=='10000'){
    
    // 返回二维码和订单号
    $pay_creat_result = array(
    	'qrcode' => $result['qr_code'],
    	'order_no' => $outTradeNo,
    	'code' => 200
    );
    
}else{

	// 返回错误信息
    $pay_creat_result = array(
    	'msg' => $result['msg'].' : '.$result['sub_msg'],
    	'code' => 404
    );
  
}
class AlipayService
{
    protected $appId;
    protected $notifyUrl;
    protected $charset;
    protected $beizhu;
    //私钥值
    protected $rsaPrivateKey;
    protected $totalFee;
    protected $outTradeNo;
    protected $orderName;

    public function __construct()
    {
        $this->charset = 'utf-8';
    }

    public function setAppid($appid)
    {
        $this->appId = $appid;
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
    }

    public function setRsaPrivateKey($saPrivateKey)
    {
        $this->rsaPrivateKey = $saPrivateKey;
    }

    public function setTotalFee($payAmount)
    {
        $this->totalFee = $payAmount;
    }

    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
    }

    public function setOrderName($orderName)
    {
        $this->orderName = $orderName;
    }
    public function setBeiZhu($beiZhu)
    {
        $this->beiZhu = $beiZhu;
    }

    /**
     * 发起订单
     * @return array
     */
    public function doPay()
    {
        //请求参数
        $requestConfigs = array(
            'out_trade_no'=>$this->outTradeNo,
            'total_amount'=>$this->totalFee, //单位 元
            'subject'=>$this->orderName,  //订单标题
            'timeout_express'=>'2h',       //该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。
            'body'=>$this->beiZhu
        );
        $commonConfigs = array(
            //公共参数
            'app_id' => $this->appId,
            'method' => 'alipay.trade.precreate',             //接口名称
            'format' => 'JSON',
            'charset'=>$this->charset,
            'sign_type'=>'RSA2',
            'timestamp'=>date('Y-m-d H:i:s'),
            'version'=>'1.0',
            'notify_url' => $this->notifyUrl,
            'biz_content'=>json_encode($requestConfigs),
        );
        $commonConfigs["sign"] = $this->generateSign($commonConfigs, $commonConfigs['sign_type']);
        $result = $this->curlPost('https://openapi.alipay.com/gateway.do?charset='.$this->charset,$commonConfigs);
        return json_decode($result,true);
    }
    public function generateSign($params, $signType = "RSA") {
        return $this->sign($this->getSignContent($params), $signType);
    }
    protected function sign($data, $signType = "RSA") {
        $priKey=$this->rsaPrivateKey;
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, version_compare(PHP_VERSION,'5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256); //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
        } else {
            openssl_sign($data, $sign, $res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }
    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }
    public function getSignContent($params) {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, $this->charset);
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }
    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {
        if (!empty($data)) {
            $fileType = $this->charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        return $data;
    }
    public function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}

// 输出JSON
echo json_encode($pay_creat_result,JSON_UNESCAPED_UNICODE);
?>

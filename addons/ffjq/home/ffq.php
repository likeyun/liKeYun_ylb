<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="color-scheme" content="light dark">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="format-detection" content="telephone=no">
	<script src="../../../js/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="./ffq.css">
</head>
<?php
    header('Content-type:text/html; Charset=utf-8');

    // 引入配置文件
    include 'ffjq_config.php';
    include '../../../db_config/db_config.php';

    // 连接数据库
    $conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
    mysqli_query($conn, "SET NAMES UTF-8"); 

    // 获取id
    $ffjq_id = $_GET["ffqid"];

    // 查询当前id下的数据
    $sql_ffqinfo = "SELECT * FROM huoma_addons_ffjq WHERE ffjq_id='$ffjq_id'";
	$result_ffqinfo = $conn->query($sql_ffqinfo);
	if ($result_ffqinfo->num_rows > 0) {

	    // 输出数据
	    while($row_ffqinfo = $result_ffqinfo->fetch_assoc()) {
	        $ffjq_title = $row_ffqinfo["ffjq_title"];
	        $ffjq_price = $row_ffqinfo["ffjq_price"];
	        $ffjq_status = $row_ffqinfo["ffjq_status"];
	        $ffjq_qrcode = $row_ffqinfo["ffjq_qrcode"];
	    }

	    if ($ffjq_status == '2') {
	    	echo '<title>提示</title>';
			echo '<div style="width:100px;height:100px;margin:100px auto 20px;">
			<img src="../../../images/warning.png" width="100" />
			</div>';
			echo '<p style="color:#666;font-size:15px;text-align:center;font-weight:bold;">该群暂停使用</p>';
		    exit;
	    }

	} else {
		echo '<title>提示</title>';
		echo '<div style="width:100px;height:100px;margin:100px auto 20px;">
		<img src="../../../images/error.png" width="100" />
		</div>';
		echo '<p style="color:#666;font-size:15px;text-align:center;font-weight:bold;">该群不存在或已被管理员删除</p>';
	    exit;
	}

	// 获取用户openid
    $wxPay = new WxpayService($mchid,$appid,$appKey,$apiKey);
    // 获取session中储存的openid
    session_start();
	if(isset($_SESSION["openid"])){
		// 如果session中有openid
		$openId = $_SESSION["openid"];
	}else{
		// 如果没有
		$openId = $wxPay->GetOpenid();
		// 将openid存入session
    	session_start();
		$_SESSION['openid'] = $openId;
	}

	// 判断是否已经获得openid
    if(!$openId){
    	echo '<title>提示</title>';
		echo '<div style="width:100px;height:100px;margin:100px auto 20px;">
		<img src="../../../images/warning.png" width="100" />
		</div>';
		echo '<p style="color:#666;font-size:15px;text-align:center;font-weight:bold;">请重新打开页面</p>';
	    exit;
    }

    // 查询该用户是否已经支付过了
    $sql_checkorder = "SELECT * FROM huoma_addons_ffjq_order WHERE ffjq_openid='$openId' AND ffjq_id='$ffjq_id'";
	$result_checkorder = $conn->query($sql_checkorder);
	if ($result_checkorder->num_rows > 0) {
		while($row_checkorder = $result_checkorder->fetch_assoc()) {
        	$bzcode = $row_checkorder["ffjq_bzcode"];
    	}
		echo '<div id="qunqrcode" style="display:block;"><p class="bzcode">进群后请将备注码<span style="color:#f00;">'.$bzcode.'</span>设置为备注</p>';
		echo '<title>加入群聊</title>';
	    echo '<img src="'.$ffjq_qrcode.'" style="width: 100%;" />';
		echo '</div>';
		exit;
	}
    
    // 统一下单
    $outTradeNo = date('Ymd').time(); // 订单号
    $payAmount = $ffjq_price; // 付款金额，单位:元
    $orderName = 'ffq'; // 订单标题
    $notifyUrl = $ffjq_notify_url; // 付款成功后的回调地址
    $payTime = time(); // 付款时间
    $attach_array = array(
    	'ffjq_title' => $ffjq_title,
    	'ffjq_id' => $ffjq_id
    );
    $attach = json_encode($attach_array);

    $jsApiParameters = $wxPay->createJsBizPackage($openId,$payAmount,$outTradeNo,$orderName,$notifyUrl,$payTime,$attach);
    $jsApiParameters = json_encode($jsApiParameters);
?>

<body>
	<div id="quncontent">
		<!-- 群头像 -->
		<div id="qunlogo">
			<img src="http://inews.gtimg.com/newsapp_bt/0/13865842860/641" />
		</div>

		<!-- 群昵称 -->
		<p id="qun_name"><?php echo $ffjq_title; ?> (<?php echo rand(100,500); ?>)</p>

		<!-- 分割线 -->
		<hr id="hr">

		<!-- 进群说明 -->
		<div id="jinqun_shuoming">
			<ol>
				<li>该群聊人数较多，为减少新消息给你带来的打扰，建议谨慎加入。</li>
				<li>你需要实名验证后才能接受邀请，可绑定银行卡进行验证。</li>
				<li>为维护微信平台绿色网络环境，请勿在群内传播违法违规内容。</li>
			</ol>
		</div>

		<!-- 进群按钮 -->
		<div id="jinqun_btn"><a href="javascript:void()" onclick="callpay();">立即支付</a></div>
	</div>

	<!-- 群二维码 -->
	<div id="qunqrcode">
		<p class="bzcode"></p>
		<?php
			echo '<title>加入群聊</title>';
			echo '<img src="'.$ffjq_qrcode.'" style="width: 100%;" />';
		?>
	</div>

	<script type="text/javascript">

    // 发起支付
    function jsApiCall()
	    {
	        WeixinJSBridge.invoke(
	            'getBrandWCPayRequest',
	            <?php echo $jsApiParameters; ?>,
	            function(res){
	                WeixinJSBridge.log(res.err_msg);
	                if(res.err_msg == "get_brand_wcpay_request:ok"){
	                	$("#jinqun_btn").html('<a href="javascript:void()" id="<?php echo $outTradeNo; ?>" onclick="checkOrder(this);">加入群聊</a>')
	                }else{
	                    alert("取消支付");
	                }
	            }
	        );
	    }
	    function callpay()
	    {
	        if (typeof WeixinJSBridge == "undefined"){
	            if( document.addEventListener ){
	                document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
	            }else if (document.attachEvent){
	                document.attachEvent('WeixinJSBridgeReady', jsApiCall);
	                document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
	            }
	        }else{
	            jsApiCall();
	        }
	    }

	    document.addEventListener(
	        "WeixinJSBridgeReady",
	        function onBridgeReady() {
	          WeixinJSBridge.call("hideOptionMenu");
	        }
      	);

    // 查询支付结果
	function checkOrder(event){
		var order = event.id;
		var ffjqtype = '<?php echo $ffjq_type; ?>';
		var ffjqhm = '<?php echo $ffjq_hm; ?>';
		$.ajax({
	      type: "GET",
	      url: "./check_order.php?order="+order,
	      success: function (data) {
	      	if (data.code == 100) {
	      		$("#qunqrcode .bzcode").html('进群后请将备注码<span style="color:#f00;">'+data.bzcode+'</span>设置为备注')
	      		$("#quncontent").css('display','none');
	    		$("#qunqrcode").css('display','block');
	      	}
	      },
	      error: function() {
	        alert("查询订单失败，请联系客服")
	      }
		});
	}
	</script>
</body>
</html>
<!-- 支付处理类 -->
<?php
	header("Content-Type:text/html; charset=utf-8");
	class WxpayService
	{
	    protected $mchid;
	    protected $appid;
	    protected $appKey;
	    protected $apiKey;
	    public $data = null;
	    public function __construct($mchid, $appid, $appKey,$key)
	    {
	        $this->mchid = $mchid; //https://pay.weixin.qq.com 产品中心-开发配置-商户号
	        $this->appid = $appid; //微信支付申请对应的公众号的APPID
	        $this->appKey = $appKey; //微信支付申请对应的公众号的APP Key
	        $this->apiKey = $key;   //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
	    }
	    /**
	     * 通过跳转获取用户的openid，跳转流程如下：
	     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
	     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
	     * @return 用户的openid
	     */
	    public function GetOpenid()
	    {
	        //通过code获得openid
	        if (!isset($_GET['code'])){
	            //触发微信返回code码
	            $scheme = $_SERVER['HTTPS']=='on' ? 'https://' : 'http://';
	            $baseUrl = urlencode($scheme.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
	            $url = $this->__CreateOauthUrlForCode($baseUrl);
	            Header("Location: $url");
	            exit();
	        } else {
	            //获取code码，以获取openid
	            $code = $_GET['code'];
	            $openid = $this->getOpenidFromMp($code);
	            return $openid;
	        }
	    }
	    /**
	     * 通过code从工作平台获取openid机器access_token
	     * @param string $code 微信跳转回来带上的code
	     * @return openid
	     */
	    public function GetOpenidFromMp($code)
	    {
	        $url = $this->__CreateOauthUrlForOpenid($code);
	        $res = self::curlGet($url);
	        //取出openid
	        $data = json_decode($res,true);
	        $this->data = $data;
	        $openid = $data['openid'];
	        return $openid;
	    }
	    /**
	     * 构造获取open和access_toke的url地址
	     * @param string $code，微信跳转带回的code
	     * @return 请求的url
	     */
	    private function __CreateOauthUrlForOpenid($code)
	    {
	        $urlObj["appid"] = $this->appid;
	        $urlObj["secret"] = $this->appKey;
	        $urlObj["code"] = $code;
	        $urlObj["grant_type"] = "authorization_code";
	        $bizString = $this->ToUrlParams($urlObj);
	        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	    }
	    /**
	     * 构造获取code的url连接
	     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
	     * @return 返回构造好的url
	     */
	    private function __CreateOauthUrlForCode($redirectUrl)
	    {
	        $urlObj["appid"] = $this->appid;
	        $urlObj["redirect_uri"] = "$redirectUrl";
	        $urlObj["response_type"] = "code";
	        $urlObj["scope"] = "snsapi_base";
	        $urlObj["state"] = "STATE"."#wechat_redirect";
	        $bizString = $this->ToUrlParams($urlObj);
	        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	    }
	    /**
	     * 拼接签名字符串
	     * @param array $urlObj
	     * @return 返回已经拼接好的字符串
	     */
	    private function ToUrlParams($urlObj)
	    {
	        $buff = "";
	        foreach ($urlObj as $k => $v)
	        {
	            if($k != "sign") $buff .= $k . "=" . $v . "&";
	        }
	        $buff = trim($buff, "&");
	        return $buff;
	    }
	    /**
	     * 统一下单
	     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
	     * @param float $totalFee 收款总费用 单位元
	     * @param string $outTradeNo 唯一的订单号
	     * @param string $orderName 订单名称
	     * @param string $notifyUrl 支付结果通知url 不要有问号
	     * @param string $timestamp 支付时间
	     * @return string
	     */
	    public function createJsBizPackage($openid, $totalFee, $outTradeNo, $orderName, $notifyUrl, $timestamp, $attach)
	    {
	        $config = array(
	            'mch_id' => $this->mchid,
	            'appid' => $this->appid,
	            'key' => $this->apiKey,
	        );
	        $orderName = iconv('GBK','UTF-8',$orderName);
	        $unified = array(
	            'appid' => $config['appid'],
	            'attach' => 'pay',             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
	            'body' => $orderName,
	            'mch_id' => $config['mch_id'],
	            'nonce_str' => self::createNonceStr(),
	            'notify_url' => $notifyUrl,
	            'openid' => $openid,            //rade_type=JSAPI，此参数必传
	            'out_trade_no' => $outTradeNo,
	            'spbill_create_ip' => '127.0.0.1',
	            'total_fee' => intval($totalFee * 100),       //单位 转为分
	            'trade_type' => 'JSAPI',
	            'attach' => $attach,
	        );
	        $unified['sign'] = self::getSign($unified, $config['key']);
	        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));
	        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
	        if ($unifiedOrder === false) {
	            die('parse xml error');
	        }
	        if ($unifiedOrder->return_code != 'SUCCESS') {
	            die($unifiedOrder->return_msg);
	        }
	        if ($unifiedOrder->result_code != 'SUCCESS') {
	            die($unifiedOrder->err_code);
	        }
	        $arr = array(
	            "appId" => $config['appid'],
	            "timeStamp" => "$timestamp",        //这里是字符串的时间戳，不是int，所以需加引号
	            "nonceStr" => self::createNonceStr(),
	            "package" => "prepay_id=" . $unifiedOrder->prepay_id,
	            "signType" => 'MD5',
	        );
	        $arr['paySign'] = self::getSign($arr, $config['key']);
	        return $arr;
	    }
	    public static function curlGet($url = '', $options = array())
	    {
	        $ch = curl_init($url);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
	    public static function curlPost($url = '', $postData = '', $options = array())
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
	    public static function createNonceStr($length = 16)
	    {
	        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	        $str = '';
	        for ($i = 0; $i < $length; $i++) {
	            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	        }
	        return $str;
	    }
	    public static function arrayToXml($arr)
	    {
	        $xml = "<xml>";
	        foreach ($arr as $key => $val) {
	            if (is_numeric($val)) {
	                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
	            } else
	                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
	        }
	        $xml .= "</xml>";
	        return $xml;
	    }
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
?>
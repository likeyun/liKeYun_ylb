<?php
header("Content-type:application/json");

// 引入配置
include '../../db_config/db_config.php';
include './config.php';

$aliPay = new AlipayService($alipayPublicKey);
//验证签名
$result = $aliPay->rsaCheck($_POST,$_POST['sign_type']);
if($result===true && $_POST['trade_status'] == 'TRADE_SUCCESS'){

    // 获得异步传过来的参数
    $out_trade_no =  $_POST['out_trade_no'];
    $body =  $_POST['body'];
    $total_amount = $_POST['total_amount'];

    // body拆分，获得续费的天数
    $tc_days = substr($body,strripos($body,"-")+1);
    // body拆分，获得userid
    $user_id = substr($body,0,strrpos($body,"-"));

    $conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }

    // 过滤重复通知
    $sql_check = "SELECT * FROM huoma_order WHERE order_no = '$out_trade_no'";
    $result = $conn->query($sql_check);
    if ($result->num_rows > 0) {
        // 订单已经成功获得通知
    }else{
        // 还没收到通知
        mysqli_query($conn,"SET NAMES UTF8");

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

        // 插入数据库
        $sql_insert = "INSERT INTO huoma_order (user_id, order_no, pay_money, xufei_daynum, pay_type) VALUES ('$user_id', '$out_trade_no', '$total_amount', '$tc_days', '支付宝当面付')";
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

    echo 'success';exit();
}
echo 'error';exit();
class AlipayService
{
    //支付宝公钥
    protected $alipayPublicKey;
    protected $charset;

    public function __construct($alipayPublicKey)
    {
        $this->charset = 'utf8';
        $this->alipayPublicKey=$alipayPublicKey;
    }

    /**
     *  验证签名
     **/
    public function rsaCheck($params) {
        $sign = $params['sign'];
        $signType = $params['sign_type'];
        unset($params['sign_type']);
        unset($params['sign']);
        return $this->verify($this->getSignContent($params), $sign, $signType);
    }

    function verify($data, $sign, $signType = 'RSA') {
        $pubKey= $this->alipayPublicKey;
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $signType) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res, version_compare(PHP_VERSION,'5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        }
//        if(!$this->checkEmpty($this->alipayPublicKey)) {
//            //释放资源
//            openssl_free_key($res);
//        }
        return $result;
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
}

?>
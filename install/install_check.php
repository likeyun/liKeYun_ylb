<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
header("Content-type:application/json");

$dburl = trim($_POST["dburl"]);
$dbuser = trim($_POST["dbuser"]);
$dbpwd = trim($_POST["dbpwd"]);
$dbname = trim($_POST["dbname"]);
$user = trim($_POST["user"]);
$pwd = trim($_POST["pwd"]);
$email = trim($_POST["email"]);

if (empty($dburl) || empty($dbuser) || empty($dbpwd) || empty($dbname) || empty($user) || empty($pwd) || empty($email)) {
  $result = array(
    "code" => "101",
    "msg" => "请把所有输入框填完再安装"
  );
}else{
  // 创建连接
  $conn = mysqli_connect($dburl, $dbuser, $dbpwd, $dbname);
  // 检测连接
  if (!$conn) {
    $error_msg = mysqli_connect_error();
    if(strpos($error_msg,'database') !== false){ 
      // 包含database则为数据库名错误
      $result = array(
        "code" => "102",
        "msg" => "数据库名称错误"
      );
    }else if(strpos($error_msg,'password') !== false){
      // 包含password则为账号密码错误
      $result = array(
        "code" => "103",
        "msg" => "数据库账号或密码错误"
      );
    }else{
      $result = array(
        "code" => "104",
        "msg" => "数据库地址错误"
      );
    }
  }else{

    // 检查是否已经安装
    $check_db_config = "../db_config/db_config.php";
    if(file_exists($check_db_config)){
      $result = array(
        "code" => "108",
        "msg" => "请勿重复安装，如需重新安装请把db_config/db_config.php删掉。"
      );
      echo json_encode($result,JSON_UNESCAPED_UNICODE);
      exit;
    }

    // 用户表
    $huoma_user = "CREATE TABLE huoma_user (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL, 
    user VARCHAR(32),
    pwd VARCHAR(32),
    user_id VARCHAR(32),
    reg_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expire_time VARCHAR(32),
    user_status VARCHAR(32) DEFAULT '1',
    user_limit VARCHAR(32) DEFAULT '1',
    email VARCHAR(32))";

    // 微信活码表
    $huoma_wx = "CREATE TABLE huoma_wx (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    wx_title VARCHAR(32),
    wx_id VARCHAR(32),
    yuming TEXT(300),
    wx_qrcode TEXT(300),
    wx_num VARCHAR(32),
    wx_shuoming TEXT(300),
    wx_status VARCHAR(32) DEFAULT '1',
    wx_update_time VARCHAR(32),
    wx_fwl VARCHAR(32) DEFAULT '0',
    wx_user VARCHAR(32))";

    // 邀请码表
    $huoma_yqm = "CREATE TABLE huoma_yqm (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    yqm VARCHAR(32),
    yqm_status INT(11),
    yqm_usetime VARCHAR(32),
    yqm_daynum INT(11))";

    // 落地页域名表
    $huoma_yuming = "CREATE TABLE huoma_yuming (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    yuming TEXT(300))";

    // 续费套餐表
    $huoma_taocan = "CREATE TABLE huoma_taocan (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    tc_title VARCHAR(32),
    tc_id VARCHAR(32),
    tc_days VARCHAR(32),
    tc_price VARCHAR(32))";

    // 系统设置表
    $huoma_set = "CREATE TABLE huoma_set (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    title TEXT(300) ,
    keywords TEXT(300),
    description TEXT(300),
    favicon TEXT(300),
    email_smtpserver VARCHAR(64),
    email_smtpserverport VARCHAR(64),
    email_smtpusermail VARCHAR(64),
    email_smtpuser VARCHAR(64),
    email_smtppass VARCHAR(64))";

    // 群子码表
    $huoma_qunzima = "CREATE TABLE huoma_qunzima (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    hmid VARCHAR(32),
    zmid VARCHAR(32),
    xuhao VARCHAR(32),
    yuzhi VARCHAR(32) DEFAULT '0',
    fwl VARCHAR(32) DEFAULT '0',
    dqdate VARCHAR(32),
    qrcode TEXT(300),
    update_time VARCHAR(32),
    zima_status VARCHAR(32) DEFAULT '2',
    code VARCHAR(32) DEFAULT '200')";

    // 群活码表
    $huoma_qun = "CREATE TABLE huoma_qun (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    qun_title VARCHAR(64),
    qun_hmid VARCHAR(32),
    qun_yuming TEXT(300),
    qun_pv VARCHAR(32) DEFAULT '0',
    qun_wx_status VARCHAR(32),
    qun_wx_qrcode TEXT(300),
    qun_status VARCHAR(32) DEFAULT '1',
    qun_creat_time VARCHAR(32),
    qun_user VARCHAR(32),
    qun_chongfu VARCHAR(32) DEFAULT '2')";

    // 支付接口表
    $huoma_payselect = "CREATE TABLE huoma_payselect (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    payapi VARCHAR(32),
    payselect VARCHAR(32) DEFAULT '1',
    paytype VARCHAR(32),
    paytitle VARCHAR(32))";

    // 订单表
    $huoma_order = "CREATE TABLE huoma_order (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    user_id VARCHAR(32),
    order_no VARCHAR(32),
    pay_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pay_money VARCHAR(11),
    xufei_daynum VARCHAR(11),
    pay_type VARCHAR(32))";

    // 活动码表
    $huoma_active = "CREATE TABLE huoma_active (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    active_id VARCHAR(32),
    active_title VARCHAR(32),
    active_yuming TEXT(300),
    active_qrcode TEXT(300),
    active_shuoming TEXT(300),
    active_pv VARCHAR(32) DEFAULT '0',
    active_status VARCHAR(32) DEFAULT '1',
    active_type VARCHAR(32),
    active_url TEXT(300),
    active_content TEXT(300),
    active_update_time VARCHAR(32),
    active_endtime VARCHAR(32),
    active_user VARCHAR(32))";

    // 判断安装结果
    if ( $conn->query($huoma_user) === TRUE
      && $conn->query($huoma_wx) === TRUE
      && $conn->query($huoma_yqm) === TRUE
      && $conn->query($huoma_yuming) === TRUE
      && $conn->query($huoma_taocan) === TRUE
      && $conn->query($huoma_set) === TRUE
      && $conn->query($huoma_qunzima) === TRUE
      && $conn->query($huoma_qun) === TRUE
      && $conn->query($huoma_payselect) === TRUE
      && $conn->query($huoma_order) === TRUE
      && $conn->query($huoma_active) === TRUE) {

      // 创建管理员账号
      $user_id = rand(10000,99999);
      $sql_creat_admin_user = "INSERT INTO huoma_user (user, pwd, user_id, email, expire_time, user_limit) VALUES ('$user', '$pwd', '$user_id', '$email', '3000-12-31', '2')";
      if (mysqli_query($conn, $sql_creat_admin_user)) {

        // 创建数据库配置文件
        $db_config_file = '<?php' . PHP_EOL . '  /**' . PHP_EOL . '   *  数据库配置' . PHP_EOL . '   *  Author：TANKING' . PHP_EOL . '   *  Date：'.date("Y-m-d").'' . PHP_EOL . '   *  Web：www.likeyuns.com' . PHP_EOL . '   **/' . PHP_EOL . '  $db_url = "'.$dburl.'";' . PHP_EOL . '  $db_user = "'.$dbuser.'";' . PHP_EOL . '  $db_pwd = "'.$dbpwd.'";' . PHP_EOL . '  $db_name = "'.$dbname.'";' . PHP_EOL . '?>';

        file_put_contents('../db_config/db_config.php', $db_config_file);

          // 当所有表格都创建成功
          // 管理员创建成功
          // 数据库配置文件创建成功
          // 才代表安装成功
          $result = array(
            "code" => "100",
            "msg" => "安装成功！"
          );
      } else {
          $result = array(
            "code" => "107",
            "msg" => "创建管理员失败！"
          );
      }

      // 断开数据库连接
      mysqli_close($conn);

    }else{
      if(strpos($conn->error,'already exists') !== false){
        $result = array(
          "code" => "105",
          "msg" => "请勿重复安装，如需重新安装请把数据库中所有huoma_前缀的表删掉。"
        );
      }else{
        $result = array(
          "code" => "106",
          "msg" => "安装失败，失败原因：".$conn->error
        );
      }
    }
  }
}

// 返回JSON
echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>
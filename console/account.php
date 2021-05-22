<?php
  // 页面字符编码
  header("Content-type:text/html;charset=utf-8");

  // 数据库配置
  include '../db_config/db_config.php';

  // 创建连接
  $conn = new mysqli($db_url, $db_user, $db_pwd, $db_name);

  // 获取设置项
  $sql_set = "SELECT * FROM huoma_set";
  $result_set = $conn->query($sql_set);
  if ($result_set->num_rows > 0) {
    while($row_set = $result_set->fetch_assoc()) {
      $title = $row_set['title'];
      $keywords = $row_set['keywords'];
      $description = $row_set['description'];
      $favicon = $row_set['favicon'];
    }
    if ($title == null || empty($title) || $title == '') {
        $title = "里客云活码系统 - www.likeyuns.com";
        $keywords = "活码,群活码,微信群活码系统,活码系统,群活码,不过期的微信群二维码,永久群二维码";
        $description = "这是一套开源、免费、可上线运营的活码系统，便于协助自己、他人进行微信私域流量资源获取，更大化地进行营销推广活动！降低运营成本，提高工作效率，获取更多资源。";
        $favicon = "../images/favicon.png";
    }
  }else{
    $title = "里客云活码系统 - www.likeyuns.com";
    $keywords = "活码,群活码,微信群活码系统,活码系统,群活码,不过期的微信群二维码,永久群二维码";
    $description = "这是一套开源、免费、可上线运营的活码系统，便于协助自己、他人进行微信私域流量资源获取，更大化地进行营销推广活动！降低运营成本，提高工作效率，获取更多资源。";
    $favicon = "../images/favicon.png";
  }
?>
<!DOCTYPE html>
<html>
<head>
  <title>个人中心 - <?php echo $title; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/jquery.min.js"></script>
  <script src="../js/Pay.Class.js?token=<?php echo md5(time()); ?>"></script>
  <script src="../js/popper.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="../css/chunk-vendors.huoma.css">
  <meta name="keywords" content="<?php echo $keywords; ?>">
  <meta name="description" content="<?php echo $description; ?>">
  <link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon" />
</head>
<body>

<!-- 全局信息提示框 -->
<div id="Result" style="display: none;"></div>

<?php
// 判断登录状态
session_start();
if(isset($_SESSION["huoma.admin"])){

  echo '<!-- 顶部导航栏 -->
<div id="topbar">
  <span class="admin-title">'.$title.'</span>
  <span class="admin-login-link"><a href="../account/exit">'.$_SESSION["huoma.admin"].' 退出</a></span>
</div>

<!-- 操作区 -->
<div class="container">
  <br/>
  <h3>个人中心</h3>
  <p>查看、编辑你的个人账号信息，续费管理等</p>
  
  <!-- 左右布局 -->
  <!-- 左侧布局 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">账号管理</button>
    <a href="./"><button type="button" class="btn btn-light">返回首页</button></a>
  </div>';

  // 获取账号信息
  $sql = "SELECT * FROM huoma_user WHERE user ='".$_SESSION["huoma.admin"]."'";
  $result = $conn->query($sql);

  // 获取套餐列表
  $sql_tc = "SELECT * FROM huoma_taocan";
  $result_tc = $conn->query($sql_tc);

  // 获取微信支付API
  $sql_wxpay = "SELECT * FROM huoma_payselect WHERE paytype='wx' AND payselect='2'";
  $result_wxpay = $conn->query($sql_wxpay);

  // 获取支付宝API
  $sql_alipay = "SELECT * FROM huoma_payselect WHERE paytype='ali' AND payselect='2'";
  $result_alipay = $conn->query($sql_alipay);
  
  if ($result->num_rows > 0) {
      echo '<!-- 右侧布局 -->
      <div class="right-nav">
        <table class="table">
          <thead>
            <tr>
              <th style="width:150px;">账号</th>
              <th>注册</th>
              <th>到期</th>
              <th style="text-align: center;">操作</th>
            </tr>
          </thead>
          <tbody>';

          // 遍历数据
          while($row = $result->fetch_assoc()) {
            $user = $row["user"];
            $reg_time = $row["reg_time"];
            $expire_time = $row["expire_time"];
            $user_status = $row["user_status"];
            $email = $row["email"];
            $user_id = $row["user_id"];
            $pwd = $row["pwd"];

            // 渲染到UI
            echo '<tr>';
              echo '<td class="td-title" style="width:150px;">'.$user.'</td>
              <td class="td-status">'.$reg_time.'</td>
              <td class="td-fwl">'.$expire_time.'</td>';
              echo '<td class="td-caozuo" style="text-align: center;">
              <a href="javascript:;" data-toggle="modal" class="update_user_btn" data-target="#ediuser_modal">修改</a>
              <a href="javascript:;" data-toggle="modal" class="update_user_btn" data-target="#xufei_modal">续费</a>
              </td>';
            echo '</tr>';
          }
          echo '</div></tbody></table>';

  }else{
    echo '<div class="right-nav">暂无账号</div>';
  }

  echo '<!-- 编辑账号信息 -->
  <div class="modal fade" id="ediuser_modal">
    <div class="modal-dialog">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">编辑账号信息</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <!-- 邮箱 -->
          <form onsubmit="return false" id="ediuser" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">新邮箱</span>
            </div>
            <input type="email" class="form-control" value="'.$email.'" placeholder="请输入新邮箱" name="new_email">
          </div>
          
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">新密码</span>
            </div>
            <input type="text" class="form-control" value="'.$pwd.'" placeholder="请输入新密码" name="new_pwd">
          </div>

          <input type="hidden" value="'.$user_id.'" name="user_id"/>
          <input type="hidden" value="'.$pwd.'" name="old_pwd"/>

          <!-- 提交 -->
          <button type="button" class="btn btn-dark" onclick="ediuser();">更新信息</button>
          </form>

        </div>
   
        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
        </div>
   
      </div>
    </div>
  </div>
  
  <!-- 续费 -->
  <div class="modal fade" id="xufei_modal">
    <div class="modal-dialog">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">续费</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <form onsubmit="return false" id="xufei">
          <select class="form-control" id="xufei_type" style="margin:15px 0;-webkit-appearance:none;">
            <option value="">选择续费方式↓↓↓</option>
            <option value="1">邀请码续费</option>
            <option value="2">在线支付续费</option>
          </select>

          <span id="yaoqingma" style="display:none;">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">邀请码</span>
            </div>
            <input type="text" class="form-control" placeholder="请粘贴邀请码" name="yqmstr">
            <input type="hidden" class="form-control" name="userid" value="'.$user_id.'">
            <input type="hidden" class="form-control" name="expire_time" value="'.$expire_time.'">
          </div>
          <button class="btn btn-secondary" onclick="yqm_xufei();">续费</button>
          </span>
          
          <span id="onlinepay" style="display:none;">
          <select class="form-control" name="taocan" id="select_taocan" style="-webkit-appearance:none;">
            <option value="">请选择续费的套餐↓↓↓</option>';
            if ($result_tc->num_rows > 0) {
              while($row_tc = $result_tc->fetch_assoc()) {
                $tc_days = $row_tc["tc_days"];
                $tc_price = $row_tc["tc_price"];
                $tc_title = $row_tc["tc_title"];
                echo '<option value="'.$tc_days.'-'.$tc_price.'">'.$tc_title.'（续期'.$tc_days.'天，'.$tc_price.'元）</option>';
              }
            }else{
              echo '<option value="">暂无套餐</option>';
            }
          echo '</select>';

          echo '<input type="hidden" value="'.$user_id.'" name="user_id" id="user_id"/>
          <br/>
          <div class="paybtn">';
          while($row_wxpay = $result_wxpay->fetch_assoc()) {
            $payapi = $row_wxpay["payapi"];
            echo '<button type="button" class="btn btn-dark" style="background:#07c160;border:1px solid #07c160;" onclick="'.$payapi.'();">微信支付</button> ';
          }
          while($row_alipay = $result_alipay->fetch_assoc()) {
            $payapi = $row_alipay["payapi"];
            echo '<button type="button" class="btn btn-dark" style="background:#1677ff;border:1px solid #1677ff;" onclick="'.$payapi.'();">支付宝</button>';
          }
          echo '</div>
          </form>
          
          <!-- 展示支付二维码 -->
          <div style="display:none;" class="pay_content">
            <div class="pay_qrcode"></div>
            <h4 class="paytips">请扫码完成支付</h4>
          </div>
          </span>

        </div>
   
        <!-- 模态框底部 -->
        <div class="modal-footer">
          <!-- 订单号 -->
          <div id="order_no" style="display:none;"></div>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
        </div>
   
      </div>
    </div>
  </div>

</div>';
}else{
  // 跳转到登陆界面
  header("Location:../account/login/");
}
?>

<script type="text/javascript">

// 监听选项
$("#xufei_type").bind('input propertychange',function(e){
  // 获取当前点击的选项
  var xufei_type = $(this).val();
  // 1代表邀请码，2代表在线支付
  if (xufei_type == 1) {
    $("#yaoqingma").css("display","block");
    $("#onlinepay").css("display","none");
  }else if (xufei_type == 2) {
    $("#yaoqingma").css("display","none");
    $("#onlinepay").css("display","block");
  }
})
</script>
</body>
</html>
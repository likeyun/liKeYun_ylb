<?php
  // 页面字符编码
  header("Content-type:text/html;charset=utf-8");

  // 数据库配置
  include '../../../db_config/db_config.php';

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
        $title = "引流宝 - 里客云开源活码系统";
        $keywords = "活码,群活码,微信群活码系统,活码系统,群活码,不过期的微信群二维码,永久群二维码";
        $description = "这是一套开源、免费、可上线运营的活码系统，便于协助自己、他人进行微信私域流量资源获取，更大化地进行营销推广活动！降低运营成本，提高工作效率，获取更多资源。";
        $favicon = "../../../images/favicon.png";
    }
  }else{
    $title = "引流宝 - 里客云开源活码系统";
    $keywords = "活码,群活码,微信群活码系统,活码系统,群活码,不过期的微信群二维码,永久群二维码";
    $description = "这是一套开源、免费、可上线运营的活码系统，便于协助自己、他人进行微信私域流量资源获取，更大化地进行营销推广活动！降低运营成本，提高工作效率，获取更多资源。";
    $favicon = "../../../images/favicon.png";
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title><?php echo $title; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../../../css/bootstrap.min.css">
  <script src="../../../js/jquery.min.js"></script>
  <script src="../../../js/popper.min.js"></script>
  <script src="../../../js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="../../../css/chunk-vendors.huoma.css">
  <link rel="stylesheet" type="text/css" href="../../../css/chunk-vendors.theme.css">
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
if(isset($_SESSION["huoma.dashboard"])){

  // 当前登录的用户
  $lguser= $_SESSION["huoma.dashboard"];

  echo '<!-- 顶部导航栏 -->
<div id="topbar">
  <span class="admin-title"><a href="./">'.$title.'</a></span>
  <span class="admin-login-link"><a href="../account/exit">'.$_SESSION["huoma.admin"].' 退出</a></span>
</div>

<!-- 操作区 -->
<div class="container">';

  echo '<br/>
  <h3>插件 / 付费进群 / 订单管理</h3> 
  <p>管理用户进群支付的订单</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <a href="ffjq_order.php?t=home/ffjq_order&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-zdy">付费订单</button></a>
    <a href="javascript:;"><button type="button" class="btn btn-zdylight" data-toggle="modal" data-target="#checkbz">查询备注码</button></a>
    <a href="index.php?t=home/index&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-zdylight">返回上一页</button></a>
    <a href="../../../dashboard/addons.php?t=home/addons&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-zdylight">返回插件中心</button></a>
    <a href="../../../dashboard/"><button type="button" class="btn btn-zdylight">返回首页</button></a>
    </div>';

      //计算订单数量
      $sql_ffjq = "SELECT * FROM huoma_addons_ffjq_order";
      $result_ffjq = $conn->query($sql_ffjq);
      $ffjq_num = $result_ffjq->num_rows;

      //每页显示的数量
      $lenght = 10;

      //当前页码
      @$page = $_GET['p']?$_GET['p']:1;

      //每页第一行
      $offset = ($page-1)*$lenght;

      //总数页
      $allpage = ceil($ffjq_num/$lenght);

      //上一页     
      $prepage = $page-1;
      if($page==1){
        $prepage=1;
      }

      //下一页
      $nextpage = $page+1;
      if($page==$allpage){
        $nextpage=$allpage;
      }

      // 获取订单列表
      if (trim(empty($_GET["ffjq_bzcode"]))) {
        $sql = "SELECT * FROM huoma_addons_ffjq_order ORDER BY ID DESC limit {$offset},{$lenght}";
      }else{
        $sql = "SELECT * FROM huoma_addons_ffjq_order WHERE ffjq_bzcode LIKE '%$_GET[ffjq_bzcode]%'";
      }
      $result = $conn->query($sql);
       
      if ($result->num_rows > 0) {

          echo '<!-- 右侧布局 -->
          <div class="right-nav">
          <table class="table">
          <thead>
          <tr>
          <th style="width:150px;">群标题</th>
          <th>备注码</th>
          <th>群ID</th>
          <th>订单号</th>
          <th>支付时间</th>
          <th>支付金额</th>
          <th style="text-align: center;">操作</th>
          </tr>
          </thead>
          <tbody>';

          // 输出数据
          while($row = $result->fetch_assoc()) {

            // 遍历数据
            $ffjq_id = $row["ffjq_id"];
            $ffjq_title = $row["ffjq_title"];
            $ffjq_time = $row["ffjq_time"];
            $ffjq_price = $row["ffjq_price"];
            $ffjq_order = $row["ffjq_order"];
            $ffjq_bzcode = $row["ffjq_bzcode"];
            $ffjq_openid = $row["ffjq_openid"];

            // 加载到ui模板
            echo '<tr>
            <td class="td-title" style="width:150px;">'.$ffjq_title.'</td>
            <td class="td-status">'.$ffjq_bzcode.'</td>
            <td class="td-status">'.$ffjq_id.'</td>
            <td class="td-status">'.$ffjq_order.'</td>
            <td class="td-status">'.$ffjq_time.'</td>
            <td class="td-fwl">¥'.$ffjq_price.'</td>
            <td class="td-caozuo" style="text-align: center;"><a href="javascript:;" id="'.$ffjq_order.'" onclick="delorder(this);" title="点击后马上就删除的哦！">删除</a></td>
            </tr>';
          }

          // 分页
          echo '<div class="fenye"><ul class="pagination pagination-sm">';
          if ($page == 1 && $allpage == 1) {
            // 当前页面是第一页，并且仅有1页
            // 不显示翻页控件
          }else if ($page == 1) {
            // 当前页面是第一页，还有下一页
            echo '<li class="page-item"><a class="page-link" href="./ffjq_order.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./ffjq_order.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }else if ($page == $allpage) {
            // 当前页面是最后一页
            echo '<li class="page-item"><a class="page-link" href="./ffjq_order.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./ffjq_order.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前页面是最后一页</a></li>';
          }else{
            echo '<li class="page-item"><a class="page-link" href="./ffjq_order.php">首页</a></li>
            <li class="page-item"><a class="page-link" href="./ffjq_order.php?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="./ffjq_order.php?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }
          echo '</ul></div></div></tbody></table>';
      } else {
			echo '<!-- 右侧布局 -->
			<div class="right-nav">暂无订单</div>';
      }
      // 断开数据库连接
      $conn->close();

      
}else{
  // 跳转到登陆界面
  header("Location:../account/login/");
}
?>
  
  <!-- 查询备注 -->
  <div class="modal fade" id="checkbz">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">查询备注</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <form action="ffjq_order.php" method="get">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">备注码</span>
            </div>
            <input type="text" class="form-control" placeholder="输入群成员备注码" name="ffjq_bzcode">
          </div>
          <p style="color: #ccc;font-size: 14px;">支付后进群，要求群成员复制备注码设置为群昵称，便于我们筛查一些通过别人支付后截图的二维码进群，查询不到的备注码就是未支付的。</p>
        </div>
   
        <!-- 模态框底部 -->
        <div class="modal-footer">
          <input type="submit" class="btn btn-tjzdy" value="查询" />
          </form>
        </div>
   
      </div>
    </div>
  </div>

</div>

<script>
// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
  $("#add_qun_hm .upload_status").css('display','none');
}

// 删除订单
function delorder(event){
  // 获得当前点击的订单
  var order = event.id;
  // 执行删除动作
  $.ajax({
      type: "GET",
      url: "./del_order_do.php?order="+order,
      success: function (data) {
        if (data.code == "100") {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 刷新列表
          location.reload();
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}


// 分享付费群
function sharequn(event){
  // 获得当前点击的群活码id
  var ffjqid = event.id;
  $.ajax({
      type: "GET",
      url: "./share_qun_do.php?ffqid="+ffjqid,
      success: function (data) {
        // 分享成功
        $("#share_qun .modal-body .link").text("链接："+data.rkurl+"");
        $("#share_qun .modal-body .qrcode").html("<img src='../../../console/qrcode.php?content="+data.rkurl+"' width='200'/>");
      },
      error : function() {
        // 分享失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}

</script>
</body>
</html>
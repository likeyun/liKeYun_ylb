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
  <title><?php echo $title; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/jquery.min.js"></script>
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

  // 当前登录的用户
  $lguser= $_SESSION["huoma.admin"];

  include '../db_config/VersionCheck.php';

  echo '<!-- 顶部导航栏 -->
<div id="topbar">
  <span class="admin-title"><a href="./">'.$title.'</a></span>
  <span class="admin-login-link"><a href="../account/exit">'.$_SESSION["huoma.admin"].' 退出</a></span>
</div>

<!-- 操作区 -->
<div class="container">';

  echo '<br/>
  <h3>微信群活码管理 V'.$version.'</h3> 
  <p>创建、编辑、删除、分享微信群活码</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">微信群活码</button>
    <button type="button" class="btn btn-light" data-toggle="modal" data-target="#add_qun_hm">创建群活码</button>
    <a href="wx.php?t=home/wx&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">客服活码</button></a>
    <a href="./active.php?t=home/active&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">活动码</button></a>';
    function Get_Addons_Dir($addons_path){
      if(is_dir($addons_path)){
        $addons_dir = scandir($addons_path);
        foreach ($addons_dir as $value){
          // 获得该目录下的文件
          if($value == '.' || $value == '..'){
            continue;
          }else if(is_dir($addons_path)){
            // 获得该目录下的addons_config配置文件的addons_title
            $addons_config = $addons_path.'/'.$value.'/addons_config.json';
            $addons_config_post = file_get_contents($addons_config,true);
            $addons_title_arr = json_decode($addons_config_post,true);
            $addons_title = $addons_title_arr["title"];
            $addons_home = $addons_path.'/'.$value.'/home/';
            echo '<a href="'.$addons_home.'"><button type="button" class="btn btn-light">'.$addons_title.'</button></a>';
          }
        }
      }
    }
    $addons_path = '../addons';
    Get_Addons_Dir($addons_path);
    echo '<a href="./account.php?t=home/account&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">个人中心</button></a>
    <a href="../account/exit?t=home/exit&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">退出登陆</button></a></div>';

      //计算总活码数量
      $sql_huoma = "SELECT * FROM huoma_qun WHERE qun_user='$lguser'";
      $result_huoma = $conn->query($sql_huoma);
      $allhuoma_num = $result_huoma->num_rows;

      //每页显示的活码数量
      $lenght = 10;

      //当前页码
      @$page = $_GET['p']?$_GET['p']:1;

      //每页第一行
      $offset = ($page-1)*$lenght;

      //总数页
      $allpage = ceil($allhuoma_num/$lenght);

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

      // 获取入口域名
      $sql_rkym = "SELECT * FROM huoma_yuming WHERE ym_type='1'";
      $result_rkym = $conn->query($sql_rkym);

      // 获取落地域名
      $sql_ldym = "SELECT * FROM huoma_yuming WHERE ym_type='2'";
      $result_ldym = $conn->query($sql_ldym);

      // 获取群活码列表
      $sql = "SELECT * FROM huoma_qun WHERE qun_user='$lguser' ORDER BY ID DESC limit {$offset},{$lenght}";
      $result = $conn->query($sql);
       
      if ($result->num_rows > 0) {

          echo '<!-- 右侧布局 -->
          <div class="right-nav">
          <table class="table">
          <thead>
          <tr>
          <th>标题</th>
          <th>状态</th>
          <th>重复进群</th>
          <th>时间</th>
          <th>访问</th>
          <th style="text-align: center;">操作</th>
          </tr>
          </thead>
          <tbody>';

          // 输出数据
          while($row = $result->fetch_assoc()) {
            // 遍历数据
            $qun_hmid = $row["qun_hmid"];
            $qun_title = $row["qun_title"];
            $qun_status = $row["qun_status"];
            $qun_creat_time = $row["qun_creat_time"];
            $qun_pv = $row["qun_pv"];
            $qun_chongfu = $row["qun_chongfu"];

            // 加载到ui模板
            echo '<tr>
            <td class="td-title">'.$qun_title.'</td>';
            if ($qun_status == 1) {
              echo '<td class="td-status"><span class="badge badge-success">正常</span></td>';
            }else if ($qun_status == 2) {
              echo '<td class="td-status"><span class="badge badge-danger">暂停</span></td>';
            }else if ($qun_status == 3) {
              echo '<td class="td-status"><span class="badge badge-danger">封禁</span></td>';
            }
            if ($qun_chongfu == 2) {
              echo '<td class="td-status"><span class="badge badge-success">允许</span></td>';
            }else if ($qun_chongfu == 1) {
              echo '<td class="td-status"><span class="badge badge-danger">禁止</span></td>';
            }
            echo '<td class="td-status">'.$qun_creat_time.'</td>
            <td class="td-fwl">'.$qun_pv.'</td>
            <td class="td-caozuo" style="text-align: center;">
            <div class="btn-group dropleft" style="cursor:pointer;">
            <span data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="badge badge-secondary">•••</span></span>
            <div class="dropdown-menu">
            <a class="dropdown-item" href="./edi_qun.php?hmid='.$qun_hmid.'&home=ediqun&lang=zh_CN&token='.md5(uniqid()).'">编辑</a>
            <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#share_qun" id="'.$qun_hmid.'" onclick="sharequn(this);">分享</a>
            <a class="dropdown-item" href="javascript:;" id="'.$qun_hmid.'" onclick="delqun(this);" title="点击后马上就删除的哦！">删除</a>
            </div>
            </div>
            </td>
            </tr>';
          }

          // 分页
          echo '<div class="fenye"><ul class="pagination pagination-sm">';
          if ($page == 1 && $allpage == 1) {
            // 当前页面是第一页，并且仅有1页
            // 不显示翻页控件
          }else if ($page == 1) {
            // 当前页面是第一页，还有下一页
            echo '<li class="page-item"><a class="page-link" href="./">首页</a></li>
            <li class="page-item"><a class="page-link" href="./?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }else if ($page == $allpage) {
            // 当前页面是最后一页
            echo '<li class="page-item"><a class="page-link" href="./">首页</a></li>
            <li class="page-item"><a class="page-link" href="./?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前页面是最后一页</a></li>';
          }else{
            echo '<li class="page-item"><a class="page-link" href="./">首页</a></li>
            <li class="page-item"><a class="page-link" href="./?p='.$prepage.'">上一页</a></li>
            <li class="page-item"><a class="page-link" href="./?p='.$nextpage.'">下一页</a></li>
            <li class="page-item"><a class="page-link" href="#">当前是第'.$page.'页</a></li>';
          }
          echo '</ul></div></div></tbody></table>';
      } else {
        echo '<div class="right-nav">暂无活码，请点击左侧创建群活码</div>';
      }
      // 断开数据库连接
      $conn->close();

      
}else{
  // 跳转到登陆界面
  header("Location:../account/login/");
}
?>
  
  <!-- 创建群活码 -->
  <div class="modal fade" id="add_qun_hm">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">创建群活码</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">

          <!-- 标题 -->
          <form role="form" action="##" onsubmit="return false" method="post" id="addqun" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">标题</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入标题" name="qun_title">
          </div>

          <!-- 入口域名 -->
          <select class="form-control" name="qun_rkym" style="-webkit-appearance:none;">
            <option value="">请选择入口域名</option>
            <?php
              if ($result_rkym->num_rows > 0) {
                while($row_rkym = $result_rkym->fetch_assoc()) {
                  $rkym = $row_rkym["yuming"];
                  echo '<option value="'.$rkym.'">'.$rkym.'</option>';
                }
                // 同时也可以选择当前系统使用的域名
                echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
              }else{
                // 没有绑定落地页，使用当前系统使用的域名
                echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
              }
            ?>
          </select>

          <!-- 落地域名 -->
          <select class="form-control" name="qun_ldym" style="-webkit-appearance:none;margin-top: 15px;">
            <option value="">请选择落地域名</option>
            <?php
              if ($result_ldym->num_rows > 0) {
                while($row_ldym = $result_ldym->fetch_assoc()) {
                  $ldym = $row_ldym["yuming"];
                  echo '<option value="'.$ldym.'">'.$ldym.'</option>';
                }
                // 同时也可以选择当前系统使用的域名
                echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
              }else{
                // 没有绑定落地页，使用当前系统使用的域名
                echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
              }
            ?>
          </select>

          <!-- 是否显示个人微信 -->
          <select class="form-control" id="grwx_status" style="margin:15px 0;-webkit-appearance:none;" name="wx_status">
            <option value="">是否显示客服微信</option>
            <option value="1">显示</option>
            <option value="2">隐藏</option>
          </select>

          <!-- 个人微信二维码 -->
          <div id="grwx_upload" style="display:none;"> 
            <div class="upload_byqun input-group mb-3">
              <input type="text" class="form-control" name="wx_qrcode" placeholder="请上传个人微信二维码或粘贴图片地址">
              <!-- 上传个人微信按钮 -->
              <div class="input-group-append" style="cursor:pointer;position: relative;">
                <span class="input-group-text">
                  <input type="file" id="select_wxqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
                </span>
              </div>
            </div>
          </div>
          </form>

          <div class="upload_status"></div>

          <!-- 说明 -->
          <p>创建完成后，点击 <span class="badge badge-secondary">•••</span> 编辑，上传群二维码</p>

        </div>
   
        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="addqun();">创建群活码</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
        </div>
   
      </div>
    </div>
  </div>


  <!-- 分享群活码 -->
  <div class="modal fade" id="share_qun">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">分享群活码</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <p class="link"></p>
          <p class="qrcode"></p>
        </div>
   
        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
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

//监听个人微信二维码的显示状态
$("#grwx_status").bind('input propertychange',function(e){
  //获取当前点击的状态
  var grwx_status = $(this).val();
  //如果开启备用群，则需要显示上传二维码和设置最大值
  if (grwx_status == 1) {
    $("#grwx_upload").css("display","block");
  }else if (grwx_status == 2) {
    //否则隐藏，不显示
    $("#grwx_upload").css("display","none");
  }
})

//监听个人微信二维码的显示状态
$("#grwx_select .form-control").bind('input propertychange',function(e){
  alert("666")
})

// 创建群活码
function addqun(){
  $.ajax({
      type: "POST",
      url: "./add_qun_do.php",
      data: $('#addqun').serialize(),
      success: function (data) {
        // 创建成功
        if (data.code==100) {
          $("#add_qun_hm .upload_status").css("display","block");
          $("#add_qun_hm .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 关闭模态框
          $('#add_qun_hm').modal('hide');
          // 刷新列表
          location.reload();
        }else{
          $("#add_qun_hm .upload_status").css("display","block");
          $("#add_qun_hm .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 创建失败
        $("#add_qun_hm .upload_status").css("display","block");
        $("#add_qun_hm .upload_status").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}


// 删除群活码
function delqun(event){
  // 获得当前点击的群活码id
  var del_qun_hmid = event.id;
  // 执行删除动作
  $.ajax({
      type: "GET",
      url: "./del_qun_do.php?hmid="+del_qun_hmid,
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


// 分享群活码
function sharequn(event){
  // 获得当前点击的群活码id
  var share_qun_hmid = event.id;
  $.ajax({
      type: "GET",
      url: "./share_qun_do.php?hmid="+share_qun_hmid,
      success: function (data) {
        // 分享成功
        $("#share_qun .modal-body .link").text("链接："+data.rkurl+"");
        $("#share_qun .modal-body .qrcode").html("<img src='./qrcode.php?content="+data.rkurl+"' width='200'/>");
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


// 上传微信群二维码
var wxqrcode_lunxun = setInterval("upload_wxqrcode()",2000);
  function upload_wxqrcode() {
  var wxqrcode_filename = $("#select_wxqrcode").val();
  if (wxqrcode_filename) {
    clearInterval(wxqrcode_lunxun);
    var addqun_form = new FormData(document.getElementById("addqun"));
    $.ajax({
      url:"upload.php",
      type:"post",
      data:addqun_form,
      cache: false,
      processData: false,
      contentType: false,
      success:function(data){
        if (data.res == 400) {
          $("#add_qun_hm .upload_status").css("display","block");
          $("#add_qun_hm .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          $("#grwx_upload .form-control").val(data.path);
          $("#grwx_upload .text").text("已上传");
        }else{
          $("#add_qun_hm .upload_status").css("display","block");
          $("#add_qun_hm .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error:function(data){
        $("#add_qun_hm .upload_status").css("display","block");
        $("#add_qun_hm .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
      },
      beforeSend:function(data){
        $("#add_qun_hm .upload_status").css("display","block");
        $("#add_qun_hm .upload_status").html("<div class=\"alert alert-warning\"><strong>正在上传...</strong></div>");
      }
    })
    // 关闭信息提示框
    setTimeout('closesctips()', 2000);
  }else{
    // console.log("等待上传");
  }
}
</script>
</body>
</html>
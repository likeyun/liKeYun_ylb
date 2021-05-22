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
  <title>编辑群活码 - <?php echo $title; ?></title>
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

  // 获得活码id
  $qun_hmid = trim($_GET["hmid"]);

  // 获取当前活码id下的相关信息
  $sql = "SELECT * FROM huoma_qun WHERE qun_hmid = '$qun_hmid'";
  $result = $conn->query($sql);
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $qun_title = $row["qun_title"];
      $qun_status = $row["qun_status"];
      $qun_creat_time = $row["qun_creat_time"];
      $qun_pv = $row["qun_pv"];
      $wx_status = $row["qun_wx_status"];
      $wx_qrcode = $row["qun_wx_qrcode"];
      $qun_yuming = $row["qun_yuming"];
      $qun_chongfu = $row["qun_chongfu"];
    }
  }

  echo '<!-- 顶部导航栏 -->
<div id="topbar">
  <span class="admin-title"><a href="../">'.$title.'</a></span>
  <span class="admin-login-link"><a href="../account/exit">'.$_SESSION["huoma.admin"].' 退出</a></span>
</div>

<!-- 操作区 -->
<div class="container">
  <br/>
  <h3>编辑群活码</h3>
  <p>编辑微信群活码、更新、替换子码</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <button type="button" class="btn btn-dark">编辑群活码</button>
    <button type="button" class="btn btn-light"><a href="./">返回首页</a></button>
  </div>';
  
  // 右侧布局
  echo '<form onsubmit="return false" id="ediqun" enctype="multipart/form-data">
  <div class="right-nav">';
    
    // 标题
    echo '<div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">标题</span>
      </div>
      <input type="text" class="form-control" value="'.$qun_title.'" placeholder="请输入标题" name="qun_title">
    </div>';

    // 落地页域名
    echo '<select class="form-control" name="qun_yuming" style="-webkit-appearance:none;"><option value="'.$qun_yuming.'">落地页域名：'.$qun_yuming.'</option>';
      // 获取落地页域名
      $sql_ym = "SELECT * FROM huoma_yuming";
      $result_ym = $conn->query($sql_ym);
      // 遍历列表
      if ($result_ym->num_rows > 0) {
        while($row_ym = $result_ym->fetch_assoc()) {
          $ym = $row_ym["yuming"];
          echo '<option value="'.$ym.'">'.$ym.'</option>';
        }
        // 同时也可以选择当前系统使用的域名
        echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
      }else{
        // 没有绑定落地页，使用当前系统使用的域名
        echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
      }
    echo '</select>';

    // 是否显示个人微信
    echo '<select class="form-control" id="grwx_status" style="margin:15px 0;-webkit-appearance:none;" name="wx_status">';
    if ($wx_status == 1) {
      echo '<option value="1">显示客服微信</option>
      <option value="2">隐藏客服微信</option>';
    }else{
      echo '<option value="2">隐藏客服微信</option>
      <option value="1">显示客服微信</option>';
    }  
    echo '</select>';

    // 个人微信二维码
    echo '<div id="grwx_upload" style="display:none;"> 
      <div class="upload_byqun input-group mb-3">
        <input type="text" class="form-control" name="wx_qrcode" value="'.$wx_qrcode.'" placeholder="请上传个人微信二维码或粘贴图片地址">
        <div class="input-group-append" style="cursor:pointer;">
          <span class="input-group-text" style="cursor:pointer;position: relative;">
            <input type="file" id="select_wxqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
          </span>
        </div>
      </div>
    </div>';

    // 重复进群设置
    echo '<select class="form-control" style="margin:15px 0;-webkit-appearance:none;" name="qun_chongfu">';
    if ($qun_chongfu == 1) {
      echo '<option value="1">禁止重复进群</option>
      <option value="2">允许重复进群</option>';
    }else{
      echo '<option value="2">允许重复进群</option>
      <option value="1">禁止重复进群</option>';
    }  
    echo '</select>';

    // 隐藏域
    echo '<input type="hidden" name="qun_hmid" value="'.$qun_hmid.'" />';

    // 活码开启状态
    echo '<select class="form-control" id="grwx_status" style="margin:15px 0;-webkit-appearance:none;" name="qun_status">';
    if ($qun_status == 3) {
      echo '<option>该活码因违规已被停止使用</option>';
    }else if ($qun_status == 1) {
      echo '<option value="1">正常使用</option>
      <option value="2">暂停使用</option>';
    }else if ($qun_status == 2) {
      echo '<option value="2">暂停使用</option>
      <option value="1">正常使用</option>';
    }
    echo '</select>';

    if ($qun_status !== '3') {
      // 更新按钮
      echo '<button type="button" class="btn btn-secondary" onclick="ediqun();">更新活码</button><br/><br/>';
    }
    
    echo '</form>';

    // 微信群二维码列表
    echo '<table class="table">
      <thead>
        <tr>
          <th>序号</th>
          <th>二维码</th>
          <th>状态</th>
          <th>时间</th>
          <th>到期</th>
          <th>阈值</th>
          <th>访问</th>
          <th style="text-align: center;">操作</th>
        </tr>
      </thead>
      <tbody>';

      // 获取当前活码id下的子码
      $sql_zima = "SELECT * FROM huoma_qunzima WHERE hmid = '$qun_hmid' ORDER BY ID ASC";
      $result_zima = $conn->query($sql_zima);
      if ($result_zima->num_rows > 0) {
        while($row_zima = $result_zima->fetch_assoc()) {
          $zmid = $row_zima["zmid"];
          $qrcode = $row_zima["qrcode"];
          $update_time = $row_zima["update_time"];
          $yuzhi = $row_zima["yuzhi"];
          $fwl = $row_zima["fwl"];
          $xuhao = $row_zima["xuhao"];
          $zima_status = $row_zima["zima_status"];
          $dqdate = $row_zima["dqdate"];

          // 遍历列表
          echo '<tr>
          <td class="td-title" style="width: 100px;">'.$xuhao.'</td>';
          if ($qrcode == '') {
            echo '<td class="td-status"><span class="badge badge-secondary">未上传</span></td>';
          }else{
            echo '<td class="td-status"><span class="badge badge-success">已上传</span></td>';
          }
          if ($zima_status == 1) {
            echo '<td class="td-status"><span class="badge badge-success">开启</span></td>';
          }else{
            echo '<td class="td-status"><span class="badge badge-danger">关闭</span></td>';
          }
          echo '<td class="td-status">'.$update_time.'</td>';
          if ($dqdate !== null) {
            echo '<td class="td-fwl">'.$dqdate.'</td>';
          }else{
            echo '<td class="td-status"><span class="badge badge-secondary">未设置</span></td>';
          }
          echo '<td class="td-fwl">'.$yuzhi.'</td>
          <td class="td-fwl">'.$fwl.'</td>
          <td class="td-caozuo" style="text-align: center;">
            <div data-toggle="modal" data-target="#edizima" id="'.$zmid.'" onclick="getzmid(this);"><span class="badge badge-secondary" style="cursor:pointer;">编辑</span></div>
          </td>
        </tr>';
        }
      }
      echo '</tbody>
    </table>';
    
    // 说明
    echo '<p>阈值：当该二维码被访问的次数达到你设定的阈值，就会自动切换下一个二维码。</p>
    <p>落地页域名：用户访问你的活码页面使用的域名。</p>
    <p>其他说明：本套系统仅支持创建7个子二维码，按顺序检索阈值和状态进行展示。</p>
  </div>

</div>';
}else{
  // 跳转到登陆界面
  header("Location:../LoginReg/Login.html");
}
?>

<!-- 编辑子码 -->
<div class="modal fade" id="edizima">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
 
      <!-- 模态框头部 -->
      <div class="modal-header">
        <h4 class="modal-title">编辑子码</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
 
      <!-- 模态框主体 -->
      <form onsubmit="return false" id="ediqunzima" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="input-group mb-3">
          <input type="text" class="form-control qrcode" placeholder="请上传群二维码" name="qrcode" style="-webkit-appearance:none;">
          <div class="input-group-append">
            <span class="input-group-text" style="cursor:pointer;position: relative;">
              <input type="file" id="select_qunqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
            </span>
          </div>
        </div>

        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">阈值</span>
          </div>
          <input type="text" class="form-control yuzhi" placeholder="达到这个访问量自动切换" name="yuzhi">
        </div>

        <select class="form-control" style="margin:15px 0;-webkit-appearance:none;" name="zima_status">
          <?php
            if ($zima_status == 2) {
              echo '<option value="1">开启</option>';
              echo '<option value="2">关闭</option>';
            }else{
              echo '<option value="2">关闭</option>';
              echo '<option value="1">开启</option>';
            }
          ?>
        </select>

         <!-- 到期天数 -->
         <select class="form-control" style="margin:15px 0;-webkit-appearance:none;" name="dqdate">
           <option id="dq_dqdate"></option>
           <option value="<?php echo date("Y-m-d",strtotime("+7 day")); ?>"><?php echo date("Y-m-d",strtotime("+7 day")); ?> 到期</option>
           <option value="<?php echo date("Y-m-d",strtotime("+6 day")); ?>"><?php echo date("Y-m-d",strtotime("+6 day")); ?> 到期</option>
           <option value="<?php echo date("Y-m-d",strtotime("+5 day")); ?>"><?php echo date("Y-m-d",strtotime("+5 day")); ?> 到期</option>
           <option value="<?php echo date("Y-m-d",strtotime("+4 day")); ?>"><?php echo date("Y-m-d",strtotime("+4 day")); ?> 到期</option>
           <option value="<?php echo date("Y-m-d",strtotime("+3 day")); ?>"><?php echo date("Y-m-d",strtotime("+3 day")); ?> 到期</option>
           <option value="<?php echo date("Y-m-d",strtotime("+2 day")); ?>"><?php echo date("Y-m-d",strtotime("+2 day")); ?> 到期</option>
           <option value="<?php echo date("Y-m-d",strtotime("+1 day")); ?>"><?php echo date("Y-m-d",strtotime("+1 day")); ?> 到期</option>
         </select>

        <!-- 隐藏域，子码id -->
        <input type="hidden" name="zmid" id="edizmid_val">

        <!-- 上传提示 -->
        <div class="upload_status"></div>
      </div>
 
      <!-- 模态框底部 -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="ediqunzima();">更新</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
      </div>
      </form>
 
    </div>
  </div>
</div>

<script>

// 延迟关闭信息提示框
function closesctips(){
  $("#Result").css('display','none');
  $("#edizima .upload_status").css("display","none");
}

// 加载后监听个人微信二维码的显示状态
$(document).ready(function(){
  var grwx_status = '<?php echo $wx_status; ?>';
  if (grwx_status == 1) {
    $("#grwx_upload").css("display","block");
  }else{
    $("#grwx_upload").css("display","none");
  }
}) 

// 手动切换监听个人微信二维码的显示状态
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

// 编辑群活码
function ediqun(){
  $.ajax({
      type: "POST",
      url: "./edi_qun_do.php",
      data: $('#ediqun').serialize(),
      success: function (data) {
        // 更新成功
        if (data.code==100) {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 关闭模态框
          $('#add_qun_hm').modal('hide');
          // 刷新列表
          location.href="./";
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 更新失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}

// 获得子码id和当前子码id下的子码二维码和阈值
function getzmid(event){
  var zmid = event.id;
  // 把子码id传到表单里
  $("#edizmid_val").val(zmid);
  // 获取子码二维码和阈值
  $.ajax({
      type: "POST",
      url: "./edi_qunzima_getzimainfo.php?zmid="+zmid,
      success: function (data) {
        // 获取成功
        if (data.code==100) {
          $("#edizima .modal-body .qrcode").val(data.qrcode);
          $("#edizima .modal-body .yuzhi").val(data.yuzhi);
          if (data.dqdate == null) {
            $("#dq_dqdate").val('<?php echo date("Y-m-d",strtotime("+7 day")); ?>');
            $("#dq_dqdate").text('<?php echo date("Y-m-d",strtotime("+7 day")); ?> 到期');
          }else{
            $("#dq_dqdate").val(data.dqdate);
            $("#dq_dqdate").text(data.dqdate+" 到期");
          }
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 获取失败
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
}

// 编辑子码
function ediqunzima(){
  $.ajax({
      type: "POST",
      url: "./edi_qunzima_do.php",
      data: $('#ediqunzima').serialize(),
      success: function (data) {
        // 更新成功
        if (data.code==100) {
          $("#edizima .upload_status").css("display","block");
          $("#edizima .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 关闭模态框
          $('#edizima').modal('hide');
          // 刷新列表
          location.reload();
        }else{
          $("#edizima .upload_status").css("display","block");
          $("#edizima .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
        }
      },
      error : function() {
        // 更新失败
        $("#edizima .upload_status").css("display","block");
        $("#edizima .upload_status").html("<div class=\"alert alert-danger\"><strong>服务器发生错误</strong></div>");
      }
  });
  // 关闭信息提示框
  setTimeout('closesctips()', 2000);
}

// 上传个人微信二维码
var wxqrcode_lunxun = setInterval("upload_wxqrcode()",2000);
  function upload_wxqrcode() {
  var wxqrcode_filename = $("#select_wxqrcode").val();
  if (wxqrcode_filename) {
    clearInterval(wxqrcode_lunxun);
    var addqun_form = new FormData(document.getElementById("ediqun"));
    $.ajax({
      url:"upload.php",
      type:"post",
      data:addqun_form,
      cache: false,
      processData: false,
      contentType: false,
      success:function(data){
        if (data.res == 400) {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          $("#grwx_upload .form-control").val(data.path);
          $("#grwx_upload .text").text("已上传");
        }else{
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-danger\"><strong>"+data+"</strong></div>");
        }
      },
      error:function(data){
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
      },
      beforeSend:function(data){
        $("#Result").css("display","block");
        $("#Result").html("<div class=\"alert alert-warning\"><strong>正在上传...</strong></div>");
      }
    })
    // 关闭信息提示框
    setTimeout('closesctips()', 2000);
  }else{
    // console.log("等待上传");
  }
}

// 上传微信群二维码（子码）
var qunqrcode_lunxun = setInterval("upload_qunzimaqrcode()",2000);
  function upload_qunzimaqrcode() {
  var qunqrcode_filename = $("#select_qunqrcode").val();
  if (qunqrcode_filename) {
    clearInterval(qunqrcode_lunxun);
    var ediqun_form = new FormData(document.getElementById("ediqunzima"));
    $.ajax({
      url:"upload.php",
      type:"post",
      data:ediqun_form,
      cache: false,
      processData: false,
      contentType: false,
      success:function(data){
        if (data.res == 400) {
          $("#edizima .upload_status").css("display","block");
          $("#edizima .upload_status").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          $("#edizima .modal-body .qrcode").val(data.path);
          $("#edizima .modal-body .text").text("已上传");
        }else{
          $("#edizima .upload_status").css("display","block");
          $("#edizima .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data+"</strong></div>");
        }
      },
      error:function(data){
        $("#edizima .upload_status").css("display","block");
        $("#edizima .upload_status").html("<div class=\"alert alert-danger\"><strong>"+data.msg+"</strong></div>");
      },
      beforeSend:function(data){
        $("#edizima .upload_status").css("display","block");
        $("#edizima .upload_status").html("<div class=\"alert alert-warning\"><strong>正在上传...</strong></div>");
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
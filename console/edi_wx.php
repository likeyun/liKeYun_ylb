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
  <title>编辑个人微信活码 - <?php echo $title; ?></title>
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

  // 获得wxid
  $wx_id = trim($_GET["wxid"]);

  // 获取当前wxid下的相关信息
  $sql_wx = "SELECT * FROM huoma_wx WHERE wx_id = '$wx_id'";
  $result_wx = $conn->query($sql_wx);
  if ($result_wx->num_rows > 0) {
    while($row_wx = $result_wx->fetch_assoc()) {
      $wx_title = $row_wx["wx_title"];
      $wx_status = $row_wx["wx_status"];
      $wx_ldym = $row_wx["wx_ldym"];
      $wx_moshi = $row_wx["wx_moshi"];

      // 渲染数据到UI
      echo '<!-- 顶部导航栏 -->
      <div id="topbar">
        <span class="admin-title"><a href="./">'.$title.'</a></span>
        <span class="admin-login-link"><a href="../account/exit">'.$_SESSION["huoma.admin"].' 退出</a></span>
      </div>

      <!-- 操作区 -->
      <div class="container">
        <br/>
        <h3>编辑客服活码</h3>
        <p>编辑客服活码，上传微信二维码</p>
        
        <!-- 左右布局 -->
        <!-- 电脑端横排列表 -->
        <div class="left-nav">
          <button type="button" class="btn btn-dark">编辑客服活码</button>
          <a href="./wx.php?t=home/ediwx&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-light">返回上一页</button></a>
          <a href="./"><button type="button" class="btn btn-light">返回首页</button></a>
        </div>

        <!-- 右侧布局 -->
        <div class="right-nav">
          <!-- 标题 -->
          <form onsubmit="return false" id="ediwx" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">标题</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入标题" value="'.$wx_title.'" name="wx_title">
          </div>';

          // 落地页域名
          echo '<select class="form-control" name="wx_ldym" style="-webkit-appearance:none;"><option value="'.$wx_ldym.'">落地页域名：'.$wx_ldym.'</option>';
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

          // 选择模式
          if ($wx_moshi == '1') {
            echo '<div class="radio">
              <input id="radio-1" name="wx_moshi" type="radio" value="1" checked>
              <label for="radio-1" class="radio-label">阈值模式</label>
              <input id="radio-2" name="wx_moshi" type="radio" value="2">
              <label for="radio-2" class="radio-label">随机模式</label>
            </div>';
          }else{
            echo '<div class="radio">
              <input id="radio-1" name="wx_moshi" type="radio" value="1">
              <label for="radio-1" class="radio-label">阈值模式</label>
              <input id="radio-2" name="wx_moshi" type="radio" value="2" checked>
              <label for="radio-2" class="radio-label">随机模式</label>
            </div>';
          }

          // 活码状态切换
          if($wx_status == '3') {
            echo '<br/><p style="color:#f00;">该活码因违规已被停止使用</p>';
          }else if ($wx_status == '1') {
            echo '<div class="radio">
              <input id="radio-5" name="wx_status" type="radio" value="1" checked>
              <label for="radio-5" class="radio-label">正常使用</label>
              <input id="radio-6" name="wx_status" type="radio" value="2">
              <label for="radio-6" class="radio-label">暂停使用</label>
            </div><br/>';
          }else if ($wx_status == '2') {
            echo '<div class="radio">
              <input id="radio-5" name="wx_status" type="radio" value="1">
              <label for="radio-5" class="radio-label">正常使用</label>
              <input id="radio-6" name="wx_status" type="radio" value="2" checked>
              <label for="radio-6" class="radio-label">暂停使用</label>
            </div><br/>';
          }
          
          echo '<!-- 隐藏域 -->
          <div>
            <input type="hidden" name="wx_id" value="'.$wx_id.'" />
          </div>';

          if ($wx_status !== '3') {
            echo '<!-- 提交按钮 -->
            <button type="button" class="btn btn-dark" onclick="ediwx();">更新活码</button><br/><br/>';
          }
          
          echo '</form>';

          echo '<table class="table">
            <thead>
              <tr>
                <th>序号</th>
                <th>二维码</th>
                <th>状态</th>
                <th>时间</th>
                <th>访问</th>
                <th>阈值</th>
                <th style="text-align: center;">操作</th>
              </tr>
            </thead>
            <tbody>';

            // 获取当前活码id下的子码
            $sql_zima = "SELECT * FROM huoma_wxzima WHERE wx_id = '$wx_id' ORDER BY ID ASC";
            $result_zima = $conn->query($sql_zima);
            if ($result_zima->num_rows > 0) {
              while($row_zima = $result_zima->fetch_assoc()) {
                $zmid = $row_zima["zmid"];
                $qrcode = $row_zima["qrcode"];
                $update_time = $row_zima["update_time"];
                $fwl = $row_zima["fwl"];
                $xuhao = $row_zima["xuhao"];
                $zima_status = $row_zima["zima_status"];
                $wx_yuzhi = $row_zima["wx_yuzhi"];

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
                echo '<td class="td-fwl">'.$update_time.'</td>
                <td class="td-fwl">'.$fwl.'</td>
                <td class="td-fwl">'.$wx_yuzhi.'</td>
                <td class="td-caozuo" style="text-align: center;">
                  <div data-toggle="modal" data-target="#edizima" id="'.$zmid.'" onclick="getzmid(this);"><span class="badge badge-secondary" style="cursor:pointer;">编辑</span></div>
                </td>
              </tr>';
              }
            }
           echo '</tbody>
          </table>

          <p style="color:#999;font-size:14px;">落地域名：用户扫码访问你的活码界面使用的域名。</p>
          <p style="color:#999;font-size:14px;">阈值模式：按照12345序号，每次扫码，达到阈值将自动切换为下一个客服二维码。</p>
          <p style="color:#999;font-size:14px;">随机模式：按照12345序号，每次扫码，展示随机序号的客服二维码。</p>
          <p style="color:#999;font-size:14px;">其他说明：本套系统仅支持创建5个客服二维码，按你设置的展示方式和状态进行展示。</p>
        </div>

      </div>';
    }
  }else{
    echo "<h1>参数错误！</h1>";
  }
}else{
  echo "未登录";
}
?>

<!-- 编辑子码 -->
<div class="modal fade" id="edizima">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
 
      <!-- 模态框头部 -->
      <div class="modal-header">
        <h4 class="modal-title">编辑客服码</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
 
      <!-- 模态框主体 -->
      <form onsubmit="return false" id="ediwxzima" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="input-group mb-3">
          <input type="text" class="form-control qrcode" placeholder="请上传微信二维码" name="qrcode" style="-webkit-appearance:none;">
          <div class="input-group-append">
            <span class="input-group-text" style="cursor:pointer;position: relative;">
              <input type="file" id="select_zimaqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
            </span>
          </div>
        </div>

        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">微信号</span>
          </div>
          <input type="text" class="form-control wxnum" placeholder="请输入微信号" name="wx_num">
        </div>

        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">阈值</span>
          </div>
          <input type="text" class="form-control wxyuzhi" placeholder="达到阈值自动切换下一个" name="wx_yuzhi">
        </div>

        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">备注</span>
          </div>
          <input type="text" class="form-control wxbeizhu" placeholder="留空则不展示备注信息" name="wx_beizhu">
        </div>
        
        <!-- 开启状态 -->
        <div class="radio"></div>

        <!-- 隐藏域，子码id -->
        <input type="hidden" name="zmid" id="edizmid_val"><br/>

        <!-- 上传提示 -->
        <div class="upload_status"></div>
      </div>
 
      <!-- 模态框底部 -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="ediwxzima();">更新</button>
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
}

// 编辑群活码
function ediwx(){
  $.ajax({
      type: "POST",
      url: "./edi_wx_do.php",
      data: $('#ediwx').serialize(),
      success: function (data) {
        // 更新成功
        if (data.code==100) {
          $("#Result").css("display","block");
          $("#Result").html("<div class=\"alert alert-success\"><strong>"+data.msg+"</strong></div>");
          // 刷新列表
          location.href="./wx.php";
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


// 上传客服二维码
var zimaqrcode_lunxun = setInterval("upload_zimazimaqrcode()",2000);
  function upload_zimazimaqrcode() {
  var zimaqrcode_filename = $("#select_zimaqrcode").val();
  if (zimaqrcode_filename) {
    clearInterval(zimaqrcode_lunxun);
    var edizima_form = new FormData(document.getElementById("ediwxzima"));
    $.ajax({
      url:"upload.php",
      type:"post",
      data:edizima_form,
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

// 获得子码id和当前子码id下的信息
function getzmid(event){
  var zmid = event.id;
  // 把子码id传到表单里
  $("#edizmid_val").val(zmid);
  // 获取子码二维码和阈值
  $.ajax({
      type: "POST",
      url: "./edi_wxzima_getzimainfo.php?zmid="+zmid,
      success: function (data) {
        // 获取成功
        if (data.code==100) {
          $("#edizima .modal-body .qrcode").val(data.qrcode);
          $("#edizima .modal-body .wxnum").val(data.wx_num);
          $("#edizima .modal-body .wxbeizhu").val(data.wx_beizhu);
          $("#edizima .modal-body .wxyuzhi").val(data.wx_yuzhi);
          if (data.zima_status == '1') {
            $("#edizima .radio").html('<input id="radio-3" name="zima_status" type="radio" value="1" checked><label for="radio-3" class="radio-label">开启</label><input id="radio-4" name="zima_status" type="radio" value="2"><label for="radio-4" class="radio-label">关闭</label>');
          }else{
            $("#edizima .radio").html('<input id="radio-3" name="zima_status" type="radio" value="1"><label for="radio-3" class="radio-label">开启</label><input id="radio-4" name="zima_status" type="radio" value="2" checked><label for="radio-4" class="radio-label">关闭</label>');
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
function ediwxzima(){
  $.ajax({
      type: "POST",
      url: "./edi_wxzima_do.php",
      data: $('#ediwxzima').serialize(),
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

</script>
</body>
</html>
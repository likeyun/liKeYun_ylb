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
  <script type="text/javascript" src="ffq.js"></script>
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

  // 检查是否存在插件需依赖的数据库表
  $sql_checktable = "SHOW TABLES like 'huoma_addons_ffjq'";
  $sql_checktable_result = $conn->query($sql_checktable);
  if ($sql_checktable_result->num_rows>0) {

  echo '<br/>
  <h3>插件 / 付费进群</h3> 
  <p>创建、编辑、删除、分享付费进群页面</p>
  
  <!-- 左右布局 -->
  <!-- 电脑端横排列表 -->
  <div class="left-nav">
    <button type="button" class="btn btn-zdy">付费群列表</button>
    <button type="button" class="btn btn-zdylight" data-toggle="modal" data-target="#add_qun_hm">创建付费群</button>
    <a href="ffjq_order.php?t=home/ffjq_order&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-zdylight">付费订单</button></a>
    <a href="../../../dashboard/addons.php?t=home/wx&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-zdylight">返回插件中心</button></a>
    <a href="../../../dashboard/"><button type="button" class="btn btn-zdylight">返回首页</button></a>
    </div>';
      
      //计算付费进群的群数量
      $sql_ffjq = "SELECT * FROM huoma_addons_ffjq WHERE ffjq_user='$lguser'";
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

      // 获取入口域名
      $sql_rkym = "SELECT * FROM huoma_yuming WHERE ym_type='1'";
      $result_rkym = $conn->query($sql_rkym);

      // 获取落地域名
      $sql_ldym = "SELECT * FROM huoma_yuming WHERE ym_type='2'";
      $result_ldym = $conn->query($sql_ldym);

      // 获取群列表
      $sql = "SELECT * FROM huoma_addons_ffjq WHERE ffjq_user='$lguser' ORDER BY ID DESC limit {$offset},{$lenght}";
      $result = $conn->query($sql);
       
      if ($result->num_rows > 0) {

          echo '<!-- 右侧布局 -->
          <div class="right-nav">
          <table class="table">
          <thead>
          <tr>
          <th style="width:200px;">标题</th>
          <th>状态</th>
          <th>价格</th>
          <th>时间</th>
          <th>访问</th>
          <th style="text-align: center;">操作</th>
          </tr>
          </thead>
          <tbody>';

          // 输出数据
          while($row = $result->fetch_assoc()) {

            // 遍历数据
            $ffjq_id = $row["ffjq_id"];
            $ffjq_title = $row["ffjq_title"];
            $ffjq_creatdate = $row["ffjq_creatdate"];
            $ffjq_pv = $row["ffjq_pv"];
            $ffjq_type = $row["ffjq_type"];
            $ffjq_user = $row["user"];
            $ffjq_rkym = $row["ffjq_rkym"];
            $ffjq_ldym = $row["ffjq_ldym"];
            $ffjq_price = $row["ffjq_price"];
            $ffjq_status = $row["ffjq_status"];

            // 加载到ui模板
            echo '<tr>
            <td class="td-title" style="width:200px;">'.$ffjq_title.'</td>';
            if ($ffjq_status == 1) {
              echo '<td class="td-status"><span class="badge badge-success">正常</span></td>';
            }else{
              echo '<td class="td-status"><span class="badge badge-danger">暂停</span></td>';
            }
            echo '<td class="td-status">¥'.number_format($ffjq_price,2,".","").'</td>';
            echo '<td class="td-status">'.$ffjq_creatdate.'</td>
            <td class="td-fwl">'.$ffjq_pv.'</td>
            <td class="td-caozuo" style="text-align: center;">
            <div class="btn-group dropleft" style="cursor:pointer;">
            <span data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="badge badge-secondary">•••</span></span>
            <div class="dropdown-menu">
            <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#edi_ffjq" id="'.$ffjq_id.'" onclick="ediffq_getinfo(this);">编辑</a>
            <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#share_qun" id="'.$ffjq_id.'" onclick="sharequn(this);">分享</a>
            <a class="dropdown-item" href="javascript:;" id="'.$ffjq_id.'" onclick="delffq(this);" title="点击后马上就删除的哦！">删除</a>';
            if ($ffjq_status == 1) {
              echo '<a class="dropdown-item" href="javascript:;" id="'.$ffjq_id.'-'.$ffjq_status.'" onclick="status_do(this);" title="暂停该群的访问">停用</a>';
            }else{
              echo '<a class="dropdown-item" href="javascript:;" id="'.$ffjq_id.'-'.$ffjq_status.'" onclick="status_do(this);" title="恢复该群的访问">启用</a>';
            }
            echo '</div>
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
      echo '<!-- 右侧布局 -->
      <div class="right-nav">暂无付费进群项目，请点击左侧创建</div>';
      }
      
    }else{
      echo '<br/>
      <h3>插件 / 付费进群</h3> 
      <p>创建、编辑、删除、分享付费进群页面</p>
      
      <!-- 左右布局 -->
      <!-- 电脑端横排列表 -->
      <div class="left-nav">
        <a href="javascript:alert(\'插件未安装\');"><button type="button" class="btn btn-zdy">付费群列表</button></a>
        <a href="javascript:alert(\'插件未安装\');"><button type="button" class="btn btn-zdylight">创建付费群</button></a>
        <a href="javascript:alert(\'插件未安装\');"><button type="button" class="btn btn-zdylight">付费订单</button></a>
        <a href="../../../dashboard/addons.php?t=home/addons&lang=zh_CN&token='.md5(uniqid()).'"><button type="button" class="btn btn-zdylight">返回插件中心</button></a>
        <a href="../../../dashboard/"><button type="button" class="btn btn-zdylight">返回首页</button></a>
        </div>';

      echo '<div class="right-nav">
      <p>在安装插件之前，请完善配置文件，编辑ffjq_config.php，填写微信支付相关信息，然后点击下方“安装插件”完成安装即可。安装插件后，将会在你的数据库创建huoma_addons_ffjq、huoma_addons_ffjq_order两个数据表。</p>
      <button type="submit" class="btn btn-tjzdy" onclick="ins_add();">安装插件</button>
      <br/><br/><p class="success" style="color:#f00;display:none;">安装成功</p>
      </div>';
    }

    // 断开数据库连接
    $conn->close();
}else{
  // 跳转到登陆界面
  header("Location:../../../dashboard/account/login/");
}
?>
  
  <!-- 创建付费群 -->
  <div class="modal fade" id="add_qun_hm">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">创建付费群</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">

          <!-- 标题 -->
          <form role="form" action="##" onsubmit="return false" method="post" id="creatffq" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">标题</span>
            </div>
            <input type="text" class="form-control" placeholder="请输入标题" name="ffjq_title">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">价格</span>
            </div>
            <input type="text" class="form-control" placeholder="请设置进群需支付的金额" name="ffjq_price">
          </div>

          <!-- 入口域名 -->
          <select class="form-control" name="ffjq_rkym" style="-webkit-appearance:none;">
            <option value="">请选择入口域名</option>
            <?php
              if ($result_rkym->num_rows > 0) {
                while($row_rkym = $result_rkym->fetch_assoc()) {
                  $rkym = $row_rkym["yuming"];
                  echo '<option value="'.$rkym.'">'.$rkym.'</option>';
                }
                // 没有绑定落地页，使用当前系统使用的域名
                // echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
              }else{
                // 没有绑定落地页，使用当前系统使用的域名
                // echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
                echo '<option value="">暂无绑定域名</option>';
              }
            ?>
          </select>

          <!-- 落地域名 -->
          <select class="form-control" name="ffjq_ldym" style="-webkit-appearance:none;margin-top: 15px;">
            <option value="">请选择落地域名</option>
            <?php
              if ($result_ldym->num_rows > 0) {
                while($row_ldym = $result_ldym->fetch_assoc()) {
                  $ldym = $row_ldym["yuming"];
                  echo '<option value="'.$ldym.'">'.$ldym.'</option>';
                }
                // 没有绑定落地页，使用当前系统使用的域名
                // echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
              }else{
                // 没有绑定落地页，使用当前系统使用的域名
                // echo '<option value="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</option>';
                echo '<option value="">暂无绑定域名</option>';
              }
            ?>
          </select>

          <!-- 群二维码上传 -->
          <div id="qun_upload" style="margin-top: 15px;"> 
            <div class="upload_byqun input-group mb-3">
              <input type="text" class="form-control" name="ffjq_qrcode" placeholder="请上传群二维码或粘贴图片地址">
              <div class="input-group-append" style="cursor:pointer;position: relative;">
                <span class="input-group-text">
                  <input type="file" id="select_qunqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
                </span>
              </div>
            </div>
          </div>

          </form>

          <div class="upload_status"></div>

        </div>
   
        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-tjzdy" onclick="addffq();">创建付费群</button>
        </div>
   
      </div>
    </div>
  </div>


  <!-- 分享付费群 -->
  <div class="modal fade" id="share_qun">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">分享付费群</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">
          <p class="link"></p>
          <p class="qrcode"></p>
        </div>

        <div class="modal-footer"></div>
   
      </div>
    </div>
  </div>


  <!-- 编辑付费群 -->
  <div class="modal fade" id="edi_ffjq">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
   
        <!-- 模态框头部 -->
        <div class="modal-header">
          <h4 class="modal-title">编辑付费群</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
   
        <!-- 模态框主体 -->
        <div class="modal-body">

          <!-- 标题 -->
          <form role="form" action="##" onsubmit="return false" method="post" id="ediffjq" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">标题</span>
            </div>
            <input type="text" class="form-control" id="ffjqtitle" placeholder="请输入标题" name="ffjq_title">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">价格</span>
            </div>
            <input type="text" class="form-control" id="ffjqprice" placeholder="请设置进群需支付的金额" name="ffjq_price">
          </div>

          <!-- 入口域名 -->
          <div id="select_rkym">
          	<select class="form-control" name="ffjq_rkym" style="-webkit-appearance:none;"></select>
          </div>

          <!-- 落地域名 -->
          <div id="select_ldym">
          	<select class="form-control" name="ffjq_ldym" id="ffjqldym" style="-webkit-appearance:none;margin-top: 15px;"></select>
          </div>

          <!-- 群二维码上传 -->
          <div class="qun_upload" style="margin-top: 15px;"> 
            <div class="upload_byqun input-group mb-3">
              <input type="text" class="form-control" id="ediffqqrcode" name="ffjq_qrcode" placeholder="请上传群二维码或粘贴图片地址">
              <div class="input-group-append" style="cursor:pointer;position: relative;">
                <span class="input-group-text">
                  <input type="file" id="edi_select_qunqrcode" class="file_btn" name="file"/><span class="text">上传图片</span>
                </span>
              </div>
            </div>
          </div>

          <!-- 用户 -->
          <input type="hidden" class="form-control" id="ffqid" name="ffjq_id">

          </form>

          <div class="upload_status"></div>

        </div>
   
        <!-- 模态框底部 -->
        <div class="modal-footer">
          <button type="button" class="btn btn-tjzdy" onclick="ediffq();">更新</button>
        </div>
   
      </div>
    </div>
  </div>

</div>
</body>
</html>
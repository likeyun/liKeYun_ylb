<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
    <script src="../js/jquery-3.4.1.min.js"></script>
    <style type="text/css">
    	*{
    		margin:0;
    		padding: 0;
    	}

    	h3{
    		text-align: center;
    		color: #333;
    		margin:35px auto 0;
    	}

		#bd-div{
			width: 88%;
			margin:30px auto;
		}

		#bd-div .formdiv{
			width: 90%;
			margin:0px auto;
		}

		#bd-div .formdiv .login_user{
			width: 100%;
			height: 45px;
			border:1px solid #ccc;
			-webkit-appearance:none;/*解决iOS Safari input按钮上的圆角问题*/
			font-size: 15px;
			text-indent: 15px;
			margin-bottom: 5px;
		}

		#bd-div .formdiv .loginbtn{
			width: 100%;
			height: 45px;
			background: #333;
			font-size: 15px;
			margin-top: 10px;
			color:#fff;
			border-radius: 100px;
			-webkit-appearance:none;/*解决iOS Safari input按钮上的圆角问题*/
		}

		#Result{
			text-align: center;
			font-size: 16px;
		}
    </style>
</head>
<?php
header("Content-Type:text/html;charset=utf-8");
//获得openid
$openid = $_GET["openid"];
//获取openid长度
$openidleng  = strlen(trim($openid));
//过滤openid
if (empty(trim($openid))) {
	echo "非法请求";
}else if ($openidleng < 28) {
	echo "非法请求";
}else{
	//验证该openid是否已经绑定账号
	require "../mysql_connect.php";
	$check_openid = mysql_query("SELECT * FROM userlist WHERE m_openid = '$openid'");
	$openid_result = mysql_num_rows($check_openid);
	if ($openid_result) {
		//如果存在该openid，就可以直接登陆

		//获取该openid的账号
		while ($row = mysql_fetch_array($check_openid)) {
			$username = $row["username"];
		}

		//存SESSION
		session_start();
		$_SESSION['www.likeyunba.com'] = $username;

		//登录成功
		header("Location:https://www.likeyun.cn/m/");

	}else{
		//否则，就提示要绑定账号和密码
		echo "<title>里客云资源站-账号绑定</title>";
		echo "<h3>初次使用微信登录需绑定账号</h3>";
		echo '<div id="bd-div">
				<div class="formdiv">
	                <form role="form" action="##" onsubmit="return false" method="post" id="loginregform">
	                    <input type="text" name="username" class="login_user" placeholder="输入账号"><br/>
	                    <input type="text" name="password" class="login_user" placeholder="输入密码"><br/>
	                    <input type="hidden" name="openid" value="'.$openid.'">
	                    <input type="submit" class="loginbtn" value="绑定" onclick="bduser()">
	                </form>
            	</div>
			  </div>

			  <div id="Result"></div>';
	}
}
?>
<!-- 关闭浮窗 -->
<script>
    function closesctips(){
        $("#Result .logintips").css('display','none');
    }
</script>

<!-- 跳转 -->
<script>
    function loginsuccess(){
        location.href="./login";
    }
</script>

<!-- AJAX -->
<script type="text/javascript">
        function bduser(){
            $.ajax({
                type: "POST",//方法
                url: "./bdwxlogin.php" ,//表单接收url
                data: $('#loginregform').serialize(),
                success: function (data) {
                    //绑定成功
                    if (data[0].result == "10000") {
                        $("#Result").html("<div class=\"logintips\">绑定成功</div>");
                        setTimeout('closesctips()', 2000);
                        setTimeout('loginsuccess()', 1000);
                    }else if (data[0].result == "10001") {
                        $("#Result").html("<div class=\"logintips\">账号还没填</div>");
                        setTimeout('closesctips()', 2000);
                    }else if (data[0].result == "10002") {
                        $("#Result").html("<div class=\"logintips\">密码还没填</div>");
                        setTimeout('closesctips()', 2000);
                    }else if (data[0].result == "10003") {
                        $("#Result").html("<div class=\"logintips\">该账号未注册</div>");
                        setTimeout('closesctips()', 2000);
                    }else if (data[0].result == "10004") {
                        $("#Result").html("<div class=\"logintips\">密码错误</div>");
                        setTimeout('closesctips()', 2000);
                    }
                },
                error : function() {
                  //绑定失败
                  $("#Result").html("<div class=\"logintips\">服务器错误</div>");
                }
            });
        }
</script>
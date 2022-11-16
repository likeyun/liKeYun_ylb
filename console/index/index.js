
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取访问量
    getPvTotal('群活码','qun');
}

// 获取登录状态
function getLoginStatus(){
    
    // 获取
    $.ajax({
        type: "POST",
        url: "../login/getLoginStatus.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 已登录
                $('#accountInfo').html('<span class="user_name">'+res.user_name+'</span><a href="javascript:;" onclick="exitLogin();">退出</a>');
                initialize_Login('login',res.user_admin)
                $("#right .data-card .data-content").css('display','block');
                $("#right .data-card .loading").css('display','none');
            }else{
                
                // 未登录
                $('#accountInfo').html('<a href="../login/">登录账号</a>');
                initialize_Login('unlogin',2);
                $("#right .data-card .data-content").css('display','none');
                $("#right .data-card .loading").css('display','block');
                warningPage('未登录或登录过期');
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 登录后的一些初始化
function initialize_Login(loginStatus,user_admin){
    
    if(loginStatus == 'login'){
        
        // 判断管理权限
        if(user_admin == '1'){
            
            // 显示创建按钮
            $('#button-view').css('display','block');
        }else{
            
            // 隐藏创建按钮
            $('#button-view').css('display','none');
        }
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
    }
}

// 注销登录
function exitLogin(){
    
    $.ajax({
        type: "POST",
        url: "../login/exitLogin.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 刷新
                location.reload();
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 用户升级初始化
function initialize_index(){
    
    $.ajax({
        type: "POST",
        url: "./initialize.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                alert(res.msg);
                location.reload();
            }else{
                
                alert(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            alert('服务器发生错误');
        }
    });
}

// 错误页面
function errorPage(text){
    $("#right .data-card .data-content").css('display','none');
    $("#right .data-card .loading").html('<img src="../../static/img/errorIcon.png"/><br/><p>'+text+'</p>');
    $("#right .data-card .loading").css('display','block');
}

// 提醒页面
function warningPage(text){
    $("#right .data-card .data-content").css('display','none');
    $("#right .data-card .loading").html('<img src="../../static/img/warningIcon.png"/><br/><p>'+text+'</p>');
    $("#right .data-card .loading").css('display','block');
}
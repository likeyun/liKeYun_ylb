
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取配置
    getNotificationConfig();
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
                // 账号及版本信息
                var $account = $(
                    '<div class="version">'+res.version+'</div>' +
                    '<div class="user_name">'+res.user_name+' <span onclick="exitLogin();" class="exitLogin">退出</span></div>'
                );
                $(".left .account").html($account);
                
                // 初始化
                initialize_Login('login',res.user_admin)
            }else{
                
                // 未登录
                // 账号及版本信息
                var $account = $(
                    '<div class="version">'+res.version+'</div>' +
                    '<div class="user_name">未登录</div>'
                );
                $(".left .account").html($account);
                
                // 初始化
                initialize_Login('unlogin');
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
        
        // 管理权限
        if(user_admin == 2){
            
            // 不渲染按钮
            $('#button-view').html('');
        }else{
            
            // 显示
            $('#button-view').css('display','block');
        }
    }else{
        
        // 不渲染按钮
        $('#button-view').html('');
    }
}

// 获取配置
function getNotificationConfig(){
    
    $.ajax({
        type: "POST",
        url: "./getNotificationConfig.php",
        success: function(res){
            
            if(res.code == 200){
                
                var $notificationConfig_HTML = $(
                    '<form id="editNotificationConfig">' +
                    '<p class="channel-title">企业微信</p>' +
    				'<span class="input-text">corpid</span>' +
                    '<input type="text" name="corpid" id="corpid" class="form-control channel-form" autocomplete="off" placeholder="企业微信corpid">' +
                    '<span class="input-text">corpsecret</span>' +
                    '<input type="text" name="corpsecret" id="corpsecret" class="form-control channel-form" autocomplete="off" placeholder="企业微信corpsecret">' +
                    '<span class="input-text">接收者ID</span>' +
                    '<input type="text" name="touser" id="touser" class="form-control channel-form" autocomplete="off" placeholder="企业微信touser">' +
                    '<span class="input-text">应用ID</span>' +
                    '<input type="text" name="agentid" id="agentid" class="form-control channel-form" autocomplete="off" placeholder="agentid">' +
                    '<hr class="channel-hr">' +
                    '<p class="channel-title">Bark</p>' +
    				'<span class="input-text">URL</span>' +
                    '<input type="text" name="bark_url" id="bark_url" class="form-control channel-form" autocomplete="off" placeholder="粘贴Bark APP的推送URL">' +
                    '<hr class="channel-hr">' +
                    '<p class="channel-title">电子邮件</p>' +
    				'<span class="input-text">发送端邮箱账号</span>' +
                    '<input type="text" name="email_acount" id="email_acount" class="form-control channel-form" autocomplete="off" placeholder="用于发送邮件的账号">' +
                    '<span class="input-text">发送端邮箱密码</span>' +
                    '<input type="text" name="email_pwd" id="email_pwd" class="form-control channel-form" autocomplete="off" placeholder="用于发送邮件的账号的授权码">' +
                    '<span class="input-text">SMTP邮件服务器</span>' +
                    '<input type="text" name="email_smtp" id="email_smtp" class="form-control channel-form" autocomplete="off" placeholder="发送端邮箱的服务器">' +
                    '<span class="input-text">邮件服务器端口</span>' +
                    '<input type="text" name="email_port" id="email_port" class="form-control channel-form" autocomplete="off" placeholder="发送端邮箱的服务器端口">' +
                    '<span class="input-text">接收通知邮箱账号</span>' +
                    '<input type="text" name="email_receive" id="email_receive" class="form-control channel-form" autocomplete="off" placeholder="通知邮件将发送至该账号">' +
                    '<hr class="channel-hr">' +
                    '<p class="channel-title">Server酱</p>' +
    				'<span class="input-text">SendKey</span>' +
                    '<input type="text" name="SendKey" id="SendKey" class="form-control channel-form" autocomplete="off" placeholder="Server酱 API SendKey">' +
                    '<hr class="channel-hr">' +
                    '<p class="channel-title">HTTP</p>' +
    				'<span class="input-text">接收POST数据的URL</span>' +
                    '<input type="text" name="http_url" id="http_url" class="form-control channel-form" autocomplete="off" placeholder="粘贴用于接收POST数据的URL">' +
                    '</form>' +
                    '<button class="default-btn" style="margin-top: 20px;" onclick="editNotificationConfig()">提交设置</button>' +
                    '<a href="https://docs.qq.com/doc/DREdWVGJxeFFOSFhI" target="blank" class="channel-doc">如何填写？阅读使用说明</a>'
                );
                $("#right .data-card .data-list").html($notificationConfig_HTML);
                
                // 将配置信息填写至表单
                $('#corpid').val(res.notificationConfig.corpid);
                $('#corpsecret').val(res.notificationConfig.corpsecret);
                $('#touser').val(res.notificationConfig.touser);
                $('#agentid').val(res.notificationConfig.agentid);
                
                $('#bark_url').val(res.notificationConfig.bark_url);
                $('#email_acount').val(res.notificationConfig.email_acount);
                $('#email_pwd').val(res.notificationConfig.email_pwd);
                $('#email_receive').val(res.notificationConfig.email_receive);
                $('#email_smtp').val(res.notificationConfig.email_smtp);
                $('#email_port').val(res.notificationConfig.email_port);
                
                $('#SendKey').val(res.notificationConfig.SendKey);
                $('#http_url').val(res.notificationConfig.http_url);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 提交配置
function editNotificationConfig(){
    
    $.ajax({
        type: "POST",
        url: "./editNotificationConfig.php",
        data: $('#editNotificationConfig').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                showNotification(res.msg);
                setTimeout("location.reload()",1500);
            }else{
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('服务器发生错误！可按F12打开开发者工具点击Network或网络查看editNotificationConfig.php的返回信息进行排查！');
        }
    });
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1.5秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 生成随机token
function creatPageToken(length) {
    var str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) 
        result += str[Math.floor(Math.random() * str.length)];
    return result;
}

// 错误页面
function errorPage(text){
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html('<img src="../../static/img/errorIcon.png"/><br/><p>'+text+'</p>');
    $("#right .data-card .loading").css('display','block');
}

// 提醒页面
function warningPage(text){
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html('<img src="../../static/img/warningIcon.png"/><br/><p>'+text+'</p>');
    $("#right .data-card .loading").css('display','block');
}

// 打开操作反馈（操作成功）
function showSuccessResult(content){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', 2500); // 2.5秒后自动关闭
}

// 打开操作反馈（操作失败）
function showErrorResult(content){
    $('#app .result').html('<div class="error">'+content+'</div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 2500); // 2.5秒后自动关闭
}

// 关闭操作反馈
function hideResult(){
    $("#app .result .success").css("display","none");
    $("#app .result .error").css("display","none");
    $("#app .result .success").text('');
    $("#app .result .error").text('');
}

// 隐藏Modal（传入节点id决定隐藏哪个Modal）
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}

// 显示Modal（传入节点id决定隐藏哪个Modal）
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
}

// 显示全局信息提示弹出提示
function showNotification(message) {
    
    // 获取文案
	$('#notification-text').text(message);
	
    // 计算文案长度并设置宽度
	var textLength = message.length * 25;
	$('#notification-text').css('width',textLength+'px');
	
    // 距离顶部的高度
	$('#notification').css('top', '25px');
	
    // 延迟隐藏
	setTimeout(function() {
		hideNotification();
	}, 3000);
}

// 隐藏全局信息提示弹出提示
function hideNotification() {
	var $notificationContainer = $('#notification');
	$notificationContainer.css('top', '-100px');
}

// 获取URL参数
function queryURLParams(url) {
    var pattern = /(\w+)=(\w+)/ig;
    var parames = {};
    url.replace(pattern, ($, $1, $2) => {
        parames[$1] = $2;
    });
    return parames;
}
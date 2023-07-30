
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的账号列表
        getUserList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getUserList();
    }
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
                // 账号信息
                var $accountInfo_HTML = $(
                    '<span class="user_name">'+res.user_name+'</span>' +
                    '<span onclick="exitLogin();">退出</span>'
                );
                $("#accountInfo").html($accountInfo_HTML);
                
                // 初始化
                initialize_Login('login',res.user_admin)
            }else{
                
                // 未登录
                $('#accountInfo').html('<a href="../login/">登录账号</a>');
                
                // 初始化
                initialize_Login('unlogin',2);
            }
        },
        error: function() {
            
            // 服务器发生错误
            errorPage('data-list','getLoginStatus.php');
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

// 获取账号列表
function getUserList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getUserList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getUserList.php?p="+pageNum
    }
    
    // 初始化
    initialize_getUserList();
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>ID</th>' +
                '   <th>账号</th>' +
                '   <th>注册时间</th>' +
                '   <th>邮箱</th>' +
                '   <th>权限</th>' +
                '   <th>管理员</th>' +
                '   <th>备注</th>' +
                '   <th>状态</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.userList.length; i++) {
                    
                    // 数据判断并处理
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）ID
                    var user_id = res.userList[i].user_id;
                    
                    // （3）账号
                    var user_name = res.userList[i].user_name;
                    
                    // （4）状态
                    if(res.userList[i].user_status == '1'){
                        
                        // 正常
                        var user_status = 
                        '<span class="switch-on" onclick="changeUserStatus('+user_id+');">'+
                        '<span class="press"></span>'+
                        '</span>';
                    }else{
                        
                        // 关闭
                        var user_status = 
                        '<span class="switch-off" onclick="changeUserStatus('+user_id+');">'+
                        '<span class="press"></span>'+
                        '</span>';
                    }
                    
                    // （5）注册时间
                    var user_creat_time = res.userList[i].user_creat_time;
                    
                    // （6）邮箱
                    var user_email = res.userList[i].user_email;
                    
                    // （7）权限
                    if(res.userList[i].user_admin == '1'){
                        
                        // 管理员
                        var user_admin = '<span>管理员</span>';
                    }else{
                        
                        // 成员
                        var user_admin = '<span title="不提供修改权限入口，确实需修改请咨询开发者！">成员</span>';
                    }
                    
                    // （8）管理员
                    var user_manager = res.userList[i].user_manager;
                    
                    // （9）备注
                    if(res.userList[i].user_beizhu == null || res.userList[i].user_beizhu == ''){
                        
                        var user_beizhu = '-';
                        
                    }else{
                        
                        var user_beizhu = res.userList[i].user_beizhu;
                    }
                    
                    // （10）到期时间
                    if(res.userList[i].user_expire == null || res.userList[i].user_expire == ''){
                        
                        var user_expire = '-';
                        
                    }else{
                        
                        var user_expire = res.userList[i].user_expire;
                    }
                    
                    
                    // 列表
                    if(res.user_admin == 1){
                        
                        // 管理员
                        var $tbody_HTML = $(
                            '<tr>' +
                            '   <td>'+xuhao+'</td>' +
                            '   <td>'+user_id+'</td>' +
                            '   <td>'+user_name+'</td>' +
                            '   <td>'+user_creat_time+'</td>' +
                            '   <td>'+user_email+'</td>' +
                            '   <td>'+user_admin+'</td>' +
                            '   <td>'+user_manager+'</td>' +
                            '   <td>'+user_beizhu+'</td>' +
                            '   <td>'+user_status+'</td>' +
                            '   <td class="dropdown-td">' +
                            '       <div class="dropdown">' +
                            '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                            '           <div class="dropdown-menu">' +
                            '               <span class="dropdown-item" data-toggle="modal" data-target="#EditUserModal" onclick="getUserInfo('+user_id+')">编辑</span>' +
                            '               <span class="dropdown-item" data-toggle="modal" data-target="#DelUserModal" onclick="askDelUser('+user_id+')">删除</span>' +
                            '           </div>' +
                            '       </div>' +
                            '   </td>' +
                            '</tr>'
                        );
                    }else{
                        
                        // 非管理员
                        var $tbody_HTML = $(
                            '<tr>' +
                            '   <td>'+xuhao+'</td>' +
                            '   <td>'+user_id+'</td>' +
                            '   <td>'+user_name+'</td>' +
                            '   <td>'+user_creat_time+'</td>' +
                            '   <td>'+user_email+'</td>' +
                            '   <td>'+user_admin+'</td>' +
                            '   <td>'+user_manager+'</td>' +
                            '   <td>'+user_beizhu+'</td>' +
                            '   <td>'+user_status+'</td>' +
                            '   <td class="dropdown-td">' +
                            '       <div class="dropdown">' +
                            '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                            '           <div class="dropdown-menu">' +
                            '               <span class="dropdown-item" data-toggle="modal" data-target="#EditUserModal" onclick="getUserInfo('+user_id+')">编辑</span>' +
                            '           </div>' +
                            '       </div>' +
                            '   </td>' +
                            '</tr>'
                        );
                    }
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页
                if(res.page == 1 && res.allpage == 1){
                    
                    // 当前页码=1 且 总页码>1
                    // 无需显示分页控件
                    $("#right .data-card .fenye").css("display","none");
                }else if(res.page == 1 && res.allpage > 1){
                    
                    // 当前页码=1 且 总页码>1
                    // 代表还有下一页
                    var $UserFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $UserFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $UserFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }
                
                // 渲染分页控件
                $("#right .data-card .fenye").html($UserFenye_HTML);
                
                // 设置URL
                if(res.page !== 1){
                    window.history.pushState('', '', '?p='+res.page+'&token='+creatPageToken(32));
                }
                
            }else{
                
                // 未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../login/');
                }
                
                // 非200状态码
                noData(res.msg);
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','getUserList.php');
        
        // 隐藏button
        $('#right .button-view').html('');
      },
    });
}

// 分页
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页用户列表
    getUserList(pageNum);
}

// 切换switch（changeUserStatus）
function changeUserStatus(user_id){

    // 修改
    $.ajax({
        type: "POST",
        url: "./changeUserStatus.php?user_id="+user_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 刷新
                getUserList();
                showNotification(res.msg);
            }else{
                
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('changeUserStatus.php发生错误！');
        }
    });
}

// 创建账号
function creatUser(){
    
    $.ajax({
        type: "POST",
        url: "./createUser.php",
        data: $('#createUser').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("CreatUserModal")', 500);
                
                // 重新加载账号列表
                setTimeout('getUserList();', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createUser.php');
        }
    });
}

// 编辑用户
function editUser(){
    
    $.ajax({
        type: "POST",
        url: "./editUser.php",
        data: $('#editUser').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("EditUserModal")', 500);
                
                // 重新加载用户列表
                setTimeout('getUserList();', 500);
                
                // 获取登录状态
                getLoginStatus();
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editUser.php');
        }
    });
}

// 询问是否要删除用户
function askDelUser(user_id){
    
    // 将群id添加到button的delChannel函数用于传参执行删除
    $('#DelUserModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delUser('+user_id+');">确定删除</button>'
    );
}

// 删除用户
function delUser(user_id){
    
    $.ajax({
        type: "GET",
        url: "./delUser.php?user_id="+user_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                // 隐藏Modal
                hideModal("DelUserModal");
                
                // 重新加载用户列表
                setTimeout('getUserList()', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delUser.php');
        }
    });
}

// 获取账号详情
function getUserInfo(user_id){

    // 初始化（将密码框清空、恢复默认样式）
    $('#user_pass_edit').val('');
    $('#user_id_edit').val('');
    $('#user_pass_count_edit').text(0);
    $('#user_pass_count_style_edit').css('color','#999');
    $('#user_pass_edit').css('border-color','#ced4da');
    
    $.ajax({
        type: "GET",
        url: "./getUserInfo.php?user_id="+user_id,
        success: function(res){

            if(res.code == 200){
                
                // 初始化
                $("#user_mb_ask_edit").empty('');
                
                // （1）账号
                $('#user_name_edit').val(res.userInfo[0].user_name);
                
                // （2）邮箱
                $('#user_email_edit').val(res.userInfo[0].user_email);
                
                // （3）获取当前设置的密保问题
                $("#user_mb_ask_edit").append(
                    '<option value="'+res.userInfo[0].user_mb_ask+'">'+res.userInfo[0].user_mb_ask+'</option>'
                );
                
                // 加载系统自带的密保问题
                getmibaoAskList();
                
                // （4）密保问题答案
                $('#user_mb_answer_edit').val(res.userInfo[0].user_mb_answer);
                
                // （4）状态
                if(res.userInfo[0].user_status == '1'){
                    
                    // 正常
                    $("#user_status_edit").html(
                        '<option value="1">正常</option><option value="2">停用</option>'
                    );
                }else{
                    
                    // 停用
                    $("#user_status_edit").html(
                        '<option value="2">停用</option><option value="1">正常</option>'
                    );
                }
 
                // （5）备注
                $('#user_beizhu_edit').val(res.userInfo[0].user_beizhu);
                
                // （6）user_id
                $('#user_id_edit').val(user_id);
                            
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getUserInfo.php');
        }
    });
}

// 查询用户
function checkUser(){
    
    $.ajax({
        type: "POST",
        url: "./checkUser.php",
        data: $('#checkUser').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                setTimeout('hideModal("checkUserModal")', 500);

                // （1）序号
                var xuhao = 1;
                
                // （2）ID
                var user_id = res.userList[0].user_id;
                
                // （3）账号
                var user_name = res.userList[0].user_name;
                
                // （4）状态
                if(res.userList[0].user_status == '1'){
                    
                    // 正常
                    var user_status = 
                    '<span class="switch-on" onclick="changeUserStatus('+user_id+');">'+
                    '<span class="press"></span>'+
                    '</span>';
                }else{
                    
                    // 关闭
                    var user_status = 
                    '<span class="switch-off" onclick="changeUserStatus('+user_id+');">'+
                    '<span class="press"></span>'+
                    '</span>';
                }
                
                // （5）注册时间
                var user_creat_time = res.userList[0].user_creat_time;
                
                // （6）邮箱
                var user_email = res.userList[0].user_email;
                
                // （7）权限
                if(res.userList[0].user_admin == '1'){
                    
                    // 管理员
                    var user_admin = '<span>管理员</span>';
                }else{
                    
                    // 成员
                    var user_admin = '<span>成员</span>';
                }
                
                // （8）管理员
                var user_manager = res.userList[0].user_manager;
                
                // （9）备注
                if(res.userList[0].user_beizhu == null || res.userList[0].user_beizhu == ''){
                    var user_beizhu = '-';
                    
                }else{
                    var user_beizhu = res.userList[0].user_beizhu;
                }
                
                // 列表
                var $tbody_HTML = $(
                    '<tr>' +
                    '   <td>'+xuhao+'</td>' +
                    '   <td>'+user_id+'</td>' +
                    '   <td>'+user_name+'</td>' +
                    '   <td>'+user_creat_time+'</td>' +
                    '   <td>'+user_email+'</td>' +
                    '   <td>'+user_admin+'</td>' +
                    '   <td>'+user_manager+'</td>' +
                    '   <td>'+user_beizhu+'</td>' +
                    '   <td>'+user_status+'</td>' +
                    '   <td class="dropdown-td">' +
                    '       <div class="dropdown">' +
                    '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                    '           <div class="dropdown-menu">' +
                    '               <span class="dropdown-item" data-toggle="modal" data-target="#EditUserModal" onclick="getUserInfo('+user_id+')">编辑</span>' +
                    '               <span class="dropdown-item" data-toggle="modal" data-target="#DelUserModal" onclick="askDelUser('+user_id+')">删除</span>' +
                    '           </div>' +
                    '       </div>' +
                    '   </td>' +
                    '</tr>'
                );
                $("#right .data-list tbody").html($tbody_HTML);
                
                // 将分页控件隐藏
                $('#right .data-card .fenye').css('display','none');
                $('#right .data-card .fenye').html('');

            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('checkUser.php');
        }
    });
}

// 获取密保问题列表
function getmibaoAskList(){
    
    var $option_HTML = $(
        '<option value="你出生的城市？">你出生的城市？</option>'+
        '<option value="你母亲的姓名？">你母亲的姓名？</option>'+
        '<option value="你高三班主任姓名？">你高三班主任姓名？</option>'+
        '<option value="你父亲的生日？">你父亲的生日？</option>'+
        '<option value="你的手机号码？">你的手机号码？</option>'+
        '<option value="身份证后8位？">身份证后8位？</option>'+
        '<option value="你毕业的大学全称？">你毕业的大学全称？</option>'
    );
    $("#user_mb_ask_edit").append($option_HTML);
}

// 分享客服码
function shareChannel(channel_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    // 分享
    $.ajax({
        type: "GET",
        url: "./shareChannel.php?channel_id="+channel_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 长链接
                $("#longUrl").text(res.longUrl);
                
                // 短链接
                $("#shortUrl").text(res.shortUrl);
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.longUrl);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('shareChannel.php');
        }
    });
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
            errorPage('data-list','exitLogin.php');
        }
    });
}

// 生成随机token
function creatPageToken(length) {
    var str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) 
        result += str[Math.floor(Math.random() * str.length)];
    return result;
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

// 隐藏Modal（传入节点id决定隐藏哪个Modal）
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}

// 显示Modal（传入节点id决定隐藏哪个Modal）
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
}

// 排查提示1
function showErrorResultForphpfileName(phpfileName){
    $('#app .result').html('<div class="error">服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+phpfileName+'的返回信息进行排查！<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a></div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 3000);
}

// 排查提示2
function errorPage(from,text){
    
    if(from == 'data-list'){
        
        $("#right .data-list").css('display','none');
        $("#right .data-card .loading").html(
            '<img src="../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
        $("#right .data-card .loading").css('display','block');
        
    }else if(from == 'qrcode-list'){

        $("#qunQrcodeListModal table").html(
            '<img src="../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
    }
}

// 暂无数据
function noData(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../static/img/noData.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
}

// 初始化（获取用户列表）
function initialize_getUserList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（创建账号）
function initialize_creatUser(){
    $('#user_name').val('');
    $('#user_pass').val('');
    $('#user_email').val('');
    $('#user_beizhu').val('');
    $('#user_mb_answer').val('');
    $('#user_name_count').text(0);
    $('#user_pass_count').text(0);
    $('#user_name_count_style').css('color','#999');
    $('#user_name').css('border-color','#ced4da');
    $('#user_pass_count_style').css('color','#999');
    $('#user_pass').css('border-color','#ced4da');
    $('#app .result .success').css('display','none');
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
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

// 获取URL参数
function queryURLParams(url) {
    var pattern = /(\w+)=(\w+)/ig;
    var parames = {};
    url.replace(pattern, ($, $1, $2) => {
        parames[$1] = $2;
    });
    return parames;
}
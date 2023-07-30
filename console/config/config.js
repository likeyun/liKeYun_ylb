
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的域名列表
        getDomainNameList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getDomainNameList();
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
                initialize_Login('unlogin');
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


// 获取默认域名
function getDefaultDomainName(){
    
    $.ajax({
        type: "POST",
        url: "./getDefaultDomainName.php",
        success: function(res){
            
            // 将默认域名添加至选项中
            $("#default_rkym").append('<option value="'+res.default_rkym+'">'+res.default_rkym+'</option>');
            $("#default_ldym").append('<option value="'+res.default_ldym+'">'+res.default_ldym+'</option>');
            $("#default_dlym").append('<option value="'+res.default_dlym+'">'+res.default_dlym+'</option>');
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getDefaultDomainName.php');
        }
    });
}

// 加载域名列表
function getDomainNameList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getDomainNameList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getDomainNameList.php?p="+pageNum
    }
    
    // 获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getDomainNameList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>ID</th>' +
                '   <th>类型</th>' +
                '   <th>域名</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.domainList.length; i++) {
                    
                    // 数据判断并处理
                    // 序号
                    var xuhao = i+1;
                    
                    // ID
                    var domain_id = res.domainList[i].domain_id;
                    
                    // 类型
                    if(res.domainList[i].domain_type == 1){
                        
                        // 入口域名
                        var domain_type = '<span>入口域名</span>';
                    }else if(res.domainList[i].domain_type == 2){
                        
                        // 落地域名
                        var domain_type = '<span>落地域名</span>';
                    }else if(res.domainList[i].domain_type == 3){
                        
                        // 短链域名
                        var domain_type = '<span>短链域名</span>';
                    }else if(res.domainList[i].domain_type == 4){
                        
                        // 备用域名
                        var domain_type = '<span>备用域名</span>';
                    }
                    
                    // 域名
                    var domain = res.domainList[i].domain;
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+domain_id+'</td>' +
                        '   <td>'+domain_type+'</td>' +
                        '   <td>'+domain+'</td>' +
                        '   <td style="text-align:right;color:#999;cursor:pointer;" data-toggle="modal" id="'+domain_id+'" data-target="#DelDomainModal" onclick="askDelDomainName(this);">删除</td>' +
                        '</tr>'
                    );
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
                    var $domainNameFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $domainNameFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $domainNameFenye_HTML = $(
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
                $("#right .data-card .fenye").html($domainNameFenye_HTML);
                
                // 设置URL
                if(res.page !== 1){
                    window.history.pushState('', '', '?p='+res.page+'&token='+creatPageToken(32));
                }
                
            }else{
                
                // 如果是未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../login/');
                }
                
                // 205状态码：无管理权限
                if(res.code == 205){
                    
                    // 无管理权限
                    noLimit(res.msg);
                }else{
                    
                    // 无数据
                    noData(res.msg);
                }
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','getDomainNameList.php');
        
        // 隐藏顶部按钮
        $('#right .button-view').html('');
      },
    });
}

// 获取域名检测配置信息
function getDomainNameCheckConfigInfo(){
    
    // 初始化
    initialize_getDomainNameCheckConfigInfo();
    
    $.ajax({
        type: "POST",
        url: "./getDomainNameCheckConfigInfo.php",
        success: function(res){
            
            // 备用域名
            const byym = res.domainNameCheckConfig.domainCheck_byym;
            
            // 通知渠道
            const domainCheck_channel = res.domainNameCheckConfig.domainCheck_channel;
            
            // 状态
            const domainCheck_status = res.domainNameCheckConfig.domainCheck_status;
            
            // 获取状态
            if(domainCheck_status == 1){
                
                // 开启
                $("#domainCheck_status").append('<option value="1">开启</option><option value="2">关闭</option>');
            }else{
                
                // 关闭
                $("#domainCheck_status").append('<option value="2">关闭</option><option value="1">开启</option>');
            }
            
            // 获取通知渠道
            if(domainCheck_channel == '未设置'){
                
                // 选择通知渠道
                $("#domainCheck_channel").append(
                    '<option value="">选择通知渠道</option>'+ 
                    '<option value="企业微信">企业微信</option>'+ 
                    '<option value="邮件">邮件</option>'+ 
                    '<option value="Bark">Bark</option>'+ 
                    '<option value="Server酱">Server酱</option>'+ 
                    '<option value="HTTP">HTTP</option>'
                );
            }else{
                
                // 先将已设置的渠道添加到第一行
                $("#domainCheck_channel").append(
                    '<option value="'+domainCheck_channel+'">'+domainCheck_channel+'</option>' +
                    '<option value="企业微信">企业微信</option>'+ 
                    '<option value="邮件">邮件</option>'+ 
                    '<option value="Bark">Bark</option>'+ 
                    '<option value="Server酱">Server酱</option>'+ 
                    '<option value="HTTP">HTTP</option>'
                );
            }
            
            // 获取备用域名
            if(byym == '未设置'){
                
                // 获取域名列表
                $("#domainCheck_byym").append('<option value="">选择备用域名</option>');
                getByDomainNameList();
                
            }else{
                
                // 将已配置的域名添加至选项中
                $("#domainCheck_byym").append('<option value="'+byym+'">'+byym+'</option>');
                getByDomainNameList();
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getDomainNameCheckConfigInfo.php');
        }
    });
}

// 获取备用域名列表
function getByDomainNameList(){
    
    $.ajax({
        type: "POST",
        url: "./getByDomainNameList.php",
        success: function(res){
            
            // 获取成功
            if(res.code == 200){
               
               for (var i = 0; i < res.domainList.length; i++) {
                
                    // 将获取到的备用域名添加到选项中
                    $("#domainCheck_byym").append(
                        '<option value="'+res.domainList[i].domain+'">'+res.domainList[i].domain+'</option>'
                    );
                }
            }else{
                
                // 暂无备用域名
                $("#domainCheck_byym").append('<option value="">暂无备用域名</option>');
            }
        },
        error: function() {
            
            // 获取失败
            showErrorResultForphpfileName('getDomainNameCheckConfigInfo.php');
        }
    });
}

// 提交域名检测设置
function editDomainNameCheckConfig(){
    
    $.ajax({
        type: "POST",
        url: "./editDomainNameCheckConfig.php",
        data: $('#editDomainNameCheckConfig').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                showSuccessResult(res.msg);
                setTimeout("hideModal('domainNameCheckConfigModal')",800);
            }else{
                
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editDomainNameCheckConfig.php');
        }
    });
}

// 分页
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getDomainNameList(pageNum);
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


// 添加域名
function addDomainName(){
    
    $.ajax({
        type: "POST",
        url: "./addDomainName.php",
        data: $('#addDomainName').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                setTimeout('hideModal("addDomainNameModal")', 500);
                
                // 重新加载域名列表
                setTimeout('getDomainNameList()', 500);
                
                // 成功
                setTimeout('showNotification("'+res.msg+'")',800);
            }else{
                
                // 失败
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('addDomainName.php');
        }
    });
}

// 询问是否要删除
function askDelDomainName(e){
    
    // 获取domain_id
    var domain_id = e.id;
    
    // 将群id添加到button的
    // delDomainName函数用于传参执行删除
    $('#DelDomainModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delDomainName('+domain_id+');">确定删除</button>'
    )
}

// 删除域名
function delDomainName(domain_id){
    
    $.ajax({
        type: "GET",
        url: "./delDomainName.php?domain_id="+domain_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("DelDomainModal");
                
                // 重新加载域名列表
                setTimeout('getDomainNameList()', 500);
                
                // 显示全局信息提示弹出提示
                showNotification(res.msg);
            }else{
                
                // 失败
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delDomainName.php');
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

// 无管理权限
function noLimit(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../static/img/noLimit.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
}

// 初始化（加载域名列表）
function initialize_getDomainNameList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（添加域名）
function initialize_addDomainName(){
    $("#domain").val('');
    $("#domain_type").val('');
    hideResult();
}

// 初始化（获取域名检测配置信息）
function initialize_getDomainNameCheckConfigInfo(){
    $("#domainCheck_status").empty('');
    $("#domainCheck_channel").empty('');
    $("#domainCheck_byym").empty('');
    hideResult();
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

console.log('%c 欢迎使用引流宝','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
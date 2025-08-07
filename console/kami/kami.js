// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码数据列表
        getKamiProjectList(pageNum);
    }else{
        
        // 获取首页
        getKamiProjectList();
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
            errorPage('data-list','getLoginStatus.php');
        }
    });
}

// 登录后的一些初始化
function initialize_Login(loginStatus,adminStatus){
    
    if(loginStatus == 'login'){
        
        if(adminStatus == 2) {
            
            // 非管理员
            noLimit('你的账号没有使用卡密分发的权限');
        }else{
            
            // 显示按钮
            $('#button-view').css('display','block');
        }
    }else{
        
        // 隐藏按钮
        $('#button-view').css('display','none');
        
        // 跳转到登录页面
        jumpUrl('../login/');
    }
}

// 获取项目列表
function getKamiProjectList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getKamiProjectList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getKamiProjectList.php?p=" + pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getKamiProjectList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>项目标题</th>' +
                '   <th>项目类型</th>' +
                '   <th>卡密总数</th>' +
                '   <th>已提取</th>' +
                '   <th>未提取</th>' +
                '   <th>重复提取</th>' +
                '   <th>间隔时间</th>' +
                '   <th>看广告</th>' +
                '   <th>创建时间</th>' +
                '   <th>上/下架</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.projectList.length; i++) {
                    
                    var kami_id = res.projectList[i].kami_id;
                    var kami_title = res.projectList[i].kami_title;
                    var kami_type = res.projectList[i].kami_type;
                    var km_total = res.projectList[i].km_total;
                    var km_isExtracted = res.projectList[i].km_isExtracted; // 已被提取
                    var km_unExtracted = res.projectList[i].km_unExtracted; // 未被提取
                    var kami_repeat_tiqu = res.projectList[i].kami_repeat_tiqu; // 重复提取
                    var kami_create_time = res.projectList[i].kami_create_time;
                    var kami_status = res.projectList[i].kami_status;
                    var kami_adStatus = res.projectList[i].kami_adStatus;
                    
                    // 上下架状态
                    if(kami_status == 1){
                        
                        // 上架
                        var kami_status_html = 
                        '<span class="switch-on" id="'+kami_id+'" onclick="changeKamiStatus(this);">'+
                        '   <span class="press"></span>'+
                        '</span>';
                    }else{
                        
                        // 下架
                        var kami_status_html = 
                        '<span class="switch-off" id="'+kami_id+'" onclick="changeKamiStatus(this);">' +
                        '   <span class="press"></span>'+
                        '</span>';
                    }
                    
                    if(kami_repeat_tiqu == 1) {
                        var kami_repeat_tiqu_html = '<span class="sj-span">允许</span>';
                        var kami_repeat_tiqu_interval = res.projectList[i].kami_repeat_tiqu_interval + '秒';
                    }else {
                        var kami_repeat_tiqu_html = '<span class="xj-span">不允许</span>';
                        var kami_repeat_tiqu_interval = ' - ';
                    }
                    
                    if(kami_adStatus == 1) {
                        var kami_adStatus_text = '<span style="color:rgb(59,94,225);">需要</span>';
                    }else {
                        var kami_adStatus_text = '<span style="color:#666;">无需</span>';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+kami_title+'</td>' +
                        '   <td>'+kami_type+'</td>' +
                        '   <td>'+km_total+'</td>' +
                        '   <td>'+km_isExtracted+'</td>' +
                        '   <td>'+km_unExtracted+'</td>' +
                        '   <td>'+kami_repeat_tiqu_html+'</td>' +
                        '   <td>'+kami_repeat_tiqu_interval+'</td>' +
                        '   <td>'+kami_adStatus_text+'</td>' +
                        '   <td>'+kami_create_time+'</td>' +
                        '   <td>'+kami_status_html+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <a href="kami.html?kami_id='+kami_id+'&p=1&token='+creatPageToken(32)+'" class="dropdown-item">卡密</a>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#editKamiProjectModal" onclick="getKamiProjectInfo('+kami_id+')">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#delKamiProjectModal" onclick="askDelKamiProject('+kami_id+')">删除</span>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                fenyeComponent(res.page,res.allpage,res.nextpage,res.prepage);
            }else{
                
                // 未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../login/');
                }
                
                // 非200状态码
                if(res.user_admin == '2') {
                    
                    // 非管理员
                    noLimit('你的账号没有使用卡密分发的权限');
                }else {
                    
                    noData(res.msg);
                }
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','getKamiProjectList.php');
      },
    });
}

// 分页组件
function fenyeComponent(thisPage,allPage,nextPage,prePage){
    
    // 设置URL路由
    setRouter(thisPage);
    
    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1 且 总页码=1
        // 无需显示分页控件
        $("#right .data-card .fenye").css("display","none");
        
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1 且 总页码>1
        // 代表还有下一页
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">'+ 
        '           <img src="../../static/img/nextPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">'+ 
        '           <img src="../../static/img/lastPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="1" onclick="getFenye(this);" title="第一页">'+ 
        '           <img src="../../static/img/firstPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '   <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">'+ 
        '       <img src="../../static/img/prevPage.png" />'+ 
        '   </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }else{
        
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="1" onclick="getFenye(this);" title="第一页">'+ 
        '           <img src="../../static/img/firstPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">'+ 
        '           <img src="../../static/img/prevPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">'+ 
        '           <img src="../../static/img/nextPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">'+ 
        '           <img src="../../static/img/lastPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }
    
    // 渲染分页组件
    $("#right .data-card .fenye").html($fenyeComponent_HTML);
}

// 获取分页数据
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getKamiProjectList(pageNum);
}

// 创建项目
function createKamiProject(){
    
    $.ajax({
        type: "POST",
        url: "./createKamiProject.php",
        data: $('#createKamiProject').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作成功
                showSuccessResult(res.msg)
                
                // 隐藏modal
                setTimeout('hideModal("createKamiProjectModal")', 500);
                
                // 重新加载列表
                setTimeout('getKamiProjectList();', 500);
            }else{
                
                // 操作失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createKamiProject.php');
        }
    });
}

// 获取项目详情
function getKamiProjectInfo(kami_id){
    
    // 初始化
    $('#editKamiProjectModal select[name="kami_type"]').empty('');
    $('#editKamiProjectModal select[name="kami_status"]').empty('');
    $('#editKamiProjectModal select[name="kami_adStatus"]').empty('');
    $('#editKamiProjectModal select[name="kami_repeat_tiqu"]').empty('');
    
    // 获取
    $.ajax({
        type: "GET",
        url: "./getKamiProjectInfo.php?kami_id=" + kami_id,
        success: function(res){

            if(res.code == 200){
                
                // 项目标题
                $('#editKamiProjectModal input[name="kami_title"]').val(res.kamiInfo.kami_title);
                
                // 是否要看广告
                if(res.kamiInfo.kami_adStatus == 1) {
                    
                    // 需要看广告
                    $('#editKamiProjectModal select[name="kami_adStatus"]').append(
                        '<option value="1">需要看广告</option>' +
                        '<option value="2">无需看广告</option>'
                    );
                }else {
                    
                    // 无需看广告
                    $('#editKamiProjectModal select[name="kami_adStatus"]').append(
                        '<option value="2">无需看广告</option>' +
                        '<option value="1">需要看广告</option>'
                    );
                }
                
                // 已选的项目类型
                $('#editKamiProjectModal select[name="kami_type"]').append(
                    '<option value="'+res.kamiInfo.kami_type+'">' + res.kamiInfo.kami_type + '</option>'
                );
                
                // 可选的项目类型
                $('#editKamiProjectModal select[name="kami_type"]').append(
                    '<option value="卡密">卡密</option>' +
                    '<option value="激活码">激活码</option>' +
                    '<option value="授权码">授权码</option>' +
                    '<option value="密钥">密钥</option>' +
                    '<option value="提取码">提取码</option>' +
                    '<option value="兑换码">兑换码</option>' +
                    '<option value="会员卡">会员卡</option>' +
                    '<option value="券号">券号</option>' +
                    '<option value="序列号">序列号</option>' +
                    '<option value="链接">链接</option>' +
                    '<option value="网址">网址</option>' +
                    '<option value="取件码">取件码</option>' +
                    '<option value="二维码">二维码</option>' +
                    '<option value="订单号">订单号</option>' +
                    '<option value="工单号">工单号</option>' +
                    '<option value="编码">编码</option>' +
                    '<option value="网盘">网盘</option>' +
                    '<option value="验证码">验证码</option>' +
                    '<option value="账号">账号</option>'
                );
                
                // 项目状态
                if(res.kamiInfo.kami_status == 1) {
                    
                    // 上架
                    $('#editKamiProjectModal select[name="kami_status"]').append(
                        '<option value="1">上架</option>' +
                        '<option value="2">下架</option>'
                    );
                }else {
                    
                    // 下架
                    $('#editKamiProjectModal select[name="kami_status"]').append(
                        '<option value="2">下架</option>' +
                        '<option value="1">上架</option>'
                    );
                }
                
                // 重复提取
                if(res.kamiInfo.kami_repeat_tiqu == 1) {
                    
                    // 允许
                    $('#editKamiProjectModal select[name="kami_repeat_tiqu"]').append(
                        '<option value="1">允许</option>' +
                        '<option value="2">不允许</option>'
                    );
                }else {
                    
                    // 不允许
                    $('#editKamiProjectModal select[name="kami_repeat_tiqu"]').append(
                        '<option value="2">不允许</option>' +
                        '<option value="1">允许</option>'
                    );
                }
                
                // 重复提取间隔时间
                $('#editKamiProjectModal input[name="kami_repeat_tiqu_interval"]').val(res.kamiInfo.kami_repeat_tiqu_interval);
                
                // ID
                $('#editKamiProjectModal input[name="kami_id"]').val(kami_id);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getKamiProjectInfo.php');
        }
    });
}

// 提交编辑
function editKamiProject(){
    
    $.ajax({
        type: "POST",
        url: "./editKamiProject.php",
        data: $('#editKamiProject').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("editKamiProjectModal")', 500);
                
                // 重新加载列表
                setTimeout('getKamiProjectList();', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editKamiProject.php');
        }
    });
}

// 询问是否要删除项目
function askDelKamiProject(kami_id){
    
    // 将群id添加到button的delKamiProject函数用于传参执行删除
    $('#delKamiProjectModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delKamiProject('+kami_id+');">确定删除</button>'
    )
}

// 删除卡密项目
function delKamiProject(kami_id){
    
    $.ajax({
        type: "POST",
        url: "./delKamiProject.php?kami_id=" + kami_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delKamiProjectModal");
                
                // 重新加载列表
                setTimeout('getKamiProjectList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delKamiProject.php');
        }
    });
}

// 获取小程序配置
function getXcxConfig(){
    
    // 初始化
    hideResult();
    
    // 获取
    $.ajax({
        type: "GET",
        url: "./getXcxConfig.php",
        success: function(res){

            if(res.code == 200){
                
                // 服务状态
                if(res.xcxConfig.kmConf_status == 1) {
                    
                    // 正常服务
                    $('#xcxConfigModal select[name="kmConf_status"]').html(
                        '<option value="1">正常服务</option>'+
                        '<option value="2">暂停服务</option>'
                    );
                }else {
                    
                    // 暂停服务
                    $('#xcxConfigModal select[name="kmConf_status"]').html(
                        '<option value="2">暂停服务</option>'+
                        '<option value="1">正常服务</option>'
                    );
                }
                
                // 提取页广告开关
                if(res.xcxConfig.kmConf_adShow == 1) {
                    
                    // 开启
                    $('#xcxConfigModal select[name="kmConf_adShow"]').html(
                        '<option value="1">开启</option>'+
                        '<option value="2">关闭</option>'
                    );
                }else {
                    
                    // 关闭
                    $('#xcxConfigModal select[name="kmConf_adShow"]').html(
                        '<option value="2">关闭</option>'+
                        '<option value="1">开启</option>'
                    );
                }
                
                // 提取页广告类型
                if(res.xcxConfig.kmConf_adType == 1) {
                    
                    // 开启
                    $('#xcxConfigModal select[name="kmConf_adType"]').html(
                        '<option value="1">Banner广告</option>'+
                        '<option value="2">视频广告</option>'
                    );
                }else {
                    
                    // 关闭
                    $('#xcxConfigModal select[name="kmConf_adType"]').html(
                        '<option value="2">视频广告</option>'+
                        '<option value="1">Banner广告</option>'
                    );
                }
                
                // 提取按钮文字
                $('#xcxConfigModal input[name="kmConf_btntext"]').val(res.xcxConfig.kmConf_btntext);
                
                // 激励视频广告开关
                if(res.xcxConfig.kmConf_jiliStatus == 1) {
                    
                    // 开启
                    $('#xcxConfigModal select[name="kmConf_jiliStatus"]').html(
                        '<option value="1">开启</option>'+
                        '<option value="2">关闭</option>'
                    );
                }else {
                    
                    // 关闭
                    $('#xcxConfigModal select[name="kmConf_jiliStatus"]').html(
                        '<option value="2">关闭</option>'+
                        '<option value="1">开启</option>'
                    );
                }
                
                // 客服二维码
                $('#xcxConfigModal input[name="kmConf_kfQrcode"]').val(res.xcxConfig.kmConf_kfQrcode);
                
                // 小程序AppId
                $('#xcxConfigModal input[name="kmConf_appid"]').val(res.xcxConfig.kmConf_appid);
                
                // 小程序AppSecret	
                $('#xcxConfigModal input[name="kmConf_appsecret"]').val(res.xcxConfig.kmConf_appsecret);
                
                // 提取页顶部标题	
                $('#xcxConfigModal input[name="kmConf_xcx_title"]').val(res.xcxConfig.kmConf_xcx_title);
                
                // 公告内容	
                $('#xcxConfigModal textarea[name="kmConf_notification_text"]').val(res.xcxConfig.kmConf_notification_text);
                
                if(res.xcxConfig.kmConf_kfQrcode) {
                    $('#xcxConfigModal .uploadText').text('重新上传');
                }
                
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getXcxConfig.php');
        }
    });
}

// 提交小程序配置
function xcxConfig(){
    
    $.ajax({
        type: "POST",
        url: "./xcxConfig.php",
        data: $('#xcxConfig').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("xcxConfigModal")', 500);

            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('xcxConfig.php');
        }
    });
}

// 上下架状态切换
function changeKamiStatus(e){
    
    $.ajax({
        type: "POST",
        url: "./changeKamiStatus.php?kami_id="+e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 获取列表
                getKamiProjectList();
                
                // 显示切换结果
                showNotification(res.msg);
                
            }else{
                
                // 非200状态码操作结果
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('changeKamiStatus.php');
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

// 无权限
function noLimit(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../static/img/noLimit.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
    
    // 不渲染以下DOM
    $('#button-view').html('');
    $("#right .data-list").html('');
}

// 生成随机token
function creatPageToken(length) {
    var str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) 
        result += str[Math.floor(Math.random() * str.length)];
    return result;
}

// 设置URL路由
function setRouter(pageNum){
    
    // 第一页不设置
    if(pageNum !== 1){
        
        // 根据页码+token设置路由
        window.history.pushState('', '', '?p='+pageNum+'&token='+creatPageToken(32));
    }
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

// 初始化（getKamiProjectList获取列表）
function initialize_getKamiProjectList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
    
    // 清空创建项目表单
    $('#createKamiProjectModal input[name="kami_title"]').val('');
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

// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    // 获取卡密项目id
    var kamiproject_id = queryURLParams(window.location.href).kami_id;
    
    // 没传入kami_id
    if(!kamiproject_id) {
        
        noData('无法获取到卡密列表')
        return;
    }
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码数据列表
        getKmList(pageNum,kamiproject_id);
    }else{
        
        // 获取首页
        getKmList(1,kamiproject_id);
    }
    
    // 将kami_id添加到导入卡密的表单中
    $('#addKamiModal input[name="kami_id"]').val(kamiproject_id);
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

// 获取卡密列表
function getKmList(pageNum,kami_id) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getKmList.php?p=1&kami_id=" + kami_id;
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getKmList.php?p=" + pageNum + '&kami_id=' + kami_id;
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getKmList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>ID</th>' +
                '   <th>卡密</th>' +
                '   <th>有效期</th>' +
                '   <th>到期时间</th>' +
                '   <th>导入时间</th>' +
                '   <th>备注</th>' +
                '   <th>状态</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.kmList.length; i++) {
                    
                    var xuhao = i+1;
                    var km = res.kmList[i].km;
                    var km_id = res.kmList[i].km_id;
                    var km_expiryDate = res.kmList[i].km_expiryDate;
                    var km_expireDate = res.kmList[i].km_expireDate;
                    var km_addtime = res.kmList[i].km_addtime;
                    var km_beizhu = res.kmList[i].km_beizhu;
                    var km_status = res.kmList[i].km_status;
                    
                    if(km_status == 1) {
                        var km_status_html = '<span class="sj-span">未被提取</span>';
                    }else {
                        var km_status_html = '<span class="xj-span">已被提取</span>';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+km_id+'</td>' +
                        '   <td>'+km+'</td>' +
                        '   <td>'+km_expiryDate+'</td>' +
                        '   <td>'+km_expireDate+'</td>' +
                        '   <td>'+km_addtime+'</td>' +
                        '   <td>'+km_beizhu+'</td>' +
                        '   <td>'+km_status_html+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#editKmModal" onclick="getKmInfo('+km_id+')">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#delKmModal" onclick="askDelKm('+km_id+')">删除</span>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                fenyeComponent(res.page,res.allpage,res.nextpage,res.prepage,kami_id);
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
        errorPage('data-list','getKmList.php');
      },
    });
}

// 分页组件
function fenyeComponent(thisPage,allPage,nextPage,prePage,kami_id){
    
    // 设置URL路由
    setRouter(thisPage, kami_id);
    
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
        '       <button id="'+nextPage+'" onclick="getFenye(this,'+kami_id+');" title="下一页">'+ 
        '           <img src="../../static/img/nextPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+allPage+'" onclick="getFenye(this,'+kami_id+');" title="最后一页">'+ 
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
        '       <button id="1" onclick="getFenye(this,'+kami_id+');" title="第一页">'+ 
        '           <img src="../../static/img/firstPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '   <button id="'+prePage+'" onclick="getFenye(this,'+kami_id+');" title="上一页">'+ 
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
        '       <button id="1" onclick="getFenye(this,'+kami_id+');" title="第一页">'+ 
        '           <img src="../../static/img/firstPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">'+ 
        '           <img src="../../static/img/prevPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+nextPage+'" onclick="getFenye(this,'+kami_id+');" title="下一页">'+ 
        '           <img src="../../static/img/nextPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+allPage+'" onclick="getFenye(this,'+kami_id+');" title="最后一页">'+ 
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
function getFenye(e,kami_id){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getKmList(pageNum, kami_id);
}

// 导入卡密
function addKami(){
    
    $.ajax({
        type: "POST",
        url: "./addKami.php",
        data: $('#addKami').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作成功
                showSuccessResult(res.msg + '！有' + res.addSuccess + '条卡密导入成功；有' + res.addRepeat + '条卡密导入重复；有' + res.addError + '条卡密导入失败。');
                
                // 隐藏Modal
                setTimeout('hideModal("addKamiModal")', 2000);
                
                // 获取到kami_id
                const kami_id = $('#addKamiModal input[name="kami_id"]').val();
                
                // 重新加载列表
                setTimeout('getKmList(1,'+kami_id+');', 2000);
                
                // 初始化导入表单
                setTimeout('init_importForm();', 2200);
            }else{
                
                // 操作失败
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('addKami.php');
        },
        beforeSend: function(){
            
            // 正在导入
            showErrorResult('正在导入...');
        }
    });
}

// 初始化导入表单
function init_importForm() {
    $('#addKamiModal .kami_TXTUpload .uploadText').text('上传文件');
    $('#addKamiModal input[name="kmFile"]').val('');
    $('#selectCSV').val('');
}

// 询问是否要清空卡密
function cleanKmModal() {
    
    // 获取kami_id
    const kami_id = $('#addKamiModal input[name="kami_id"]').val();
    
    // 将给button添加清空卡密的函数
    $('#cleanKmModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="cleanKm('+kami_id+');">确定清空</button>'
    );
}

// 清空卡密
function cleanKm(kami_id) {
    
    $.ajax({
        type: "POST",
        url: "./cleanKm.php?kami_id=" + kami_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作成功
                // 隐藏Modal
                hideModal("cleanKmModal");
                
                // 显示操作结果
                showNotification(res.msg);
                
                // 重新加载列表
                setTimeout('getKmList('+kami_id+');', 1000);
                
                // 分页组件DOM删掉
                setTimeout("$('#right .data-card .fenye').html('')", 1200);
            }else{
                
                // 操作失败
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('addKami.php');
        }
    });
}

// 获取卡密详情
function getKmInfo(km_id){
    
    // 初始化
    $('#editKmModal select[name="km_status"]').empty('');
    
    // 获取
    $.ajax({
        type: "POST",
        url: "./getKmInfo.php?km_id=" + km_id,
        success: function(res){

            if(res.code == 200){
                
                // 卡密内容
                $('#editKmModal input[name="km"]').val(res.kmInfo.km);
                
                // 有效期
                $('#editKmModal input[name="km_expiryDate"]').val(res.kmInfo.km_expiryDate);
                
                // 到期时间
                $('#editKmModal input[name="km_expireDate"]').val(res.kmInfo.km_expireDate);
                
                // 备注
                $('#editKmModal input[name="km_beizhu"]').val(res.kmInfo.km_beizhu);
                
                // 卡密id
                $('#editKmModal input[name="km_id"]').val(km_id);
                
                // 卡密状态
                if(res.kmInfo.km_status == 1) {
                    
                    // 未使用
                    $('#editKmModal select[name="km_status"]').append(
                        '<option value="1">未使用</option>' +
                        '<option value="2">已使用</option>'
                    );
                }else {
                    
                    // 已使用
                    $('#editKmModal select[name="km_status"]').append(
                        '<option value="2">已使用</option>' +
                        '<option value="1">未使用</option>'
                    );
                }
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getKmInfo.php');
        }
    });
}

// 提交编辑
function editKm(){
    
    $.ajax({
        type: "POST",
        url: "./editKm.php",
        data: $('#editKm').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("editKmModal")', 500);
                
                // 获取kami_id
                const kami_id = $('#addKamiModal input[name="kami_id"]').val();
                
                // 重新加载列表
                setTimeout('getKmList(1,'+kami_id+');', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editKm.php');
        }
    });
}

// 询问是否要删除卡密
function askDelKm(km_id){
    $('#delKmModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delKm('+km_id+');">确定删除</button>'
    )
}

// 确定删除卡密
function delKm(km_id){
    
    $.ajax({
        type: "POST",
        url: "./delKm.php?km_id=" + km_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delKmModal");
                
                // 获取kami_id
                const kami_id = $('#addKamiModal input[name="kami_id"]').val();
                
                // 显示删除结果
                showNotification(res.msg);
                
                // 重新加载列表
                setTimeout('getKmList(1, '+kami_id+')', 500);
            }else{
                
                // 失败
                showNotification(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('delKm.php');
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
                
                // 提取间隔
                $('#xcxConfigModal input[name="kmConf_Interval"]').val(res.xcxConfig.kmConf_Interval);
                
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
                
                // Banner广告ID
                $('#xcxConfigModal input[name="kmConf_bannerID"]').val(res.xcxConfig.kmConf_bannerID);
                
                // 视频广告ID
                $('#xcxConfigModal input[name="kmConf_videoID"]').val(res.xcxConfig.kmConf_videoID);
                
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
                
                // 激励视频广告ID
                $('#xcxConfigModal input[name="kmConf_jiliID"]').val(res.xcxConfig.kmConf_jiliID);
                
                // 客服二维码
                $('#xcxConfigModal input[name="kmConf_kfQrcode"]').val(res.xcxConfig.kmConf_kfQrcode);
                
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

// 设置URL路由
function setRouter(pageNum,kami_id){
    
    // 第一页不设置
    if(pageNum !== 1){
        
        // 根据页码+token设置路由
        window.history.pushState('', '', '?kami_id='+kami_id+'&p='+pageNum+'&token='+creatPageToken(32));
    }
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

// 初始化（getKmList获取列表）
function initialize_getKmList(){
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
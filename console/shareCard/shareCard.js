window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    // 根据页码加载
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的列表
        getshareCardList(pageNum);
    }else{
        
        // 获取首页
        getshareCardList();
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
        
        // 显示创建按钮
        $('#button-view').css('display','block');
        
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
    }
}

// 获取分享卡片列表
function getshareCardList(pageNum) {
    
    // 判断是否有pageNum参数
    if(!pageNum){
        
        // 如果没有
        // 默认第1页
        reqUrl = "./getshareCardList.php";
    }else{
        
        // 如果有
        // 请求pageNum的那一页
        reqUrl = "./getshareCardList.php?p="+pageNum
        
        // 设置URL路由
        setRouter(pageNum);
    }
    
    // 获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getshareCardList();
            
            if(res.adminCode == 2){
                
                // 没有管理权限
                $('#button-view .gzhConfig').html('');
            }
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>ID</th>' +
                '   <th>标题</th>' +
                '   <th>创建时间</th>' +
                '   <th>访问次数</th>' +
                '   <th>模式</th>' +
                '   <th>状态</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 200状态码
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.shareCardList.length; i++) {
                    
                    // 序号
                    var xuhao = i+1;
                    
                    // ID
                    var shareCard_id = res.shareCardList[i].shareCard_id;
                    
                    // 标题
                    var shareCard_title = res.shareCardList[i].shareCard_title;
                    
                    // 创建时间
                    var shareCard_create_time = res.shareCardList[i].shareCard_create_time;
                    
                    // 访问次数
                    var shareCard_pv = res.shareCardList[i].shareCard_pv;
                    
                    // 状态
                    if(res.shareCardList[i].shareCard_status == '1'){
                        
                        // 正常
                        var shareCard_status = 
                        '<span class="switch-on" id="'+shareCard_id+'" onclick="changeshareCardStatus(this);">'+
                        '<span class="press"></span>'+
                        '</span>';
                    }else{
                        
                        // 关闭
                        var shareCard_status = 
                        '<span class="switch-off" id="'+shareCard_id+'" onclick="changeshareCardStatus(this);">'+
                        '<span class="press"></span>'+
                        '</span>';
                    }
                    
                    // 模式
                    if(res.shareCardList[i].shareCard_model == '1'){
                        
                        // 测试号
                        var shareCard_model = '测试号';
                    }else if(res.shareCardList[i].shareCard_model == '2'){
                        
                        // 认证号
                        var shareCard_model = '认证号';
                    }else if(res.shareCardList[i].shareCard_model == '3'){
                        
                        // Safari分享
                        var shareCard_model = 'Safari分享';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+shareCard_id+'</td>' +
                        '   <td>'+shareCard_title+'</td>' +
                        '   <td>'+shareCard_create_time+'</td>' +
                        '   <td>'+shareCard_pv+'</td>' +
                        '   <td>'+shareCard_model+'</td>' +
                        '   <td>'+shareCard_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#ShareCardModal" onclick="shareCard('+shareCard_id+')">分享</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#editShareCardModal" onclick="getshareCardInfo('+shareCard_id+')">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#DelshareCardModal" onclick="askDelshareCard('+shareCard_id+')">删除</span>' +
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
                noData(res.msg);
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','getshareCardList.php');
      },
    });
}

// 分页组件
function fenyeComponent(thisPage,allPage,nextPage,prePage){
    
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
    getshareCardList(pageNum);
}

// 创建分享卡片
function createShareCard(){
    
    $.ajax({
        type: "POST",
        url: "./createShareCard.php",
        data: $('#createShareCard').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏modal
                setTimeout('hideModal("createShareCardModal")', 500);
                
                // 重新加载中间页列表
                setTimeout('getshareCardList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createShareCard.php');
        }
    });
}

// 编辑分享卡片
function editShareCard(){
    
    $.ajax({
        type: "POST",
        url: "./editShareCard.php",
        data: $('#editShareCard').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏editShareCardModal
                setTimeout('hideModal("editShareCardModal")', 500);
                
                // 重新加载中间页列表
                setTimeout('getshareCardList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editShareCard.php');
        }
    });
}

// 询问是否要删除分享卡片
function askDelshareCard(shareCardid){
    
    // 将群id添加到button的
    // delshareCard函数用于传参执行删除
    $('#DelshareCardModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delshareCard('+shareCardid+');">确定删除</button>'
    )
}

// 删除分享卡片
function delshareCard(shareCardid){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delshareCard.php?shareCardid="+shareCardid,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("DelshareCardModal");
                
                // 重新加载分享卡片列表
                setTimeout('getshareCardList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delshareCard.php');
        }
    });
}

// 获取分享卡片详情
function getshareCardInfo(shareCard_id){
    
    $.ajax({
        type: "GET",
        url: "./getshareCardInfo.php?shareCard_id="+shareCard_id,
        success: function(res){

            if(res.code == 200){
                
                // 分享标题
                $('#editShareCardModal input[name="shareCard_title"]').val(res.shareCardInfo.shareCard_title);
                
                // 分享摘要
                $('#editShareCardModal input[name="shareCard_desc"]').val(res.shareCardInfo.shareCard_desc);
                
                // 分享缩略图
                $("#editShareCardModal .button_text").text('上传图片');
                $("#editShareCardModal .button_sucaiku").text('从素材库选择');
                $('#editShareCardModal input[name="shareCard_img"]').val(res.shareCardInfo.shareCard_img);
                
                // 获取域名列表
                getDomainNameList('edit');
                
                // 获取当前设置的域名
                $('#editShareCardModal select[name="shareCard_ldym"]').append(
                    '<option value="'+res.shareCardInfo.shareCard_ldym+'">'+res.shareCardInfo.shareCard_ldym+'</option>'
                );
                
                // 获取当前设置的模式
                if(res.shareCardInfo.shareCard_model == '1') {
                    $('#editShareCardModal select[name="shareCard_model"]').append(
                        '<option value="'+res.shareCardInfo.shareCard_model+'">测试号</option>' +
                        '<option value="2">认证号</option>' +
                        '<option value="3">Safari分享</option>'
                    );
                }else if(res.shareCardInfo.shareCard_model == '2') {
                    $('#editShareCardModal select[name="shareCard_model"]').append(
                        '<option value="'+res.shareCardInfo.shareCard_model+'">认证号</option>' +
                        '<option value="1">测试号</option>' +
                        '<option value="3">Safari分享</option>'
                    );
                }else if(res.shareCardInfo.shareCard_model == '3') {
                    $('#editShareCardModal select[name="shareCard_model"]').append(
                        '<option value="'+res.shareCardInfo.shareCard_model+'">Safari分享</option>' +
                        '<option value="1">测试号</option>' +
                        '<option value="2">认证号</option>'
                    );
                }

                // 目标链接
                $('#editShareCardModal input[name="shareCard_url"]').val(res.shareCardInfo.shareCard_url);
                
                // shareCard_id
                $('#editShareCardModal input[name="shareCard_id"]').val(res.shareCardInfo.shareCard_id);
                            
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getshareCardInfo.php');
        }
    });
}

// 获取分享卡片配置
function getshareCardConfig(){
    
    // 初始化
    hideResult();
    
    $.ajax({
        type: "GET",
        url: "./getshareCardConfig.php",
        success: function(res){

            if(res.code == 200){
                
                // （1）appid
                $('#appid').val(res.shareCardConfig.appid);
                
                // （2）appsecret
                $('#appsecret').val(res.shareCardConfig.appsecret);
                            
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getshareCardConfig.php');
        }
    });
}

// 提交配置
function configshareCard(){
    
    $.ajax({
        type: "POST",
        url: "./configshareCard.php",
        data: $('#configshareCard').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("configshareCardModal")', 500);

            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('configshareCard.php');
        }
    });
}

// 使用appendOptionsToSelect函数来为每个select元素处理选项的添加
function appendOptionsToSelect(selectElement, dataList) {
    
    if (dataList.length > 0) {
        
        // 有域名
        for (var i = 0; i < dataList.length; i++) {
            
            // 添加至指定的节点
            selectElement.append(
                '<option value="' + dataList[i].domain + '">' + dataList[i].domain + '</option>'
            );
        }
    } else {
        
        // 暂无域名
        selectElement.append('<option value="">暂无域名</option>');
    }
}

// 获取域名列表
function getDomainNameList(module){
    
    // 初始化
    initialize_getDomainNameList(module);

    // 获取
    $.ajax({
        type: "GET",
        url: "../public/getDomainNameList.php",
        success: function (res) {
            
            // 成功
            if (res.code == 200) {
                
                // 将落地域名添加至选项中
                appendOptionsToSelect($("select[name='shareCard_ldym']"), res.ldymList);
            } else {
                
                // 操作失败
                showErrorResult(res.msg);
            }
        },
        error: function () {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！');
        }
    });
}

// 分享卡片
function shareCard(shareCard_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    // 分享
    $.ajax({
        type: "GET",
        url: "./shareCard.php?shareCard_id="+shareCard_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 长链接
                $("#longUrl").text(res.longUrl);
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.longUrl);
                $('#scanTips').text(res.scanTips);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('shareCard.php');
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


// 切换switch
// changeshareCardStatus
function changeshareCardStatus(e){

    // 修改
    $.ajax({
        type: "POST",
        url: "./changeshareCardStatus.php?shareCard_id="+e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 刷新
                getshareCardList();
                showNotification(res.msg);
            }else{
                
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('changeshareCardStatus.php发生错误!');
        }
    });
}

// 上传
document.addEventListener('DOMContentLoaded', function() {
    
    // 选择文件（创建）
    $('#createShareCardModal input[name="file"]').change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("createShareCard"));
            
            // 上传缩略图
            uploadDescImg(imageData,'createShareCardModal');
        }
        
    });
    
    // 选择文件（编辑）
    $('#editShareCardModal input[name="file"]').change(function(e) {
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        
        if (fileSelect.length > 0) {
 
            // 获取表单选中的数据
            var imageData = new FormData(document.getElementById("editShareCard"));
            
            // 上传缩略图
            uploadDescImg(imageData,'editShareCardModal');
        }
        
    });
    
    // 上传缩略图
    function uploadDescImg(imageData,fromPannel){
        
        $.ajax({
            url: "../upload.php",
            type: "POST",
            data: imageData,
            cache: false,
            processData: false,
            contentType: false,
            success: function(res) {
                
                if(res.code == 200){
                    
                    // 上传成功
                    if(fromPannel == 'createShareCardModal'){
                        
                        // 将图片地址添加到创建Modal的输入框中
                        $('#createShareCardModal input[name="shareCard_img"]').val(res.url);
                    
                        // 修改上传按钮的文字
                        $('#createShareCardModal .button_local .button_text').text('重新上传');
                    }else{
                        
                        // 将图片地址添加到创建Modal的输入框中
                        $('#editShareCardModal input[name="shareCard_img"]').val(res.url);
                    
                        // 修改上传按钮的文字
                        $('#editShareCardModal .button_local .button_text').text('重新上传');
                    }
                    
                    // 显示上传信息提示
                    showSuccessResult(res.msg);
                    
                }else{
                    
                    // 上传失败
                    showErrorResult(res.msg);
                }
            },
            error: function() {
                
                // 上传失败
                showErrorResultForphpfileName('upload.php');
            },
            beforeSend: function() {
                
                // 上传过程中
                showErrorResult('上传中...');
            }
        });
    }
    
    // 上传至素材库
    $("#uploadSuCaiTosuCaiKu").change(function(e){
        
        // 获取选择的文件
        var fileSelect = e.target.files;
        if(fileSelect.length>0){
            
            // file表单数据
            var imageData = new FormData(document.getElementById("uploadSuCaiTosuCaiKuForm"));
            
            // 获取fromPannel
            var fromPannel = $('#suCaiKu input[name="uploadSuCai_fromPannel"]').val();
            
            $.ajax({
                url:"../public/uploadToSuCaiKu.php",
                type:"POST",
                data:imageData,
                cache: false,
                processData: false,
                contentType: false,
                success: function(res) {
                    
                    if(res.code == 200){
                        
                        // 上传成功
                        // 刷新素材库
                        getSuCai('1',fromPannel);
                        
                        // 上传成功
                        showSuccessResult(res.msg)
                    }else{
                        
                        // 上传失败
                        showErrorResult(res.msg)
                    }
                    
                    // 清空file控件的选择
                    $('#uploadSuCaiTosuCaiKu').val('');
                },
                error: function() {
                    
                    // 上传失败
                    showErrorResultForphpfileName('uploadToSuCaiKu.php');
                },
                beforeSend: function() {
                    
                    showErrorResult('上传中...');
                }
            })
        }
    })
})
        
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

// 生成随机token
function creatPageToken(length) {
    var str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) 
        result += str[Math.floor(Math.random() * str.length)];
    return result;
}

// 隐藏Modal
// 传入Modal_id决定隐藏哪个Modal
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}

// 显示Modal
// 传入Modal_id决定隐藏哪个Modal
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

// 初始化
// getshareCardList获取分享卡片列表
function initialize_getshareCardList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化
// 获取域名列表
function initialize_getDomainNameList(module){
    
    // 默认值
    $('#createShareCardModal input[name="shareCard_title"]').val('');
    $('#createShareCardModal input[name="shareCard_desc"]').val('');
    $('#createShareCardModal input[name="shareCard_img"]').val('');
    $('#createShareCardModal input[name="shareCard_url"]').val('');
    $("#createShareCardModal .button_text").text('上传图片');
    $("#createShareCardModal .button_sucaiku").text('从素材库选择');
    $('select[name="shareCard_ldym"]').empty();
    $('#editShareCardModal select[name="shareCard_model"]').empty();
    $('input[name="shareCard_title"]').attr('autocomplete','off');
    $('input[name="shareCard_desc"]').attr('autocomplete','off');
    $('input[name="shareCard_img"]').attr('autocomplete','off');
    $('input[name="shareCard_url"]').attr('autocomplete','off');
    hideResult();
}

// 获取素材
function getSuCai(pageNum,fromPannel){
    
    // 初始化
    $('#suCaiKu .modal-body .sucai-view').empty('');
    
    // 关闭创建分享卡
    hideModal('createShareCardModal');
    
    // 关闭编辑分享卡
    hideModal('editShareCardModal');
    
    // 打开素材库界面
    showModal('suCaiKu');
    
    // 将fromPannel的值设置到隐藏的表单中
    $('#suCaiKu input[name="uploadSuCai_fromPannel"]').val(fromPannel);
    
    // 判断是否有pageNum参数传过来
    if(pageNum == undefined){
        
        // 没有参数就设置默认值
        var pageNum = 1;
    }
    
    // 获取从哪个面板点击打开的
    if(fromPannel == 'createShareCardModal'){
        
        // 上一个面板是 createShareCardModal 
        // 渲染出来的关闭按钮是需要返回 createShareCardModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'createShareCardModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'editShareCardModal'){
        
        // 上一个面板是 editShareCardModal
        // 渲染出来的关闭按钮是需要返回 editShareCardModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'editShareCardModal\')">&times;</button>'
        );
    }
    
    // 获取素材列表
    $.ajax({
        type: "POST",
        url: "../public/getSuCaiList.php?p="+pageNum,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.suCaiList.length; i++) {
                    
                    // 素材ID
                    var sucai_id = res.suCaiList[i].sucai_id;
                    
                    // 素材文件名
                    var sucai_filename = res.suCaiList[i].sucai_filename;
                    
                    // 素材备注
                    var sucai_beizhu = res.suCaiList[i].sucai_beizhu;
                    
                    // 根据fromPannel决定点击事件
                    if(fromPannel == 'createShareCardModal'){
                        
                        // 新增
                        var clickFunction = 'selectSucaiForSuoLuetu('+sucai_id+')';
                        
                    }else if(fromPannel == 'editShareCardModal'){
                        
                        // 更新
                        var clickFunction = 'selectSucaiUpdateSuoLuetu('+sucai_id+')';
                    }
                    
                    var $sucaiList_HTML = $(
                    '<div class="sucai_msg" title="'+sucai_beizhu+'" onclick="'+clickFunction+'">' +
                    '   <div class="sucai_cover">' +
                    '       <img src="../upload/'+sucai_filename+'" />' +
                    '   </div>' +
                    '   <div class="sucai_name">'+sucai_filename+'</div>' +
                    '</div>'
                    );
                    
                    // 渲染HTML
                    $('#suCaiKu .modal-body .sucai-view').append($sucaiList_HTML);
                }
            }else{
                
                // 获取失败
                getSuCaiFail(res.msg);
            }
            
            // 分页控件
            if(res.totalNum > 12){
                
                // 渲染分页控件
                suCaifenyeControl(pageNum,fromPannel,res.nextpage,res.prepage,res.allpage);
                
            }else{
                
                // 隐藏分页控件
                $('#suCaiKu .fenye').css('display','none');
            }
        },
        error: function() {
            
            // 服务器发生错误
            getSuCaiFail('服务器发生错误，请检查getSuCaiList.php服务是否正常！');
        }
    });
}

// 获取素材失败
function getSuCaiFail(text){
    
    $('#suCaiKu .modal-body .sucai-view').html(
        '<div class="loading">'+
        '   <img src="../../static/img/noRes.png" class="noRes"/>' +
        '   <br/><p>'+text+'</p>'+
        '</div>'
    );
}

// 选择当前点击的素材
// 作为创建分享卡的缩略图
function selectSucaiForSuoLuetu(sucai_id){
    
    $.ajax({
        type: "POST",
        url: "./selectSucaiForSuoLuetu.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功选择素材
                // 将图片地址添加到创建Modal的输入框中
                $('#createShareCardModal input[name="shareCard_img"]').val(res.suoLuetuUrl);
                
                // 修改打开素材库的按钮文字
                $('#createShareCardModal .button_sucaiku').text('重新选择');
                
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                // 显示操作反馈
                showSuccessResultTimes('已选择',1200);
                
                // 打开创建面板
                setTimeout("showModal('createShareCardModal')",1300);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSucaiForSuoLuetu.php');
        }
    });
}

// 选择当前点击的素材
// 用于更新缩略图
function selectSucaiUpdateSuoLuetu(sucai_id){
    
    $.ajax({
        type: "POST",
        url: "./selectSucaiForSuoLuetu.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功选择素材
                // 将图片地址添加到创建Modal的输入框中
                $('#editShareCardModal input[name="shareCard_img"]').val(res.suoLuetuUrl);
                
                // 修改打开素材库的按钮文字
                $('#editShareCardModal .button_sucaiku').text('重新选择');
                
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                // 显示操作反馈
                showSuccessResultTimes('已选择',1200);
                
                // 打开创建面板
                setTimeout("showModal('editShareCardModal')",1300);
                
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSucaiUpdateQunQrcode.php');
        }
    });
}

// 素材库分页控件
function suCaifenyeControl(thisPage,fromPannel,nextPage,prePage,allPage){

    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1 且 总页码=1
        // 无需显示分页控件
        $('#suCaiKu .fenye').css('display','none');
        
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1 且 总页码>1
        // 代表还有下一页
        // 需要显示下一页、最后一页控件
        
        // 控件HTML结构
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="'+nextPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
        '           <img src="../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示控件
        $('#suCaiKu .fenye').css('display','block');
        
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        // 需要显示第一页、上一页控件
        
        // 控件HTML结构
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示控件
        $('#suCaiKu .fenye').css('display','block');
        
    }else{
        
        // 其他情况
        // 需要显示所有控件
        
        // 控件HTML结构
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'_'+fromPannel+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
        '           <img src="../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示控件
        $('#suCaiKu .fenye').css('display','block');
    }
    
    // 渲染分页控件
    $('#suCaiKu .fenye').html($suCaiFenye);
}

// 获取素材库分页数据
function getSuCaiFenyeData(e){
    
    var FenyeData = e.id;
    var FenyeData_parts = FenyeData.split("_");
    var pageNum = FenyeData_parts[0]; // 页码
    var fromPannel = FenyeData_parts[1]; // 来源
    
    // 获取该页列表
    getSuCai(pageNum,fromPannel);
}

// 为了便于继续操作二维码列表
// 素材库的界面关闭后
// 点击右上角X会继续打开二维码列表
function hideSuCaiPannel(fromPannel){
    
    // 先隐藏 suCaiKu 面板
    hideModal('suCaiKu');
    
    // 根据fromPannel决定打开哪个 Modal
    if(fromPannel == 'createShareCardModal'){
        
        // 显示 createShareCardModal
        showModal('createShareCardModal')
    }else if(fromPannel == 'editShareCardModal'){
        
        // 显示 editShareCardModal
        showModal('editShareCardModal')
    }
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 打开操作反馈
// 操作成功
function showSuccessResult(content){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', 2500); // 2.5秒后自动关闭
}

// 打开操作反馈
// 操作失败
function showErrorResult(content){
    $('#app .result').html('<div class="error">'+content+'</div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 2500); // 2.5秒后自动关闭
}

// 打开操作反馈（操作成功）
function showSuccessResultTimes(content,times){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', times);
}

// 关闭操作反馈
function hideResult(){
    $("#app .result .success").css("display","none");
    $("#app .result .error").css("display","none");
    $("#app .result .success").text('');
    $("#app .result .error").text('');
}

// 设置URL路由
function setRouter(pageNum){
    
    // 第一页不设置
    if(pageNum !== 1){
        
        // 根据页码+token设置路由
        window.history.pushState('', '', '?p='+pageNum+'&token='+creatPageToken(32));
    }
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
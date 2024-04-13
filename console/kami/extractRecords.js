
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码数据列表
        getExtractRecords(pageNum);
    }else{
        
        // 获取首页
        getExtractRecords();
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

// 获取提取记录
function getExtractRecords(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getExtractRecords.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getExtractRecords.php?p=" + pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getExtractRecords();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>卡密项目标题</th>' +
                '   <th>openid</th>' +
                '   <th>卡密</th>' +
                '   <th>卡密有效期</th>' +
                '   <th>提取时间</th>' +
                '   <th>设备品牌</th>' +
                '   <th>设备型号</th>' +
                '   <th>操作系统</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.extractRecords.length; i++) {
                    
                    var xuhao = i+1;
                    var extracted_id = res.extractRecords[i].id;
                    var kami_title = res.extractRecords[i].kami_title;
                    var kami_id = res.extractRecords[i].kami_id;
                    var openid = res.extractRecords[i].openid;
                    var km = res.extractRecords[i].km;
                    var km_expiryDate = res.extractRecords[i].km_expiryDate;
                    var tiqu_time = res.extractRecords[i].tiqu_time;
                    var brand = res.extractRecords[i].brand;
                    var model = res.extractRecords[i].model;
                    var system = res.extractRecords[i].system;
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+kami_title+'</td>' +
                        '   <td>'+openid+'</td>' +
                        '   <td>'+km+'</td>' +
                        '   <td>'+km_expiryDate+'</td>' +
                        '   <td>'+tiqu_time+'</td>' +
                        '   <td>'+brand+'</td>' +
                        '   <td>'+model+'</td>' +
                        '   <td>'+system+'</td>' +
                        '   <td>' +
                        '       <a href="javascript:;" data-toggle="modal" data-target="#delExtractRecordsModal" onclick="askDelExtractRecords('+extracted_id+')">删除</a>' +
                        '       <a href="javascript:;" data-openid="'+openid+'" data-kamiid="'+kami_id+'" data-toggle="modal" data-target="#banOpenidModal" onclick="askBanOpenid(this)">封禁</a>' +
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
    getExtractRecords(pageNum);
}

// 询问是否要删除这条提取记录
function askDelExtractRecords(extracted_id){
    
    // 将群id添加到button的delExtractRecords函数用于传参执行删除
    $('#delExtractRecordsModal .modal-footer').html(
        '<button type="button" class="default-btn center-btn" onclick="delExtractRecords('+extracted_id+');">确定删除</button>'
    )
}

// 删除这条提取记录
function delExtractRecords(extracted_id){
    
    $.ajax({
        type: "POST",
        url: "./delExtractRecords.php?extracted_id=" + extracted_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delExtractRecordsModal");
                
                // 重新加载列表
                setTimeout('getExtractRecords()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delExtractRecords.php');
        }
    });
}

// 询问是否要封禁这个openid提取当前卡密项目
function askBanOpenid(element){
    
    const openid = element.getAttribute('data-openid');
    const kami_id = element.getAttribute('data-kamiid');
    
    // 将群id添加到button的banOpenid函数用于传参执行删除
    $('#banOpenidModal .modal-footer').html(
        '<button type="button" data-openid="'+openid+'" data-kamiid="'+kami_id+'" class="default-btn center-btn" onclick="banOpenid(this);">确定封禁</button>'
    )
}

// 确定封禁Openid
function banOpenid(element){
    
    const openId = element.getAttribute('data-openid');
    const KamiId = element.getAttribute('data-kamiid');
    
    $.ajax({
        type: "POST",
        url: "banOpenid.php?openid=" + openId + '&kami_id=' + KamiId,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("banOpenidModal");
                
                // 显示结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('banOpenid.php');
        }
    });
}

// 确定清空所有提取记录
function cleanAllExtractRecords() {
    
    $.ajax({
        type: "POST",
        url: "cleanAllExtractRecords.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                setTimeout('hideModal("cleanAllExtractRecordsModal")', 500);
                setTimeout('showNotification("'+res.msg+'")', 800);
                setTimeout('getExtractRecords()', 1000);
                
                // 分页组件DOM删掉
                setTimeout("$('#right .data-card .fenye').html('')", 1200);
            }else {
                
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('cleanAllExtractRecords.php发生错误');
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
function setRouter(pageNum){
    
    // 第一页不设置
    if(pageNum !== 1){
        
        // 根据页码+token设置路由
        window.history.pushState('', '', '?p='+pageNum+'&token='+creatPageToken(32));
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

// 初始化（getExtractRecords）
function initialize_getExtractRecords(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
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
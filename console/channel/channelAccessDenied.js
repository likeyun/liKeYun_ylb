
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的黑名单IP列表
        getChannelAccessDeniedList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getChannelAccessDeniedList(1);
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
                $('#accountInfo').html('<span class="user_name">'+res.user_name+'</span><a href="javascript:;" onclick="exitLogin();">退出</a>');
                initialize_Login('login')
            }else{
                
                // 未登录
                $('#accountInfo').html('<a href="../login/">登录账号</a>');
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
function initialize_Login(loginStatus){
    
    if(loginStatus == 'login'){
        
        // 显示创建按钮
        $('#button-view').css('display','block');
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
    }
}

// 获取黑名单IP列表
function getChannelAccessDeniedList(pageNum) {
    
    // 初始化
    $("#right .data-list tbody").empty('');
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getchannelAccessDeniedList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getchannelAccessDeniedList.php?p="+pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>IP地址</th>' +
                '   <th>封禁时间</th>' +
                '   <th style="text-align:right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.accessDeniedIPList.length; i++) {
                    
                    // 数据判断并处理
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）IP地址
                    var data_ip = res.accessDeniedIPList[i].data_ip;
                    
                    // （3）封禁时间
                    var accessdenied_time = res.accessDeniedIPList[i].accessdenied_time;
                    
                    // （4）ID
                    var id = res.accessDeniedIPList[i].id;
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+data_ip+'</td>' +
                        '   <td>'+accessdenied_time+'</td>' +
                        '   <td style="text-align:right;cursor:pointer;" id="'+data_ip+'" onclick="delAccessDenied(this);">解封</td>' +
                        '</tr>' +
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
                    var $channelAccessDeniedIPListFenye_HTML = $(
                    '<ul>' +
                    '   <li><button onclick="getFenye('+res.nextpage+');" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.allpage+');" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $channelAccessDeniedIPListFenye_HTML = $(
                    '<ul>' +
                    '   <li><button onclick="getFenye(1);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.prepage+');" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $channelAccessDeniedIPListFenye_HTML = $(
                    '<ul>' +
                    '   <li><button onclick="getFenye(1);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.prepage+');" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.nextpage+');" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.allpage+');" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }
                // 渲染分页控件
                $("#right .data-card .fenye").html($channelAccessDeniedIPListFenye_HTML);
                // 设置URL
                if(res.page !== 1){
                    window.history.pushState('', '', '?p='+res.page+'&token='+creatPageToken(32));
                }
                
            }else{
                
                // 非200状态码
                warningPage(res.msg)
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('服务器发生错误！')
      },
    });
}

// 分页
function getFenye(pageNum){
    
    // 获取该页列表
    getChannelAccessDeniedList(pageNum);
}

// 询问是否要清空
function askCleanAllChannelData(channel_id){
    
    // 将群id添加到button的CleanAllChannelData函数用于传参执行删除
    $('#CleanAllChannelData .modal-footer').html('<button type="button" class="default-btn" onclick="CleanAllChannelData('+channel_id+');">确定清空</button>')
}

// 清空数据
function CleanAllChannelData(channel_id){
    
    $.ajax({
        type: "GET",
        url: "./cleanAllChannelData.php?channel_id="+channel_id,
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


// 解封IP
function delAccessDenied(e){
    $.ajax({
        type: "POST",
        url: "./delAccessDenied.php",
        data:{
            'data_ip':e.id
        },
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 显示已解封
                showTopAlert('已解封');
                
                // 获取黑名单IP列表
                getChannelAccessDeniedList(1);
                
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
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
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
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

// 顶部操作结果信息提示框
function showTopAlert(content){
    $('#topAlert').text(content);
    $('#topAlert').css('display','block');
    setTimeout('hideTopAlert()', 2500); // 2.5秒后自动关闭
}

// 关闭顶部操作结果信息提示框
function hideTopAlert(){
    $('#topAlert').css('display','none');
    $("#topAlert").text('');
}

// 隐藏Modal（传入节点id决定隐藏哪个Modal）
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}
// 显示Modal（传入节点id决定隐藏哪个Modal）
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
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
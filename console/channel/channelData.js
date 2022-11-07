
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    var channelid = queryURLParams(window.location.href).channelid;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的渠道码数据列表
        getChannelDataList(channelid,pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getChannelDataList(channelid,1);
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

// 获取渠道码数据列表
function getChannelDataList(channelid,pageNum) {
    
    // 初始化
    $("#right .data-list tbody").empty('');
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getChannelDataList.php?channel_id="+channelid;
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getChannelDataList.php?channel_id="+channelid+"&p="+pageNum
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
                '   <th>来源渠道</th>' +
                '   <th>来源IP</th>' +
                '   <th>来源设备</th>' +
                '   <th>来源时间</th>' +
                '   <th>访问量</th>' +
                '   <th style="text-align:right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.channelDataList.length; i++) {
                    
                    // 将清空数据按钮显示出来
                    $('#CleanAllChannelDataBtn').html('<button class="default-btn" data-toggle="modal" data-target="#CleanAllChannelData" onclick="askCleanAllChannelData('+channelid+');">清空数据</button>');
                    
                    // 数据判断并处理
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）数据来源
                    var data_referer = res.channelDataList[i].data_referer;
                    
                    // （3）来源设备
                    var data_device = res.channelDataList[i].data_device;
                    
                    // （4）来源IP
                    var data_ip = res.channelDataList[i].data_ip;
                    
                    // （5）访问量
                    var data_pv = res.channelDataList[i].data_pv;
                    
                    // （6）访问时间
                    var data_creat_time = res.channelDataList[i].data_creat_time;
                    
                    // （7）数据ID
                    var data_id = res.channelDataList[i].data_id;
                    
                    // （8）渠道码标题
                    var channel_title = res.channel_title;
                    
                    // 设备图标
                    if (data_device.includes('Android') === true) {
                        
                        // Android
                        var data_deviceIcon = 'android.png';
                    }else if(data_device.includes('iOS') === true) {
                        
                        // iOS
                        var data_deviceIcon = 'ios.png';
                    }else if(data_device.includes('Windows') === true) {
                        
                        // Windows
                        var data_deviceIcon = 'windows.png';
                    }else if(data_device.includes('Mac') === true) {
                        
                        // Mac
                        var data_deviceIcon = 'ios.png';
                    }else if(data_device.includes('Linux') === true) {
                        
                        // Linux
                        var data_deviceIcon = 'linux.png';
                    }else if(data_device.includes('iPad') === true) {
                        
                        // Linux
                        var data_deviceIcon = 'ios.png';
                    }else if(data_device.includes('未知设备') === true) {
                        
                        // 其它操作系统
                        var data_deviceIcon = 'weizhi.png';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+data_referer+'</td>' +
                        '   <td>'+data_ip+'</td>' +
                        '   <td><img src="../../static/img/'+data_deviceIcon+'" style="width:15px;height:15px;margin-right:10px;" />'+data_device+'</td>' +
                        '   <td>'+data_creat_time+'</td>' +
                        '   <td>'+data_pv+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <a class="dropdown-item" href="javascript:;" title="将ip加入黑名单" id="'+data_ip+'" onclick="AccessDenied(this)">封禁IP</a>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                    $("#channel_title").text(channel_title);
                }
                
                // 分页
                if(res.page == 1 && res.allpage == 1){
                    
                    // 当前页码=1 且 总页码>1
                    // 无需显示分页控件
                    $("#right .data-card .fenye").css("display","none");
                }else if(res.page == 1 && res.allpage > 1){
                    
                    // 当前页码=1 且 总页码>1
                    // 代表还有下一页
                    var $ChannelDataFenye_HTML = $(
                    '<ul>' +
                    '   <li><button onclick="getFenye('+res.channel_id+','+res.nextpage+');" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.channel_id+','+res.allpage+');" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $ChannelDataFenye_HTML = $(
                    '<ul>' +
                    '   <li><button onclick="getFenye('+res.channel_id+',1);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.channel_id+','+res.prepage+');" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $ChannelDataFenye_HTML = $(
                    '<ul>' +
                    '   <li><button onclick="getFenye('+res.channel_id+',1);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.channel_id+','+res.prepage+');" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.channel_id+','+res.nextpage+');" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button onclick="getFenye('+res.channel_id+','+res.allpage+');" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }
                // 渲染分页控件
                $("#right .data-card .fenye").html($ChannelDataFenye_HTML);
                // 设置URL
                if(res.page !== 1){
                    window.history.pushState('', '', '?channelid='+res.channel_id+'&p='+res.page+'&token='+creatPageToken(32));
                }
                
            }else{
                
                // 非200状态码
                warningPage(res.msg)
                // 将清空数据按钮隐藏
                $('#channel_title_h5 .tint-btn').css('display','none');
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('服务器发生错误！')
      },
    });
}

// 分页
function getFenye(channel_id,pageNum){
    
    // 获取该页列表
    getChannelDataList(channel_id,pageNum);
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
                
                // 显示清空结果
                // 先隐藏Modal
                hideModal('CleanAllChannelData')
                
                // 再显示已清空
                showTopAlert('已清空');
                
                // 最后刷新数据列表
                $('#channel_title').html('');
                $('#CleanAllChannelDataBtn').html('');
                getChannelDataList(channel_id,1);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}


// 封禁IP
function AccessDenied(e){
    $.ajax({
        type: "POST",
        url: "./AccessDenied.php",
        data:{
            'data_ip':e.id
        },
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 显示封禁结果
                showTopAlert(res.msg);
            }else{
                
                showTopAlert(res.msg);
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

// 生成随机token
function creatPageToken(length) {
    var str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) 
        result += str[Math.floor(Math.random() * str.length)];
    return result;
}

// 隐藏Modal（传入节点id决定隐藏哪个Modal）
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}
// 显示Modal（传入节点id决定隐藏哪个Modal）
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
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

// 获取URL参数
function queryURLParams(url) {
    var pattern = /(\w+)=(\w+)/ig;
    var parames = {};
    url.replace(pattern, ($, $1, $2) => {
        parames[$1] = $2;
    });
    return parames;
}
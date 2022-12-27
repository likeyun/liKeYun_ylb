
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的渠道码数据列表
        getChannelList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getChannelList();
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
                initialize_Login('login')
            }else{
                
                // 未登录
                $('#accountInfo').html('<a href="../login/">登录账号</a>');
                
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
function initialize_Login(loginStatus){
    
    if(loginStatus == 'login'){
        
        // 显示创建按钮
        $('#button-view').css('display','block');
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
    }
}

// 获取渠道码列表
function getChannelList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getChannelList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getChannelList.php?p="+pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getchannelList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>标题</th>' +
                '   <th>状态</th>' +
                '   <th>创建时间</th>' +
                '   <th>访问量</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.channelList.length; i++) {
                    
                    // 数据判断并处理
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）标题
                    var channel_title = res.channelList[i].channel_title;
                    
                    // （3）状态
                    if(res.channelList[i].channel_status == '1'){
                        
                        // 正常
                        var channel_status = '<span>正常</span>';
                    }else{
                        
                        // 关闭
                        var channel_status = '<span class="status_close">停用</span>';
                    }
                    
                    // （4）创建时间
                    var channel_creat_time = res.channelList[i].channel_creat_time;
                    
                    // （5）访问量
                    var channel_pv = res.channelList[i].channel_pv;
                    
                    // （6）渠道码ID
                    var channel_id = res.channelList[i].channel_id;
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+channel_title+'</td>' +
                        '   <td>'+channel_status+'</td>' +
                        '   <td>'+channel_creat_time+'</td>' +
                        '   <td>'+channel_pv+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#shareChannelHm" onclick="shareChannel('+channel_id+')">分享</a>' +
                        '               <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#EditChannelHm" onclick="getChannelInfo(this)" id="'+channel_id+'">编辑</a>' +
                        '               <a class="dropdown-item" href="./channelData.html?channelid='+channel_id+'" title="查看当前渠道的数据">数据</a>' +
                        '               <a class="dropdown-item" href="javascript:;" id="'+channel_id+'" data-toggle="modal" data-target="#DelChannelHm" onclick="askDelChannel(this)">删除</a>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
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
                    var $ChannelFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $ChannelFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $ChannelFenye_HTML = $(
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
                $("#right .data-card .fenye").html($ChannelFenye_HTML);
                // 设置URL
                if(res.page !== 1){
                    window.history.pushState('', '', '?p='+res.page+'&token='+creatPageToken(32));
                }
                
            }else{
                
                // 非200状态码
                warningPage(res.msg)
                
                // 如果是未登录
                // 3秒后自动跳转到登录页面
                if(res.code == 201){
                    redirectLoginPage(3000);
                }
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('服务器发生错误！')
      },
    });
}

// 跳转到登录界面
function redirectLoginPage(second){
    
    // second毫秒后跳转
    setTimeout('location.href="../login/";', second);
}

// 分页
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getChannelList(pageNum);
}

// 创建渠道码
function creatChannel(){
    $.ajax({
        type: "POST",
        url: "./creatChannel.php",
        data: $('#creatChannel').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏modal
                setTimeout('hideModal("CreatChannelHm")', 500);
                
                // 重新加载客服码列表
                setTimeout('getChannelList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 编辑渠道码
function editChannel(){
    $.ajax({
        type: "POST",
        url: "./editChannel.php",
        data: $('#editChannel').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏EditChannelHm modal
                setTimeout('hideModal("EditChannelHm")', 500);
                
                // 重新加载渠道码列表
                setTimeout('getChannelList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 询问是否要删除渠道活码
function askDelChannel(e){
    
    // 获取channel_id
    var channel_id = e.id;
    
    // 将群id添加到button的delChannel函数用于传参执行删除
    $('#DelChannelHm .modal-footer').html('<button type="button" class="default-btn" onclick="delChannel('+channel_id+');">确定删除</button>')
}

// 删除客服码
function delChannel(channel_id){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delChannel.php?channel_id="+channel_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                // 隐藏modal
                hideModal("DelChannelHm");
                
                // 重新加载群列表
                setTimeout('getChannelList()', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 获取渠道码详情
function getChannelInfo(e){

    // 获取channel_id
    var channel_id = e.id;
    
    // 根据channel_id获取渠道码详情
    $.ajax({
        type: "GET",
        url: "./getChannelInfo.php?channel_id="+channel_id,
        success: function(res){

            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // （1）标题
                $('#channel_title_edit').val(res.channelInfo.channel_title);
                
                // 获取域名列表
                getDomainNameList('edit')
                
                // （2）获取当前设置的域名
                $("#channel_rkym_edit").append('<option value="'+res.channelInfo.channel_rkym+'">'+res.channelInfo.channel_rkym+'</option>');
                $("#channel_ldym_edit").append('<option value="'+res.channelInfo.channel_ldym+'">'+res.channelInfo.channel_ldym+'</option>');
                $("#channel_dlym_edit").append('<option value="'+res.channelInfo.channel_dlym+'">'+res.channelInfo.channel_dlym+'</option>');
                
                
                // （3）渠道码状态
                if(res.channelInfo.channel_status == '1'){
                    
                    // 正常
                    $("#channel_status_edit").html('<option value="1">正常</option><option value="2">停用</option>');
                }else{
                    
                    // 停用
                    $("#channel_status_edit").html('<option value="2">停用</option><option value="1">正常</option>');
                }
                
                // 推广链接（4）
                $('#channel_url_edit').val(res.channelInfo.channel_url);
                
                // channel_id
                $('#channel_id_edit').val(channel_id);
                            
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 获取域名列表
function getDomainNameList(module){
    
    // 判断是作用于哪个模块的
    if(module == 'creat'){
        
        // 初始化
        initialize_getDomainNameList(module);
        
        // 获取
        $.ajax({
            type: "GET",
            url: "./getDomainNameList.php",
            success: function(res){
                
                // 成功
                if(res.code == 200){
                    
                    // 操作反馈（操作成功）
                    // 判断rkymList是否有域名
                    if(res.rkymList.length>0){;
                        for (var i=0; i<res.rkymList.length; i++) {
                            $("#channel_rkym").append('<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>');
                        }
                    }else{
                        $("#channel_rkym").append('<option value="">暂无入口域名</option>');
                    }
                    // 判断ldymList是否有域名
                    if(res.ldymList.length>0){
                        for (var i=0; i<res.ldymList.length; i++) {
                            $("#channel_ldym").append('<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>');
                        }
                    }else{
                        $("#channel_ldym").append('<option value="">暂无落地域名</option>');
                    }
                    // 判断dlymList是否有域名
                    if(res.dlymList.length>0){
                        for (var i=0; i<res.dlymList.length; i++) {
                            $("#channel_dlym").append('<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>');
                        }
                    }else{
                        $("#channel_dlym").append('<option value="">暂无短链域名</option>');
                    }
                }else{
                    
                    // 操作反馈（操作失败）
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
            }
        });
    }else if(module == 'edit'){
        
        // 初始化
        initialize_getDomainNameList(module);
        
        // 获取
        $.ajax({
            type: "GET",
            url: "./getDomainNameList.php",
            success: function(res){
                
                // 成功
                if(res.code == 200){
                    
                    // 操作反馈（操作成功）
                    // 判断rkymList是否有域名
                    if(res.rkymList.length>0){;
                        for (var i=0; i<res.rkymList.length; i++) {
                            $("#channel_rkym_edit").append('<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>');
                        }
                    }else{
                        $("#channel_rkym_edit").append('<option value="">暂无入口域名</option>');
                    }
                    // 判断ldymList是否有域名
                    if(res.ldymList.length>0){
                        for (var i=0; i<res.ldymList.length; i++) {
                            $("#channel_ldym_edit").append('<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>');
                        }
                    }else{
                        $("#channel_ldym_edit").append('<option value="">暂无落地域名</option>');
                    }
                    // 判断dlymList是否有域名
                    if(res.dlymList.length>0){
                        for (var i=0; i<res.dlymList.length; i++) {
                            $("#channel_dlym_edit").append('<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>');
                        }
                    }else{
                        $("#channel_dlym_edit").append('<option value="">暂无短链域名</option>');
                    }
                }else{
                    
                    // 操作反馈（操作失败）
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
            }
        });
    }
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
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
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

// 为了便于继续操作二维码列表
// 编辑群二维码的编辑框关闭后
// 点击右上角X会立即打开二维码列表
function hideEditKfZm(){
    hideModal('EditKfZm');
    showModal('kfZima')
}

// 计算过去多长时间
function getDateDiff(dateTimeStamp) {
    var minute = 1000 * 60;
    var hour = minute * 60;
    var day = hour * 24;
    var halfamonth = day * 15;
    var month = day * 30;
    var now = new Date().getTime();
    var diffValue = now - dateTimeStamp;
    var monthC = diffValue / month;
    var weekC = diffValue / (7 * day);
    var dayC = diffValue / day;
    var hourC = diffValue / hour;
    var minC = diffValue / minute;
    if (monthC >= 1) {
        passTime = parseInt(monthC) + "个月前";
    } else if (weekC >= 1) {
        passTime = parseInt(weekC) + "周前";
    } else if (dayC >= 1) {
        passTime = parseInt(dayC) + "天前";
    } else if (hourC >= 1) {
        passTime = parseInt(hourC) + "小时前";
    } else if (minC >= 1) {
        passTime = parseInt(minC) + "分钟前";
    } else {
        passTime = "刚刚";
    }
    return passTime;
}

// 时间字符串转换为时间戳
function getDateTimeStamp(dateStr){
    return Date.parse(dateStr.replace(/-/gi,"/"));
}

// 重新上传
function newUpload(){
    
    // 将图片预览隐藏，将上传控件打开
    $('#EditKfHm .modal-body .upload_file').css('display','block');
    $('#EditKfHm .modal-body .qrcode_preview').css('display','none');
    $('#channel_channel_edit').val('');
    $('#EditKfZm .modal-body .upload_file').css('display','block');
    $('#EditKfZm .modal-body .qrcode_preview').css('display','none');
    $('#zm_qrcode_edit').val('');
}

// 显示客服二维码
function showKfQrcode(channel_kf){
    // 开关选项
    $("#channel_channel_status_edit").html('<option value="1">显示客服二维码</option><option value="2">隐藏客服二维码</option>');
    // 还没上传过客服二维码
    if(channel_kf == ''){
        // 图片预览隐藏，上传控件显示
        $('#EditQunHm .modal-body .wxqrcode_preview').css('display','none');
        $('#EditQunHm .modal-body .upload_kf').css('display','block');
    }else{
        // 上传过客服二维码
        // 图片预览显示，上传控件隐藏
        $('#EditQunHm .modal-body .wxqrcode_preview').css('display','block');
        $('#EditQunHm .modal-body .upload_kf').css('display','none');
        var $previewQrcode_HTML = $(
            '<img src="'+channel_kf+'" class="wxqrcode" />' +
            '<p class="newUpload" onclick="newUpload();">重新上传</p>'
        );
        $('#EditQunHm .modal-body .wxqrcode_preview').html($previewQrcode_HTML);
    }
}

// 隐藏客服二维码
function hideKfQrcode(channel_kf){
    // 开关选项
    $("#channel_channel_status_edit").html('<option value="2">隐藏客服二维码</option><option value="1">显示客服二维码</option>');
    // 还没上传过客服二维码
    if(channel_kf == ''){
        // 图片预览隐藏，上传控件隐藏
        $('#EditQunHm .modal-body .wxqrcode_preview').css('display','none');
        $('#EditQunHm .modal-body .upload_kf').css('display','none');
    }else{
        // 上传过客服二维码
        // 图片预览隐藏，上传控件隐藏
        $('#EditQunHm .modal-body .wxqrcode_preview').css('display','none');
        $('#EditQunHm .modal-body .upload_kf').css('display','none');
        // 提前将图片预览加载出来便于切换的时候能显示预览
        var $previewQrcode_HTML = $(
            '<img src="'+channel_kf+'" class="wxqrcode" />' +
            '<p class="newUpload" onclick="newUpload();">重新上传</p>'
        );
        $('#EditQunHm .modal-body .wxqrcode_preview').html($previewQrcode_HTML);
    }
}

// 监听客服显示和隐藏的切换状态
function getKfOptionSelectVal(){
    
    if($('#channel_channel_status_edit').val() == '1'){
        // 还没上传过客服二维码
        // 图片预览隐藏，上传控件显示
        if($('#channel_channel_edit').val() == ''){
            $('#EditQunHm .modal-body .upload_kf').css('display','block')
            $('#EditQunHm .modal-body .wxqrcode_preview').css('display','none');
        }else{
            // 上传过客服二维码
            // 图片预览显示，上传控件隐藏
            $('#EditQunHm .modal-body .upload_kf').css('display','none')
            $('#EditQunHm .modal-body .wxqrcode_preview').css('display','block');
        }
    }else{
        // 图片预览隐藏，上传控件隐藏
        $('#EditQunHm .modal-body .upload_kf').css('display','none')
        $('#EditQunHm .modal-body .wxqrcode_preview').css('display','none');
    }
}

// 计算几天后的日期
function getDaysAfter(todatDate, days) {
    const milliseconds = 1000 * 60 * 60 * 24 * days;
    const afterTime = new Date(todatDate).getTime() + milliseconds;
    let dateObj = new Date(afterTime);
    let yearNum = dateObj.getYear()+1900;
    let monthNum = dateObj.getMonth()+1;
    let dayNum = dateObj.getDate();
    return yearNum + '-' + monthNum + '-' + dayNum;
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

// 没有获取到客服子码
function noZmData(text){
    $("#kfZima .loading").css('display','block');
    $("#kfZima .loading").html('<img src="../../static/img/warningIcon.png"/><br/><p>'+text+'</p>');
}

// 初始化（获取客服子码列表）
function initialize_kfzimaList(){
    // 清空原加载的列表
    $("#kfZima .modal-body .kfzima-list tbody").empty('');
    // 隐藏loading
    $("#kfZima .loading").css('display','none');
    // 清空上传控件选择的文件
    $("#uploadZmQrcode").val('');
}

// 初始化（编辑群活码上传控件）
function initialize_uploadKf(){
    $('#channel_channel_edit').val('');
    $('#uploadKfQrcode').val('');
    $('#EditQunHm .modal-body .upload_kf').css('display','block');
    $('#EditQunHm .modal-body .wxqrcode_preview').css('display','none');
    $('#EditQunHm .modal-body .wxqrcode_preview').html('');
}

// 初始化（getchannelList获取渠道码列表）
function initialize_getchannelList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'creat'){
        
        // 将所有值清空
        $("#channel_title").val('');
        $("#channel_rkym").empty();
        $("#channel_ldym").empty();
        $("#channel_dlym").empty();
        hideResult();
        
        // 设置默认值
        $("#channel_rkym").append('<option value="">选择入口域名</option>');
        $("#channel_ldym").append('<option value="">选择落地域名</option>');
        $("#channel_dlym").append('<option value="">选择短链域名</option>');
    }else if(module == 'edit'){
        
        // 将所有值清空
        $("#channel_rkym_edit").empty();
        $("#channel_ldym_edit").empty();
        $("#channel_dlym_edit").empty();
        hideResult();
    }

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
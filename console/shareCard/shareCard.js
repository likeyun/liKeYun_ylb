
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的列表
        getshareCardList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
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
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
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
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getshareCardList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getshareCardList.php?p="+pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getshareCardList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>ID</th>' +
                '   <th>标题</th>' +
                '   <th>创建时间</th>' +
                '   <th>访问次数</th>' +
                '   <th>状态</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.shareCardList.length; i++) {
                    
                    // 数据判断并处理
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）ID
                    var shareCard_id = res.shareCardList[i].shareCard_id;
                    
                    // （3）标题
                    var shareCard_title = res.shareCardList[i].shareCard_title;
                    
                    // （4）创建时间
                    var shareCard_create_time = res.shareCardList[i].shareCard_create_time;
                    
                    // （5）访问次数
                    var shareCard_pv = res.shareCardList[i].shareCard_pv;
                    
                    // （6）状态
                    if(res.shareCardList[i].shareCard_status == '1'){
                        
                        // 正常
                        var shareCard_status = '<span class="switch-on" id="'+shareCard_id+'" onclick="changeshareCardStatus(this);"><span class="press"></span></span>';
                    }else{
                        
                        // 关闭
                        var shareCard_status = '<span class="switch-off" id="'+shareCard_id+'" onclick="changeshareCardStatus(this);"><span class="press"></span></span>';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+shareCard_id+'</td>' +
                        '   <td>'+shareCard_title+'</td>' +
                        '   <td>'+shareCard_create_time+'</td>' +
                        '   <td>'+shareCard_pv+'</td>' +
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
                
                // 分页
                if(res.page == 1 && res.allpage == 1){
                    
                    // 当前页码=1 且 总页码>1
                    // 无需显示分页控件
                    $("#right .data-card .fenye").css("display","none");
                }else if(res.page == 1 && res.allpage > 1){
                    
                    // 当前页码=1 且 总页码>1
                    // 代表还有下一页
                    var $Fenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $Fenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $Fenye_HTML = $(
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
                $("#right .data-card .fenye").html($Fenye_HTML);
                
                // 设置URL
                if(res.page !== 1){
                    window.history.pushState('', '', '?p='+res.page+'&token='+creatPageToken(32));
                }
                
            }else{
                
                // 非200状态码
                if(res.code == 205){
                    
                    // 205状态码代表用户升级版本但未初始化
                    warningPage('<p>检测到你正在升级版本</p><button onclick="Upgrade();" class="default-btn" style="cursor:pointer;">'+res.msg+'</button>');
                    $('#button-view').html('');
                }else{
                    
                    warningPage(res.msg);
                }
                
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
    getshareCardList(pageNum);
}

// 升级
function Upgrade(){
    
    $.ajax({
        type: "POST",
        url: "./Upgrade.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                alert(res.msg);
                location.reload();
            }else{
                
                alert(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            alert('服务器发生错误');
        }
    });
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
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
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
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
        }
    });
}

// 询问是否要删除分享卡片
function askDelshareCard(shareCardid){
    
    // 将群id添加到button的delshareCard函数用于传参执行删除
    $('#DelshareCardModal .modal-footer').html('<button type="button" class="default-btn" onclick="delshareCard('+shareCardid+');">确定删除</button>')
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
                
                // 操作反馈（操作成功）
                // 隐藏DelshareCardModal
                hideModal("DelshareCardModal");
                
                // 重新加载分享卡片列表
                setTimeout('getshareCardList()', 500);
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

// 获取分享卡片详情
function getshareCardInfo(shareCard_id){
    
    // 根据shareCard_id获取详情
    $.ajax({
        type: "GET",
        url: "./getshareCardInfo.php?shareCard_id="+shareCard_id,
        success: function(res){

            if(res.code == 200){
                
                // （1）分享标题
                $('#shareCard_title_edit').val(res.shareCardInfo.shareCard_title);
                
                // （2）分享摘要
                $('#shareCard_desc_edit').val(res.shareCardInfo.shareCard_desc);
                
                // （3）分享缩略图
                $("#editShareCardModal .select_text").text('上传图片');
                $('#shareCard_img_edit').val(res.shareCardInfo.shareCard_img);
                
                // 获取域名列表
                getDomainNameList('edit');
                
                // （4）获取当前设置的域名
                $("#shareCard_rkym_edit").append('<option value="'+res.shareCardInfo.shareCard_rkym+'">'+res.shareCardInfo.shareCard_rkym+'</option>');
                $("#shareCard_ldym_edit").append('<option value="'+res.shareCardInfo.shareCard_ldym+'">'+res.shareCardInfo.shareCard_ldym+'</option>');
                
                // （5）目标链接
                $('#shareCard_url_edit').val(res.shareCardInfo.shareCard_url);
                
                // （6）ID
                $('#shareCard_id_edit').val(res.shareCardInfo.shareCard_id);
                            
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

// 获取分享卡片配置
function getshareCardConfig(){
    
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
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！')
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
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏configshareCardModal
                setTimeout('hideModal("configshareCardModal")', 500);

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
    if(module == 'create'){
        
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
                            $("#shareCard_rkym").append('<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>');
                        }
                    }else{
                        $("#shareCard_rkym").append('<option value="">暂无入口域名</option>');
                    }
                    // 判断ldymList是否有域名
                    if(res.ldymList.length>0){
                        for (var i=0; i<res.ldymList.length; i++) {
                            $("#shareCard_ldym").append('<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>');
                        }
                    }else{
                        $("#shareCard_ldym").append('<option value="">暂无落地域名</option>');
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
                            $("#shareCard_rkym_edit").append('<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>');
                        }
                    }else{
                        $("#shareCard_rkym_edit").append('<option value="">暂无入口域名</option>');
                    }
                    // 判断ldymList是否有域名
                    if(res.ldymList.length>0){
                        for (var i=0; i<res.ldymList.length; i++) {
                            $("#shareCard_ldym_edit").append('<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>');
                        }
                    }else{
                        $("#shareCard_ldym_edit").append('<option value="">暂无落地域名</option>');
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


// 切换switch（changeshareCardStatus）
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

// 初始化（getshareCardList获取分享卡片列表）
function initialize_getshareCardList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'create'){
        
        // 将所有值清空
        $("#shareCard_title").val('');
        $("#shareCard_desc").val('');
        $("#shareCard_img").val('');
        $("#shareCard_url").val('');
        $("#createShareCardModal .select_text").text('上传图片');
        $("#shareCard_rkym").empty();
        $("#shareCard_ldym").empty();
        hideResult();
        
        // 设置默认值
        $("#shareCard_rkym").append('<option value="">选择入口域名</option>');
        $("#shareCard_ldym").append('<option value="">选择落地域名</option>');
        
    }else if(module == 'edit'){
        
        // 将所有值清空
        $("#shareCard_rkym_edit").empty();
        $("#shareCard_ldym_edit").empty();
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
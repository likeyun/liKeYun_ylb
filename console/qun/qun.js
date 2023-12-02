
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的群活码列表
        getQunList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getQunList();
    }
    
    // clipboard插件
    var clipboard = new ClipboardJS('#shareQunModal .modal-footer button');
    clipboard.on('success', function(e) {
        
        // 复制成功
        $('#shareQunModal .modal-footer button').text('已复制');
    });
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
            showErrorResultForphpfileName('getLoginStatus.php');
        }
    });
}

// 登录后的一些初始化
function initialize_Login(loginStatus){
    
    if(loginStatus == 'login'){
        
        // 显示
        showElementBy('#button-view')
    }else{
        
        // 隐藏
        hideElementBy('#button-view')
    }
}

// 加载群活码列表
function getQunList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getQunList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getQunList.php?p="+pageNum
        
        // 设置URL路由
        setRouter(pageNum);
    }
    
    // 获取群活码列表
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getQunList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>标题</th>' +
                '   <th>客服</th>' +
                '   <th>去重</th>' +
                '   <th>安全提示</th>' +
                '   <th>创建时间</th>' +
                '   <th>总访问量</th>' +
                '   <th>今天访问量</th>' +
                '   <th>状态</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 200状态码
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.qunList.length; i++) {
                    
                    // 序号
                    var xuhao = i+1;
                    
                    // ID
                    var qun_id = res.qunList[i].qun_id;
                    
                    // 状态
                    if(res.qunList[i].qun_status == '1'){
                        
                        // 正常
                        var qun_status = 
                        '<span class="switch-on" id="'+qun_id+'" onclick="changeQunStatus(this);">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }else{
                        
                        // 关闭
                        var qun_status = 
                        '<span class="switch-off" id="'+qun_id+'" onclick="changeQunStatus(this);">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }
                    
                    // 客服显示状态
                    if(res.qunList[i].qun_kf_status == '1'){
                        
                        // 显示
                        var qun_kf_status = '<span>显示</span>';
                    }else{
                        
                        // 隐藏
                        var qun_kf_status = '<span>隐藏</span>';
                    }
                    
                    // 去重
                    if(res.qunList[i].qun_qc == '1'){
                        
                        // 正常
                        var qun_qc = 
                        '<span class="switch-on" id="'+qun_id+'" onclick="changeQunQc(this);">'+
                        '   <span class="press"></span>'+
                        '</span>';
                    }else{
                        
                        // 关闭
                        var qun_qc = 
                        '<span class="switch-off" id="'+qun_id+'" onclick="changeQunQc(this);">' +
                        '   <span class="press"></span>'+
                        '</span>';
                    }
                    
                    // （5）顶部扫码安全提示
                    if(res.qunList[i].qun_safety == '1'){
                        
                        // 开启
                        var qun_safety = '显示';
                    }else{
                        
                        // 关闭
                        var qun_safety = '隐藏';
                    }
                    
                    // 今天访问量
                    var qun_today_pv = JSON.parse(res.qunList[i].qun_today_pv.toString()).pv;
                    var qun_today_date = JSON.parse(res.qunList[i].qun_today_pv.toString()).date;
                    
                    // 获取日期
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    const todayDate = `${year}-${month}-${day}`;
                    
                    if(qun_today_date == todayDate){
                        
                        // 日期一致
                        // 显示今天的访问量
                        var qun_pv_today = qun_today_pv;
                    }else{
                        
                        // 日期不一致
                        // 显示0
                        var qun_pv_today = 0;
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+res.qunList[i].qun_title+'</td>' +
                        '   <td>'+qun_kf_status+'</td>' +
                        '   <td>'+qun_qc+'</td>' +
                        '   <td>'+qun_safety+'</td>' +
                        '   <td>'+res.qunList[i].qun_creat_time+'</td>' +
                        '   <td>'+res.qunList[i].qun_pv+'</td>' +
                        '   <td>'+qun_pv_today+'</td>' +
                        '   <td>'+qun_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#shareQunModal" onclick="shareQun('+qun_id+')">分享</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#editQunModal" onclick="getQunInfo(this)" id="'+qun_id+'">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#qunQrcodeListModal" onclick="getQunQrcodeList('+qun_id+');">上传</span>' +
                        '               <span class="dropdown-item" id="'+qun_id+'" data-toggle="modal" data-target="#delQunModal" onclick="askDelQun(this)">删除</span>' +
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
        errorPage('data-list','getQunList.php');
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
    getQunList(pageNum);
}

// 加载群二维码列表
function getQunQrcodeList(qun_id) {
    
    // 表头
    var $zm_thead_HTML = $(
        '<tr>' +
        '   <th>序号</th>' +
        '   <th>阈值</th>' +
        '   <th>访问量</th>' +
        '   <th>更新</th>' +
        '   <th>到期</th>' +
        '   <th>群主</th>' +
        '   <th>状态</th>' +
        '   <th style="text-align: right;">操作</th>' +
        '</tr>'
    );
    
    // 渲染HTML
    $("#qunQrcodeListModal .modal-body .qunQrcodeList thead").html($zm_thead_HTML);
    
    // 异步获取
    $.ajax({
        type: "POST",
        url: "./getQunQrcodeList.php?qun_id="+qun_id,
        success: function(res){
            
            // 初始化
            initialize_getQunQrcodeList();
            
            // 200状态码
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.qunQrcodeList.length; i++) {
                    
                    // 序号
                    var xuhao = i+1;
                    
                    // 微信群二维码id
                    var zm_id = res.qunQrcodeList[i].zm_id;
                    
                    // 微信群二维码状态
                    if(res.qunQrcodeList[i].zm_status == '1'){
                        
                        // 正常
                        var zm_status = 
                        '<span class="switch-on" onclick="changeQunQrcodeStatus('+zm_id+');">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }else{
                        
                        // 关闭
                        var zm_status = 
                        '<span class="switch-off" onclick="changeQunQrcodeStatus('+zm_id+');">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }
                    
                    // 计算更新时间
                    // 距离现在过去多长时间
                    var updatePassTime = getDateDiff(getDateTimeStamp(res.qunQrcodeList[i].zm_update_time));
                    
                    // 今天日期
                    var nowDate = new Date();
                    var year = nowDate.getFullYear();
                    var month = nowDate.getMonth() + 1;
                    var date = nowDate.getDate();
                    var todayDate = year + '-' + month + '-' + date;
                    
                    // 到期日期
                    var daoqiDate = getDaysAfter(todayDate, 7);
                    
                    // 如果到期日期等于今天
                    if(daoqiDate == todayDate){
                        
                        // 修改变量
                        daoqiDate = '已到期';
                    }
                    
                    // 群主
                    if(res.qunQrcodeList[i].zm_leader == '' || res.qunQrcodeList[i].zm_leader == null){
                        
                        // 未设置群主
                        var zm_leader = '<span>未设置</span>';
                    }else{
                        
                        // 已设置群主
                        var zm_leader = res.qunQrcodeList[i].zm_leader;
                    }
                    
                    // 列表
                    var $zm_tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+res.qunQrcodeList[i].zm_yz+'</td>' +
                        '   <td>'+res.qunQrcodeList[i].zm_pv+'</td>' +
                        '   <td>'+updatePassTime+'</td>' +
                        '   <td>'+daoqiDate+'</td>' +
                        '   <td>'+zm_leader+'</td>' +
                        '   <td id="qunzima_status_'+zm_id+'">'+zm_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#editQunQrcodeModal" onclick="getQunzmInfo(this)" id="'+zm_id+'">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#delQunQrcodeModal" onclick="askDelQunQrcode(this)" id="'+zm_id+'">删除</span>' +
                        '               <span class="dropdown-item" title="重置阈值和访问量为0" onclick="resetQunQrcode(this)" id="'+zm_id+'">重置</span>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>'
                    );
                    
                    // 渲染HTML
                    $("#qunQrcodeListModal .modal-body .qunQrcodeList tbody").append($zm_tbody_HTML);
                }
            }else{
                
                // 非200状态码
                noZmData('暂无二维码');
            }
            
            // 群标题
            $("#qunQrcodeListModalTitle").text(res.qun_title);
            
            // 给【从素材库选择】这个button增加一个data-qid的属性
            $('#qunQrcodeListModal .sucaiku').attr('data-qid',qun_id);
            
      },
      error: function(){
        
        // 发生错误
        errorPage('qrcode-list','getQunQrcodeList.php');
      },
    });
    
    // qun_id
    $("#uploadQunQrcode_qunid").val(qun_id);
}

// 刷新群二维码列表
// 只要作用于上传成功后获取最新列表
function freshenQunQrcodeList(qun_id){
    
    $.ajax({
        type: "POST",
        url: "./getQunQrcodeList.php?qun_id="+qun_id,
        success: function(res){
            
            // 初始化
            initialize_getQunQrcodeList();
            
            // 状态码200
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.qunQrcodeList.length; i++) {
                    
                    // 序号
                    var xuhao = i+1;
                    
                    // 微信群二维码id
                    var zm_id = res.qunQrcodeList[i].zm_id;

                    // 状态
                    if(res.qunQrcodeList[i].zm_status == '1'){
                        
                        // 正常
                        var zm_status = 
                        '<span class="switch-on" onclick="changeQunQrcodeStatus('+zm_id+');">'+
                        '<span class="press"></span>'+
                        '</span>';
                    }else{
                        
                        // 关闭
                        var zm_status = 
                        '<span class="switch-off" onclick="changeQunQrcodeStatus('+zm_id+');">'+
                        '<span class="press"></span>'+
                        '</span>';
                    }
                    
                    // 计算更新时间
                    // 距离现在过去多长时间
                    var updatePassTime = getDateDiff(getDateTimeStamp(res.qunQrcodeList[i].zm_update_time));
                    
                    // 今天日期
                    var nowDate = new Date();
                    var year = nowDate.getFullYear();
                    var month = nowDate.getMonth() + 1;
                    var date = nowDate.getDate();
                    var todayDate = year + '-' + month + '-' + date;
                    
                    // 到期日期
                    var daoqiDate = getDaysAfter(todayDate, 7);
                    
                    // 如果到期日期等于今天
                    if(daoqiDate == todayDate){
                        
                        // 更新变量
                        daoqiDate = '已到期';
                    }
                    
                    // 群主
                    if(res.qunQrcodeList[i].zm_leader == '' || res.qunQrcodeList[i].zm_leader == null){
                        
                        // 未设置群主
                        var zm_leader = '<span>未设置</span>';
                    }else{
                        
                        // 已设置群主
                        var zm_leader = res.qunQrcodeList[i].zm_leader;
                    }
                    
                    // 列表
                    var $zm_tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+res.qunQrcodeList[i].zm_yz+'</td>' +
                        '   <td>'+res.qunQrcodeList[i].zm_pv+'</td>' +
                        '   <td>'+updatePassTime+'</td>' +
                        '   <td>'+daoqiDate+'</td>' +
                        '   <td>'+zm_leader+'</td>' +
                        '   <td id="qunzima_status_'+zm_id+'">'+zm_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#editQunQrcodeModal" onclick="getQunzmInfo(this)" id="'+zm_id+'">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#delQunQrcodeModal" onclick="askDelQunQrcode(this)" id="'+zm_id+'">删除</span>' +
                        '               <span class="dropdown-item" title="重置阈值和访问量为0" onclick="resetQunQrcode(this)" id="'+zm_id+'">重置</span>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>'
                    );
                    
                    // 渲染HTML
                    $("#qunQrcodeListModal .modal-body .qunQrcodeList tbody").append($zm_tbody_HTML);
                }
            }else{
                
                // 暂无二维码
                noZmData('暂无二维码');
            }
            
            // 群标题
            $("#qunQrcodeListModalTitle").text(res.qun_title);
      },
      error: function(){
        
        // 发生错误
        errorPage('qrcode-list','getQunQrcodeList.php');
      },
    });
}

// 创建群活码
function createQun(){
    $.ajax({
        type: "POST",
        url: "./createQun.php",
        data: $('#createQun').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("createQunModal")', 500);
                
                // 重新加载群列表
                setTimeout('getQunList();', 500);
                
                // 打开上传群二维码面板
                setTimeout('showModal("qunQrcodeListModal")', 1300);
                
                // 隐藏Result
                setTimeout('hideResult()', 1400);
                
                // 获取群二维码列表
                getQunQrcodeList(res.qun_id);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createQun.php');
        }
    });
}

// 编辑群活码
function editQun(){
    $.ajax({
        type: "POST",
        url: "./editQun.php",
        data: $('#editQun').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("editQunModal")', 500);
                
                // 重新加载群列表
                setTimeout('getQunList()', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editQun.php');
        }
    });
}

// 编辑群二维码
function editQunQrcode(){
    $.ajax({
        type: "POST",
        url: "./editQunQrcode.php",
        data: $('#editQunQrcode').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
    
                // 隐藏Modal
                hideModal("editQunQrcodeModal")
                
                // 打开Modal
                showModal("qunQrcodeListModal")
                
                // 重新加载群列表
                freshenQunQrcodeList(res.qun_id)
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editQunqrcode.php');
        }
    });
}

// 询问是否要删除
function askDelQun(e){
    
    // 获取qun_id
    var qun_id = e.id;
    
    // 将群id添加到button
    // 的delQun函数用于传参执行删除
    $('#delQunModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delQun('+qun_id+');">确定删除</button>'
    )
}

// 删除群活码
function delQun(qun_id){
    
    // 执行删除
    $.ajax({
        type: "GET",
        url: "./delQun.php?qun_id="+qun_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delQunModal");
                
                // 重新加载群活码列表
                setTimeout('getQunList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delQun.php');
        }
    });
}

// 获取群活码详情
function getQunInfo(e){
    
    // 初始化上传控件
    initialize_uploadKf();
    
    // 获取qun_id
    var qun_id = e.id;
    
    // 根据qun_id获取群详情
    $.ajax({
        type: "GET",
        url: "./getQunInfo.php?qun_id="+qun_id,
        success: function(res){

            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 标题
                $('input[name="qun_title"]').val(res.qunInfo.qun_title);
                
                // 群备注
                $('textarea[name="qun_beizhu"]').val(res.qunInfo.qun_beizhu);
                
                // 获取域名列表
                getDomainNameList('edit');
                
                // 入口域名
                $('select[name="qun_rkym"]').append(
                    '<option value="'+res.qunInfo.qun_rkym+'">'+res.qunInfo.qun_rkym+'</option>'
                );
                
                // 落地域名
                $('select[name="qun_ldym"]').append(
                    '<option value="'+res.qunInfo.qun_ldym+'">'+res.qunInfo.qun_ldym+'</option>'
                );
                
                // 短链域名
                $('select[name="qun_dlym"]').append(
                    '<option value="'+res.qunInfo.qun_dlym+'">'+res.qunInfo.qun_dlym+'</option>'
                );
                
                // 获取当前设置的通知渠道
                if(res.qunInfo.qun_notify){
                    $('select[name="qun_notify"]').append(
                        '<option value="'+res.qunInfo.qun_notify+'">'+res.qunInfo.qun_notify+'</option>' +
                        '<option value="企业微信">企业微信</option>' +
                        '<option value="邮件">邮件</option>' +
                        '<option value="Bark">Bark</option>' +
                        '<option value="Server酱">Server酱</option>' +
                        '<option value="HTTP">HTTP</option>' +
                        '<option value="">不通知</option>'
                    );
                }else{
                    $('select[name="qun_notify"]').append(
                        '<option value="">选择通知渠道</option>' +
                        '<option value="企业微信">企业微信</option>' +
                        '<option value="邮件">邮件</option>' +
                        '<option value="Bark">Bark</option>' +
                        '<option value="Server酱">Server酱</option>' +
                        '<option value="HTTP">HTTP</option>' +
                        '<option value="">不通知</option>'
                    );
                }
                
                // 活码状态
                if(res.qunInfo.qun_status == '1'){
                    
                    $('select[name="qun_status"]').html(
                        '<option value="1">正常</option><option value="2">停用</option>'
                    );
                }else{
                    
                    $('select[name="qun_status"]').html(
                        '<option value="2">停用</option><option value="1">正常</option>'
                    );
                }
                
                // 顶部扫码安全提示
                if(res.qunInfo.qun_safety == '1'){
                    
                    $('select[name="qun_safety"]').html(
                        '<option value="1">显示</option><option value="2">隐藏</option>'
                    );
                }else{
                    
                    $('select[name="qun_safety"]').html(
                        '<option value="2">隐藏</option><option value="1">显示</option>'
                    );
                }
                
                // 客服二维码的显示状态
                if(res.qunInfo.qun_kf_status == 1){
                    
                    $('select[name="qun_kf_status"]').html(
                        '<option value="1">显示</option><option value="2">隐藏</option>'
                    );
                }else{
                    
                    $('select[name="qun_kf_status"]').html(
                        '<option value="2">隐藏</option><option value="1">显示</option>'
                    );
                }
                
                // 显示客服二维码预览
                if(res.qunInfo.qun_kf){
                    
                    // 如果有客服二维码Url
                    // 隐藏上传入口
                    $('#editQunModal .modal-body .upload_file').css('display','none');
                            
                    // 显示预览
                    $('#editQunModal .modal-body .qrcode_preview').css('display','block');
                    $('#editQunModal .modal-body .qrcode_preview').html(
                        '<img src="'+res.qunInfo.qun_kf+'" class="qrcode" />' +
                        '<p class="uploadSuccess_Reupload" onclick="newUpload()">重新上传</p>' +
                        '<div class="Re-upload selectFromSCK" onclick="getSuCai(\'1\',\'editQunModal\');">从素材库选择</div>'
                    );
                }
                
                // 去重功能
                if(res.qunInfo.qun_qc == '1'){
                    
                    $('select[name="qun_qc"]').html(
                        '<option value="1">开启</option><option value="2">关闭</option>'
                    );
                }else{
                    
                    $('select[name="qun_qc"]').html(
                        '<option value="2">关闭</option><option value="1">开启</option>'
                    );
                }

                // qun_id
                $('input[name="qun_id"]').val(qun_id);
                
                // qun_kf
                $('input[name="qun_kf"]').val(res.qunInfo.qun_kf);
                            
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getQunInfo.php');
        }
    });
}

// 获取群二维码详情
function getQunzmInfo(e){
    
    // 获取zm_id
    var zm_id = e.id;
    
    // 根据zm_id获取群详情
    $.ajax({
        type: "GET",
        url: "./getQunzmInfo.php?zm_id="+zm_id,
        success: function(res){
            
            // 隐藏群二维码管理面板
            hideModal('qunQrcodeListModal');

            if(res.code == 200){
                
                // 阈值
                $('#zm_yz_edit').val(res.qunzmInfo.zm_yz);
                
                // 群主微信号
                $('#zm_leader_edit').val(res.qunzmInfo.zm_leader);
                
                // 二维码使用状态
                if(res.qunzmInfo.zm_status == '1'){
                    
                    $("#zm_status_edit").html('<option value="1">正常</option><option value="2">停用</option>');
                }else{
                    
                    $("#zm_status_edit").html('<option value="2">停用</option><option value="1">正常</option>');
                }
                
                // 获取二维码
                $('#editQunQrcodeModal .modal-body .qrcode_preview').css('display','block');
                $('#editQunQrcodeModal .modal-body .upload_file').css('display','none');
                
                // 显示二维码及重新上传控件
                var $previewQrcode_HTML = $(
                    '<img src="'+res.qunzmInfo.zm_qrcode+'" class="qrcode" />' +
                    '<div>' +
                    '   <div class="Re-upload reUpload" onclick="newUpload();">重新上传</div>' +
                    '   <div class="Re-upload selectFromSCK" onclick="getSuCai(\'1\',\'editQunQrcodeModal\');">从素材库选择</div>' +
                    '</div>'
                );
                $('#editQunQrcodeModal .modal-body .qrcode_preview').html($previewQrcode_HTML);
                
                // zm_id
                $('#zm_id_edit').val(zm_id);
                
                // zm_qrcode_edit
                $('#zm_qrcode_edit').val(res.qunzmInfo.zm_qrcode);
                            
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getQunzmInfo.php');
        }
    });
}

// 使用 appendOptionsToSelect函数来为每个select元素处理选项的添加
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
                
                // 将入口、落地、短链域名添加至选项中
                appendOptionsToSelect($("select[name='qun_rkym']"), res.rkymList);
                appendOptionsToSelect($("select[name='qun_ldym']"), res.ldymList);
                appendOptionsToSelect($("select[name='qun_dlym']"), res.dlymList);
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

// 询问是否要删除群二维码
function askDelQunQrcode(e){
    
    // 获取zm_id
    var zm_id = e.id;
    
    // 隐藏modal
    hideModal("qunQrcodeListModal");
    
    // 将群zm_id添加到button
    // 的delQunQrcode函数用于传参执行删除
    $('#delQunQrcodeModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delQunQrcode('+zm_id+');">确定删除</button>'
    )
}

// 删除群二维码
function delQunQrcode(zm_id){
    
    // 执行删除
    $.ajax({
        type: "GET",
        url: "./delQunQrcode.php?zm_id="+zm_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                // 隐藏Modal
                hideModal("delQunQrcodeModal")
                
                // 打开Modal
                showModal("qunQrcodeListModal")
                
                // 刷新群二维码列表
                freshenQunQrcodeList(res.qun_id);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delQunQrcode.php');
        }
    });
}

// 重置阈值和访问量为0
function resetQunQrcode(e){
    
    // zm_id
    var zm_id = e.id;
    
    // 执行重置
    $.ajax({
        type: "GET",
        url: "./resetQunQrcode.php?zm_id="+zm_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){

                // 刷新二维码列表
                freshenQunQrcodeList(res.qun_id)
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('resetQunQrcode.php');
        }
    });
}

// 分享群活码
function shareQun(qun_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    $.ajax({
        type: "GET",
        url: "./shareQun.php?qun_id="+qun_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 长链接
                $("#longUrl").text(res.longUrl);
                
                // 短链接
                $("#shortUrl").html('<span id="qun_'+qun_id+'">'+res.shortUrl+'</span>');
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.qrcodeUrl);
                
                // 复制按钮
                $('#shareQunModal .modal-footer').html(
                    '<button class="default-btn" data-clipboard-action="copy" data-clipboard-target="#qun_'+qun_id+'">复制链接</button>'
                );
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('shareQun.php');
        }
    });
}

// 获取素材
function getSuCai(pageNum,fromPannel){
    
    // 初始化
    $('#suCaiKu .modal-body .sucai-view').empty('');
    
    // 关闭二维码上传界面
    hideModal('qunQrcodeListModal');
    
    // 关闭编辑群二维码界面
    hideModal('editQunQrcodeModal');
    
    // 关闭编辑群活码界面
    hideModal('editQunModal');
    
    // 打开素材库界面
    showModal('suCaiKu');
    
    // 将fromPannel的值设置到隐藏的表单中
    $('#suCaiKu input[name="upload_sucai_fromPannel"]').val(fromPannel);
    
    // 获取到qunid
    var qunid = $('#qunQrcodeListModal .default-btn.sucaiku').attr('data-qid');
    
    // 将qunid设置到表单中便于传参
    $('#suCaiKu input[name="upload_sucai_qunid"]').val(qunid);
    
    // 判断是否有pageNum参数传过来
    if(pageNum == undefined){
        
        // 没有参数就设置默认值
        var pageNum = 1;
    }
    
    // 获取从哪个面板点击打开的
    if(fromPannel == 'editQunQrcodeModal'){
        
        // 上一个面板是 editQunQrcodeModal 
        // 渲染出来的关闭按钮是需要返回 editQunQrcodeModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'editQunQrcodeModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'qunQrcodeListModal'){
        
        // 上一个面板是 qunQrcodeListModal
        // 渲染出来的关闭按钮是需要返回 qunQrcodeListModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'qunQrcodeListModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'editQunModal'){
        
        // 上一个面板是 editQunModal
        // 渲染出来的关闭按钮是需要返回 editQunModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'editQunModal\')">&times;</button>'
        );
    }
    
    // 开始获取素材列表
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
                    if(fromPannel == 'editQunQrcodeModal'){
                        
                        // 更新
                        var clickFunction = 'selectSucaiUpdateQunQrcode('+sucai_id+')';
                        
                    }else if(fromPannel == 'qunQrcodeListModal'){
                        
                        // 新增
                        var clickFunction = 'selectSucai('+sucai_id+','+qunid+')';
                    }else if(fromPannel == 'editQunModal'){
                        
                        // 新增
                        var clickFunction = 'selectSucaiForQun('+sucai_id+')';
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
// 添加新的群二维码
// 注意：仅作用于添加新的群二维码
function selectSucai(sucai_id,qunid){
    
    $.ajax({
        type: "POST",
        url: "./selectSuCaiForQunQrcode.php?sucai_id="+sucai_id+"&qunid="+qunid,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功选择素材
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                // 显示操作反馈
                showSuccessResultTimes('已选择',1500);
                
                // 打开上传群二维码面板
                setTimeout("showModal('qunQrcodeListModal')",1300);
                
                // 刷新群二维码列表
                setTimeout("freshenQunQrcodeList("+qunid+")",1500);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSuCaiForQunQrcode.php');
        }
    });
}

// 选择当前点击的素材
// 用于更新群二维码
// 注意：仅作用于更新群二维码
function selectSucaiUpdateQunQrcode(sucai_id){
    
    $.ajax({
        type: "POST",
        url: "./selectSucaiUpdateQunQrcode.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 将群二维码设置到表单中
                $('#zm_qrcode_edit').val(res.qunQrcodeUrl);
                
                // 设置新的预览
                $('#editQunQrcodeModal .modal-body .qrcode_preview').html(
                    '<img src="'+res.qunQrcodeUrl+'" class="qrcode" />' +
                    '<p class="uploadSuccess">已选取素材</p>'
                );
                
                // 成功选择素材
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                // 显示操作反馈
                showSuccessResultTimes('已选择',1100);
                
                // 打开编辑群二维码Modal
                setTimeout("showModal('editQunQrcodeModal')",1200);
                
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSucaiUpdateQunQrcode.php');
        }
    });
}

// 选择当前点击的素材
// 用于更新群活码的客服二维码
function selectSucaiForQun(sucai_id){
    
    $.ajax({
        type: "POST",
        url: "./selectSucaiForQun.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 将群客服二维码设置到表单中
                $('input[name="qun_kf"]').val(res.kfQrcodeUrl);
                
                // 预览已选择的素材
                // 隐藏上传入口
                $('#editQunModal .modal-body .upload_file').css('display','none');
                        
                // 显示预览
                $('#editQunModal .modal-body .qrcode_preview').css('display','block');
                $('#editQunModal .modal-body .qrcode_preview').html(
                    '<img src="'+res.kfQrcodeUrl+'" class="qrcode" />' +
                    '<p class="uploadSuccess_Reupload" onclick="newUpload()">重新上传</p>' +
                    '<div class="Re-upload selectFromSCK" onclick="getSuCai(\'1\',\'editQunModal\');">从素材库选择</div>'
                );
                
                // 成功选择素材
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                // 显示操作反馈
                showSuccessResultTimes('已选择',1100);
                
                // 打开编辑群活码Modal
                setTimeout("showModal('editQunModal')",1200);
                
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSucaiForQun.php');
        }
    });
    
    // 解决一个bug
    setTimeout("$('body').attr('class', 'modal-open')",1600);
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
            showErrorResultForphpfileName('exitLogin.php');
        }
    });
}

// 切换switch
// changeQunStatus
function changeQunStatus(e){

    // 修改
    $.ajax({
        type: "POST",
        url: "./changeQunStatus.php?qun_id="+e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 刷新
                getQunList();
                showNotification(res.msg);
            }else{
                
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('changeQunStatus.php');
        }
    });
}

// 切换switch
// changeQunQrcodeStatus
function changeQunQrcodeStatus(zmid){

    // 修改
    $.ajax({
        type: "POST",
        url: "./changeQunQrcodeStatus.php?zm_id="+zmid,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 更新switch状态
                showQunQrcodeSwitchNewStatus(res.zm_status,zmid);
                
                // 显示切换结果
                showSuccessResult(res.msg);
                
            }else{
                
                // 非200状态码操作结果
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('changeQunQrcodeStatus.php');
        }
    });
}

// 切换switch
// changeQunQc
function changeQunQc(e){
    
    $.ajax({
        type: "POST",
        url: "./changeQunQc.php?qun_id="+e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 获取列表
                getQunList();
                
                // 显示切换结果
                showNotification(res.msg);
                
            }else{
                
                // 非200状态码操作结果
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('changeQunQc.php');
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
// 点击右上角X会继续打开二维码列表
function hideEditQunQrcodeModal(){
    hideModal('editQunQrcodeModal');
    showModal('qunQrcodeListModal');
    
    // 解决一个bug
    setTimeout("$('body').attr('class', 'modal-open')",1600);
}

// 为了便于继续操作二维码列表
// 素材库的界面关闭后
// 点击右上角X会继续打开二维码列表
function hideSuCaiPannel(fromPannel){
    
    // 先隐藏 suCaiKu 面板
    hideModal('suCaiKu');
    
    // 根据fromPannel决定打开哪个 Modal
    if(fromPannel == 'editQunQrcodeModal'){
        
        // 显示 editQunQrcodeModal
        showModal('editQunQrcodeModal')
    }else if(fromPannel == 'qunQrcodeListModal'){
        
        // 显示 qunQrcodeListModal
        showModal('qunQrcodeListModal')
    }else if(fromPannel == 'editQunModal'){
        
        // 显示 editQunModal
        showModal('editQunModal')
    }
    
    // 解决一个bug
    setTimeout("$('body').attr('class', 'modal-open')",1600);
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
    // editQunModal
    $('#editQunModal .modal-body .upload_file').css('display','block');
    $('#editQunModal .modal-body .qrcode_preview').css('display','none');
    $('input[name="qun_kf"]').val('');
    
    // editQunQrcodeModal
    $('#editQunQrcodeModal .modal-body .upload_file').css('display','block');
    $('#editQunQrcodeModal .modal-body .qrcode_preview').css('display','none');
    $('#zm_qrcode_edit').val('');
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

// 提醒页面
function warningPage(text){
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html('<img src="../../static/img/warningIcon.png"/><br/><p>'+text+'</p>');
    $("#right .data-card .loading").css('display','block');
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

// 没有获取到群二维码
function noZmData(text){
    $("#qunQrcodeListModal .loading").css('display','block');
    $("#qunQrcodeListModal .loading").html('<img src="../../static/img/noRes.png" /><br/><p>'+text+'</p>');
}

// 初始化（获取群二维码列表，getQunQrcodeList）
function initialize_getQunQrcodeList(){
    $("#qunQrcodeListModal .modal-body .qunQrcodeList tbody").empty('');
    $("#qunQrcodeListModal .loading").css('display','none');
    $("#uploadQunQrcode").val('');
}

// 初始化（编辑群活码上传控件）
function initialize_uploadKf(){
    $('input[name="qun_kf"]').val('');
    $('select[name="qun_notify"]').empty('');
    $('#selectKfQrcode').val('');
    $('#editQunModal .modal-body .upload_file').css('display','block');
    $('#editQunModal .modal-body .qrcode_preview').css('display','none');
    $('#editQunModal .modal-body .qrcode_preview').html('');
}

// 初始化（获取群活码列表，getQunList）
function initialize_getQunList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化
// 获取域名列表
function initialize_getDomainNameList(module){
    
    // 默认值
    $('#createQunModal input[name="qun_title"]').val('');
    $('select[name="qun_rkym"]').empty();
    $('select[name="qun_ldym"]').empty();
    $('select[name="qun_dlym"]').empty();
    hideResult();
}

// 打开操作反馈（操作成功）
function showSuccessResult(content){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', 2500); // 2.5秒后自动关闭
}

// 打开操作反馈（操作成功）
function showSuccessResultTimes(content,times){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', times);
}

// 打开操作反馈（操作失败）
function showErrorResult(content){
    $('#app .result').html('<div class="error">'+content+'</div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 2500);
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

// 关闭操作反馈
function hideResult(){
    $("#app .result .success").css("display","none");
    $("#app .result .error").css("display","none");
    $("#app .result .success").text('');
    $("#app .result .error").text('');
}

// 显示群二维码切换后的状态
function showQunQrcodeSwitchNewStatus(status,zmid){
    
    if(status == 1){
        $('#qunzima_status_'+zmid).html(
            '<span class="switch-on" onclick="changeQunQrcodeStatus('+zmid+');">' +
            '<span class="press"></span></span>'
        );  
    }else{
        
        $('#qunzima_status_'+zmid).html(
            '<span class="switch-off" onclick="changeQunQrcodeStatus('+zmid+');">' +
            '<span class="press"></span></span>'
        );
    }
}

// 显示指定元素
function showElementBy(csspath){
    
    // 传入CSS路径
    $(csspath).css('display','block');
}

// 隐藏指定元素
function hideElementBy(csspath){
    
    // 传入CSS路径
    $(csspath).css('display','none');
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

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 设置URL路由
function setRouter(pageNum){
    
    // 根据页码+token设置路由
    window.history.pushState('', '', '?p='+pageNum+'&token='+creatPageToken(32));
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
console.log('%c 作者：TANKING','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
console.log('%c 作者博客：https://segmentfault.com/u/tanking','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
console.log('%c 开源地址：https://github.com/likeyun/liKeYun_Ylb','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
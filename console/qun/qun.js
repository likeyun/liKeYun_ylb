
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
                '   <th>访问量</th>' +
                '   <th>状态</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.qunList.length; i++) {
                    
                    // 数据判断并处理
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）状态
                    if(res.qunList[i].qun_status == '1'){
                        
                        // 正常
                        var qun_status = '<span class="switch-on" id="'+res.qunList[i].qun_id+'" onclick="changeQunStatus(this);"><span class="press"></span></span>';
                    }else{
                        
                        // 关闭
                        var qun_status = '<span class="switch-off" id="'+res.qunList[i].qun_id+'" onclick="changeQunStatus(this);"><span class="press"></span></span>';
                    }
                    
                    // （3）客服
                    if(res.qunList[i].qun_kf_status == '1'){
                        
                        // 显示
                        var qun_kf_status = '<span>显示</span>';
                    }else{
                        
                        // 隐藏
                        var qun_kf_status = '<span>隐藏</span>';
                    }
                    
                    // 去重（4）
                    if(res.qunList[i].qun_qc == '1'){
                        
                        // 开启
                        var qun_qc = '开启';
                    }else{
                        
                        // 关闭
                        var qun_qc = '关闭';
                    }
                    
                    // （5）顶部扫码安全提示
                    if(res.qunList[i].qun_safety == '1'){
                        
                        // 开启
                        var qun_safety = '显示';
                    }else{
                        
                        // 关闭
                        var qun_safety = '隐藏';
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
                        '   <td>'+qun_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#shareQunHm" onclick="shareQun('+res.qunList[i].qun_id+')">分享</a>' +
                        '               <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#EditQunHm" onclick="getQunInfo(this)" id="'+res.qunList[i].qun_id+'">编辑</a>' +
                        '               <a class="dropdown-item" href="javascript:;" id="'+res.qunList[i].qun_id+'" data-toggle="modal" data-target="#qunZima" onclick="getQunzmList(this);">上传</a>' +
                        '               <a class="dropdown-item" href="javascript:;" id="'+res.qunList[i].qun_id+'" data-toggle="modal" data-target="#DelQunHm" onclick="askDelQun(this)">删除</a>' +
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
                    var $qunFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $qunFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $qunFenye_HTML = $(
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
                $("#right .data-card .fenye").html($qunFenye_HTML);
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

// 分页
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getQunList(pageNum);
}

// 加载群子码列表
function getQunzmList(e) {
    
    // 获取qun_id
    var qun_id = e.id;
    
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
    $("#qunZima .modal-body .qunzima-list thead").html($zm_thead_HTML);
    
    // 异步获取
    $.ajax({
        type: "POST",
        url: "./getQunzmList.php?qun_id="+qun_id,
        success: function(res){
            
            // 初始化
            initialize_getQunzmList();
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.qunzmList.length; i++) {
                    
                    // 序号
                    var xuhao = i+1;
                    
                    // zm_id
                    var zm_id = res.qunzmList[i].zm_id;
                    
                    // 状态
                    if(res.qunzmList[i].zm_status == '1'){
                        
                        // 正常
                        var zm_status = '<span class="switch-on" onclick="changeQunzmStatus('+zm_id+');"><span class="press"></span></span>';
                    }else{
                        
                        // 关闭
                        var zm_status = '<span class="switch-off" onclick="changeQunzmStatus('+zm_id+');"><span class="press"></span></span>';
                    }
                    
                    // 计算更新时间距离现在过去多长时间
                    var updatePassTime = getDateDiff(getDateTimeStamp(res.qunzmList[i].zm_update_time));
                    
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
                        daoqiDate = '已到期';
                    }
                    // 群主
                    if(res.qunzmList[i].zm_leader == '' || res.qunzmList[i].zm_leader == null){
                        // 未设置群主
                        var zm_leader = '<span>未设置</span>';
                    }else{
                        // 已设置群主
                        var zm_leader = res.qunzmList[i].zm_leader;
                    }
                    
                    // 列表
                    var $zm_tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+res.qunzmList[i].zm_yz+'</td>' +
                        '   <td>'+res.qunzmList[i].zm_pv+'</td>' +
                        '   <td>'+updatePassTime+'</td>' +
                        '   <td>'+daoqiDate+'</td>' +
                        '   <td>'+zm_leader+'</td>' +
                        '   <td id="qunzima_status_'+zm_id+'">'+zm_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#EditQunZm" onclick="getQunzmInfo(this)" id="'+zm_id+'">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#DelQunZm" onclick="askDelQunzm(this)" id="'+zm_id+'">删除</span>' +
                        '               <span class="dropdown-item" title="重置阈值和访问量为0" onclick="resetQunzm(this)" id="'+zm_id+'">重置</span>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#qunZima .modal-body .qunzima-list tbody").append($zm_tbody_HTML);
                }
            }else{
                
                // 非200状态码
                noZmData('暂无二维码')
            }
            // 群标题
            $("#qun_title_uploadQunZima").text(res.qun_title);
            
      },
      error: function(){
        
        // 发生错误
        errorPage('服务器发生错误！')
      },
    });
    // qun_id
    $("#uploadZmQrcode_qun_id").val(qun_id);
}

// 刷新群子码列表（用于上传成功后获取最新列表）
function freshenQunZmList(qun_id){
    // 刷新
    $.ajax({
        type: "POST",
        url: "./getQunzmList.php?qun_id="+qun_id,
        success: function(res){
            
            // 初始化
            initialize_getQunzmList();
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.qunzmList.length; i++) {
                    
                    // 序号
                    var xuhao = i+1;
                    
                    // zm_id
                    var zm_id = res.qunzmList[i].zm_id;

                    // 状态
                    if(res.qunzmList[i].zm_status == '1'){
                        
                        // 正常
                        var zm_status = '<span class="switch-on" onclick="changeQunzmStatus('+zm_id+');"><span class="press"></span></span>';
                    }else{
                        
                        // 关闭
                        var zm_status = '<span class="switch-off" onclick="changeQunzmStatus('+zm_id+');"><span class="press"></span></span>';
                    }
                    // 计算更新时间距离现在过去多长时间
                    var updatePassTime = getDateDiff(getDateTimeStamp(res.qunzmList[i].zm_update_time));
                    
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
                        daoqiDate = '已到期';
                    }
                    // 群主
                    if(res.qunzmList[i].zm_leader == '' || res.qunzmList[i].zm_leader == null){
                        // 未设置群主
                        var zm_leader = '<span>未设置</span>';
                    }else{
                        // 已设置群主
                        var zm_leader = res.qunzmList[i].zm_leader;
                    }
                    
                    // 列表
                    var $zm_tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+res.qunzmList[i].zm_yz+'</td>' +
                        '   <td>'+res.qunzmList[i].zm_pv+'</td>' +
                        '   <td>'+updatePassTime+'</td>' +
                        '   <td>'+daoqiDate+'</td>' +
                        '   <td>'+zm_leader+'</td>' +
                        '   <td id="qunzima_status_'+zm_id+'">'+zm_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#EditQunZm" onclick="getQunzmInfo(this)" id="'+zm_id+'">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#DelQunZm" onclick="askDelQunzm(this)" id="'+zm_id+'">删除</span>' +
                        '               <span class="dropdown-item" title="重置阈值和访问量为0" onclick="resetQunzm(this)" id="'+zm_id+'">重置</span>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#qunZima .modal-body .qunzima-list tbody").append($zm_tbody_HTML);
                }
            }else{
                
                // 暂无二维码（获取不到群子码列表）
                noZmData('暂无二维码')
            }
            // 群标题
            $("#qun_title_uploadQunZima").text(res.qun_title);
            
      },
      error: function(){
        
        // 发生错误
        errorPage('服务器发生错误！')
      },
    });
}

// 创建群活码
function creatQun(){
    $.ajax({
        type: "POST",
        url: "./creatQun.php",
        data: $('#creatQun').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                // 隐藏modal
                setTimeout('hideModal("CreatQunHm")', 500);
                // 重新加载群列表
                setTimeout('getQunList();', 500);
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


// 编辑群活码
function editQun(){
    $.ajax({
        type: "POST",
        url: "./editQun.php",
        data: $('#editQun').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                // 隐藏modal
                setTimeout('hideModal("EditQunHm")', 500);
                // 重新加载群列表
                setTimeout('getQunList()', 500);
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

// 编辑群二维码（群子码）
function editQunzm(){
    $.ajax({
        type: "POST",
        url: "./editQunzm.php",
        data: $('#editQunzm').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
    
                // 隐藏EditQunZm modal
                hideModal("EditQunZm")
                // 打开qunZima modal
                showModal("qunZima")
                // 重新加载群列表
                freshenQunZmList(res.qun_id)
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

// 询问是否要删除
function askDelQun(e){
    
    // 获取qun_id
    var qun_id = e.id;
    // 将群id添加到button的delQun函数用于传参执行删除
    $('#DelQunHm .modal-footer').html('<button type="button" class="default-btn" onclick="delQun('+qun_id+');">确定删除</button>')
}

// 删除群活码
function delQun(qun_id){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delQun.php?qun_id="+qun_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                // 隐藏modal
                hideModal("DelQunHm");
                
                // 重新加载群列表
                setTimeout('getQunList()', 500);
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

// 获取群详情
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
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 标题
                $('#qun_title_edit').val(res.qunInfo.qun_title);
                
                // 群备注
                $('#qun_beizhu_edit').val(res.qunInfo.qun_beizhu);
                
                // 获取域名列表
                getDomainNameList('edit')
                
                // 获取当前设置的域名
                $("#qun_rkym_edit").append('<option value="'+res.qunInfo.qun_rkym+'">'+res.qunInfo.qun_rkym+'</option>');
                $("#qun_ldym_edit").append('<option value="'+res.qunInfo.qun_ldym+'">'+res.qunInfo.qun_ldym+'</option>');
                $("#qun_dlym_edit").append('<option value="'+res.qunInfo.qun_dlym+'">'+res.qunInfo.qun_dlym+'</option>');
                
                // 活码状态
                if(res.qunInfo.qun_status == '1'){
                    $("#qun_status_edit").html('<option value="1">正常</option><option value="2">停用</option>');
                }else{
                    $("#qun_status_edit").html('<option value="2">停用</option><option value="1">正常</option>');
                }
                
                // 顶部扫码安全提示
                if(res.qunInfo.qun_safety == '1'){
                    $("#qun_safety_edit").html('<option value="1">显示</option><option value="2">隐藏</option>');
                }else{
                    $("#qun_safety_edit").html('<option value="2">隐藏</option><option value="1">显示</option>');
                }
                
                // 客服
                if(res.qunInfo.qun_kf_status == '1'){
                    // 显示客服二维码
                    showKfQrcode(res.qunInfo.qun_kf);
                }else{
                    // 隐藏客服二维码
                    hideKfQrcode(res.qunInfo.qun_kf);
                }
                
                // 去重功能
                if(res.qunInfo.qun_qc == '1'){
                    $("#qun_qc_edit").html('<option value="1">开启</option><option value="2">关闭</option>');
                }else{
                    $("#qun_qc_edit").html('<option value="2">关闭</option><option value="1">开启</option>');
                }

                // qun_id
                $('#qun_id_edit').val(qun_id);
                // qun_kf
                $('#qun_kf_edit').val(res.qunInfo.qun_kf);
                            
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

// 获取群子码详情
function getQunzmInfo(e){
    
    // 获取zm_id
    var zm_id = e.id;
    
    // 根据zm_id获取群详情
    $.ajax({
        type: "GET",
        url: "./getQunzmInfo.php?zm_id="+zm_id,
        success: function(res){
            
            // 隐藏群子码管理面板
            hideModal('qunZima');

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
                $('#EditQunZm .modal-body .qrcode_preview').css('display','block');
                $('#EditQunZm .modal-body .upload_file').css('display','none');
                var $previewQrcode_HTML = $(
                    '<img src="'+res.qunzmInfo.zm_qrcode+'" class="qrcode" />' +
                    '<p class="newUpload" onclick="newUpload();">重新上传</p>'
                );
                $('#EditQunZm .modal-body .qrcode_preview').html($previewQrcode_HTML);
                
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
                            $("#qun_rkym").append('<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>');
                        }
                    }else{
                        $("#qun_rkym").append('<option value="">暂无入口域名</option>');
                    }
                    // 判断ldymList是否有域名
                    if(res.ldymList.length>0){
                        for (var i=0; i<res.ldymList.length; i++) {
                            $("#qun_ldym").append('<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>');
                        }
                    }else{
                        $("#qun_ldym").append('<option value="">暂无落地域名</option>');
                    }
                    // 判断dlymList是否有域名
                    if(res.dlymList.length>0){
                        for (var i=0; i<res.dlymList.length; i++) {
                            $("#qun_dlym").append('<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>');
                        }
                    }else{
                        $("#qun_dlym").append('<option value="">暂无短链域名</option>');
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
                            $("#qun_rkym_edit").append('<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>');
                        }
                    }else{
                        $("#qun_rkym_edit").append('<option value="">暂无入口域名</option>');
                    }
                    // 判断ldymList是否有域名
                    if(res.ldymList.length>0){
                        for (var i=0; i<res.ldymList.length; i++) {
                            $("#qun_ldym_edit").append('<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>');
                        }
                    }else{
                        $("#qun_ldym_edit").append('<option value="">暂无落地域名</option>');
                    }
                    // 判断dlymList是否有域名
                    if(res.dlymList.length>0){
                        for (var i=0; i<res.dlymList.length; i++) {
                            $("#qun_dlym_edit").append('<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>');
                        }
                    }else{
                        $("#qun_dlym_edit").append('<option value="">暂无短链域名</option>');
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

// 询问是否要删除群子码
function askDelQunzm(e){
    
    // 获取zm_id
    var zm_id = e.id;
    
    // 将群子码列表modal隐藏
    hideModal("qunZima");
    
    // 将群zm_id添加到button的delQunzm函数用于传参执行删除
    $('#DelQunZm .modal-footer').html('<button type="button" class="default-btn" onclick="delQunzm('+zm_id+');">确定删除</button>')
}

// 删除群子码
function delQunzm(zm_id){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delQunzm.php?zm_id="+zm_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                // 隐藏DelQunZm modal
                hideModal("DelQunZm")
                
                // 打开qunZima modal
                showModal("qunZima")
                
                // 刷新子码列表
                freshenQunZmList(res.qun_id)
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

// 重置阈值和访问量为0
function resetQunzm(e){
    
    // zm_id
    var zm_id = e.id;
    
    // 执行重置
    $.ajax({
        type: "GET",
        url: "./resetQunzm.php?zm_id="+zm_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){

                // 刷新子码列表（resetQunzm.php需返回qun_id）
                freshenQunZmList(res.qun_id)
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

// 分享群活码
function shareQun(qun_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    // 分享
    $.ajax({
        type: "GET",
        url: "./shareQun.php?qun_id="+qun_id,
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

// 切换switch（changeQunStatus）
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

// 切换switch（changeQunzmStatus）
function changeQunzmStatus(zmid){

    // 修改
    $.ajax({
        type: "POST",
        url: "./changeQunzmStatus.php?zm_id="+zmid,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 更新switch状态
                showQunzmSwitchNewStatus(res.zm_status,zmid);
                
                // 显示切换结果
                showSuccessResult(res.msg);
                
            }else{
                
                // 非200状态码操作结果
                showErrorResult(res.msg);
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

// 为了便于继续操作二维码列表
// 编辑群二维码的编辑框关闭后
// 点击右上角X会立即打开二维码列表
function hideEditQunZm(){
    hideModal('EditQunZm');
    showModal('qunZima')
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
    $('#EditQunHm .modal-body .upload_file').css('display','block');
    $('#EditQunHm .modal-body .qrcode_preview').css('display','none');
    $('#qun_kf_edit').val('');
    $('#EditQunZm .modal-body .upload_file').css('display','block');
    $('#EditQunZm .modal-body .qrcode_preview').css('display','none');
    $('#zm_qrcode_edit').val('');
}

// 显示客服入口
function showKfQrcode(qun_kf){
    
    // 开关选项
    $("#qun_kf_status_edit").html('<option value="1">显示客服入口</option><option value="2">隐藏客服入口</option>');
    
    // 还没上传过客服二维码
    if(qun_kf == ''){
       
        // 图片预览隐藏，上传控件显示
        $('#EditQunHm .modal-body .qrcode_preview').css('display','none');
        $('#EditQunHm .modal-body .upload_file').css('display','block');
    }else{
       
        // 上传过客服二维码
        // 图片预览显示，上传控件隐藏
        $('#EditQunHm .modal-body .qrcode_preview').css('display','block');
        $('#EditQunHm .modal-body .upload_file').css('display','none');
        var $previewQrcode_HTML = $(
            '<img src="'+qun_kf+'" class="qrcode" />' +
            '<p class="newUpload" onclick="newUpload();">重新上传</p>'
        );
        $('#EditQunHm .modal-body .qrcode_preview').html($previewQrcode_HTML);
    }
}

// 隐藏客服入口
function hideKfQrcode(qun_kf){
    
    // 开关选项
    $("#qun_kf_status_edit").html('<option value="2">隐藏客服入口</option><option value="1">显示客服入口</option>');
    
    // 还没上传过客服二维码
    if(qun_kf == ''){
        
        // 图片预览隐藏，上传控件隐藏
        $('#EditQunHm .modal-body .qrcode_preview').css('display','none');
        $('#EditQunHm .modal-body .upload_file').css('display','none');
    }else{
        
        // 上传过客服二维码
        // 图片预览隐藏，上传控件隐藏
        $('#EditQunHm .modal-body .qrcode_preview').css('display','none');
        $('#EditQunHm .modal-body .upload_file').css('display','none');
        
        // 提前将图片预览加载出来便于切换的时候能显示预览
        var $previewQrcode_HTML = $(
            '<img src="'+qun_kf+'" class="qrcode" />' +
            '<p class="newUpload" onclick="newUpload();">重新上传</p>'
        );
        $('#EditQunHm .modal-body .qrcode_preview').html($previewQrcode_HTML);
    }
}

// 监听客服显示和隐藏的切换状态
function getKfOptionSelectVal(){
    
    if($('#qun_kf_status_edit').val() == '1'){
        
        // 还没上传过客服二维码
        // 图片预览隐藏，上传控件显示
        if($('#qun_kf_edit').val() == ''){
            
            $('#EditQunHm .modal-body .upload_file').css('display','block')
            $('#EditQunHm .modal-body .qrcode_preview').css('display','none');
        }else{
            
            // 上传过客服二维码
            // 图片预览显示，上传控件隐藏
            $('#EditQunHm .modal-body .upload_file').css('display','none')
            $('#EditQunHm .modal-body .qrcode_preview').css('display','block');
        }
    }else{
        // 图片预览隐藏，上传控件隐藏
        $('#EditQunHm .modal-body .upload_file').css('display','none')
        $('#EditQunHm .modal-body .qrcode_preview').css('display','none');
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

// 没有获取到群子码
function noZmData(text){
    $("#qunZima .loading").css('display','block');
    $("#qunZima .loading").html('<img src="../../static/img/warningIcon.png"/><br/><p>'+text+'</p>');
}

// 初始化（获取群子码列表，getQunzmList）
function initialize_getQunzmList(){
    $("#qunZima .modal-body .qunzima-list tbody").empty('');
    $("#qunZima .loading").css('display','none');
    $("#uploadZmQrcode").val('');
}

// 初始化（编辑群活码上传控件）
function initialize_uploadKf(){
    $('#qun_kf_edit').val('');
    $('#selectQrcode').val('');
    $('#EditQunHm .modal-body .upload_file').css('display','block');
    $('#EditQunHm .modal-body .qrcode_preview').css('display','none');
    $('#EditQunHm .modal-body .qrcode_preview').html('');
}

// 初始化（获取群活码列表，getQunList）
function initialize_getQunList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'creat'){
        
        // 将所有值清空
        $("#qun_title").val('');
        $("#qun_rkym").empty();
        $("#qun_ldym").empty();
        $("#qun_dlym").empty();
        hideResult();
        
        // 设置默认值
        $("#qun_rkym").append('<option value="">选择入口域名</option>');
        $("#qun_ldym").append('<option value="">选择落地域名</option>');
        $("#qun_dlym").append('<option value="">选择短链域名</option>');
    }else if(module == 'edit'){
        
        // 将所有值清空
        $("#qun_rkym_edit").empty();
        $("#qun_ldym_edit").empty();
        $("#qun_dlym_edit").empty();
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

// 跳转到登录界面
function redirectLoginPage(second){
    
    // second毫秒后跳转
    setTimeout('location.href="../login/";', second);
}

// 关闭操作反馈
function hideResult(){
    $("#app .result .success").css("display","none");
    $("#app .result .error").css("display","none");
    $("#app .result .success").text('');
    $("#app .result .error").text('');
}

// 显示子码切换后的状态
function showQunzmSwitchNewStatus(status,zmid){
    if(status == 1){
        $('#qunzima_status_'+zmid).html('<span class="switch-on" onclick="changeQunzmStatus('+zmid+');"><span class="press"></span></span>');  
    }else{
        $('#qunzima_status_'+zmid).html('<span class="switch-off" onclick="changeQunzmStatus('+zmid+');"><span class="press"></span></span>');
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

// 获取URL参数
function queryURLParams(url) {
    var pattern = /(\w+)=(\w+)/ig;
    var parames = {};
    url.replace(pattern, ($, $1, $2) => {
        parames[$1] = $2;
    });
    return parames;
}
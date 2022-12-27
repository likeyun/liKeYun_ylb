
// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的客服码列表
        getKfList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getKfList();
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

// 获取客服码列表
function getKfList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getKfList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getKfList.php?p="+pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getKfList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>序号</th>' +
                '   <th>标题</th>' +
                '   <th>在线状态</th>' +
                '   <th>循环模式</th>' +
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
                for (var i=0; i<res.kfList.length; i++) {
                    
                    // 数据判断并处理
                    // （1）序号
                    var xuhao = i+1;
                    
                    // 客服ID
                    var kf_id = res.kfList[i].kf_id;
                    
                    // （2）标题
                    var kf_title = res.kfList[i].kf_title;
                    
                    // （3）状态
                    if(res.kfList[i].kf_status == '1'){
                        
                        // 正常
                        var kf_status = '<span class="switch-on" id="'+kf_id+'" onclick="changeKfStatus(this);"><span class="press"></span></span>';
                    }else{
                        
                        // 关闭
                        var kf_status = '<span class="switch-off" id="'+kf_id+'" onclick="changeKfStatus(this);"><span class="press"></span></span>';
                    }
                    
                    // （4）在线状态
                    if(res.kfList[i].kf_online == '1'){
                        
                        // 显示
                        var kf_online = '<span>显示</span>';
                    }else{
                        
                        // 隐藏
                        var kf_online = '<span>隐藏</span>';
                    }
                    
                    // （5）循环模式
                    if(res.kfList[i].kf_model == '1'){
                        
                        // 顺序模式
                        var kf_model = '阈值';
                    }else{
                        
                        // 随机模式
                        var kf_model = '随机';
                    }
                    
                    // （6）顶部扫码安全提示
                    if(res.kfList[i].kf_safety == '1'){
                        
                        // 开启
                        var kf_safety = '显示';
                    }else{
                        
                        // 关闭
                        var kf_safety = '隐藏';
                    }
                    
                    // （7）创建时间
                    var kf_creat_time = res.kfList[i].kf_creat_time;
                    
                    // （8）访问量
                    var kf_pv = res.kfList[i].kf_pv;
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+kf_title+'</td>' +
                        '   <td>'+kf_online+'</td>' +
                        '   <td>'+kf_model+'</td>' +
                        '   <td>'+kf_safety+'</td>' +
                        '   <td>'+kf_creat_time+'</td>' +
                        '   <td>'+kf_pv+'</td>' +
                        '   <td>'+kf_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#shareKfHm" onclick="shareKf('+kf_id+')">分享</a>' +
                        '               <a class="dropdown-item" href="javascript:;" data-toggle="modal" data-target="#EditKfHm" onclick="getKfInfo(this)" id="'+kf_id+'">编辑</a>' +
                        '               <a class="dropdown-item" href="javascript:;" id="'+kf_id+'" title="上传客服二维码" data-toggle="modal" data-target="#kfZima" onclick="getKfzmList(this);">上传</a>' +
                        '               <a class="dropdown-item" href="javascript:;" id="'+kf_id+'" data-toggle="modal" data-target="#DelKfHm" onclick="askDelKf(this)">删除</a>' +
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
                    var $kfFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="'+res.nextpage+'" onclick="getFenye(this);" title="下一页"><img src="../../static/img/nextPage.png" /></button></li>' +
                    '   <li><button id="'+res.allpage+'" onclick="getFenye(this);" title="最后一页"><img src="../../static/img/lastPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else if(res.page == res.allpage){
                    
                    // 当前页码=总页码
                    // 代表这是最后一页
                    var $kfFenye_HTML = $(
                    '<ul>' +
                    '   <li><button id="1" onclick="getFenye(this);" title="第一页"><img src="../../static/img/firstPage.png" /></button></li>' +
                    '   <li><button id="'+res.prepage+'" onclick="getFenye(this);" title="上一页"><img src="../../static/img/prevPage.png" /></button></li>' +
                    '</ul>'
                    );
                    $("#right .data-card .fenye").css("display","block");
                }else{
                    
                    var $kfFenye_HTML = $(
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
                $("#right .data-card .fenye").html($kfFenye_HTML);
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
    getKfList(pageNum);
}

// 切换switch（changeKfStatus）
function changeKfStatus(e){

    // 修改
    $.ajax({
        type: "POST",
        url: "./changeKfStatus.php?kf_id="+e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 刷新
                getKfList();
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

// 获取客服子码列表（二维码列表）
function getKfzmList(e) {
    
    // 获取kf_id
    var kf_id = e.id;
    
    // 表头
    var $zm_thead_HTML = $(
        '<tr>' +
        '   <th>序号</th>' +
        '   <th>阈值</th>' +
        '   <th>访问量</th>' +
        '   <th>更新</th>' +
        '   <th>微信号</th>' +
        '   <th>状态</th>' +
        '   <th style="text-align: right;">操作</th>' +
        '</tr>'
    );
    $("#kfZima .modal-body .kfzima-list thead").html($zm_thead_HTML);
    
    // 获取
    $.ajax({
        type: "POST",
        url: "./getKfzmList.php?kf_id="+kf_id,
        success: function(res){
            
            // 初始化
            initialize_kfzimaList();
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.kfzmList.length; i++) {
                    
                    // ID
                    var zm_id = res.kfzmList[i].zm_id;
                    
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）状态
                    if(res.kfzmList[i].zm_status == '1'){
                        
                        // 正常
                        var zm_status = '<span class="switch-on" onclick="changeKfzmStatus('+zm_id+');"><span class="press"></span></span>';
                    }else{
                        
                        // 关闭
                        var zm_status = '<span class="switch-off" onclick="changeKfzmStatus('+zm_id+');"><span class="press"></span></span>';
                    }
                    
                    // 阈值
                    var zm_yz = res.kfzmList[i].zm_yz;
                    
                    // 访问量
                    var zm_pv = res.kfzmList[i].zm_pv;
                    
                    // 计算更新时间距离现在过去多长时间
                    var updatePassTime = getDateDiff(getDateTimeStamp(res.kfzmList[i].zm_update_time));
                    
                    // 微信号
                    if(res.kfzmList[i].zm_num == '' || res.kfzmList[i].zm_num == null){
                        // 未设置
                        var zm_num = '<span>未设置</span>';
                    }else{
                        // 已设置
                        var zm_num = res.kfzmList[i].zm_num;
                    }
                    
                    // 列表
                    var $zm_tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+zm_yz+'</td>' +
                        '   <td>'+zm_pv+'</td>' +
                        '   <td>'+updatePassTime+'</td>' +
                        '   <td>'+zm_num+'</td>' +
                        '   <td id="kfzima_status_'+zm_id+'">'+zm_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#EditKfZm" onclick="getKfzmInfo(this)" id="'+zm_id+'">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#DelKfZm" onclick="askDelKfzm(this)" id="'+zm_id+'">删除</span>' +
                        '               <span class="dropdown-item" title="重置阈值和访问量为0" onclick="resetKfzm(this)" id="'+zm_id+'">重置</span>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#kfZima .modal-body .kfzima-list tbody").append($zm_tbody_HTML);
                }
            }else{
                
                // 非200状态码
                noZmData('暂无二维码')
            }
            // 客服码标题
            $("#kf_title_uploadKfZima").text(res.kf_title);
            
      },
      error: function(){
        
        // 发生错误
        errorPage('服务器发生错误！')
      },
    });
    // kf_id
    $("#uploadZmQrcode_kf_id").val(kf_id);
}

// 刷新客服子码列表（二维码列表）
// （用于上传、编辑等操作成功后获取最新列表）
function freshenKfzmList(kf_id){
    
    // 获取
    $.ajax({
        type: "POST",
        url: "./getKfzmList.php?kf_id="+kf_id,
        success: function(res){
            
            // 初始化
            initialize_kfzimaList();
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.kfzmList.length; i++) {
                    
                    // ID
                    var zm_id = res.kfzmList[i].zm_id;
                    
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）状态
                    if(res.kfzmList[i].zm_status == '1'){
                        
                        // 正常
                        var zm_status = '<span class="switch-on" onclick="changeKfzmStatus('+zm_id+');"><span class="press"></span></span>';
                    }else{
                        
                        // 关闭
                        var zm_status = '<span class="switch-off" onclick="changeKfzmStatus('+zm_id+');"><span class="press"></span></span>';
                    }
                    
                    // 阈值
                    var zm_yz = res.kfzmList[i].zm_yz;
                    
                    // 访问量
                    var zm_pv = res.kfzmList[i].zm_pv;
                    
                    // 计算更新时间距离现在过去多长时间
                    var updatePassTime = getDateDiff(getDateTimeStamp(res.kfzmList[i].zm_update_time));
                    
                    // 群主
                    if(res.kfzmList[i].zm_num == '' || res.kfzmList[i].zm_num == null){
                        // 未设置群主
                        var zm_num = '<span>未设置</span>';
                    }else{
                        // 已设置群主
                        var zm_num = res.kfzmList[i].zm_num;
                    }
                    
                    // 列表
                    var $zm_tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+zm_yz+'</td>' +
                        '   <td>'+zm_pv+'</td>' +
                        '   <td>'+updatePassTime+'</td>' +
                        '   <td>'+zm_num+'</td>' +
                        '   <td id="kfzima_status_'+zm_id+'">'+zm_status+'</td>' +
                        '   <td class="dropdown-td">' +
                        '       <div class="dropdown">' +
                        '    	    <button type="button" class="dropdown-btn" data-toggle="dropdown">•••</button>' +
                        '           <div class="dropdown-menu">' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#EditKfZm" onclick="getKfzmInfo(this)" id="'+zm_id+'">编辑</span>' +
                        '               <span class="dropdown-item" data-toggle="modal" data-target="#DelKfZm" onclick="askDelKfzm(this)" id="'+zm_id+'">删除</span>' +
                        '               <span class="dropdown-item" title="重置阈值和访问量为0" onclick="resetKfzm(this)" id="'+zm_id+'">重置</span>' +
                        '           </div>' +
                        '       </div>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#kfZima .modal-body .kfzima-list tbody").append($zm_tbody_HTML);
                }
            }else{
                
                // 非200状态码
                noZmData('暂无二维码')
            }
      },
      error: function(){
        
        // 发生错误
        errorPage('服务器发生错误！')
      },
    });
}

// 切换switch（changeKfzmStatus）
function changeKfzmStatus(zmid){

    // 修改
    $.ajax({
        type: "POST",
        url: "./changeKfzmStatus.php?zm_id="+zmid,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 更新switch状态
                showKfzmSwitchNewStatus(res.zm_status,zmid);
                
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

// 创建客服码
function creatKf(){
    $.ajax({
        type: "POST",
        url: "./creatKf.php",
        data: $('#creatKf').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                // 隐藏modal
                setTimeout('hideModal("CreatKfHm")', 500);
                // 重新加载客服码列表
                setTimeout('getKfList();', 500);
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


// 编辑客服码
function editKf(){
    $.ajax({
        type: "POST",
        url: "./editKf.php",
        data: $('#editKf').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                // 隐藏modal
                setTimeout('hideModal("EditKfHm")', 500);
                // 重新加载客服码列表
                setTimeout('getKfList();', 500);
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

// 编辑群二维码（客服子码）
function editKfzm(){
    $.ajax({
        type: "POST",
        url: "./editKfzm.php",
        data: $('#editKfzm').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
    
                // 隐藏EditKfZm modal
                hideModal("EditKfZm")
                
                // 打开kfZima modal
                showModal("kfZima")
                
                // 重新加载客服子码列表
                freshenKfzmList(res.kf_id)
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
function askDelKf(e){
    
    // 获取kf_id
    var kf_id = e.id;
    // 将群id添加到button的delQun函数用于传参执行删除
    $('#DelKfHm .modal-footer').html('<button type="button" class="default-btn" onclick="delKf('+kf_id+');">确定删除</button>')
}

// 删除客服码
function delKf(kf_id){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delKf.php?kf_id="+kf_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                // 隐藏modal
                hideModal("DelKfHm");
                // 重新加载群列表
                setTimeout('getKfList()', 500);
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

// 获取客服码详情
function getKfInfo(e){

    // 获取kf_id
    var kf_id = e.id;
    
    // 根据kf_id获取客服码详情
    $.ajax({
        type: "GET",
        url: "./getKfInfo.php?kf_id="+kf_id,
        success: function(res){

            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // （1）标题
                $('#kf_title_edit').val(res.kfInfo.kf_title);
                
                // 备注信息
                $('#kf_beizhu_edit').val(res.kfInfo.kf_beizhu);
                
                // 获取域名列表
                getDomainNameList('edit')
                
                // 获取当前设置的域名
                $("#kf_rkym_edit").append('<option value="'+res.kfInfo.kf_rkym+'">'+res.kfInfo.kf_rkym+'</option>');
                $("#kf_ldym_edit").append('<option value="'+res.kfInfo.kf_ldym+'">'+res.kfInfo.kf_ldym+'</option>');
                $("#kf_dlym_edit").append('<option value="'+res.kfInfo.kf_dlym+'">'+res.kfInfo.kf_dlym+'</option>');
                
                
                // 活码状态
                if(res.kfInfo.kf_status == '1'){
                    $("#kf_status_edit").html('<option value="1">正常</option><option value="2">停用</option>');
                }else{
                    $("#kf_status_edit").html('<option value="2">停用</option><option value="1">正常</option>');
                }
                
                // 循环模式
                if(res.kfInfo.kf_model == '1'){
                    $("#kf_model_edit").html('<option value="1">阈值模式</option><option value="2">随机模式</option>');
                }else{
                    $("#kf_model_edit").html('<option value="2">随机模式</option><option value="1">阈值模式</option>');
                }
                
                // 在线状态
                if(res.kfInfo.kf_online == '1'){
                    $("#kf_online_edit").html('<option value="1">显示</option><option value="2">隐藏</option>');
                }else{
                    $("#kf_online_edit").html('<option value="2">隐藏</option><option value="1">显示</option>');
                }
                
                // 顶部扫码安全提示
                if(res.kfInfo.kf_safety == '1'){
                    $("#kf_safety_edit").html('<option value="1">显示</option><option value="2">隐藏</option>');
                }else{
                    $("#kf_safety_edit").html('<option value="2">隐藏</option><option value="1">显示</option>');
                }
                
                // kf_id
                $('#kf_id_edit').val(kf_id);
                            
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

// 获取客服子码详情
function getKfzmInfo(e){
    
    // 获取zm_id
    var zm_id = e.id;
    
    // 根据zm_id获取客服子码详情
    $.ajax({
        type: "GET",
        url: "./getKfzmInfo.php?zm_id="+zm_id,
        success: function(res){
            
            // 隐藏群子码管理面板
            hideModal('kfZima');

            if(res.code == 200){
                
                // 根据循环模式来决定是否需要显示阈值输入框
                if(res.kf_model == 1){
                    
                    // 阈值模式
                    $('#zm_yz_input').css('display','block');
                }else{
                    
                    // 随机模式
                    $('#zm_yz_input').css('display','none');
                }
                
                // （1）阈值
                $('#zm_yz_edit').val(res.kfzmInfo.zm_yz);
                
                // 客服微信号
                $('#zm_num').val(res.kfzmInfo.zm_num);
                
                // 客服二维码使用状态
                if(res.kfzmInfo.zm_status == '1'){
                    
                    // 正常
                    $("#zm_status_edit").html('<option value="1">正常</option><option value="2">停用</option>');
                }else{
                    
                    // 停用
                    $("#zm_status_edit").html('<option value="2">停用</option><option value="1">正常</option>');
                }
                
                // 获取客服二维码
                $('#EditKfZm .modal-body .qrcode_preview').css('display','block');
                $('#EditKfZm .modal-body .upload_file').css('display','none');
                var $previewQrcode_HTML = $(
                    '<img src="'+res.kfzmInfo.zm_qrcode+'" class="qrcode" />' +
                    '<p class="newUpload" onclick="newUpload();">重新上传</p>'
                );
                $('#EditKfZm .modal-body .qrcode_preview').html($previewQrcode_HTML);
                
                // zm_id
                $('#zm_id_edit').val(zm_id);
                
                // kf_model_edit
                $('#kf_model_editzm').val(res.kf_model);
                
                // zm_qrcode_edit
                $('#zm_qrcode_edit').val(res.kfzmInfo.zm_qrcode);
                            
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
                            $("#kf_rkym").append('<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>');
                        }
                    }else{
                        $("#kf_rkym").append('<option value="">暂无入口域名</option>');
                    }
                    // 判断ldymList是否有域名
                    if(res.ldymList.length>0){
                        for (var i=0; i<res.ldymList.length; i++) {
                            $("#kf_ldym").append('<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>');
                        }
                    }else{
                        $("#kf_ldym").append('<option value="">暂无落地域名</option>');
                    }
                    // 判断dlymList是否有域名
                    if(res.dlymList.length>0){
                        for (var i=0; i<res.dlymList.length; i++) {
                            $("#kf_dlym").append('<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>');
                        }
                    }else{
                        $("#kf_dlym").append('<option value="">暂无短链域名</option>');
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
                            $("#kf_rkym_edit").append('<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>');
                        }
                    }else{
                        $("#kf_rkym_edit").append('<option value="">暂无入口域名</option>');
                    }
                    // 判断ldymList是否有域名
                    if(res.ldymList.length>0){
                        for (var i=0; i<res.ldymList.length; i++) {
                            $("#kf_ldym_edit").append('<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>');
                        }
                    }else{
                        $("#kf_ldym_edit").append('<option value="">暂无落地域名</option>');
                    }
                    // 判断dlymList是否有域名
                    if(res.dlymList.length>0){
                        for (var i=0; i<res.dlymList.length; i++) {
                            $("#kf_dlym_edit").append('<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>');
                        }
                    }else{
                        $("#kf_dlym_edit").append('<option value="">暂无短链域名</option>');
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

// 询问是否要删除客服子码
function askDelKfzm(e){
    
    // 获取zm_id
    var zm_id = e.id;
    
    // 将客服子码列表modal隐藏
    hideModal("kfZima");
    
    // 将群zm_id添加到button的delKfzm函数用于传参执行删除
    $('#DelKfZm .modal-footer').html('<button type="button" class="default-btn" onclick="delKfzm('+zm_id+');">确定删除</button>')
}

// 删除客服子码
function delKfzm(zm_id){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delKfzm.php?zm_id="+zm_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                // 隐藏DelKfZm modal
                hideModal("DelKfZm")
                
                // 打开kfZima modal
                showModal("kfZima")
                
                // 刷新客服子码列表
                freshenKfzmList(res.kf_id)
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
function resetKfzm(e){
    
    // zm_id
    var zm_id = e.id;
    
    // 执行重置
    $.ajax({
        type: "GET",
        url: "./resetKfzm.php?zm_id="+zm_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){

                // 刷新子码列表（resetKfzm.php需返回kf_id）
                freshenKfzmList(res.kf_id)
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

// 分享客服码
function shareKf(kf_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    // 分享
    $.ajax({
        type: "GET",
        url: "./shareKf.php?kf_id="+kf_id,
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
    $('#kf_kf_edit').val('');
    $('#EditKfZm .modal-body .upload_file').css('display','block');
    $('#EditKfZm .modal-body .qrcode_preview').css('display','none');
    $('#zm_qrcode_edit').val('');
}

// 显示客服二维码
function showKfQrcode(kf_kf){
    // 开关选项
    $("#kf_kf_status_edit").html('<option value="1">显示客服二维码</option><option value="2">隐藏客服二维码</option>');
    // 还没上传过客服二维码
    if(kf_kf == ''){
        // 图片预览隐藏，上传控件显示
        $('#EditQunHm .modal-body .wxqrcode_preview').css('display','none');
        $('#EditQunHm .modal-body .upload_kf').css('display','block');
    }else{
        // 上传过客服二维码
        // 图片预览显示，上传控件隐藏
        $('#EditQunHm .modal-body .wxqrcode_preview').css('display','block');
        $('#EditQunHm .modal-body .upload_kf').css('display','none');
        var $previewQrcode_HTML = $(
            '<img src="'+kf_kf+'" class="wxqrcode" />' +
            '<p class="newUpload" onclick="newUpload();">重新上传</p>'
        );
        $('#EditQunHm .modal-body .wxqrcode_preview').html($previewQrcode_HTML);
    }
}

// 隐藏客服二维码
function hideKfQrcode(kf_kf){
    // 开关选项
    $("#kf_kf_status_edit").html('<option value="2">隐藏客服二维码</option><option value="1">显示客服二维码</option>');
    // 还没上传过客服二维码
    if(kf_kf == ''){
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
            '<img src="'+kf_kf+'" class="wxqrcode" />' +
            '<p class="newUpload" onclick="newUpload();">重新上传</p>'
        );
        $('#EditQunHm .modal-body .wxqrcode_preview').html($previewQrcode_HTML);
    }
}

// 监听客服显示和隐藏的切换状态
function getKfOptionSelectVal(){
    
    if($('#kf_kf_status_edit').val() == '1'){
        // 还没上传过客服二维码
        // 图片预览隐藏，上传控件显示
        if($('#kf_kf_edit').val() == ''){
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
    $('#kf_kf_edit').val('');
    $('#uploadKfQrcode').val('');
    $('#EditQunHm .modal-body .upload_kf').css('display','block');
    $('#EditQunHm .modal-body .wxqrcode_preview').css('display','none');
    $('#EditQunHm .modal-body .wxqrcode_preview').html('');
}

// 初始化（getKfList获取客服列表）
function initialize_getKfList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'creat'){
        
        // 将所有值清空
        $("#kf_title").val('');
        $("#kf_rkym").empty();
        $("#kf_ldym").empty();
        $("#kf_dlym").empty();
        hideResult();
        
        // 设置默认值
        $("#kf_rkym").append('<option value="">选择入口域名</option>');
        $("#kf_ldym").append('<option value="">选择落地域名</option>');
        $("#kf_dlym").append('<option value="">选择短链域名</option>');
    }else if(module == 'edit'){
        
        // 将所有值清空
        $("#kf_rkym_edit").empty();
        $("#kf_ldym_edit").empty();
        $("#kf_dlym_edit").empty();
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

// 显示子码切换后的状态
function showKfzmSwitchNewStatus(status,zmid){
    if(status == 1){
        $('#kfzima_status_'+zmid).html('<span class="switch-on" onclick="changeKfzmStatus('+zmid+');"><span class="press"></span></span>');  
    }else{
        $('#kfzima_status_'+zmid).html('<span class="switch-off" onclick="changeKfzmStatus('+zmid+');"><span class="press"></span></span>');
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

// 跳转到登录界面
function redirectLoginPage(second){
    
    // second毫秒后跳转
    setTimeout('location.href="../login/";', second);
}
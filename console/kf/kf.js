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
    
    // clipboard插件
    var clipboard = new ClipboardJS('#shareKf .modal-footer button');
    clipboard.on('success', function(e) {
        
        // 复制成功
        $('#shareKf .modal-footer button').text('已复制');
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
                // 账号及版本信息
                var $account = $(
                    '<div class="version">'+res.version+'</div>' +
                    '<div class="user_name">'+res.user_name+' <span onclick="exitLogin();" class="exitLogin">退出</span></div>'
                );
                $(".left .account").html($account);
                
                // 初始化
                initialize_Login('login');
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
            showNotification('getLoginStatus.php发生错误');
        }
    });
}

// 登录后的一些初始化
function initialize_Login(loginStatus){
    
    if(loginStatus == 'login'){
        
        // 显示
        $('#button-view').css('display','block');
    }else{
        
        // 隐藏
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
        
        // 设置路由
        setRouter(pageNum);
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
                '   <th>ID</th>' +
                '   <th>标题</th>' +
                '   <th>备注</th>' +
                '   <th>循环模式</th>' +
                '   <th>创建时间</th>' +
                '   <th>访问量</th>' +
                '   <th>去重</th>' +
                '   <th>其它</th>' +
                '   <th>状态</th>' +
                '   <th>操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.kfList.length; i++) {
                    
                    // 客服ID
                    var kf_id = res.kfList[i].kf_id;
                    
                    // （2）标题
                    var kf_title = res.kfList[i].kf_title;
                    
                    // （3）状态
                    if(res.kfList[i].kf_status == '1'){
                        
                        // 正常
                        var kf_status = 
                        '<span class="switch-on" id="'+kf_id+'" onclick="changeKfStatus(this);">'+
                        '   <span class="press"></span>'+
                        '</span>';
                    }else{
                        
                        // 关闭
                        var kf_status = 
                        '<span class="switch-off" id="'+kf_id+'" onclick="changeKfStatus(this);">'+
                        '   <span class="press"></span>'+
                        '</span>';
                    }
                    
                    // 去重
                    if(res.kfList[i].kf_qc == '1'){
                        
                        // 开
                        var kf_qc = 
                        '<span class="switch-on" id="'+kf_id+'" onclick="changeKfQc(this);">'+
                        '   <span class="press"></span>'+
                        '</span>';
                    }else{
                        
                        // 关
                        var kf_qc = 
                        '<span class="switch-off" id="'+kf_id+'" onclick="changeKfQc(this);">'+
                        '   <span class="press"></span>'+
                        '</span>';
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
                    
                    // 今天访问量
                    var kf_today_pv = JSON.parse(res.kfList[i].kf_today_pv.toString()).pv;
                    var kf_today_date = JSON.parse(res.kfList[i].kf_today_pv.toString()).date;
                    
                    // 获取日期
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    const todayDate = `${year}-${month}-${day}`;
                    
                    if(kf_today_date == todayDate){
                        
                        // 日期一致
                        // 显示今天的访问量
                        var kf_pv_today = kf_today_pv;
                    }else{
                        
                        // 日期不一致
                        // 显示0
                        var kf_pv_today = 0;
                    }
                    
                    // 仅限后台可见的备注信息
                    let kf_beizhu_ht;
                    if(res.kfList[i].kf_beizhu_ht){
                        
                        // 有数据
                        kf_beizhu_ht = res.kfList[i].kf_beizhu_ht;
                    }else{
                        
                        // 无数据
                        kf_beizhu_ht = '-';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr style="white-space: nowrap;">' +
                        '   <td>'+kf_id+'</td>' +
                        '   <td>'+kf_title+'</td>' +
                        '   <td>'+kf_beizhu_ht+'</td>' +
                        '   <td><span class="light-tag">'+kf_model+'</span></td>' +
                        '   <td>'+kf_creat_time+'</td>' +
                        '   <td>' +
                        '       <div>总访问量：'+kf_pv+'</div>' + 
                        '       <div>今天访问：'+kf_pv_today+'</div>' + 
                        '   </td>' +
                        '   <td title="开了之后，扫过码的人以后只能看到第一次扫的码。">'+kf_qc+'</td>' +
                        '   <td>' +
                        '       <div>在线状态：'+kf_online+'</div>' + 
                        '       <div>安全提示：'+kf_safety+'</div>' + 
                        '   </td>' +
                        '   <td>'+kf_status+'</td>' +
                        '   <td class="cz-tags">' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#shareKf" onclick="shareKf('+kf_id+')">分享</span>' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#EditKfModal" onclick="getKfInfo(this)" id="'+kf_id+'">编辑</span>' +
                        '       <span class="light-tag" id="'+kf_id+'" data-toggle="modal" data-toggle="modal" data-target="#kfQrcodeListModal" onclick="getKfQrcodeList(this);">上传</span>' +
                        '       <span class="light-tag" onclick="resetKfPv('+kf_id+')" title="重置总访问量和今日访问量">重置</span>' +
                        '       <span class="light-tag" id="'+kf_id+'" data-toggle="modal" data-target="#delKfModal" onclick="askDelKf(this)">删除</span>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                getFenyeComponent(res.page,res.nextpage,res.prepage,res.allpage);
                
                // 设置路由
                setRouter(res.page);
                
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
        errorPage('data-list','getKfList.php');
      },
    });
}

// 分页组件
function getFenyeComponent(thisPage,nextPage,prePage,allPage){
    
    // 分页
    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1且总页码=1
        // 无需显示分页控件
        $("#right .data-card .fenye").css("display","none");
        
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1且总页码>1
        // 代表还有下一页
        var $getFenyeComponent_HTML = $(
        '<ul>' +
        '   <li>' +
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">' +
        '       <img src="../../static/img/nextPage.png" /></button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">' +
        '       <img src="../../static/img/lastPage.png" /></button>' +
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        var $getFenyeComponent_HTML = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1" onclick="getFenye(this);" title="第一页">' +
        '       <img src="../../static/img/firstPage.png" /></button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">' +
        '       <img src="../../static/img/prevPage.png" /></button>' +
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
    }else{
        
        // 显示所有组件
        var $getFenyeComponent_HTML = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1" onclick="getFenye(this);" title="第一页">' +
        '           <img src="../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">' +
        '           <img src="../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">' +
        '           <img src="../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">' +
        '           <img src="../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
    }
    
    // 渲染分页组件
    $("#right .data-card .fenye").html($getFenyeComponent_HTML);
}

// 分页
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getKfList(pageNum);
}

// 切换switch
// changeKfStatus
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
                
                // 显示全局信息提示弹出提示
                showNotification(res.msg);
            }else{
                
                // 显示全局信息提示弹出提示
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('changeKfStatus.php发生错误！');
        }
    });
}

// 去重开关切换
function changeKfQc(e){

    // 修改
    $.ajax({
        type: "POST",
        url: "./changeKfQc.php?kf_id="+e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 刷新
                getKfList();
                
                // 显示全局信息提示弹出提示
                showNotification(res.msg);
            }else{
                
                // 显示全局信息提示弹出提示
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('changeKfQc.php发生错误！');
        }
    });
}

// 加载客服二维码列表
function getKfQrcodeList(e) {
    
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
        '   <th>备注</th>' +
        '   <th>状态</th>' +
        '   <th>操作</th>' +
        '</tr>'
    );
    $("#kfQrcodeListModal .modal-body .kfQrcodeList thead").html($zm_thead_HTML);
    
    // 获取
    $.ajax({
        type: "POST",
        url: "./getKfQrcodeList.php?kf_id="+kf_id,
        success: function(res){
            
            // 初始化
            initialize_kfQrcodeListModal();
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.kfQrcodeList.length; i++) {
                    
                    // ID
                    var zm_id = res.kfQrcodeList[i].zm_id;
                    
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）状态
                    if(res.kfQrcodeList[i].zm_status == '1'){
                        
                        // 正常
                        var zm_status = 
                        '<span class="switch-on" onclick="changeKfQrcodeStatus('+zm_id+');">'+
                        '   <span class="press"></span>'+
                        '</span>';
                    }else{
                        
                        // 关闭
                        var zm_status = 
                        '<span class="switch-off" onclick="changeKfQrcodeStatus('+zm_id+');">'+
                        '<span class="press"></span>'+
                        '</span>';
                    }
                    
                    // 阈值
                    var zm_yz = res.kfQrcodeList[i].zm_yz;
                    
                    // 访问量
                    var zm_pv = res.kfQrcodeList[i].zm_pv;
                    
                    // 计算更新时间距离现在过去多长时间
                    var updatePassTime = getDateDiff(getDateTimeStamp(res.kfQrcodeList[i].zm_update_time));
                    
                    // 微信号
                    if(res.kfQrcodeList[i].zm_num == '' || res.kfQrcodeList[i].zm_num == null){
                        
                        // 未设置
                        var zm_num = '<span>未设置</span>';
                    }else{
                        
                        // 已设置
                        var zm_num = res.kfQrcodeList[i].zm_num;
                    }
                    
                    // 仅限后台可见的备注信息
                    let zm_beizhu_ht;
                    if(res.kfQrcodeList[i].zm_beizhu_ht){
                        
                        // 有数据
                        zm_beizhu_ht = res.kfQrcodeList[i].zm_beizhu_ht;
                    }else{
                        
                        // 无数据
                        zm_beizhu_ht = '-';
                    }
                    
                    // 列表
                    var $zm_tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+zm_yz+'</td>' +
                        '   <td>'+zm_pv+'</td>' +
                        '   <td>'+updatePassTime+'</td>' +
                        '   <td>'+zm_num+'</td>' +
                        '   <td>'+zm_beizhu_ht+'</td>' +
                        '   <td id="kfQrcodeStatus_'+zm_id+'">'+zm_status+'</td>' +
                        '   <td class="cz-tags">' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#EditKfQrcodeModal" onclick="getKfQrcodeInfo(this)" id="'+zm_id+'">编辑</span>' +
                        '       <span class="light-tag" title="重置阈值和访问量为0" onclick="resetKfQrcode(this)" id="'+zm_id+'">重置</span>' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#DelKfQrcode" onclick="DelKfQrcodePre(this)" id="'+zm_id+'">删除</span>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#kfQrcodeListModal .modal-body .kfQrcodeList tbody").append($zm_tbody_HTML);
                }
            }else{
                
                // 非200状态码
                noQrcodeData('暂无二维码')
            }
            
            // 客服二维码Modal标题
            $("#kfQrcodeList_Pannel_Title").text(res.kf_title);
            
            // 给【从素材库选择】这个button增加一个data-kid的属性
            $('#kfQrcodeListModal .sucaiku').attr('data-kid',kf_id);
            
      },
      error: function(){
        
        // 发生错误
        errorPage('qrcode-list','getKfQrcodeList.php');
      },
    });
    
    // 将kf_id添加到本地上传的隐藏表单中
    $("#uploadKfQrcode_kf_id").val(kf_id);
}

// 刷新客服二维码列表
function refreshKfQrcodeList(kf_id){
    
    // 获取
    $.ajax({
        type: "POST",
        url: "./getKfQrcodeList.php?kf_id="+kf_id,
        success: function(res){
            
            // 初始化
            initialize_kfQrcodeListModal();
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.kfQrcodeList.length; i++) {
                    
                    // ID
                    var zm_id = res.kfQrcodeList[i].zm_id;
                    
                    // （1）序号
                    var xuhao = i+1;
                    
                    // （2）状态
                    if(res.kfQrcodeList[i].zm_status == '1'){
                        
                        // 正常
                        var zm_status = 
                        '<span class="switch-on" onclick="changeKfQrcodeStatus('+zm_id+');">'+
                        '   <span class="press"></span>'+
                        '</span>';
                    }else{
                        
                        // 关闭
                        var zm_status = 
                        '<span class="switch-off" onclick="changeKfQrcodeStatus('+zm_id+');">'+
                        '   <span class="press"></span>'+
                        '</span>';
                    }
                    
                    // 阈值
                    var zm_yz = res.kfQrcodeList[i].zm_yz;
                    
                    // 访问量
                    var zm_pv = res.kfQrcodeList[i].zm_pv;
                    
                    // 计算更新时间距离现在过去多长时间
                    var updatePassTime = getDateDiff(getDateTimeStamp(res.kfQrcodeList[i].zm_update_time));
                    
                    // 群主
                    if(res.kfQrcodeList[i].zm_num == '' || res.kfQrcodeList[i].zm_num == null){
                        
                        // 未设置群主
                        var zm_num = '<span>未设置</span>';
                    }else{
                        
                        // 已设置群主
                        var zm_num = res.kfQrcodeList[i].zm_num;
                    }
                    
                    // 仅限后台可见的备注信息
                    let zm_beizhu_ht;
                    if(res.kfQrcodeList[i].zm_beizhu_ht){
                        
                        // 有数据
                        zm_beizhu_ht = res.kfQrcodeList[i].zm_beizhu_ht;
                    }else{
                        
                        // 无数据
                        zm_beizhu_ht = '-';
                    }
                    
                    // 列表
                    var $zm_tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+xuhao+'</td>' +
                        '   <td>'+zm_yz+'</td>' +
                        '   <td>'+zm_pv+'</td>' +
                        '   <td>'+updatePassTime+'</td>' +
                        '   <td>'+zm_num+'</td>' +
                        '   <td>'+zm_beizhu_ht+'</td>' +
                        '   <td id="kfQrcodeStatus_'+zm_id+'">'+zm_status+'</td>' +
                        '   <td class="cz-tags">' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#EditKfQrcodeModal" onclick="getKfQrcodeInfo(this)" id="'+zm_id+'">编辑</span>' +
                        '       <span class="light-tag" title="重置阈值和访问量为0" onclick="resetKfQrcode(this)" id="'+zm_id+'">重置</span>' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#DelKfQrcode" onclick="DelKfQrcodePre(this)" id="'+zm_id+'">删除</span>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#kfQrcodeListModal .modal-body .kfQrcodeList tbody").append($zm_tbody_HTML);
                }
            }else{
                
                // 非200状态码
                noQrcodeData('暂无二维码');
            }
      },
      error: function(){
        
        // 发生错误
        errorPage('qrcode-list','getKfQrcodeList.php');
      },
    });
}

// 切换switch
// changeKfQrcodeStatus
function changeKfQrcodeStatus(zmid){

    // 修改
    $.ajax({
        type: "POST",
        url: "./changeKfQrcodeStatus.php?zm_id="+zmid,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 更新switch状态
                showKfQrcodeSwitchNewStatus(res.zm_status,zmid);
                
                // 显示切换结果
                showSuccessResult(res.msg);
                
            }else{
                
                // 非200状态码操作结果
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('changeKfQrcodeStatus.php发生错误！');
        }
    });
}

// 创建客服码
function createKf(){
    
    $.ajax({
        type: "POST",
        url: "./createKf.php",
        data: $('#createKf').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("createKfModal")', 500);

                // 重新加载客服码列表
                setTimeout('getKfList();', 600);
                
                // 打开客服二维码列表
                setTimeout('showModal("kfQrcodeListModal")', 700);
                
                // 给【从素材库选择】这个button增加一个data-kid的属性
                $('#kfQrcodeListModal .sucaiku').attr('data-kid',res.kf_id);
                
                // 创建成功后的初始化
                initialize_createKfSuccess(res.kf_id,res.kf_title);
            }else{
                
                // 操作失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createKf.php');
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
                
                // 隐藏m
                setTimeout('hideModal("EditKfModal")', 500);
                
                // 重新加载客服码列表
                setTimeout('getKfList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editKf.php');
        }
    });
}

// 提交编辑客服二维码
function editKfQrcode(){
    $.ajax({
        type: "POST",
        url: "./editKfQrcode.php",
        data: $('#editKfQrcode').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
    
                // 隐藏编辑面板
                hideModal("EditKfQrcodeModal")
                
                // 打开客服二维码列表
                showModal("kfQrcodeListModal")
                
                // 刷新客服二维码列表
                refreshKfQrcodeList(res.kf_id)
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editKfQrcode.php');
        }
    });
}

// 询问是否要删除
function askDelKf(e){
    
    // 获取kf_id
    var kf_id = e.id;
    
    // 将群id添加到button的
    // delQun函数用于传参执行删除
    $('#delKfModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delKf('+kf_id+');">确定删除</button>'
    )
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
                
                // 隐藏Modal
                hideModal("delKfModal");
                
                // 重新加载群列表
                setTimeout('getKfList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delKf.php');
        }
    });
}

// 获取客服码详情
function getKfInfo(e){

    // 获取kf_id
    var kf_id = e.id;
    
    $.ajax({
        type: "GET",
        url: "./getKfInfo.php?kf_id="+kf_id,
        success: function(res){

            if(res.code == 200){
                
                // 操作成功
                showSuccessResult(res.msg);
                
                // （1）标题
                $('#EditKfModal input[name="kf_title"]').val(res.kfInfo.kf_title);
                
                // 活码页面的备注信息
                $('#EditKfModal textarea[name="kf_beizhu"]').val(res.kfInfo.kf_beizhu);
                
                // 仅限后台可见的备注信息
                $('#EditKfModal input[name="kf_beizhu_ht"]').val(res.kfInfo.kf_beizhu_ht);
                
                // 获取域名列表
                getDomainNameList('edit');
                
                // 活码状态
                if(res.kfInfo.kf_status == '1'){
                    
                    // 正常
                    $('#EditKfModal select[name="kf_status"]').html(
                        '<option value="1">正常</option><option value="2">停用</option>'
                    );
                }else{
                    
                    // 停用
                    $('#EditKfModal select[name="kf_status"]').html(
                        '<option value="2">停用</option><option value="1">正常</option>'
                    );
                }
                
                // 循环模式
                if(res.kfInfo.kf_model == '1'){
                    
                    // 阈值模式
                    $('#EditKfModal select[name="kf_model"]').html(
                        '<option value="1">阈值模式</option><option value="2">随机模式</option>'
                    );
                }else{
                    
                    // 随机模式
                    $('#EditKfModal select[name="kf_model"]').html(
                        '<option value="2">随机模式</option><option value="1">阈值模式</option>'
                    );
                }
                
                // 在线状态
                if(res.kfInfo.kf_online == '1'){
                    
                    $('#EditKfModal select[name="kf_online"]').html(
                        '<option value="1">显示</option><option value="2">隐藏</option>'
                    );
                    
                }else{
                    
                    $('#EditKfModal select[name="kf_online"]').html(
                        '<option value="2">隐藏</option><option value="1">显示</option>'
                    );
                }
                
                // 顶部扫码安全提示
                if(res.kfInfo.kf_safety == '1'){
                    
                    // 显示
                    $('#EditKfModal select[name="kf_safety"]').html(
                        '<option value="1">显示</option><option value="2">隐藏</option>'
                    );
                }else{
                    
                    // 隐藏
                    $('#EditKfModal select[name="kf_safety"]').html(
                        '<option value="2">隐藏</option><option value="1">显示</option>'
                    );
                }
                
                // 获取当前设置的域名
                $('#EditKfModal select[name="kf_rkym"]').val(res.kfInfo.kf_rkym);
                $('#EditKfModal select[name="kf_ldym"]').val(res.kfInfo.kf_ldym);
                $('#EditKfModal select[name="kf_dlym"]').val(res.kfInfo.kf_dlym);
                
                // 在线时间Json配置
                $('#EditKfModal textarea[name="kf_onlinetimes"]').val(res.kfInfo.kf_onlinetimes);
                
                // kf_id
                $('#EditKfModal input[name="kf_id"]').val(kf_id);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getKfInfo.php');
        }
    });
}

// 获取客服二维码详情
function getKfQrcodeInfo(e){
    
    // 获取zm_id
    var zm_id = e.id;
    
    $.ajax({
        type: "GET",
        url: "./getKfQrcodeInfo.php?zm_id="+zm_id,
        success: function(res){
            
            // 隐藏客服二维码列表
            hideModal('kfQrcodeListModal');

            if(res.code == 200){
                
                // 根据循环模式来决定
                // 是否需要显示阈值输入框
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
                
                // 后台备注
                $('#zm_beizhu_ht').val(res.kfzmInfo.zm_beizhu_ht);
                
                // 客服二维码使用状态
                if(res.kfzmInfo.zm_status == '1'){
                    
                    // 正常
                    $("#zm_status_edit").html(
                        '<option value="1">正常</option><option value="2">停用</option>'
                    );
                }else{
                    
                    // 停用
                    $("#zm_status_edit").html(
                        '<option value="2">停用</option><option value="1">正常</option>'
                    );
                }
                
                // 获取客服二维码
                $('#EditKfQrcodeModal .modal-body .qrcode_preview').css('display','block');
                $('#EditKfQrcodeModal .modal-body .upload_file').css('display','none');
                
                // 显示二维码及重新上传控件
                var $previewQrcode_HTML = $(
                    '<img src="'+res.kfzmInfo.zm_qrcode+'" class="qrcode" />' +
                    '<div>' +
                    '   <div class="Re-upload reUpload" onclick="newUpload();">+ 重新上传</div>' +
                    '   <div class="Re-upload selectFromSCK" onclick="getSuCai(\'1\',\'EditKfQrcodeModal\');">+ 从素材库选择</div>' +
                    '</div>'
                );
                $('#EditKfQrcodeModal .modal-body .qrcode_preview').html($previewQrcode_HTML);
                
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
            showErrorResultForphpfileName('getKfQrcodeInfo.php');
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
                
                // 将入口、落地、短链域名添加至选项中
                appendOptionsToSelect($("select[name='kf_rkym']"), res.rkymList);
                appendOptionsToSelect($("select[name='kf_ldym']"), res.ldymList);
                appendOptionsToSelect($("select[name='kf_dlym']"), res.dlymList);
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

// 询问是否要删除客服二维码
function DelKfQrcodePre(e){
    
    // 获取zm_id
    var zm_id = e.id;
    
    // 隐藏客服二维码列表
    hideModal("kfQrcodeListModal");
    
    // 将群zm_id添加到button的delKfQrcode函数用于传参执行删除
    $('#DelKfQrcode .modal-footer').html(
        '<button type="button" class="default-btn" onclick="delKfQrcode('+zm_id+');">确定删除</button>'
    )
}

// 删除客服二维码
function delKfQrcode(zm_id){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delKfQrcode.php?zm_id="+zm_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
  
                // 隐藏Modal
                hideModal("DelKfQrcode")
                
                // 打开Modal
                showModal("kfQrcodeListModal")
                
                // 刷新客服二维码列表
                refreshKfQrcodeList(res.kf_id)
            }else{
                
                // 操作失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delKfQrcode.php');
        }
    });
}

// 重置阈值和访问量为0
function resetKfQrcode(e){
    
    // zm_id
    var zm_id = e.id;
    
    if(confirm("确定要重置？")) {
        
        // 执行重置
        $.ajax({
            type: "GET",
            url: "./resetKfQrcode.php?zm_id="+zm_id,
            success: function(res){
                
                // 成功
                if(res.code == 200){
    
                    // 刷新客服二维码列表
                    refreshKfQrcodeList(res.kf_id)
                }else{
                    
                    // 操作失败
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                showErrorResultForphpfileName('resetKfQrcode.php');
            }
        });
    }
    
}

// 分享客服码
function shareKf(kf_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    $.ajax({
        type: "GET",
        url: "./shareKf.php?kf_id="+kf_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 长链接
                $("#longUrl").text(res.longUrl);
                
                // 短链接
                $("#shortUrl").html('<span id="kf_'+kf_id+'">'+res.shortUrl+'</span>');
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.qrcodeUrl);
                
                // 复制按钮
                $('#shareKf .modal-footer').html(
                    '<button class="default-btn" data-clipboard-action="copy" data-clipboard-target="#kf_'+kf_id+'">复制链接</button>'
                );
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('shareKf.php');
        }
    });
}

// 获取素材
function getSuCai(pageNum,fromPannel){
    
    // 初始化
    $('#suCaiKu .modal-body .sucai-view').empty('');
    
    // 关闭二维码上传界面
    hideModal('kfQrcodeListModal');
    
    // 关闭编辑客服二维码界面
    hideModal('EditKfQrcodeModal');
    
    // 打开素材库界面
    showModal('suCaiKu');
    
    // 将fromPannel的值设置到隐藏的表单中
    $('#suCaiKu input[name="upload_sucai_fromPannel"]').val(fromPannel);
    
    // 获取到kfid
    var kfid = $('#kfQrcodeListModal .default-btn.sucaiku').attr('data-kid');
    
    // 将kfid设置到表单中便于传参
    $('#upload_sucai_kfid').val(kfid);
    
    // 判断是否有pageNum参数传过来
    if(pageNum == undefined){
        
        // 没有参数就设置默认值
        var pageNum = 1;
    }
    
    // 获取从哪个面板点击打开的
    if(fromPannel == 'EditKfQrcodeModal'){
        
        // 上一个面板是 EditKfQrcodeModal 
        // 渲染出来的关闭按钮是需要返回 EditKfQrcodeModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'EditKfQrcodeModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'kfQrcodeListModal'){
        
        // 上一个面板是 kfQrcodeListModal
        // 渲染出来的关闭按钮是需要返回 kfQrcodeListModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'kfQrcodeListModal\')">&times;</button>'
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
                    if(fromPannel == 'EditKfQrcodeModal'){
                        
                        // 更新
                        var clickFunction = 'selectSucaiUpdateKfQrcode('+sucai_id+')';
                        
                    }else if(fromPannel == 'kfQrcodeListModal'){
                        
                        // 新增
                        var clickFunction = 'selectSucai('+sucai_id+','+kfid+')';
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
// 添加新的客服二维码
// 注意：仅作用于添加新的客服二维码
function selectSucai(sucai_id,kfid){
    
    $.ajax({
        type: "POST",
        url: "./selectSuCaiForKfQrcode.php?sucai_id="+sucai_id+"&kfid="+kfid,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功选择素材
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                // 显示操作反馈
                showSuccessResultTimes('已选择',1500);
                
                // 打开客服二维码列表
                setTimeout("showModal('kfQrcodeListModal')",1300);
                
                // 刷新客服二维码列表
                setTimeout("refreshKfQrcodeList("+kfid+")",1500);
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
function selectSucaiUpdateKfQrcode(sucai_id){
    
    $.ajax({
        type: "POST",
        url: "./selectSucaiUpdateKfQrcode.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 将客服二维码设置到表单中
                $('#zm_qrcode_edit').val(res.kfQrcodeUrl);
                
                // 设置新的预览
                $('#EditKfQrcodeModal .modal-body .qrcode_preview').html(
                    '<img src="'+res.kfQrcodeUrl+'" class="qrcode" />' +
                    '<p class="uploadSuccess">已选取素材</p>'
                );
                
                // 成功选择素材
                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",1000);
                
                // 显示操作反馈
                showSuccessResultTimes('已选择',1100);
                
                // 打开编辑客服二维码Modal
                setTimeout("showModal('EditKfQrcodeModal')",1200);
                
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

// 重置客服访问量
function resetKfPv(kf_id){
    
    if(confirm("确定要重置？")) {
        $.ajax({
            type: "POST",
            url: "resetKfPv.php?kf_id=" + kf_id,
            success: function(res){
                
                // 成功
                showNotification(res.msg);
                setTimeout('getKfList()',500);
            },
            error: function() {
                
                // 服务器发生错误
                showNotification('服务器发生错误');
            }
        });
    }
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

// 生成随机token
function creatPageToken(length) {
    
    var str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) 
        result += str[Math.floor(Math.random() * str.length)];
    return result;
}

// 为了便于继续操作二维码列表
// 编辑客服二维码的编辑框关闭后
// 点击右上角X会立即打开客服二维码列表
function hideEditKfQrcodeModal(){
    
    // 关闭编辑客服二维码
    hideModal('EditKfQrcodeModal');
    
    // 打开客服二维码列表
    showModal('kfQrcodeListModal')
}

// 为了便于继续操作二维码列表
// 素材库的界面关闭后
// 点击右上角X会继续打开二维码列表
function hideSuCaiPannel(fromPannel){
    
    // 先隐藏 suCaiKu 面板
    hideModal('suCaiKu');
    
    // 根据fromPannel决定打开哪个 Modal
    if(fromPannel == 'EditKfQrcodeModal'){
        
        // 显示 EditKfQrcodeModal
        showModal('EditKfQrcodeModal')
    }else if(fromPannel == 'kfQrcodeListModal'){
        
        // 显示 kfQrcodeListModal
        showModal('kfQrcodeListModal')
    }
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
    
    // 将图片预览隐藏
    // 将上传控件打开
    $('#EditKfModal .modal-body .upload_file').css('display','block');
    $('#EditKfModal .modal-body .qrcode_preview').css('display','none');
    $('#kf_kf_edit').val('');
    $('#EditKfQrcodeModal .modal-body .upload_file').css('display','block');
    $('#EditKfQrcodeModal .modal-body .qrcode_preview').css('display','none');
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

        $("#kfQrcodeListModal table").html(
            '<img src="../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
    }
    
}

// 没有获取到客服二维码
function noQrcodeData(text){
    $("#kfQrcodeListModal .loading").css('display','block');
    $("#kfQrcodeListModal .loading").html('<img src="../../static/img/noRes.png" /><br/><p>'+text+'</p>');
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

// 初始化（获取客服二维码列表）
function initialize_kfQrcodeListModal(){
    
    // 清空原加载的列表
    $("#kfQrcodeListModal .modal-body .kfQrcodeList tbody").empty('');
    
    // 隐藏loading
    $("#kfQrcodeListModal .loading").css('display','none');
    
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

// 初始化
// 获取域名列表
function initialize_getDomainNameList(module){
    
    // 默认值
    $('#createKfModal input[name="kf_title"]').val('');
    $('select[name="kf_rkym"]').empty();
    $('select[name="kf_ldym"]').empty();
    $('select[name="kf_dlym"]').empty();
    hideResult();
    
    // 在线时间Json配置
    // 1、2、3、4、5、6、7代表星期一至星期日
    // morning 上午在线时间段
    // afternoon 下午在线时间段
    // evening 晚上在线时间段
    // ---------------------
    // 如需要设置全天不在线
    // 将三个时间段都设为00:00-00:00
    const onlineTimesJsonData = {
      "1": {
        "morning": "09:00-12:00",
        "afternoon": "14:00-18:00",
        "evening": "20:00-22:00"
      },
      "2": {
        "morning": "09:00-12:00",
        "afternoon": "14:00-18:00",
        "evening": "20:00-22:00"
      },
      "3": {
        "morning": "09:00-12:00",
        "afternoon": "14:00-18:00",
        "evening": "20:00-22:00"
      },
      "4": {
        "morning": "09:00-12:00",
        "afternoon": "14:00-18:00",
        "evening": "20:00-22:00"
      },
      "5": {
        "morning": "09:00-12:00",
        "afternoon": "14:00-18:00",
        "evening": "20:00-22:00"
      },
      "6": {
        "morning": "00:00-00:00",
        "afternoon": "00:00-00:00",
        "evening": "00:00-00:00"
      },
      "7": {
        "morning": "00:00-00:00",
        "afternoon": "00:00-00:00",
        "evening": "00:00-00:00"
      }
    };
    const onlineTimesJsonString = JSON.stringify(onlineTimesJsonData, null, 2);
    document.getElementById("onlineTimesJsonString").value = onlineTimesJsonString;
}

// 创建成功后的初始化
function initialize_createKfSuccess(kf_id,kf_title){
    
    // 隐藏创建成功的提示
    hideResult();
    
    // 设置客服二维码面板的标题
    $('#kfQrcodeList_Pannel_Title').text(kf_title);
    
    // 将kf_id添加到本地上传的隐藏表单中
    $('#uploadKfQrcode_kf_id').val(kf_id);
    
    // 显示表头和空白内容文字提示
    var $qrcode_thead_HTML = $(
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
    $("#kfQrcodeListModal .modal-body .kfQrcodeList thead").html($qrcode_thead_HTML);
    
    // 空数据
    noQrcodeData('暂无二维码');
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

// 显示客服二维码切换后的状态
function showKfQrcodeSwitchNewStatus(status,zmid){
    if(status == 1){
        
        // 开启
        $('#kfQrcodeStatus_'+zmid).html(
            '<span class="switch-on" onclick="changeKfQrcodeStatus('+zmid+');"><span class="press"></span></span>'
        );  
    }else{
        
        // 关闭
        $('#kfQrcodeStatus_'+zmid).html(
            '<span class="switch-off" onclick="changeKfQrcodeStatus('+zmid+');"><span class="press"></span></span>'
        );
    }
}

// 打开操作反馈（操作成功）
function showSuccessResultTimes(content,times){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', times); // times秒后自动关闭
}

// 设置路由
function setRouter(pageNum){
    
    // 当前页码不等于1的时候
    if(pageNum !== 1){
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

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

console.log('%c 欢迎使用引流宝','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
console.log('%c 作者：TANKING','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
console.log('%c 作者博客：https://segmentfault.com/u/tanking','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
console.log('%c 开源地址：https://github.com/likeyun/liKeYun_Ylb','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
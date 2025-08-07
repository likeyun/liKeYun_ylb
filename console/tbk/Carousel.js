// 进入就加载
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    // 根据页码获取数据
    if(typeof pageNum !== "undefined"){
        
        // 获取当前页码的单页数据列表
        getCarouselList(pageNum);
    }else{
        
        // 获取不到页码就获取首页
        getCarouselList();
    }
    
    // clipboard插件
    var clipboard = new ClipboardJS('#shareMultiSPAModal .modal-footer button');
    clipboard.on('success', function(e) {
        
        // 复制成功
        $('#shareMultiSPAModal .modal-footer button').text('已复制');
    });
    
    // 监听multi_project可编辑的DIV的输入
    $("#createSpaModal .multi_project").on("input", function() {
        
        // 实时获取输入的内容
        const multi_project_content = $(this).val();
        
        // 将内容实时加入到表单输入框
        $('#createSpaModal input[name="multiSPA_project"]').val(multi_project_content.replace(/\n/g, "<br/>"));
    });
    
    $("#editSpaModal .multi_project").on("input", function() {
        
        // 实时获取输入的内容
        const multi_project_content = $(this).val();
        
        // 将内容实时加入到表单输入框
        $('#editSpaModal input[name="multiSPA_project"]').val(multi_project_content.replace(/\n/g, "<br/>"));
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

// 获取单页列表
function getCarouselList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./getCarouselList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./getCarouselList.php?p="+pageNum
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getCarouselList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th style="text-align:left;">选择</th>' +
                '   <th>单页ID</th>' +
                '   <th>标题</th>' +
                '   <th>短链接</th>' +
                '   <th>创建时间</th>' +
                '   <th>访问量</th>' +
                '   <th>状态</th>' +
                '   <th style="text-align: right;">操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 200状态码
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.carouselSPAList.length; i++) {
                    
                    // 单行数据对象
                    // 用于编辑、查看、分享时的参数传递
                    var carouselSPAInfoObject = {
                        Carousel_id: res.carouselSPAList[i].Carousel_id,
                        Carousel_title: res.carouselSPAList[i].Carousel_title,
                        Carousel_dlym: res.carouselSPAList[i].Carousel_dlym,
                        Carousel_rkym: res.carouselSPAList[i].Carousel_rkym,
                        Carousel_ldym: res.carouselSPAList[i].Carousel_ldym
                    };
                    
                    // 状态切换
                    if(res.carouselSPAList[i].Carousel_status == 1){
                        
                        // 正常
                        var Carousel_status = 
                        '<span class="switch-on" id="'+res.carouselSPAList[i].Carousel_id+'" onclick="changeCarouselStatus(this);">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }else{
                        
                        // 关闭
                        var Carousel_status = 
                        '<span class="switch-off" id="'+res.carouselSPAList[i].Carousel_id+'" onclick="changeCarouselStatus(this);">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>' +
                        '       <span class="selectCarouselSPA" id="nodeid_'+res.carouselSPAList[i].Carousel_id+'" onclick="batchSelecteCarouselSPA(this,'+res.carouselSPAList.length+')"></span>' +
                        '   </td>' +
                        '   <td>'+res.carouselSPAList[i].Carousel_id+'</td>' +
                        '   <td>'+res.carouselSPAList[i].Carousel_title+'</td>' +
                        '   <td style="display: flex; justify-content: center; align-items: center;">' +
                        '       <span class="carousel_dwz">'+res.carouselSPAList[i].Carousel_dlym+'/cal/'+res.carouselSPAList[i].Carousel_key+'</span>' +
                        '       <a href="https://api.cl2wm.cn/api/qrcode/code?text='+res.carouselSPAList[i].Carousel_dlym+'/cal/'+res.carouselSPAList[i].Carousel_key+'" class="carousel_scan" target="blank"><div class="scanicon" title="扫码预览"></div></a>' +
                        '   </td>' +
                        '   <td>'+res.carouselSPAList[i].Carousel_create_time+'</td>' +
                        '   <td>'+res.carouselSPAList[i].Carousel_pv+'</td>' +
                        '   <td>'+Carousel_status+'</td>' +
                        '   <td style="text-align:right;">' +
                        '       <span data-toggle="modal" data-target="#uploadCarouselPicsModal" onclick="uploadCarouselPicsPannel('+res.carouselSPAList[i].Carousel_id+')" class="cz-click">上传</span>' +
                        '       <span data-toggle="modal" class="cz-click" onclick=\'getCarouselSPAInfo('+JSON.stringify(carouselSPAInfoObject)+')\'>编辑</span>' +
                        '       <span data-toggle="modal" data-target="#delCarouselConfirmModal" onclick="delCarouselConfirmModal('+res.carouselSPAList[i].Carousel_id+')" class="cz-click">删除</span>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                fenyeComponent(res.page,res.allpage,res.nextpage,res.prepage);
                
                // 设置URL路由
                setRouter(pageNum);
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
        errorPage('data-list','getCarouselList.php');
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
        var $MultiSPAFenye_HTML = $(
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
        var $MultiSPAFenye_HTML = $(
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
        
        var $MultiSPAFenye_HTML = $(
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
    $("#right .data-card .fenye").html($MultiSPAFenye_HTML);
}

// 获取分页数据
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getCarouselList(pageNum);
}

// 创建单页
function createCarouselSPA(){
    
    $.ajax({
        type: "POST",
        url: "./createCarouselSPA.php",
        data: $('#createCarouselSPA').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 创建成功
                showSuccessResult(res.msg)
                
                // 隐藏modal
                setTimeout('hideModal("createSpaModal")', 500);
                
                // 刷新列表
                setTimeout('getCarouselList()', 700);

            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createCarouselSPA.php');
        }
    });
    
}

// 编辑单页
function editCarouselSPA(){
    
    $.ajax({
        type: "POST",
        url: "./editCarousel.php",
        data: $('#editCarouselSPA').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg);
                
                // 隐藏 editSpaModal
                setTimeout('hideModal("editSpaModal")', 500);
                
                // 重新加载单页列表
                setTimeout('getCarouselList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('ediCarousel.php');
        }
    });
}

// 删除确认
function delCarouselConfirmModal(Carousel_id){
    
    // 将id绑定到确认按钮
    $('#delCarouselConfirmModal .modal-footer').html(
        '<button type="button" class="default-btn center-btn" onclick="delCarousel('+Carousel_id+');">确定删除</button>'
    )
}

// 删除单页
function delCarousel(Carousel_id){
    
    // 删除
    $.ajax({
        type: "GET",
        url: "./delCarousel.php?Carousel_id="+Carousel_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delCarouselConfirmModal");
                
                // 重新加载单页列表
                setTimeout('getCarouselList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delCarousel.php');
        }
    });
}

// 获取单页详情
function getCarouselSPAInfo(carouselSPAInfoObject){

    // 获取域名列表
    getDomainNameList('edit');
    
    // 当前设置的域名
    setTimeout(function() {
        $("#editSpaModal select[name='Carousel_dlym']").val(carouselSPAInfoObject.Carousel_dlym);
        $("#editSpaModal select[name='Carousel_rkym']").val(carouselSPAInfoObject.Carousel_rkym);
        $("#editSpaModal select[name='Carousel_ldym']").val(carouselSPAInfoObject.Carousel_ldym);
    },100)
    
    // 将落地页详情数据填写到表单中
    $('#editSpaModal input[name="Carousel_title"]').val(carouselSPAInfoObject.Carousel_title);
    $('#editSpaModal input[name="Carousel_id"]').val(carouselSPAInfoObject.Carousel_id);
    
    // 显示Modal
    showModal('editSpaModal');
}

// 获取域名列表
function getDomainNameList(module){
    
    // 创建
    if(module == 'create'){
        
        // 初始化
        initialize_getDomainNameList(module);
        
        // 获取
        $.ajax({
            type: "GET",
            url: "../public/getDomainNameList.php",
            success: function(res){
                
                // 成功
                if(res.code == 200){
                    
                    // rkymList有域名
                    if(res.rkymList.length>0){
                        
                        for (var i=0; i<res.rkymList.length; i++) {
                            
                            // 将入口域名列表加到表单中
                            $("select[name='Carousel_rkym']").append(
                                '<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='Carousel_rkym']").append('<option value="">暂无入口域名</option>');
                    }
                    
                    // ldymList有域名
                    if(res.ldymList.length>0){
                        
                        for (var i=0; i<res.ldymList.length; i++) {
                            
                            // 将落地域名列表加到表单中
                            $("select[name='Carousel_ldym']").append(
                                '<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='Carousel_ldym']").append('<option value="">暂无落地域名</option>');
                    }
                    
                    // dlymList有域名
                    if(res.dlymList.length>0){
                        
                        for (var i=0; i<res.dlymList.length; i++) {
                            
                            // 将落地域名列表加到表单中
                            $("select[name='Carousel_dlym']").append(
                                '<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='Carousel_dlym']").append('<option value="">暂无短链域名</option>');
                    }
                }else{
                    
                    // 操作反馈（操作失败）
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                showErrorResultForphpfileName('getDomainNameList.php');
            }
        });
    }else if(module == 'edit'){
        
        // 初始化
        initialize_getDomainNameList(module);
        
        // 获取
        $.ajax({
            type: "GET",
            url: "../public/getDomainNameList.php",
            success: function(res){
                
                // 成功
                if(res.code == 200){
                    
                    // rkymList有域名
                    if(res.rkymList.length>0){
                        
                        for (var i=0; i<res.rkymList.length; i++) {
                            
                            // 将入口域名列表加到表单中
                            $("select[name='Carousel_rkym']").append(
                                '<option value="'+res.rkymList[i].domain+'">'+res.rkymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='Carousel_rkym']").append('<option value="">暂无入口域名</option>');
                    }
                    
                    // ldymList有域名
                    if(res.ldymList.length>0){
                        
                        for (var i=0; i<res.ldymList.length; i++) {
                            
                            // 将落地域名列表加到表单中
                            $("select[name='Carousel_ldym']").append(
                                '<option value="'+res.ldymList[i].domain+'">'+res.ldymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='Carousel_ldym']").append('<option value="">暂无落地域名</option>');
                    }
                    
                    // dlymList有域名
                    if(res.dlymList.length>0){
                        
                        for (var i=0; i<res.dlymList.length; i++) {
                            
                            // 将落地域名列表加到表单中
                            $("select[name='Carousel_dlym']").append(
                                '<option value="'+res.dlymList[i].domain+'">'+res.dlymList[i].domain+'</option>'
                            );
                        }
                    }else{
                        
                        // 没有域名
                        $("select[name='Carousel_dlym']").append('<option value="">暂无短链域名</option>');
                    }
                }else{
                    
                    // 操作反馈（操作失败）
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                showErrorResultForphpfileName('getDomainNameList.php');
            }
        });
    }
}

// 预上传轮播图
function uploadCarouselPicsPannel(Carousel_id) {
    
    // 将 Carousel_id 设置到表单
    $('#uploadCarouselPicsModal input[name="Carousel_id"]').val(Carousel_id);
    
    // 初始化
    $('#uploadCarouselPicsModal .upload-btn-view .btn-text').text('上传图片');
    
    // 加载列表
    getCarouselPics(Carousel_id);
}

// 添加轮播图
function addCarouselPics() {

    $.ajax({
        type: "POST",
        url: "./addCarouselPics.php",
        data: $('#uploadCarouselPics').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg);
                
                // 获取当前pannel的Carousel_id
                const Carousel_id = $('#uploadCarouselPicsModal input[name="Carousel_id"]').val();
                
                // 重新加载列表
                setTimeout('getCarouselPics('+Carousel_id+')', 500);
                
                // 初始化表单
                $('#uploadCarouselPicsModal input[name="pic_url"]').val('');
                $('#uploadCarouselPicsModal input[name="pic_desc"]').val('');
                $('#uploadCarouselPicsModal .upload-btn-view .btn-text').text('继续上传');
            }else{
                
                // 操作反馈（操作失败）
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('addCarouselPics.php');
        }
    });
}

// 轮播图列表
function getCarouselPics(Carousel_id) {
    
    // 初始化
    $("#uploadCarouselPicsModal .table tbody").empty();
    
    $.ajax({
        type: "POST",
        url: "./getCarouselPics.php?Carousel_id="+Carousel_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 遍历数据
                if(res.carouselPics.length > 0) {
                    
                    for (var i=0; i<res.carouselPics.length; i++) {
                        
                        // 列表
                        var $tbody_HTML = $(
                        '<tr>' +
                        '<td style="text-align:left;">' +
                        '    <img src="'+res.carouselPics[i].pic_url+'" style="width:60px;" />' +
                        '</td>' +
                        '<td style="max-width:200px;">'+res.carouselPics[i].pic_desc+'</td>' +
                        '<td style="text-align:right;">' +
                        '   <span class="cz-click delCarouselPicConfrim_'+res.carouselPics[i].pic_id+'" onclick="delCarouselPicConfrim('+res.carouselPics[i].pic_id+')">删除</span>' +
                        '</td>' +
                        '</tr>'
                        );
                        $("#uploadCarouselPicsModal .table tbody").append($tbody_HTML);
                        
                        // 隐藏noData
                        $("#uploadCarouselPicsModal .noData").css('display','none');
                    }
                }else {
                    
                    // 暂无数据
                    $("#uploadCarouselPicsModal .noData").css('display','block');
                    $("#uploadCarouselPicsModal .noData").text(res.msg);
                }
            }else {
                
                // 获取失败
                $("#uploadCarouselPicsModal .noData").css('display','block');
                $("#uploadCarouselPicsModal .noData").text(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            $("#uploadCarouselPicsModal .noData").text('服务器发生错误');
        }
    });
}

// 删除轮播图确认
function delCarouselPicConfrim(pic_id) {
    
    if(pic_id) {
        
        // 再次确认
        $('.delCarouselPicConfrim_'+pic_id).html('<span onclick="delCarouselPic('+pic_id+')">确认删除？</span>');
        
        // 改变确认删除按钮的样式
        $('.delCarouselPicConfrim_'+pic_id).addClass('redConfrimBtn');
    }
}

// 删除轮播图
function delCarouselPic(pic_id) {
    
    if(pic_id) {
        $.ajax({
            type: "POST",
            url: "./delCarouselPic.php?pic_id="+pic_id,
            success: function(res){
                
                // 成功
                // 获取当前pannel的Carousel_id
                const Carousel_id = $('#uploadCarouselPicsModal input[name="Carousel_id"]').val();
                
                // 重新加载列表
                setTimeout('getCarouselPics('+Carousel_id+')', 500);
            },
            error: function() {
                
                // 服务器发生错误
                errorPage('data-list','exitLogin.php');
            }
        });
    }
}

// 切换状态
function changeCarouselStatus(e) {
    
    $.ajax({
        type: "POST",
        url: "./changeCarouselStatus.php?Carousel_id="+e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                getCarouselList();
                showNotification(res.msg)
            }else{
                
                // 操作失败
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('服务器发生错误');
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

// 根据短网址Key或标题查询
function searchCarousel() {
    
    // 获取关键词
    const keywordOrKey = $('input[name="keywordOrKey"]').val();
    
    if(!keywordOrKey){
        
        // 空值
        showNotification('请输入标题关键词或Key');
        
        // 设置表单边框为红色
        $('input[name="keywordOrKey"]').css('border-color','#f00');
    }else{
        
        // 查询
        $.ajax({
            type: "POST",
            url: './searchCarousel.php?keywordOrKey='+keywordOrKey,
            success: function(res){
                
                // 初始化
                initialize_getCarouselList();
                
                // 表头
                var $thead_HTML = $(
                    '<tr>' +
                    '   <th style="text-align:left;">选择</th>' +
                    '   <th>单页ID</th>' +
                    '   <th>标题</th>' +
                    '   <th>短链接</th>' +
                    '   <th>创建时间</th>' +
                    '   <th>访问量</th>' +
                    '   <th>状态</th>' +
                    '   <th style="text-align: right;">操作</th>' +
                    '</tr>'
                );
                $("#right .data-list thead").html($thead_HTML);
                
                // 200状态码
                if(res.code == 200){
                    
                    // 遍历数据
                    for (var i=0; i<res.carouselSPAList.length; i++) {
                        
                        // 单行数据对象
                        // 用于编辑、查看、分享时的参数传递
                        var carouselSPAInfoObject = {
                            Carousel_id: res.carouselSPAList[i].Carousel_id,
                            Carousel_title: res.carouselSPAList[i].Carousel_title,
                            Carousel_dlym: res.carouselSPAList[i].Carousel_dlym,
                            Carousel_rkym: res.carouselSPAList[i].Carousel_rkym,
                            Carousel_ldym: res.carouselSPAList[i].Carousel_ldym
                        };
                        
                        // 状态切换
                        if(res.carouselSPAList[i].Carousel_status == 1){
                            
                            // 正常
                            var Carousel_status = 
                            '<span class="switch-on" id="'+res.carouselSPAList[i].page_id+'" onclick="changeCarouselStatus(this);">' +
                            '   <span class="press"></span>' +
                            '</span>';
                        }else{
                            
                            // 关闭
                            var Carousel_status = 
                            '<span class="switch-off" id="'+res.carouselSPAList[i].page_id+'" onclick="changeCarouselStatus(this);">' +
                            '   <span class="press"></span>' +
                            '</span>';
                        }
                        
                        // 列表
                        var $tbody_HTML = $(
                            '<tr>' +
                            '   <td>' +
                            '       <span class="selectCarouselSPA" id="nodeid_'+res.carouselSPAList[i].Carousel_id+'" onclick="batchSelecteCarouselSPA(this,'+res.carouselSPAList.length+')"></span>' +
                            '   </td>' +
                            '   <td>'+res.carouselSPAList[i].Carousel_id+'</td>' +
                            '   <td>'+res.carouselSPAList[i].Carousel_title+'</td>' +
                            '   <td style="display: flex; justify-content: center; align-items: center;">' +
                            '       <span class="carousel_dwz">'+res.carouselSPAList[i].Carousel_dlym+'/cal/'+res.carouselSPAList[i].Carousel_key+'</span>' +
                            '       <a href="https://api.cl2wm.cn/api/qrcode/code?text='+res.carouselSPAList[i].Carousel_dlym+'/cal/'+res.carouselSPAList[i].Carousel_key+'" class="carousel_scan" target="blank"><div class="scanicon" title="扫码预览"></div></a>' +
                            '   </td>' +
                            '   <td>'+res.carouselSPAList[i].Carousel_create_time+'</td>' +
                            '   <td>'+res.carouselSPAList[i].Carousel_pv+'</td>' +
                            '   <td>'+Carousel_status+'</td>' +
                            '   <td style="text-align:right;">' +
                            '       <span data-toggle="modal" data-target="#sharePageModal" onclick="sharePage('+res.carouselSPAList[i].Carousel_id+')" class="cz-click">上传</span>' +
                            '       <span data-toggle="modal" class="cz-click" onclick=\'getCarouselSPAInfo('+JSON.stringify(carouselSPAInfoObject)+')\'>编辑</span>' +
                            '       <span data-toggle="modal" data-target="#delCarouselConfirmModal" onclick="delCarouselConfirmModal('+res.carouselSPAList[i].Carousel_id+')" class="cz-click">删除</span>' +
                            '   </td>' +
                            '</tr>'
                        );
                        $("#right .data-list tbody").append($tbody_HTML);
                    }
                    
                    // 删除分页组件
                    $('.data-card .fenye').remove();
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
            showNotification('searchMultiSPA.php服务错误！');
          },
        });
        
        // 恢复表单样式
        $('input[name="keywordOrKey"]').css('border-color','#CED4DA');
    }
}

// 存储被选中行的 idNumber
var selectedRows = [];
function batchSelecteCarouselSPA(e) {
    
    // 获取被点击的 <tr> 元素的 ID
    var Carousel_id = e.id;

    // 分离出数字部分
    var idNumber = Carousel_id.replace('nodeid_', ''); // 移除 'nodeid_' 部分

    // 判断是否已经选中
    var isSelected = $('#nodeid_' + idNumber).hasClass('selectedCarouselSPA');

    // 如果已经选中，则移除样式并从数组中移除该 idNumber，否则添加样式并添加到数组中
    if (isSelected) {
        $('#nodeid_' + idNumber).removeClass('selectedCarouselSPA').text('');
        var index = selectedRows.indexOf(idNumber);
        if (index !== -1) {
            selectedRows.splice(index, 1);
        }
    } else {
        $('#nodeid_' + idNumber).addClass('selectedCarouselSPA').text('√');
        selectedRows.push(idNumber);
    }
    
    if(selectedRows.length > 0) {
        $('.batchDeleteCarouselSPA').html(
            '<button class="tint-btn" style="margin-left:5px;" onclick="batchDeleteCarouselSPA()">删除选中项</button>'
        );
    }else {
        $('.batchDeleteCarouselSPA').html('')
    }
}

// 执行批量删除
function batchDeleteCarouselSPA () {
    
    var successCount = 0; // 记录操作成功的次数
    var errorCount = 0; // 记录操作失败的次数

    if(selectedRows.length > 0) {
        for (var i = 0; i < selectedRows.length; i++) {
            $.ajax({
                type: "POST",
                url: "./delCarousel.php?Carousel_id="+selectedRows[i],
                success: function(res){
                    
                    // 成功
                    if(res.code == 200){
                        successCount++;
                    }else {
                        errorCount++;
                    }
                    
                    // 检查是否所有请求都已完成
                    if (successCount + errorCount === selectedRows.length) {
                        
                        // 反馈操作结果
                        showNotification('成功删除'+ successCount +'条记录');
                        
                        // 清空选中的行
                        selectedRows = [];
                        
                        // 获取列表
                        getCarouselList();
                    }
                },
                error: function() {
                    
                    errorCount++;
                    
                    // 检查是否所有请求都已完成
                    if (successCount + errorCount === selectedRows.length) {
                        
                        // 反馈操作结果
                        showNotification('服务器发生错误');
                    }
                }
            });
        }
    } else {
        showNotification('暂未选择删除项');
    }
}

// 为了便于继续操作创建单页
// 素材库的界面关闭后
// 点击右上角X会继续打开创建单页
function hideSuCaiPannel(fromPannel){
    
    // 先隐藏 suCaiKu 面板
    hideModal('suCaiKu');
    
    // 根据fromPannel决定打开哪个 Modal
    if(fromPannel == 'createSpaModal'){
        
        // 显示 createSpaModal
        showModal('createSpaModal')
    }else if(fromPannel == 'editSpaModal'){
        
        // 显示 editSpaModal
        showModal('editSpaModal')
    }
}

// 打开操作反馈（操作成功）
function showSuccessResultTimes(content,times){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', times);
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

// 初始化（getCarouselList 获取单页列表）
function initialize_getCarouselList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'create'){
        
        // 将所有值清空
        $("input[name='Carousel_title']").val('');
        $("select[name='Carousel_rkym']").empty();
        $("select[name='Carousel_ldym']").empty();
        $("select[name='Carousel_dlym']").empty();
        hideResult();
        
    }else if(module == 'edit'){
        
        // 将所有值清空
        $("select[name='Carousel_rkym']").empty();
        $("select[name='Carousel_ldym']").empty();
        $("select[name='Carousel_dlym']").empty();
        hideResult();
    }
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 打开操作反馈
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

// 设置URL路由
function setRouter(pageNum){
    
    // 第一页不设置
    if(pageNum !== 1 && typeof pageNum !== "undefined"){
        
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
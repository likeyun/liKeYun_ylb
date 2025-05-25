window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取IP
    getIPTotal();
    
    // 加载项目说明
    projectInfo();
}

// 获取登录状态
function getLoginStatus(){
    
    $.ajax({
        type: "POST",
        url: "../login/getLoginStatus.php",
        success: function(res){
            
            // 成功
            // 账号及版本信息
            if(res.code == 200){
                
                // 已登录
                var $account = $(
                    '<div class="version">'+res.version+'</div>' +
                    '<div class="user_name">'+res.user_name+' <span onclick="exitLogin();" class="exitLogin">退出</span></div>'
                );
                $(".left .account").html($account);
                $("#right .data-card .data-content").css('display','block');
                $("#right .data-card .loading").css('display','none');
                
                // 设置data-card里面的用户名
                $('.data-card-1 .card-left .userinfoCard .username').text(res.user_name);
                if(res.user_admin == 1) {
                    $('.data-card-1 .card-left .userinfoCard .userlimit').text('超级管理员');
                }else {
                    $('.data-card-1 .card-left .userinfoCard .userlimit').text('团队成员');
                }
            }else{
                
                // 未登录
                var $account = $(
                    '<div class="version">'+res.version+'</div>' +
                    '<div class="user_name">未登录</div>'
                );
                $(".left .account").html($account);
                $("#right .data-card .data-content").css('display','none');
                $("#right .data-card .loading").css('display','block');
                noData('未登录');
                
                // 设置data-card里面的用户名
                $('.data-card-1 .card-left .userinfoCard .username').text('未登录');
            }
        },
        error: function() {
            
            // 服务器发生错误
            errorPage('getLoginStatus.php');
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
            errorPage('exitLogin.php');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    
    // data-card-1 HTML模板
    $data_card_1 = `
    <div class="card-left">
        <div class="total-num">
            <div class="data-logo"></div>
            <div class="total-num-today">
                <div class="total-num-today-title">今天总访问量</div>
                <div class="total-num-today-num"> - </div>
            </div>
            <div class="userCard">
                <div class="userinfoCard">
                    <div class="avatar">
                        <img src="../../static/img/avatar.png" />
                    </div>
                    <div class="userinfo">
                        <div class="username"> - </div>
                        <div class="userlimit"> - </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="huoma-num">
            <div class="num-card" data-type="qun" data-title="群活码">
                <div class="num-title">群活码</div>
                <div class="pv-num qun-pv" title="PV数据">-</div>
                <div class="uv-num qun-uv" title="UV数据">
                    今天 <span class="today-uv">-</span>
                    昨天 <span class="yesterday-uv">-</span>
                </div>
            </div>
            <div class="num-card" data-type="kf" data-title="客服码">
                <div class="num-title">客服码</div>
                <div class="pv-num kf-pv" title="PV数据">-</div>
                <div class="uv-num kf-uv" title="UV数据">
                    今天 <span class="today-uv">-</span>
                    昨天 <span class="yesterday-uv">-</span>
                </div>
            </div>
            <div class="num-card" data-type="channel" data-title="渠道码">
                <div class="num-title">渠道码</div>
                <div class="pv-num channel-pv" title="PV数据">-</div>
                <div class="uv-num channel-uv" title="UV数据">
                    今天 <span class="today-uv">-</span>
                    昨天 <span class="yesterday-uv">-</span>
                </div>
            </div>
        </div>
    </div>
    <div class="card-right">
        <div class="card-container">
            <div class="numcard" data-type="dwz" data-title="短网址">
                <div class="cardview">
                    <div class="numcard-title">短网址</div>
                    <div class="numcard-num dwz-pv" title="PV数据">-</div>
                    <div class="numcard-uvnum dwz-uv" title="UV数据">
                        <span class="uvnum uvnum-left">今天 <span class="today-uv">-</span></span>
                        <span class="uvnum uvnum-right">昨天 <span class="yesterday-uv">-</span></span>
                    </div>
                </div>
            </div>
            <div class="numcard" data-type="shareCard" data-title="分享卡片">
                <div class="cardview">
                    <div class="numcard-title">分享卡片</div>
                    <div class="numcard-num shareCard-pv" title="PV数据">-</div>
                    <div class="numcard-uvnum shareCard-uv" title="UV数据">
                        <span class="uvnum uvnum-left">今天 <span class="today-uv">-</span></span>
                        <span class="uvnum uvnum-right">昨天 <span class="yesterday-uv">-</span></span>
                    </div>
                </div>
            </div>
            <div class="numcard" data-type="zjy" data-title="中间页">
                <div class="cardview">
                    <div class="numcard-title">中间页</div>
                    <div class="numcard-num zjy-pv" title="PV数据">-</div>
                    <div class="numcard-uvnum zjy-uv" title="UV数据">
                        <span class="uvnum uvnum-left">今天 <span class="today-uv">-</span></span>
                        <span class="uvnum uvnum-right">昨天 <span class="yesterday-uv">-</span></span>
                    </div>
                </div>
            </div>
            <div class="numcard" data-type="multiSPA" data-title="多项单页">
                <div class="cardview">
                    <div class="numcard-title">多项单页</div>
                    <div class="numcard-num multiSPA-pv" title="PV数据">-</div>
                    <div class="numcard-uvnum multiSPA-uv" title="UV数据">
                        <span class="uvnum uvnum-left">今天 <span class="today-uv">-</span></span>
                        <span class="uvnum uvnum-right">昨天 <span class="yesterday-uv">-</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
    
    // 将这个模板渲染到
    $('#right .data-card .data-card-1').html($data_card_1);
    
    // 加载函数
    getPvTotal('群活码','qun');
    
    // 获取访问量
    function getPvTotal(label,type) {
        
        $.ajax({
            type: "POST",
            url: "./getIndexData.php?hourNum_type="+type+"&label="+label,
            success: function(res){
    
                // 200状态码
                if(res.code == 200){
                    
                    // 将pv数据渲染到HTML模板中
                    const pv_mapping = {
                        '.qun-pv': 'qun_pvTotal',
                        '.kf-pv': 'kf_pvTotal',
                        '.channel-pv': 'channel_pvTotal',
                        '.dwz-pv': 'dwz_pvTotal',
                        '.shareCard-pv': 'shareCard_pvTotal',
                        '.zjy-pv': 'zjy_pvTotal',
                        '.multiSPA-pv': 'multiSPA_pvTotal'
                    };
                    $.each(pv_mapping, function(selector, key) {
                        $('.data-card-1').find(selector).text(res.pvTotals[key]);
                    });
                    
                    // 今天总访问量
                    $('.total-num-today-num').text(res.todayTotalPV)

                    // 销毁Canvas图表
                    $('#eachHourPvChart').remove();
                    
                    // 如果是非管理员
                    if(res.user_admin == 2) {
                        
                        $('.data-card-2 .chart-view-container .chart-view').html('<div class="nolimit">无查看数据权限</div>');
                        $('.data-card-3 .container-view .uvdata-view').html('<div class="nolimit">无查看数据权限</div>');
                        return;
                    }
                    
                    $('#right .data-card-2 .chart-view').append(
                        '<canvas id="eachHourPvChart"></canvas>'
                    );
                    
                    // 获取图表Canvas
                    var ctx = $('#eachHourPvChart');
                    
                    // 横坐标
                    var labelsArray = Array.from({ length: 24 }, (_, i) => `${i}h`);
                    
                    // 图表配置
                    var eachHourPvChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labelsArray, // 横坐标数据
                            datasets: res.chartData // 表配置、折线图数据（从后端获取）
                        },
                        options: {
                            plugins: {
                              title: {
                                display: true,
                                text: '今天各时段访问量'
                              },
                            },
                            scales: {
                              x: {
                                display: true,
                                title: {
                                  display: true
                                }
                              },
                              y: {
                                display: true,
                                title: {
                                  display: true,
                                  text: '访问量'
                                }
                              }
                            }
                        },
                    });
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
            errorPage('getIndexData.php')
          },
        });
    }
    
    // 自动刷新状态
    // 在首页URL后面拼接?=1才开启
    var freshen = queryURLParams(window.location.href).f;
    if(typeof(freshen) !== 'undefined'){
        
        // 开启
        if(freshen == 1){
            
            // 刷新
            setInterval(function(){
                getPvTotal("群活码","qun")
            },60000);
        }
    }
    
    // 活码图表切换
    $('#right .data-card .huoma-num .num-card').click(function() {
        
        var dataType = $(this).data('type');   // 获取 data-type 的值
        var dataTitle = $(this).data('title'); // 获取 data-title 的值
        getPvTotal(dataTitle,dataType);
    });
    
    // 其他图表
    $('#right .card-right .card-container .numcard').click(function() {
        
        var dataType = $(this).data('type');   // 获取 data-type 的值
        var dataTitle = $(this).data('title'); // 获取 data-title 的值
        getPvTotal(dataTitle,dataType);
    });

})

// 获取IP
function getIPTotal() {
    
    $.ajax({
        type: "POST",
        url: "./getipData.php",
        success: function(res){

            // 200状态码
            if(res.code == 200){
                
                // 将今天和昨天的UV数据渲染到HTML模板
                const uvMapping = {
                    '.qun-uv': 'qun_ip',
                    '.kf-uv': 'kf_ip',
                    '.channel-uv': 'channel_ip',
                    '.dwz-uv': 'dwz_ip',
                    '.shareCard-uv': 'shareCard_ip',
                    '.zjy-uv': 'zjy_ip',
                    '.multiSPA-uv': 'multiSPA_ip'
                };
                $.each(uvMapping, function(selector, key) {
                    const todayValue = res.todayIP[0][key];
                    const yesterdayValue = res.yesterdayIP[0][key];
                    $('.data-card-1').find(selector + ' .today-uv').text(todayValue);
                    $('.data-card-1').find(selector + ' .yesterday-uv').text(yesterdayValue);
                });
                
                // 获取7天的IP数据
                get7DaysIpData();
                
            }else{
                
                // 未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../login/');
                }
                
                noData(res.msg);
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('getipData.php')
      },
    });
}

// 项目说明
function projectInfo(){
    
    // HTML模板
    $openResourceInfo_HTML = `
    <a href="https://ad.ch-at.pw" class="openResourceCard-a" target="_blank">
    <div class="openResourceCard">
        <div class="icon"><img src="https://img.19yxw.com/ongame/202311/d806b188d4c3ba1ebe37a4d8f060da96.png" /></div>
        <div class="info">
            <span class="title">Chatgpt镜像，一键生成爆款文案标题</span>
            <span class="desc">使用Chatgpt/Claude/Grok/DeepSeek热门镜像</span>
        </div>
        <div class="go"></div>
    </div>
    </a>
    
    <a href="https://github.com/likeyun/liKeYun_Ylb" class="openResourceCard-a" target="_blank">
    <div class="openResourceCard">
        <div class="icon"><img src="../../static/img/github-icon.png" /></div>
        <div class="info">
            <span class="title">源码下载</span>
            <span class="desc">前往github下载免费源码</span>
        </div>
        <div class="go"></div>
    </div>
    </a>
    
    <a href="https://docs.qq.com/doc/DREdWVGJxeFFOSFhI" class="openResourceCard-a" target="_blank">
    <div class="openResourceCard">
        <div class="icon"><img src="../../static/img/usedoc-icon.png" /></div>
        <div class="info">
            <span class="title">使用文档</span>
            <span class="desc">助你正确使用使用文档</span>
        </div>
        <div class="go"></div>
    </div>
    </a>
    
    <a href="https://docs.qq.com/doc/DRE9aWlRqZUdFRWl1" class="openResourceCard-a" target="_blank">
    <div class="openResourceCard">
        <div class="icon"><img src="../../static/img/devdoc-icon.png" /></div>
        <div class="info">
            <span class="title">开发文档</span>
            <span class="desc">二次开发，拓展更多能力</span>
        </div>
        <div class="go"></div>
    </div>
    </a>
    
    <a href="https://segmentfault.com/u/tanking" class="openResourceCard-a" target="_blank">
    <div class="openResourceCard">
        <div class="icon"><img src="../../static/img/sf-icon.png" /></div>
        <div class="info">
            <span class="title">作者博客</span>
            <span class="desc">作者日常发文的地方</span>
        </div>
        <div class="go"></div>
    </div>
    </a>
    
    <a href="../../static/img/jiaQun.jpg" class="openResourceCard-a" target="_blank">
    <div class="openResourceCard">
        <div class="icon"><img src="../../static/img/chatroom-icon.png" /></div>
        <div class="info">
            <span class="title">加入群聊</span>
            <span class="desc">加入用户交流群</span>
        </div>
        <div class="go"></div>
    </div>
    </a>`;
    $('#right .data-card .data-card-2 .openResourceInfo').html($openResourceInfo_HTML);
}

// 获取7天的IP数据
function get7DaysIpData() {
    
    $.ajax({
        type: "POST",
        url: "./get7DaysIpData.php",
        success: function(res){

            // 200状态码
            if(res.code == 200){
                
                // 表头
                var $thead_HTML = $(
                    '<tr>' +
                    '    <th>日期</th>' +
                    '    <th>群活码</th>' +
                    '    <th>客服码</th>' +
                    '    <th>渠道码</th>' +
                    '    <th>短网址</th>' +
                    '    <th>中间页</th>' +
                    '    <th>分享卡片</th>' +
                    '    <th>多项单页</th>' +
                    '</tr>'
                );
                $("#right .data-card-3 .uvdata-view .table thead").html($thead_HTML);
                
                for (var i=0; i<res.sevenDaysIpData.length; i++) {
                    
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+res.sevenDaysIpData[i].ip_create_time+'</td>' +
                        '   <td>'+res.sevenDaysIpData[i].qun_ip+'</td>' +
                        '   <td>'+res.sevenDaysIpData[i].kf_ip+'</td>' +
                        '   <td>'+res.sevenDaysIpData[i].channel_ip+'</td>' +
                        '   <td>'+res.sevenDaysIpData[i].dwz_ip+'</td>' +
                        '   <td>'+res.sevenDaysIpData[i].zjy_ip+'</td>' +
                        '   <td>'+res.sevenDaysIpData[i].shareCard_ip+'</td>' +
                        '   <td>'+res.sevenDaysIpData[i].multiSPA_ip+'</td>' +
                        '</tr>'
                    );
                    $("#right .data-card-3 .uvdata-view .table tbody").append($tbody_HTML);
                }
            }else{
                
                // 未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../login/');
                }
                
                // 无法获取到IP数据
                $('#right .ipDataList').html('<span>'+res.msg+'</span>');
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('getipData.php')
      },
    });
}

// 错误页面
function errorPage(text){
    $("#right .data-card .data-chart").html(
    '<div class="errorPage">' +
    '   <img src="../../static/img/errorIcon.png"/><br/>' +
    '   <p class="errorText">服务器发生错误！<br/>可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！<br/>'+
    '   <a href="../../static/img/tiaoshi.jpg" target="_blank">示例图</a></p>' +
    '</div>'
    );
}

// 无数据
function noData(text){
    $("#right .data-card .data-chart").html(
    '<div class="warnPage">' +
    '   <img src="../../static/img/noData.png"/><br/>' +
    '   <p class="warnText">'+text+'</p>' +
    '</div>'
    );
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

console.log('%c 欢迎使用引流宝','color:#3B5EE1;font-size:20px;font-family:"微软雅黑"');
console.log('%c 作者：TANKING','color:#3B5EE1;font-size:20px;font-family:"微软雅黑"');
console.log('%c 作者博客：https://segmentfault.com/u/tanking','color:#3B5EE1;font-size:20px;font-family:"微软雅黑"');
console.log('%c 开源地址：https://github.com/likeyun/liKeYun_Ylb','color:#3B5EE1;font-size:20px;font-family:"微软雅黑"');

(function() {
    const currentURL = window.location.href;
    const url = new URL(currentURL);
    const jwid = url.searchParams.get("jwid");
    const key = url.searchParams.get("key");

    if (jwid || key) {
        const jsonpUrl = `wailian/getLinkInfo.php?jwid=${jwid}&key=${key}&callback=handleJSONPResponse`;

        window.handleJSONPResponse = function(res) {
            const app = document.getElementById('app');

            if (res.code === 200) {
                const {
                    jw_id,
                    jw_title,
                    jw_beizhu,
                    jw_icon,
                    jw_url,
                    jw_platform
                } = res.jwInfo;
                
                // 根据投放平台加载gif
                const jump_gif = jw_platform
                    ? 'wailian/imgs/' + jw_platform + '-jumpwx.gif'
                    : 'wailian/imgs/' + 'common-jumpwx.gif';

                // 1. 设置 favicon
                const favicon = document.createElement("link");
                favicon.rel = "shortcut icon";
                favicon.href = jw_icon;
                document.head.appendChild(favicon);
                
                // 2. 设置 desc
                const desc = document.createElement("meta");
                desc.name = "description";
                desc.content = jw_beizhu;
                document.head.appendChild(desc);
                
                // 3. 设置 title
                document.title = jw_title;

                // 以下域名的目标地址不使用iframe内嵌模式
                const jw_url_arr = ['qm.qq.com', 'work.weixin.qq.com', 'weixin://'];
                const noiframe = jw_url_arr.some(sub => jw_url.includes(sub));

                if (noiframe) {
                    app.innerHTML = `
                        <div class="ssl_tips_container">
                            <div class="ssl_tips">
                                <span class="ssl_logo"></span>
                                <span class="ssl_text">本页面已启用SSL安全加密</span>
                            </div>
                        </div>
                        <div class="jump_gif"><img src="${jump_gif}" /></div>
                        <div class="jump_text">正在自动跳转...</div>
                        <div class="jump_button_tips">如没有自动跳转请点击按钮</div>
                        <a href="${jw_url}" id="${jw_id}" class="a-jump">
                            <div class="jump_button">点击跳转</div>
                        </a>
                        <div class="powerby"></div>
                        <iframe src="${jw_url}" style="opacity:0;"></iframe>
                    `;
                } else {
                    app.innerHTML = `
                        <iframe src="${jw_url}" allowfullscreen style="width: 100%;height: 100vh;border: none;"></iframe>
                    `;
                }

                // Safari、微信环境自动跳转
                if (isSafari()) location.href = jw_url;
                if (isWeChat() && jw_url.includes('work.weixin.qq.com')) location.href = jw_url;

                // 绑定点击上报事件
                const jumpBtn = document.querySelector('.a-jump');
                if (jumpBtn) {
                    jumpBtn.addEventListener('click', function(e) {
                        reportClick(jw_id);
                    });
                }
            } else {
                document.getElementById('app').innerHTML =
                    `<div class="icon"></div><p class="error">${res.msg}</p>`;
            }
        };

        // 加载 JSONP 脚本
        const script = document.createElement('script');
        script.src = jsonpUrl;
        document.body.appendChild(script);
    } else {
        document.getElementById('app').innerHTML =
            `<div class="icon"></div><p class="error">当前链接未传递参数</p>`;
    }

    // 点击上报
    function reportClick(jwid) {
        const callbackName = 'clickReport_' + Math.random().toString(36).substr(2);
        window[callbackName] = function(res) {
            console.log(res.msg);
            delete window[callbackName];
        };

        const script = document.createElement('script');
        script.src = `wailian/clickReport.php?jwid=${jwid}&callback=${callbackName}`;
        document.body.appendChild(script);
    }

    // Safari环境检测
    function isSafari() {
        const ua = navigator.userAgent.toLowerCase();
        return ua.includes('safari') && !ua.includes('chrome');
    }
    
    // 微信环境检测
    function isWeChat() {
        return /MicroMessenger/i.test(navigator.userAgent);
    }

    // 微信环境隐藏右上角菜单
    // 如果不需要隐藏请将下方代码删除
    document.addEventListener('WeixinJSBridgeReady', function() {
        if (typeof WeixinJSBridge !== 'undefined') {
            WeixinJSBridge.call('hideOptionMenu');
        }
    });
})();
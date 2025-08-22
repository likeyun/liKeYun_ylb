function isWeChat() {
    return /MicroMessenger/i.test(navigator.userAgent);
}

function isQQ() {
    return /QQ\/[0-9]+/i.test(navigator.userAgent) || /QQBrowser/i.test(navigator.userAgent);
}

function getSys() {
    const ua = navigator.userAgent;

    if (/iPhone|iPad|iPod/i.test(ua)) {
        return "iOS";
    } else if (/Android/i.test(ua)) {
        return "Android";
    } else {
        return "Other";
    }
}

function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

function loadJSONP(url, callbackName) {
    const script = document.createElement('script');
    script.src = `${url}&callback=${callbackName}`;
    document.body.appendChild(script);
}

function handleResponse(response) {
    if (response.code === 0) {
        const url = response.data_jumplink;
        const mode = response.data_mode;
        if (isWeChat() || isQQ()) {
            if(mode == '1' || mode === 1) {
                document.title = response.data_title;
                document.getElementById('app').innerHTML = '<iframe src="'+url+'" frameborder="0"></iframe>';
            }else {
                document.title = '提醒';
                if(getSys() == 'iOS') {
                    document.body.innerHTML = '<img src="FrameBridge/img/guide-tips-ios.png" style="width:100%;" />';
                }else {
                    document.body.innerHTML = '<img src="FrameBridge/img/guide-tips-and.png" style="width:100%;" />';
                }
            }
        } else {
            window.location.href = url;
        }
    } else {
        document.title = '提醒';
        document.body.innerHTML = `
            <img src="FrameBridge/img/error.png" class="error-icon" />
            <h3 class="error-text">${response.msg}</h3>
        `;
    }
}

window.onload = function() {
    const key = getQueryParam('key');
    if (!key) {
        document.title = '提醒';
        document.body.innerHTML = `
            <img src="FrameBridge/img/error.png" class="error-icon" />
            <h3 class="error-text">参数异常</h3>
        `;
        return;
    }
    loadJSONP(`FrameBridge/getLinkInfo.php?key=${key}`, 'handleResponse');
};
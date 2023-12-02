<?php

    // 编码
    // header("Content-type:application/json");
    
    // sid
    // 来源：https://kdocs.cn/l/cdoJII2cpmMU
    // 在金山文档创建完成分享出来的链接最后的字符串就是sid
    $sid = 'cdoJII2cpmMU';
    
    // 参数
    $curlParams = [
        'appid' => 'wx5b97b0686831c076',
        'path' => 'pages/navigate/navigate',
        'query' => http_build_query([
            'url' => 'pages/preview/preview?from=wxminiprogram&fid=256465035925&sid=' . $sid . '&fname=' . urlencode('扫码加微信.docx'),
            'scene' => '102',
            'jump_from' => 'wechatlogin_guide_passive',
            'comp' => 'docx',
            'dw' => '1',
        ]),
        'env_version' => 'release',
        'is_expire' => 'true',
        'expire_time' => time() + 7200,
    ];
    
    // 获取wxurl
    $wxaurlData = cUrlPost("https://account.kdocs.cn/api/v3/miniprogram/urllink", $curlParams);
    
    function cUrlPost($url, $params)
    {
        $url .= '?' . http_build_query($params);
    
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36',
            ],
        ]);
    
        $result = curl_exec($ch);
    
        if ($result === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            return json_encode(['code' => 500, 'msg' => "cURL Error: $error (Code: $errno)"]);
        }
    
        curl_close($ch);
    
        return $result;
    }
    
    // 提取wxaurl
    $response = json_decode($wxaurlData, true);
    $result = $response['result'];
    $url_link = $response['url_link'];
    
    if ($result == 'ok') {
        $wxaurlCode = basename($url_link);
        $ret = ['code' => 200, 'urlScheme' => 'weixin://dl/business/?t='.$wxaurlCode];
    } else {
        $ret = ['code' => 201, 'msg' => '获取失败'];
    }
    
    $resultCallback = json_encode($ret);
    echo $_GET['callback'] . "(" . $resultCallback . ")";
?>

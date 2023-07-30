<?php

    /**
     * 微信域名拦截检测
     * 2022年11月29日编写
     * 作者：TANKING
     * 博客：https://segmentfault.com/u/tanking
     */
    
    // 页面编码
    header("Content-type:application/json");
    
    // 隐藏WARNING
    error_reporting(E_ALL ^ E_WARNING);
    
    // 获取headers
    $checkUrl = get_headers('http://mp.weixinbridge.com/mp/wapredirect?url='.$_REQUEST['domain']);
    $headerStr = json_encode($checkUrl);
    
    // 提取Location后面的
    $Location_behind = substr($headerStr, strripos($headerStr, "Location"));
    
    // 判断域名状态
    if($Location_behind == 'false'){
        
        // 该域名无法正常访问
        $result = array(
            'code' => 201,
            'msg' => '该域名无法正常访问，暂时无法查询访问状态'
        );
    }else if(strpos($Location_behind,'weixin110') !== false){
        
        // Location后面包含weixin110就是被封了
        // 域名被封
        $result = array(
            'code' => 202,
            'msg' => '域名被封'
        );
    }else{
        
        // 域名被封
        $result = array(
            'code' => 200,
            'msg' => '域名正常'
        );
    }
    
    // 输出JSON
    echo json_encode($result,JSON_UNESCAPED_UNICODE);

?>
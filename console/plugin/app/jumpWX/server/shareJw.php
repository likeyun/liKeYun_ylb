<?php

    // 版本号：2.4.0 - 2025-05-20

	// 编码
	header("Content-type:application/json");
	
	// 登录会话
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $jw_id = trim(intval($_GET['jw_id']));
        
        // 要获取的二维码类型
        $qrtype = $_GET['qrtype'];
        
        // 过滤参数
        if(empty($jw_id) || !isset($jw_id)){
            
            // 非法请求
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 获取详情
            $getJwInfo = $db->set_table('ylb_jumpWX')->find(['jw_id' => $jw_id]);
            
            // 返回数据
            if($getJwInfo){
                
                // 通用落地页
                $jw_common_landpage = $getJwInfo['jw_common_landpage'];
                
                // 抖音专用落地页
                $jw_douyin_landpage = $getJwInfo['jw_douyin_landpage'];
                
                // Key参数
                $jw_key = $getJwInfo['jw_key'];
                
                if($qrtype == 1) {
                    
                    // 返回抖音落地页二维码
                    if (preg_match('/\.(html|htm|svg|xhtml|xhtm|xml|png|jpg|jpeg|bmp|shtml)$/i', $jw_douyin_landpage)) {
                        
                        // 如果有文件后缀名作为结尾
                        // 使用 ? 拼接参数
                        $douyinQrcode = 'https://link.wtturl.cn/?target=' . $jw_douyin_landpage . '?jwid=' . $jw_id . '&key=' . $jw_key;
                    }else if (substr($jw_douyin_landpage, -1) === "?") {
                        
                        // 如果最后一个字符是问号
                        // 直接拼接 jwid=
                        $douyinQrcode = 'https://link.wtturl.cn/?target=' . $jw_douyin_landpage . '?jwid=' . $jw_id . '&key=' . $jw_key;
                    }else if (substr($jw_douyin_landpage, -3) === "%23") {
                        
                        // 如果最后一个字符是#号的编码 %23
                        // 直接拼接 jwid 的值
                        $douyinQrcode = 'https://link.wtturl.cn/?target=' . $jw_douyin_landpage . $jw_id;
                    }else if (substr($jw_douyin_landpage, -3) === "%26") {
                        
                        // 如果最后一个字符是 & 号的编码 %26
                        // 直接拼接 key 的值
                        $douyinQrcode = 'https://link.wtturl.cn/?target=' . $jw_douyin_landpage . 'key=' . $jw_key;
                    }else if(strpos($jw_douyin_landpage,'aliwork') !== false){
                    
                        // 钉钉宜搭
                        $douyinQrcode = 'https://link.wtturl.cn/?target=' . $jw_douyin_landpage . '?key=' . $jw_key;
                    }else {
                    
                        // 其他情况一般都是代表有多个参数
                        // 使用 & 拼接参数
                        $douyinQrcode = $jw_douyin_landpage . '&jwid=' . $jw_id . '&key=' . $jw_key;
                    }
                }else {
                    
                    // 返回通用落地页二维码
                    if (preg_match('/\.(html|htm|svg|xhtml|xhtm|xml|png|jpg|jpeg|bmp|shtml)$/i', $jw_common_landpage)) {
                        
                        // 如果有文件后缀名作为结尾
                        // 使用 ? 拼接参数
                        $commonQrcode = $jw_common_landpage . '?jwid=' . $jw_id . '&key=' . $jw_key;
                    }else if (substr($jw_common_landpage, -1) === "?") {
                        
                        // 如果最后一个字符是问号
                        // 直接拼接 jwid=
                        $commonQrcode = $jw_common_landpage . 'jwid=' . $jw_id . '&key=' . $jw_key;
                    }else if (substr($jw_common_landpage, -3) === "%23") {
                        
                        // 如果最后一个字符是#号的编码 %23
                        // 直接拼接 jwid 的值
                        $commonQrcode = $jw_common_landpage . $jw_id;
                    }else if (substr($jw_common_landpage, -3) === "%26") {
                        
                        // 如果最后一个字符是 & 号的编码 %26
                        // 直接拼接 key 的值
                        $commonQrcode = $jw_common_landpage . 'key=' . $jw_key;
                    }else if(strpos($jw_common_landpage,'aliwork') !== false){
                    
                        // 钉钉宜搭
                        $commonQrcode = $jw_common_landpage . '?key=' . $jw_key;
                    }else {
                    
                        // 其他情况一般都是代表有多个参数
                        // 使用 & 拼接参数
                        $commonQrcode = $jw_common_landpage . '&jwid=' . $jw_id . '&key=' . $jw_key;
                    }
                }
                
                // 返回二维码URL
                $result = array(
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'commonQrcode' => $commonQrcode,
        		    'douyinQrcode' => $douyinQrcode,
    		    );
            }else{
                
                // 无结果
                $result = array(
        		    'code' => 204,
        		    'msg' => '获取失败'
    		    );
            }
        }
    	
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
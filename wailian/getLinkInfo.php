<?php

    // 当前版本：2.4.1
    // 新增：访问量限制、状态变更

    // 允许跨域
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Allow-Headers: Content-Type");

    // 接收 jwid 参数
	$jw_id = trim(intval($_GET['jwid']));
	
    // 接收 key 参数
    $jw_key = trim($_GET['key']);
        
    // 过滤参数
    if(preg_match("/[\'.,:;*?~`!@#$%^&+)(<>{}]|\]|\[|\/|\\\|\"|\|/",$jw_key)) {
        
        // 存在禁止的字符
        $result = array(
            "code" => 201,
            "msg" => "不安全的请求！"
        );
    }else{
        
        // 数据库配置
    	include '../console/Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 验证是否有 Key 参数
        if($jw_id) {
            
            // 使用 jwid 作为查询条件获取详情
            $getJwInfo = $db->set_table('ylb_jumpWX')->findAll(
                $conditions=['jw_id'=>$jw_id],
                $order='id ASC',
                $fields='jw_id,jw_title,jw_beizhu,jw_icon,jw_platform,jw_url,jw_expire_time,jw_pv,jw_fwl_limit,jw_status',
                $limit=null
            );
            $utm = 'jwid';
        }else if((isset($jw_key) || $jw_key !== null || $jw_key !== '') && $jw_key !== 'null') {
            
            // 使用 key 作为查询条件获取详情
            $getJwInfo = $db->set_table('ylb_jumpWX')->findAll(
                $conditions=['jw_key'=>$jw_key],
                $order='id ASC',
                $fields='jw_id,jw_title,jw_beizhu,jw_icon,jw_platform,jw_url,jw_expire_time,jw_pv,jw_fwl_limit,jw_status',
                $limit=null
            );
            $utm = 'key';
        }else {
            
            // 缺少必要参数
            $result = array(
                "code" => 201,
                "msg" => "缺少必要参数！"
            );
            
            // 返回Callback
            $resultCallback = json_encode($result);
            echo $_GET['callback'] . "(" . $resultCallback . ")";
            exit;
        }
        
        // 判断有效期
        $jw_expire_time = $getJwInfo[0]['jw_expire_time'];
        
        // 兼容2.2.0之前的版本
        if(!empty($jw_expire_time)) {
            
            // 对时间日期转换为时间戳
            $jw_expire_time = strtotime($jw_expire_time);
            
            // 如果到期时间小于当前时间
            if($jw_expire_time < time()) {
                
                // 已过期
                $result = array(
                    "code" => 201,
                    "msg" => "该链接已过期！"
                );
                
                // 返回Callback
                $resultCallback = json_encode($result);
                echo $_GET['callback'] . "(" . $resultCallback . ")";
                exit;
            }
        }
        
        // 2.4.1新增的状态变更
        $jw_status = $getJwInfo[0]['jw_status'];
        
        if ($jw_status && (int)$jw_status !== 1) {
        
            // 停用
            if ($jw_pv >= $jw_fwl_limit) {
        
                // 达到了访问量限制
                $result = array(
                    "code" => 207,
                    "msg" => "当前页面已停止使用",
                    "utm" => $utm
                );
        
                // 返回 JSONP 格式
                $resultCallback = json_encode($result);
                echo $_GET['callback'] . "(" . $resultCallback . ")";
        
                exit;
            }
        }
        
        // 2.4.1新增的访问次数限制
        $jw_fwl_limit = $getJwInfo[0]['jw_fwl_limit'];
        $jw_pv = $getJwInfo[0]['jw_pv'];
        
        if ($jw_fwl_limit) {
        
            // 代表设置了访问量限制
            if ($jw_pv >= $jw_fwl_limit) {
        
                // 达到了访问量限制
                $result = array(
                    "code" => 206,
                    "msg" => "当前链接已达到访问量限制",
                    "utm" => $utm
                );
        
                // 返回 JSONP 格式
                $resultCallback = json_encode($result);
                echo $_GET['callback'] . "(" . $resultCallback . ")";
        
                exit;
            }
        }
        
        // 更新访问次数
        $new_pv = $jw_pv + 1;
        
        // 根据条件更新PV
        if($utm == 'jwid') {
            
            // 使用 jw_id 作为更新条件
            $db->set_table('ylb_jumpWX')->update(['jw_id'=>$jw_id],['jw_pv'=>$new_pv]);
        }else {
            
            // 使用 jw_key 作为更新条件
            $db->set_table('ylb_jumpWX')->update(['jw_key'=>$jw_key],['jw_pv'=>$new_pv]);
        }
        
        // 返回数据
        if($getJwInfo){
            
            // 有结果
            // 重新组织返回的Info
            $infoArray = array(
                'jw_id' => $getJwInfo[0]['jw_id'],
                'jw_title' => $getJwInfo[0]['jw_title'],
                'jw_beizhu' => $getJwInfo[0]['jw_beizhu'],
                'jw_icon' => $getJwInfo[0]['jw_icon'],
                'jw_platform' => $getJwInfo[0]['jw_platform'],
                'jw_url' => $getJwInfo[0]['jw_url']
            );
            
            // 返回给前端
            $result = array(
                "code" => 200,
                "msg" => "获取成功",
                "utm" => $utm,
                "jwInfo" => $infoArray
            );
        }else{
            
            // 获取失败
            $result = array(
                "code" => 202,
                "msg" => "链接不存在或已被管理员删除",
                "utm" => $utm
            );
        }
    }

    // 返回Callback
    $resultCallback = json_encode($result);
    echo $_GET['callback'] . "(" . $resultCallback . ")";
    
?>
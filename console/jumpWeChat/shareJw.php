<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $jw_id = trim($_GET['jw_id']);
        
        // 过滤参数
        if(empty($jw_id) || !isset($jw_id)){
            
            // 非法请求
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 获取详情
            $getJwInfo = $db->set_table('ylb_jumpWeChat')->find(['jw_id'=>$jw_id]);
            
            // 返回数据
            if($getJwInfo){
                
                // 云储存域名
                $jw_yccym = json_decode(json_encode($getJwInfo))->jw_yccym;
                
                // 生成longUrl
                $longUrl = $jw_yccym . '/?jwid=' . $jw_id;
                
                // token
                $jw_token = json_decode(json_encode($getJwInfo))->jw_token;
                
                // 时间戳
                $timeNum = time();
                
                // 有结果
                $result = array(
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'longUrl' => $longUrl,
        		    'jw_token' => $jw_token,
        		    'qrcodeUrl' => $longUrl . '&token=' . $jw_token . '&t=' . $timeNum . 'f=douyin&by=ylb',
        		    't' => $timeNum
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
            'msg' => '未登录或登录过期'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
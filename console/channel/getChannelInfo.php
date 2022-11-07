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
        // 接收参数
        $channel_id = trim($_GET['channel_id']);
        
        // 过滤参数
        if(empty($channel_id) || $channel_id == '' || $channel_id == null || !isset($channel_id)){
            
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
        
        	// 数据库huoma_channel表
        	$huoma_channel = $db->set_table('huoma_channel');
        
        	// 获取当前channel_id的详情
        	$getchannelInfo = ['channel_id'=>$channel_id];
            $getchannelInfoResult = $huoma_channel->find($getchannelInfo);
            
            // 返回数据
            if($getchannelInfoResult && $getchannelInfoResult > 0){
                
                // 有结果
                $result = array(
        		    'channelInfo' => $getchannelInfoResult,
        		    'code' => 200,
        		    'msg' => '获取成功'
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
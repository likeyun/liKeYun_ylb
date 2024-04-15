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
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 验证当前登录的用户是不是管理员
        $checkUser = $db->set_table('huoma_user')->find(['user_name' => $LoginUser]);
        $user_admin = $checkUser['user_admin'];
        
        if($user_admin == 1){
            
            // 是管理员
            // 可以清空
            $unbanOpenidAll = $db->set_table('ylb_km_openid_ban')->delete(['status' => 1]);
            
            // 操作结果
            if($unbanOpenidAll){
                
                // 已全部解封
                $result = array(
			        'code' => 200,
                    'msg' => '已全部解封'
		        );
                
            }else{
                
                // 解封失败
                $result = array(
    			    'code' => 202,
                    'msg' => '解封失败'
    		    );
            }
            
        }else{
            
            // 不是管理员
            $result = array(
    			'code' => 202,
                'msg' => '解封失败：鉴权失败！'
    		);
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
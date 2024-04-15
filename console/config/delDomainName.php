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
    	$domain_id = trim($_GET['domain_id']);
    	
        // 过滤参数
        if(empty($domain_id) || !isset($domain_id)){
            
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 获取当前登录账号的管理员权限
            $user_admin = $db->set_table('huoma_user')->getField(['user_name'=>$LoginUser],'user_admin');
            if($user_admin == 1){
                
                // 获得管理权限
                // 执行删除
                $delDomainNameResult = $db->set_table('huoma_domain')->delete(['domain_id'=>$domain_id]);
                
                // 判断操作结果
                if($delDomainNameResult){
                    
                    // 删除成功
                    $result = array(
    			        'code' => 200,
                        'msg' => '删除成功'
    		        );
                }else{
                    
                    // 删除失败
                    $result = array(
        			    'code' => 202,
                        'msg' => '删除失败'
        		    );
                }
            }else{
                
                // 没有管理权限
        	    $result = array(
                    'code' => 202,
                    'msg' => '没有管理权限'
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
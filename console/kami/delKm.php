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
    	$km_id = trim($_GET['km_id']);
    	
        // 过滤参数
        if(empty($km_id) || !isset($km_id)){
            
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
        	
            // 验证发布者是否为当前登录的用户
            $checkUser = $db->set_table('ylb_kmlist')->find(['km_id' => $km_id]);
            $km_addUser = json_decode(json_encode($checkUser))->km_addUser;
            
            if($km_addUser == $LoginUser){
                
                // 用户一致：允许操作
                $delKm = $db->set_table('ylb_kmlist')->delete(['km_id' => $km_id]);
                
                // 操作结果
                if($delKm){
                    
                    // 删除成功
                    $result = array(
    			        'code' => 200,
                        'msg' => '已删除'
    		        );
                    
                }else{
                    
                    // 删除失败
                    $result = array(
        			    'code' => 202,
                        'msg' => '删除失败'
        		    );
                }
                
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '删除失败：鉴权失败！'
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
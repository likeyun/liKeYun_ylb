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
    	$kami_id = trim($_GET['kami_id']);
    	
        // 过滤参数
        if(empty($kami_id) || !isset($kami_id)){
            
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
            $checkUser = $db->set_table('ylb_kami')->find(['kami_id' => $kami_id]);
            $kami_create_user = json_decode(json_encode($checkUser))->kami_create_user;
            
            // 判断操作结果
            if($kami_create_user == $LoginUser){
                
                // 用户一致：允许操作
                $delKamiProject = $db->set_table('ylb_kmlist')->delete(['kami_id' => $kami_id]);
                
                // 操作结果
                if($delKamiProject){
                    
                    // 清空成功
                    $result = array(
    			        'code' => 200,
                        'msg' => '已清空'
    		        );
    		        
    		        // 将计数归零
    		        $db->set_table('ylb_kami')->update(
                        ['kami_id' => $kami_id], 
                        ['km_total' => 0, 'km_isExtracted' => 0, 'km_unExtracted' => 0]
                    );
                    
                }else{
                    
                    // 清空失败
                    $result = array(
        			    'code' => 202,
                        'msg' => '清空失败'
        		    );
                }
                
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '清空失败：鉴权失败！'
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
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
    	$data_ip = trim($_POST['data_ip']);
    	
        // 过滤参数
        if(empty($data_ip) || !isset($data_ip)){
            
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

            // 验证用户
            $checkUser = $db->set_table('huoma_channel_accessdenied')->find(['data_ip' => $data_ip]);
            $add_user = json_decode(json_encode($checkUser))->add_user;
            
            // 判断操作结果
            if($add_user == $LoginUser){
                
                // 允许操作
                $delipResult = $db->set_table('huoma_channel_accessdenied')->delete(['data_ip' => $data_ip]);
                
                // 操作结果
                if($delipResult){
                    
                    // 已解封
                    $result = array(
    			        'code' => 200,
                        'msg' => '已解封'
    		        );
                    
                }else{
                    
                    // 删除失败
                    $result = array(
        			    'code' => 202,
                        'msg' => '解封失败'
        		    );
                }
                
            }else{
                
                // 禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '解封失败：禁止操作！'
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
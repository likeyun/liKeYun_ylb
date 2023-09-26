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
    	$shareCard_id = trim($_GET['shareCardid']);
    	
        // 过滤参数
        if(empty($shareCard_id) || !isset($shareCard_id)){
            
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
        	
            // 验证当前要删除的shareCard_id的发布者是否为当前登录的用户
            $getCreateUserResult = $db->set_table('huoma_shareCard')->find(['shareCard_id'=>$shareCard_id]);
            $shareCard_create_user = json_decode(json_encode($getCreateUserResult))->shareCard_create_user;
            
            // 判断操作结果
            if($shareCard_create_user == $LoginUser){
                
                // 用户一致：允许操作
                $delshareCardResult = $db->set_table('huoma_shareCard')->delete(['shareCard_id'=>$shareCard_id]);
                
                // 判断操作结果
                if($delshareCardResult){
                    
                    // 删除成功
                    $result = array(
    			        'code' => 200,
                        'msg' => '删除成功'
    		        );
                    
                }else{
                    
                    // 解析报错信息
                    $errorInfo = json_decode(json_encode($delshareCardResult,true))[2];
                    if(!$errorInfo){
                        
                        // 如果没有报错信息
                        $errorInfo = '未知';
                    }
                    // 删除失败
                    $result = array(
        			    'code' => 202,
                        'msg' => '删除失败，原因：'.$errorInfo
        		    );
                }
                
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '删除失败：无法获取到数据，原因：数据已被删除、数据不存在、获取数据失败等...'
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
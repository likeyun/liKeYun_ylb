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
    	$data_ip = trim($_POST['data_ip']);
    	
        // 过滤参数
        if(empty($data_ip) || $data_ip == '' || $data_ip == null || !isset($data_ip)){
            
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

            // 验证当前要删除的data_ip的发布者是否为当前登录的用户
            $getdata_ip_add_user= ['data_ip'=>$data_ip];
            $getdata_ip_add_user_Result = $db->set_table('huoma_channel_accessdenied')->find($getdata_ip_add_user);
            $add_user = json_decode(json_encode($getdata_ip_add_user_Result))->add_user;
            
            // 判断操作结果
            if($add_user == $LoginUser){
                
                // 用户一致：允许操作
                $delip = ['data_ip'=>$data_ip];
                $delipResult = $db->set_table('huoma_channel_accessdenied')->delete($delip);
                
                // 判断操作结果
                if($delipResult){
                    
                    // 已解封
                    $result = array(
    			        'code' => 200,
                        'msg' => '已解封'
    		        );
                    
                }else{
                    
                    // 解析报错信息
                    $errorInfo = json_decode(json_encode($delipResult,true))[2];
                    if(!$errorInfo){
                        
                        // 如果没有报错信息
                        $errorInfo = '未知';
                    }
                    // 删除失败
                    $result = array(
        			    'code' => 202,
                        'msg' => '解封失败，原因：'.$errorInfo
        		    );
                }
                
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '解封失败：无法获取到数据，原因：数据已被删除、数据不存在、获取数据失败等...'
        		);
            }
        }
    	
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录或登录失效'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
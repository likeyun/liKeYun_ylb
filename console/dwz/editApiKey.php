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
    	$apikey = trim($_POST['apikey']);
    	$apikey_secrete = trim($_POST['apikey_secrete']);
    	$apikey_ip = trim($_POST['apikey_ip']);
    	$apikey_quota = trim($_POST['apikey_quota']);
    	$apikey_num = trim($_POST['apikey_num']);
    	$apikey_expire = $_POST['apikey_expire'];
    	$apikey_status = trim($_POST['apikey_status']);
    	$apikey_id = trim($_POST['apikey_id']);
    	
        // 过滤参数
        if(empty($apikey) || $apikey == '' || $apikey == null || !isset($apikey)){
            
            $result = array(
                'code' => 203,
                'msg' => 'ApiKey未填写'
            );
        }else if(empty($apikey_secrete) || $apikey_secrete == '' || $apikey_secrete == null || !isset($apikey_secrete)){
            
            $result = array(
                'code' => 203,
                'msg' => '$ApiSecrete未填写'
            );
        }else if(empty($apikey_quota) || $apikey_quota == '' || $apikey_quota == null || !isset($apikey_quota)){
            
            $result = array(
                'code' => 203,
                'msg' => '请求配额不可为空'
            );
        }else if(!isset($apikey_num)){
            
            $result = array(
                'code' => 203,
                'msg' => '请求次数不可为空'
            );
        }else if(empty($apikey_expire) || $apikey_expire == '' || $apikey_expire == null || !isset($apikey_expire)){
            
            $result = array(
                'code' => 203,
                'msg' => '到期时间不可为空'
            );
        }else if(empty($apikey_status) || $apikey_status == '' || $apikey_status == null || !isset($apikey_status)){
            
            $result = array(
                'code' => 203,
                'msg' => '状态未设置'
            );
        }else if(empty($apikey_id) || $apikey_id == '' || $apikey_id == null || !isset($apikey_id)){
            
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
        	
        	// 获取当前登录用户的管理权限
            $user_admin = json_decode(json_encode($db->set_table('huoma_user')->find(['user_name'=>$LoginUser])))->user_admin;
            if($user_admin == 2){
                
                // 没有管理权限
                $result = array(
                    'code' => 202,
                    'msg' => '没有管理权限'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
        	
            // 验证当前要编辑的apikey_id的发布者是否为当前登录的用户
            $getapikeyid = ['apikey_id'=>$apikey_id];
            $getapikeyidResult = $db->set_table('huoma_dwz_apikey')->find($getapikeyid);
            $apikey_creat_user = json_decode(json_encode($getapikeyidResult))->apikey_creat_user;
            
            // 判断操作结果
            if($apikey_creat_user == $LoginUser){

                // 用户一致：允许操作
                // 参数
                $updateapikeyData = [
                    'apikey' => $apikey,
                    'apikey_secrete' => $apikey_secrete,
                    'apikey_ip' => $apikey_ip,
                    'apikey_quota' => $apikey_quota,
                    'apikey_num' => $apikey_num,
                    'apikey_expire' => $apikey_expire.' '.date('H:i:s'),
                    'apikey_status' => $apikey_status
                ];
                
                // 更新条件
                $updateapikeyCondition = [
                    'apikey_id' => $apikey_id,
                    'apikey_creat_user' => $LoginUser
                ];
                
                // 提交更新
                $updateapikey = $db->set_table('huoma_dwz_apikey')->update($updateapikeyCondition,$updateapikeyData);
                
                // 判断操作结果
                if($updateapikey){
                    
                    // 更新成功
                    $result = array(
			            'code' => 200,
                        'msg' => '更新成功'
		            );
                }else{
                    
                    // 更新失败
                    $result = array(
			            'code' => 202,
                        'msg' => '更新失败'
		            );
                }
                
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '非法请求'
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
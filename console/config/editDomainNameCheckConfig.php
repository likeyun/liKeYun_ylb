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
    	$domainCheck_status = trim($_POST['domainCheck_status']);
    	$domainCheck_channel = trim($_POST['domainCheck_channel']);
    	$domainCheck_byym = trim($_POST['domainCheck_byym']);
    	
        // 过滤参数
        if(empty($domainCheck_status) || !isset($domainCheck_status)){
            
            $result = array(
                'code' => 203,
                'msg' => '状态未选择'
            );
        }else if(empty($domainCheck_channel) || !isset($domainCheck_channel)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择通知渠道'
            );
        }else if(empty($domainCheck_byym) || !isset($domainCheck_byym)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择备用域名'
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
            
            // 参数
            $domainCheckParams = [
                'domainCheck_status' => $domainCheck_status,
                'domainCheck_channel' => $domainCheck_channel,
                'domainCheck_byym' => $domainCheck_byym
            ];
            
            // 提交更新
            $setDomainCheck = $db->set_table('huoma_domainCheck')->update(['id' => 1],$domainCheckParams);

            if($setDomainCheck){
                
                // 已设置
                $result = array(
		            'code' => 200,
                    'msg' => '已设置'
	            );
            }else{
                
                // 设置失败
                $result = array(
		            'code' => 202,
                    'msg' => '设置失败'
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
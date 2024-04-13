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
            // 获取默认域名设置
            // 入口域名
            $default_rkym_Result = $db->set_table('huoma_default_domain')->find(['domain_type' => '1']);
            if($default_rkym_Result){
                
                $default_rkym = json_decode(json_encode($default_rkym_Result))->domain;
            }else{
                
                $default_rkym = '未设置';
            }
            
            // 落地域名
            $default_ldym_Result = $db->set_table('huoma_default_domain')->find(['domain_type' => '2']);
            if($default_ldym_Result){
                
                $default_ldym = json_decode(json_encode($default_ldym_Result))->domain;
            }else{
                
                $default_ldym = '未设置';
            }
            
            // 短链域名
            $default_dlym_Result = $db->set_table('huoma_default_domain')->find(['domain_type' => '3']);
            if($default_dlym_Result){
                
                $default_dlym = json_decode(json_encode($default_dlym_Result))->domain;
            }else{
                
                $default_dlym = '未设置';
            }
            
            // 返回域名数据
            $result = array(
                'code' => 200,
                'msg' => '获取成功',
                'default_rkym' => $default_rkym,
                'default_ldym' => $default_ldym,
                'default_dlym' => $default_dlym
            );
            
    	}else{
    	    
    	   // 没有管理权限
    	   $result = array(
                'code' => 204,
                'msg' => '没有管理权限'
            );
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
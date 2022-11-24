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
    	$dwz_title = trim($_POST['dwz_title']);
    	$dwz_rkym = trim($_POST['dwz_rkym']);
    	$dwz_zzym = trim($_POST['dwz_zzym']);
    	$dwz_dlym = trim($_POST['dwz_dlym']);
    	$dwz_status = trim($_POST['dwz_status']);
    	$dwz_key = trim($_POST['dwz_key']);
    	$dwz_type = trim($_POST['dwz_type']);
    	$dwz_url = trim($_POST['dwz_url']);
    	$dwz_id = trim($_POST['dwz_id']);
    	
    	// 验证URL合法性
        function is_url($url){
            $r = "/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is";
            if(preg_match($r,$url)){
                
                return TRUE;
            }else{
                
                return FALSE;
            }
        }
    	
        // 过滤参数
        if(empty($dwz_title) || $dwz_title == '' || $dwz_title == null || !isset($dwz_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未设置'
            );
        }else if(empty($dwz_rkym) || $dwz_rkym == '' || $dwz_rkym == null || !isset($dwz_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($dwz_zzym) || $dwz_zzym == '' || $dwz_zzym == null || !isset($dwz_zzym)){
            
            $result = array(
                'code' => 203,
                'msg' => '中转域名未选择'
            );
        }else if(empty($dwz_dlym) || $dwz_dlym == '' || $dwz_dlym == null || !isset($dwz_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else if(empty($dwz_status) || $dwz_status == '' || $dwz_status == null || !isset($dwz_status)){
            
            $result = array(
                'code' => 203,
                'msg' => '状态未选择'
            );
        }else if(empty($dwz_type) || $dwz_type == '' || $dwz_type == null || !isset($dwz_type)){
            
            $result = array(
                'code' => 203,
                'msg' => '访问限制未选择'
            );
        }else if(empty($dwz_key) || $dwz_key == '' || $dwz_key == null || !isset($dwz_key)){
            
            $result = array(
                'code' => 203,
                'msg' => '短网址Key未填写'
            );
        }else if(empty($dwz_url) || $dwz_url == '' || $dwz_url == null || !isset($dwz_url)){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接未填写'
            );
        }else if(is_url($dwz_url) === FALSE){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接不是正确的URL格式'
            );
        }else if(empty($dwz_id) || $dwz_id == '' || $dwz_id == null || !isset($dwz_id)){
            
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
        	
            // 验证当前要编辑的dwz_id的发布者是否为当前登录的用户
            $getdwzid = ['dwz_id'=>$dwz_id];
            $getdwzidResult = $db->set_table('huoma_dwz')->find($getdwzid);
            $dwz_creat_user = json_decode(json_encode($getdwzidResult))->dwz_creat_user;
            
            // 判断操作结果
            if($dwz_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                // 参数
                $updatedwzData = [
                    'dwz_title' => $dwz_title,
                    'dwz_status' => $dwz_status,
                    'dwz_rkym' => $dwz_rkym,
                    'dwz_zzym' => $dwz_zzym,
                    'dwz_dlym' => $dwz_dlym,
                    'dwz_key' => $dwz_key,
                    'dwz_type' => $dwz_type,
                    'dwz_url' => $dwz_url
                ];
                
                // 更新条件
                $updatedwzCondition = [
                    'dwz_id' => $dwz_id,
                    'dwz_creat_user' => $LoginUser
                ];
                
                // 提交更新
                $updatedwz = $db->set_table('huoma_dwz')->update($updatedwzCondition,$updatedwzData);
                
                // 判断操作结果
                if($updatedwz){
                    
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
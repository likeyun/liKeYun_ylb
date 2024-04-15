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
    	$dwz_title = trim($_POST['dwz_title']);
    	$dwz_rkym = trim($_POST['dwz_rkym']);
    	$dwz_zzym = trim($_POST['dwz_zzym']);
    	$dwz_dlym = trim($_POST['dwz_dlym']);
    	$dwz_key = trim($_POST['dwz_key']);
    	$dwz_type = trim($_POST['dwz_type']);
    	$dwz_url = trim($_POST['dwz_url']);
    	$dwz_lxymStatus = trim($_POST['dwz_lxymStatus']);
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
        if(empty($dwz_title) || !isset($dwz_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未设置'
            );
        }else if(empty($dwz_rkym) || !isset($dwz_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($dwz_zzym) || !isset($dwz_zzym)){
            
            $result = array(
                'code' => 203,
                'msg' => '中转域名未选择'
            );
        }else if(empty($dwz_dlym) || !isset($dwz_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else if(empty($dwz_type) || !isset($dwz_type)){
            
            $result = array(
                'code' => 203,
                'msg' => '访问限制未选择'
            );
        }else if(empty($dwz_key) || !isset($dwz_key)){
            
            $result = array(
                'code' => 203,
                'msg' => '短网址Key未填写'
            );
        }else if(empty($dwz_lxymStatus) || !isset($dwz_lxymStatus)){
            
            $result = array(
                'code' => 203,
                'msg' => '轮询域名启用状态未选择'
            );
        }else if(empty($dwz_url) || !isset($dwz_url)){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接未填写'
            );
        }else if(is_url($dwz_url) === FALSE){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接不是正确的URL格式'
            );
        }else if(empty($dwz_id) || !isset($dwz_id)){
            
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
        	
        	// 验证是否有轮询域名
            if($dwz_lxymStatus == 1) {
                
                $checkLunXunDomain = $db->set_table('huoma_domain')->find(['domain_type' => 6]);
                if(!$checkLunXunDomain){
                    
                    // 域名库里面没有轮询域名
                    $result = array(
                        'code' => 202,
                        'msg' => '域名库里面没有轮询域名，请前往配置中心添加。'
                    );
                    echo json_encode($result,JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        	
            // 验证用户
            $getdwzid = ['dwz_id'=>$dwz_id];
            $getdwzidResult = $db->set_table('huoma_dwz')->find($getdwzid);
            $dwz_creat_user = json_decode(json_encode($getdwzidResult))->dwz_creat_user;
            
            // 验证当前的dwz_key是否已经被设置过
            $checkDwzKey = $db->set_table('huoma_dwz')->find(['dwz_key'=>$dwz_key]);
            
            if($checkDwzKey) {
                
                // 当前短网址Key的创建者
                $dwzKeyCreateUser = json_decode(json_encode($checkDwzKey))->dwz_creat_user;
                
                if($dwzKeyCreateUser !== $LoginUser) {
                    
                    // 如果不是当前创建者的
                    $result = array(
                        'code' => 203,
                        'msg' => '你设置的短网址Key已被其它账号使用'
                    );
                    echo json_encode($result,JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            // 判断操作结果
            if($dwz_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                $updatedwzData = [
                    'dwz_title' => $dwz_title,
                    'dwz_rkym' => $dwz_rkym,
                    'dwz_zzym' => $dwz_zzym,
                    'dwz_dlym' => $dwz_dlym,
                    'dwz_key' => $dwz_key,
                    'dwz_type' => $dwz_type,
                    'dwz_lxymStatus' => $dwz_lxymStatus,
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
            'msg' => '未登录'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
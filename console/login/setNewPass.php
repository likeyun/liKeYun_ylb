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
	
    // 非法字符过滤
	include './sqlRep.php';
	
	// 接收参数
	$user_name = trim(sqlRep($_POST['user_name']));
	$user_email = trim($_POST['user_email']);
	$user_mb_answer = trim(sqlRep($_POST['user_mb_answer']));
	$user_pass = trim(sqlRep($_POST['user_pass']));
	
    // 过滤参数
    if(empty($user_name) || $user_name == '' || $user_name == null || !isset($user_name)){
        
        $result = array(
		    'code' => 203,
            'msg' => '账号未填写'
	    );
    }else if(preg_match("/[\x7f-\xff]/", $user_name)){
        
        $result = array(
		    'code' => 203,
            'msg' => '账号不能存在中文'
	    );
    }else if(preg_match("/[\',:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_name)){
        
        $result = array(
		    'code' => 203,
            'msg' => '账号不能存在特殊字符'
	    );
    }else if(empty($user_email) || $user_email == '' || $user_email == null || !isset($user_email)){
        
        $result = array(
		    'code' => 203,
            'msg' => '邮箱未填写'
	    );
    }else if(preg_match("/[\x7f-\xff]/", $user_email)){
        
        $result = array(
		    'code' => 203,
            'msg' => '邮箱不能存在中文'
	    );
    }else if(empty($user_mb_answer) || $user_mb_answer == '' || $user_mb_answer == null || !isset($user_mb_answer)){
        
        $result = array(
		    'code' => 203,
            'msg' => '密保问题答案未填写'
	    );
    }else if(empty($user_pass) || $user_pass == '' || $user_pass == null || !isset($user_pass)){
        
        $result = array(
		    'code' => 203,
            'msg' => '新密码未填写'
	    );
    }else if(preg_match("/[\x7f-\xff]/", $user_pass)){
        
        $result = array(
		    'code' => 203,
            'msg' => '密码不能存在中文'
	    );
    }else if(preg_match("/[\',:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_pass)){
        
        $result = array(
		    'code' => 203,
            'msg' => '密码不能存在特殊符号'
	    );
    }else if(strlen($user_pass) <= 8){
        
        $result = array(
		    'code' => 203,
            'msg' => '密码不能小于8位数'
	    );
    }else{
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    
    	// 数据库huoma_user表
    	$huoma_user = $db->set_table('huoma_user');
    	
    	// 根据账号、邮箱、密保问题答案来验证用户信息的准确性
        $userinfoCheck = ['user_name'=>$user_name,'user_email'=>$user_email,'user_mb_answer'=>$user_mb_answer];
        $userinfoCheckResult = $huoma_user->find($userinfoCheck);
        if($userinfoCheckResult){
            
            // 验证通过
            // 更新密码
            $setNewPass = ['user_pass'=>MD5($user_pass)];
            $setNewPassResult = $huoma_user->update(['id'=>1],$setNewPass);
            
            // 验证更新结果
            if($setNewPassResult){
                
                $result = array(
                    'code' => 200,
                    'msg' => '重置密码成功！'
                );
            }else{
                
                $result = array(
                    'code' => 202,
                    'msg' => '重置密码失败'
                );
            }
        }else{
            
            $result = array(
                'code' => 202,
                'msg' => '账号、邮箱、密保问题不匹配！'
            );
        }
        
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
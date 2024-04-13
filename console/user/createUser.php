<?php
    
    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 接收参数
        $user_name = trim($_POST['user_name']);
        $user_pass = trim($_POST['user_pass']);
        $user_email = trim($_POST['user_email']);
        $user_mb_ask = trim($_POST['user_mb_ask']);
        $user_mb_answer = trim($_POST['user_mb_answer']);
        $user_beizhu = trim($_POST['user_beizhu']);
        $user_group = trim($_POST['user_group']);
        
        // sql防注入
        if(
            preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_name) || 
            preg_match("/[\',:;*?~`!#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_email) || 
            preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_pass)){
                
                $result = array(
    		        'code' => 203,
                    'msg' => '你输入的邮箱、账号、密码可能包含了一些不安全字符'
    	        );
    	        echo json_encode($result,JSON_UNESCAPED_UNICODE);
    	        exit;
        }else if(
            preg_match("/(and|or|select|update|drop|DROP|insert|create|delete|like|where|join|script|set)/i",$user_name) || 
            preg_match("/(and|or|select|update|drop|DROP|insert|create|delete|like|where|join|script|set)/i",$user_pass)
        ){
            
            $result = array(
    	        'code' => 203,
                'msg' => '你输入的账号、密码包含了一些不安全字符'
            );
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 过滤参数
        if(empty($user_name) || $user_name == '' || $user_name == null || !isset($user_name)){
            
            $result = array(
                'code' => 203,
                'msg' => '账号未设置'
            );
        }else if(strlen($user_name) < 5){
            
            $result = array(
                'code' => 203,
                'msg' => '账号不得小于5位数'
            );
        }else if(strlen($user_name) > 15){
            
            $result = array(
                'code' => 203,
                'msg' => '账号不得大于15位数'
            );
        }else if(preg_match("/[\x7f-\xff]/", $user_name)){
        
            $result = array(
    		    'code' => 203,
                'msg' => '账号不能存在中文或中文符号'
    	    );
        }else if(preg_match("/[\',:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_name)){
        
            $result = array(
    		    'code' => 203,
                'msg' => '账号不能存在特殊字符'
    	    );
        }else if(empty($user_pass) || $user_pass == '' || $user_pass == null || !isset($user_pass)){
            
            $result = array(
                'code' => 203,
                'msg' => '密码未设置'
            );
        }else if(strlen($user_pass) < 5){
            
            $result = array(
                'code' => 203,
                'msg' => '密码不得小于5位数'
            );
        }else if(strlen($user_pass) > 32){
            
            $result = array(
                'code' => 203,
                'msg' => '密码不得大于32位数'
            );
        }else if(preg_match("/[\x7f-\xff]/", $user_pass)){
        
            $result = array(
    		    'code' => 203,
                'msg' => '密码不能存在中文或中文符号'
    	    );
        }else if(preg_match("/[\',:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_pass)){
        
            $result = array(
    		    'code' => 203,
                'msg' => '密码不能存在特殊字符'
    	    );
        }else if(empty($user_email) || $user_email == '' || $user_email == null || !isset($user_email)){
            
            $result = array(
                'code' => 203,
                'msg' => '邮箱未填写'
            );
        }else if(!filter_var($user_email, FILTER_VALIDATE_EMAIL)){
            
            $result = array(
                'code' => 203,
                'msg' => '邮箱不符合规则'
            );
        }else if(empty($user_mb_ask) || $user_mb_ask == '' || $user_mb_ask == null || !isset($user_mb_ask)){
            
            $result = array(
                'code' => 203,
                'msg' => '密保问题未选择'
            );
        }else if(empty($user_mb_answer) || $user_mb_answer == '' || $user_mb_answer == null || !isset($user_mb_answer)){
            
            $result = array(
                'code' => 203,
                'msg' => '密保问题答案未填写'
            );
        }else if(empty($user_group) || !isset($user_group)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择用户组'
            );
        }else{
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
        	// 数据库huoma_user表
        	$huoma_user = $db->set_table('huoma_user');
        	
            // 查询数据库是否已经存在同user_name的用户
            $checkTheSameUserName = ['user_name'=>$user_name];
            $checkTheSameUserNameResult = $huoma_user->find($checkTheSameUserName);
            
            // 用户ID生成
            $user_id = rand(100000,999999);
            
            // 查询数据库是否已经存在同user_id
            $checkTheSameUserId = ['user_id'=>$user_id];
            $checkTheSameUserIdResult = $huoma_user->find($checkTheSameUserId);
            if($checkTheSameUserIdResult){
                
                // 存在同user_id
                // 重置user_id
                $user_id = rand(100001,999998);
            }
            
            // 判断执行结果
            if($checkTheSameUserNameResult){
                
                // 存在同user_name的用户
                // 创建失败
                $result = array(
                    'code' => 202,
                    'msg' => '该账号已被注册'
                );
            }else{
                
                // 不存在同user_name的用户
                // 允许创建用户
                // 字段
                $creatuser = [
                    'user_name'=>$user_name,
                    'user_pass'=>MD5($user_pass),
                    'user_email'=>$user_email,
                    'user_mb_ask'=>$user_mb_ask,
                    'user_mb_answer'=>$user_mb_answer,
                    'user_manager'=>$_SESSION["yinliubao"],
                    'user_beizhu'=>$user_beizhu,
                    'user_group'=>$user_group,
                    'user_id'=>$user_id
                ];
                
                // 获取当前登录账号的管理员权限
                $getuserAdmin = ['user_name'=>$_SESSION["yinliubao"]];
                $getuserAdminResult = $huoma_user->find($getuserAdmin);
                
                // 权限 1管理 2非管理
                $user_admin = json_decode(json_encode($getuserAdminResult))->user_admin;
                
                // 判断管理权限
                if($user_admin == 1){
                    
                    // 管理员
                    // 执行SQL
                    $creatuserResult = $huoma_user->add($creatuser);
                    
                    // 判断执行结果
                    if($creatuserResult){
                        
                        // 成功
                        $result = array(
                            'code' => 200,
                            'msg' => '创建成功'
                        );
                    }else{
                        
                        // 失败
                        $result = array(
                            'code' => 202,
                            'msg' => '创建失败'
                        );
                    }
                }else{
                    
                    // 非管理员
                    // 失败
                    $result = array(
                        'code' => 202,
                        'msg' => '创建失败，没有创建权限！'
                    );
                }
                
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
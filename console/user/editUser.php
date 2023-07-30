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
    	$user_pass = trim($_POST['user_pass']);
    	$user_email = trim($_POST['user_email']);
    	$user_mb_ask = trim($_POST['user_mb_ask']);
    	$user_mb_answer = trim($_POST['user_mb_answer']);
    	$user_status = trim($_POST['user_status']);
    	$user_beizhu = trim($_POST['user_beizhu']);
    	$user_id = trim($_POST['user_id']);
    	
    	// sql防注入
        if(
            preg_match("/[\',:;*?~`!#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_email) || 
            preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_pass)){
                
                $result = array(
    		        'code' => 203,
                    'msg' => '你输入的邮箱、密码可能包含了一些不安全字符'
    	        );
    	        echo json_encode($result,JSON_UNESCAPED_UNICODE);
    	        exit;
        }else if(
            preg_match("/(and|or|select|update|drop|DROP|insert|create|delete|like|where|join|script|set)/i",$user_pass)
        ){
            
            $result = array(
    	        'code' => 203,
                'msg' => '你输入的密码包含了一些不安全字符'
            );
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
            exit;
        }
    	
        // 过滤参数
        if(empty($user_email) || $user_email == '' || $user_email == null || !isset($user_email)){
            
            $result = array(
			    'code' => 203,
                'msg' => '邮箱未填写'
		    );
        }else if(empty($user_mb_ask) || $user_mb_ask == '' || $user_mb_ask == null || !isset($user_mb_ask)){
            
            $result = array(
			    'code' => 203,
                'msg' => '密保问题未选择'
		    );
        }else if(empty($user_mb_answer) || $user_mb_answer == '' || $user_mb_answer == null || !isset($user_mb_answer)){
            
            $result = array(
			    'code' => 203,
                'msg' => '密保答案未填写'
		    );
        }else if(empty($user_status) || $user_status == '' || $user_status == null || !isset($user_status)){
            
            $result = array(
			    'code' => 203,
                'msg' => '状态未设置'
		    );
        }else if(empty($user_id) || $user_id == '' || $user_id == null || !isset($user_id)){
            
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
        
        	// 数据库huoma_user表
        	$huoma_user = $db->set_table('huoma_user');
        	
            // 获取当前登录账号的操作权限
            $getuserAdmin = ['user_name'=>$LoginUser];
            $getuserAdminResult = $huoma_user->find($getuserAdmin);
            $user_admin = json_decode(json_encode($getuserAdminResult))->user_admin;
            
            // 判断操作权限
            if($user_admin == 1){
                
                // 管理员：可进入下一步操作
                // 验证是否输入了新密码
                if(empty($user_pass) || $user_pass == '' || $user_pass == null || !isset($user_pass)){
                    
                    // 没有输入新密码
                    // 可以更新数据了
                    // 需要更新的字段
                    $updateuserData = [
                        'user_email' => $user_email,
                        'user_mb_ask' => $user_mb_ask,
                        'user_mb_answer' => $user_mb_answer,
                        'user_status' => $user_status,
                        'user_beizhu' => $user_beizhu
                    ];
                    
                    // 更新条件
                    $updateuserCondition = [
                        'user_id' => $user_id
                    ];
                    
                    // 执行更新操作
                    $updateuser = $huoma_user->update($updateuserCondition,$updateuserData);
                    
                    // 判断操作结果
                    if($updateuser){
                        
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
                    
                    // 输入了新密码
                    // 对新密码进一步过滤
                    if(strlen($user_pass) < 5){
            
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
                            'msg' => '密码不能存在中文'
                	    );
                    }else if(preg_match("/[\',:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_pass)){
                    
                        $result = array(
                		    'code' => 203,
                            'msg' => '密码不能存在特殊字符'
                	    );
                    }else{
                        
                        // 符合密码规则
                        // 可以更新数据了
                        // 需要更新的字段
                        $updateuserData = [
                            'user_pass' => MD5($user_pass),
                            'user_email' => $user_email,
                            'user_mb_ask' => $user_mb_ask,
                            'user_mb_answer' => $user_mb_answer,
                            'user_status' => $user_status,
                            'user_beizhu' => $user_beizhu
                        ];
                        
                        // 更新条件
                        $updateuserCondition = [
                            'user_id' => $user_id
                        ];
                        
                        // 执行更新操作
                        $updateuser = $huoma_user->update($updateuserCondition,$updateuserData);
                        
                        // 判断操作结果
                        if($updateuser){
                            
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
                    }
                }
            }else{

                // 非管理员：禁止直接操作，需要进一步鉴权
                // 验证当前要操作的user_id是否与当前登录账号相符
                $getuserName = ['user_id'=>$user_id];
                $getuserNameResult = $huoma_user->find($getuserName);
                $user_name = json_decode(json_encode($getuserNameResult))->user_name;
                if($user_name == $LoginUser){
                    
                    // user_id与当前登录的账号相符：允许下一步操作
                    // 验证是否输入了新密码
                    if(empty($user_pass) || $user_pass == '' || $user_pass == null || !isset($user_pass)){
                        
                        // 没有输入新密码
                        // 可以更新数据了
                        // 需要更新的字段
                        $updateuserData = [
                            'user_email' => $user_email,
                            'user_mb_ask' => $user_mb_ask,
                            'user_mb_answer' => $user_mb_answer,
                            'user_status' => $user_status,
                            'user_beizhu' => $user_beizhu
                        ];
                        
                        // 更新条件
                        $updateuserCondition = [
                            'user_id' => $user_id
                        ];
                        
                        // 执行更新操作
                        $updateuser = $huoma_user->update($updateuserCondition,$updateuserData);
                        
                        // 判断操作结果
                        if($updateuser){
                            
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
                        
                        // 输入了新密码
                        // 对新密码进一步过滤
                        if(strlen($user_pass) < 5){
                
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
                                'msg' => '密码不能存在中文'
                    	    );
                        }else if(preg_match("/[\',:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$user_pass)){
                        
                            $result = array(
                    		    'code' => 203,
                                'msg' => '密码不能存在特殊字符'
                    	    );
                        }else{
                            
                            // 符合密码规则
                            // 可以更新数据了
                            // 需要更新的字段
                            $updateuserData = [
                                'user_pass' => MD5($user_pass),
                                'user_email' => $user_email,
                                'user_mb_ask' => $user_mb_ask,
                                'user_mb_answer' => $user_mb_answer,
                                'user_status' => $user_status,
                                'user_beizhu' => $user_beizhu
                            ];
                            
                            // 更新条件
                            $updateuserCondition = [
                                'user_id' => $user_id
                            ];
                            
                            // 执行更新操作
                            $updateuser = $huoma_user->update($updateuserCondition,$updateuserData);
                            
                            // 判断操作结果
                            if($updateuser){
                                
                                // 更新成功
                                $result = array(
                        		    'code' => 200,
                                    'msg' => '更新成功，请重新登录！'
                        	    );
                        	    
                        	    // 因修改了密码
                    	        // 需要重新登录
                    	        unset($_SESSION["yinliubao"]);
                            }else{
                                
                                // 更新失败
                                $result = array(
                        		    'code' => 202,
                                    'msg' => '更新失败'
                        	    );
                            }
                        }
                    }
                }else{
                    
                    // 不相符：不允许操作
                    $result = array(
            		    'code' => 202,
                        'msg' => '非法操作！没有操作权限！'
            	    );
                }
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
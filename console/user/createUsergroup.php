<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $usergroup_name = trim($_POST['usergroup_name']);
        
        // 过滤参数
        if(empty($usergroup_name) || !isset($usergroup_name)){
            
            $result = array(
                'code' => 203,
                'msg' => '请设置用户组名称'
            );
        }else if(strlen($usergroup_name) >= 30) {
            
            $result = array(
                'code' => 203,
                'msg' => '最长支持10个中文或32个英文数字'
            );
        }else{
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 先查询是否存在相同用户组名称
            $checkUsrgroup = $db->set_table('ylb_usergroup')->find(['usergroup_name'=>$usergroup_name]);
            
            // 结果
            if($checkUsrgroup){
                
                // 存在相同用户组名称
                $result = array(
                    'code' => 202,
                    'msg' => '该用户组已存在'
                );
            }else{
                
                // 不存在
                // 创建用户组
                $createUsergroupParams = [
                    'usergroup_name' => $usergroup_name,
                    'usergroup_id' => '10' . mt_rand(1000,9999),
                    'navList' => '[{"href":"../index/","icon":"i-data","text":"数据"},{"href":"../qun/","icon":"i-hm","text":"活码"},{"href":"../dwz/","icon":"i-dwz","text":"短网址"},{"href":"../tbk/","icon":"i-tbk","text":"淘宝客"},{"href":"../plugin/","icon":"i-plugin","text":"插件中心"},{"href":"../sucai/","icon":"i-sucai","text":"素材管理"},{"href":"../user/","icon":"i-account","text":"账号管理"}]'
                ];
                
                // 获取当前登录账号的管理员权限
                $checkAdmin = $db->set_table('huoma_user')->find(['user_name' => $_SESSION["yinliubao"]])['user_admin'];
                
                // 判断管理权限
                if($checkAdmin == 1){
                    
                    // 管理员
                    // 执行SQL
                    $createUsergroup = $db->set_table('ylb_usergroup')->add($createUsergroupParams);
                    
                    // 结果
                    if($createUsergroup){
                        
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
            'msg' => '未登录或登录失效'
        );
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
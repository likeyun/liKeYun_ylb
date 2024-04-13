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
        $domain = trim($_POST['domain']);
        $domain_type = trim($_POST['domain_type']);
        
        // 验证域名的合法性
        function is_url($url){
            $r = "/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is";
            if(preg_match($r,$url)){
                return true;
            }else{
                return false;
            }
        }
        
        // 验证域名结尾是否包含 / 这个符号
        if(substr($domain,-1) == '/'){
            
            // 结尾有/这个符号
            $domain = substr($domain,0,-1);
        }
        
        // 过滤参数
        if(empty($domain) || !isset($domain)){
            
            $result = array(
                'code' => 203,
                'msg' => '域名未填写'
            );
        }else if(is_url($domain) == false){
            
            $result = array(
                'code' => 203,
                'msg' => '你输入的不是正确的域名格式'
            );
        }else if(empty($domain_type) || !isset($domain_type)){
            
            $result = array(
                'code' => 203,
                'msg' => '域名类型未选择'
            );
        }else{
            
            // 域名ID生成
            $domain_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            if($domain_type == 1){
                $domain_type_text = '入口域名';
            }else if($domain_type == 2){
                $domain_type_text = '落地域名';
            }else if($domain_type == 3){
                $domain_type_text = '短链域名';
            }else if($domain_type == 4){
                $domain_type_text = '备用域名';
            }else if($domain_type == 5){
                $domain_type_text = '对象存储域名';
            }else if($domain_type == 6){
                $domain_type_text = '轮询域名';
            }
            
            // 获取当前登录账号的管理员权限
            $user_admin = $db->set_table('huoma_user')->getField(['user_name'=>$_SESSION["yinliubao"]],'user_admin');
            if($user_admin == 1){
                
                // 获得管理权限
                // 验证域名是否已被添加
                $checkDomainExist = ['domain' => $domain,'domain_type' => $domain_type];
                $checkDomainExistResult = $db->set_table('huoma_domain')->getCount($checkDomainExist);
            	
            	// 插入参数
                $addDomainName_Sql = [
                    'domain'=>$domain,
                    'domain_type'=>$domain_type,
                    'domain_id'=>$domain_id,
                    'domain_usergroup'=>'["默认"]',
                ];
                
                // 验证是否已经添加过
                if($checkDomainExistResult > 0){
                    
                    // 成功
                    $result = array(
                        'code' => 202,
                        'msg' => '该域名已被添加为'.$domain_type_text.'，可尝试添加为其它类型！'
                    );
                }else{
                    
                    // 执行添加
                    $addDomainNameResult = $db->set_table('huoma_domain')->add($addDomainName_Sql);
                    if($addDomainNameResult){
                        
                        // 成功
                        $result = array(
                            'code' => 200,
                            'msg' => '添加成功'
                        );
                    }else{
                        
                        // 失败
                        $result = array(
                            'code' => 202,
                            'msg' => '添加失败'
                        );
                    }
                }
            }else{
                
                // 没有管理权限
        	    $result = array(
                    'code' => 202,
                    'msg' => '没有管理权限'
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
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
        $apikey_user = trim($_POST['apikey_user']);
        $apikey_ip = trim($_POST['apikey_ip']);
        $apikey_expire = trim($_POST['apikey_expire']);
        $apikey_creat_user = $_SESSION["yinliubao"];
        
        // 过滤参数
        if(empty($apikey_user) || $apikey_user == '' || $apikey_user == null || !isset($apikey_user)){
            
            $result = array(
                'code' => 203,
                'msg' => '请设置一个用户名'
            );
        }else if(empty($apikey_expire) || $apikey_expire == '' || $apikey_expire == null || !isset($apikey_expire)){
            
            $result = array(
                'code' => 203,
                'msg' => '请设置到期时间'
            );
        }else{
            
            // ID生成
            $apikey_id = rand(100000,999999);
            
            // 随机生成ApiKey
            function creatApiKey($length){
                //字符组合
                $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $len = strlen($str)-1;
                $randstr = '';
                for ($i=0;$i<$length;$i++) {
                    $num=mt_rand(0,$len);
                    $randstr .= $str[$num];
                }
                return $randstr;
            }
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
        	// 获取当前登录用户的管理权限
            $user_admin = json_decode(json_encode($db->set_table('huoma_user')->find(['user_name'=>$apikey_creat_user])))->user_admin;
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
            $creatApiKey = [
                'apikey_user'=>$apikey_user,
                'apikey_ip'=>$apikey_ip,
                'apikey_expire'=>$apikey_expire.' '.date('H:i:s'),
                'apikey_creat_user'=>$apikey_creat_user,
                'apikey'=>creatApiKey(10),
                'apikey_secrete'=>creatApiKey(28),
                'apikey_id'=>$apikey_id
            ];
            
            // 验证用户名是否重复
            $checkApiKeyUser = $db->set_table('huoma_dwz_apikey')->find(['apikey_user'=>$apikey_user]);
            if($checkApiKeyUser){
                
                // 存在该用户
                // 失败
                $result = array(
                    'code' => 202,
                    'msg' => '已存在相同用户名'
                );
            }else{
                
                // 不存在该用户
                // 执行SQL
                $creatApiKeyResult = $db->set_table('huoma_dwz_apikey')->add($creatApiKey);
                
                // 判断执行结果
                if($creatApiKeyResult){
                    
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
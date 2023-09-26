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
        $data_ip = trim($_POST['data_ip']);
        
        // 过滤参数
        if(empty($data_ip) || $data_ip == '' || $data_ip == null || !isset($data_ip)){
            
            $result = array(
                'code' => 203,
                'msg' => '非法请求'
            );
        }else{
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 查询是否已经存在这个IP
            $checkIPexits = $db->set_table('huoma_channel_accessdenied')->find(['data_ip'=>$data_ip]);
            if($checkIPexits){
                
                // 存在IP
                $result = array(
                    'code' => 200,
                    'msg' => '该IP已在黑名单'
                );
            }else{
                
                // 不存在这个IP
                // 将IP加入黑名单列表
                $accessDeniedResult = $db->set_table('huoma_channel_accessdenied')->add(['data_ip'=>$data_ip,'add_user'=>$_SESSION["yinliubao"]]);
                
                // 判断执行结果
                if($accessDeniedResult){
                    
                    // 成功
                    $result = array(
                        'code' => 200,
                        'msg' => '已封禁'
                    );
                }else{
                    
                    // 失败
                    $result = array(
                        'code' => 202,
                        'msg' => '加入黑名单失败'
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
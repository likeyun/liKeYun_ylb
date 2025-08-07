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
        $shareCard_title = trim($_POST['shareCard_title']);
        $shareCard_desc = trim($_POST['shareCard_desc']);
        $shareCard_img = trim($_POST['shareCard_img']);
        $shareCard_ldym = trim($_POST['shareCard_ldym']);
        $shareCard_url = trim($_POST['shareCard_url']);
        $shareCard_model = trim($_POST['shareCard_model']);
        $shareCard_create_user = trim($_SESSION["yinliubao"]);
        
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
        if(empty($shareCard_title) || !isset($shareCard_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '分享标题未填写'
            );
        }else if(empty($shareCard_desc) || !isset($shareCard_desc)){
            
            $result = array(
                'code' => 203,
                'msg' => '分享摘要未填写'
            );
        }else if(empty($shareCard_img) || !isset($shareCard_img)){
            
            $result = array(
                'code' => 203,
                'msg' => '分享缩略图未上传'
            );
        }else if(empty($shareCard_model) || !isset($shareCard_model)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择模式'
            );
        }else if(empty($shareCard_ldym) || !isset($shareCard_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择落地域名'
            );
        }else if(empty($shareCard_url) || !isset($shareCard_url)){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接未填写'
            );
        }else if(is_url($shareCard_url) === FALSE){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接不是正确的Url'
            );
        }else{
            
            // ID生成
            $shareCard_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
        	// 参数
            $createshareCard = [
                'shareCard_id' => $shareCard_id,
                'shareCard_title'=>$shareCard_title,
                'shareCard_desc'=>$shareCard_desc,
                'shareCard_img'=>$shareCard_img,
                'shareCard_model'=>$shareCard_model,
                'shareCard_ldym'=>$shareCard_ldym,
                'shareCard_url'=>$shareCard_url,
                'shareCard_create_user'=>$shareCard_create_user
            ];
            
            // 执行SQL
            $createshareCardResult = $db->set_table('huoma_shareCard')->add($createshareCard);
            
            // 判断执行结果
            if($createshareCardResult){
                
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
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
        $channel_title = trim($_POST['channel_title']);
        $channel_rkym = trim($_POST['channel_rkym']);
        $channel_ldym = trim($_POST['channel_ldym']);
        $channel_dlym = trim($_POST['channel_dlym']);
        $channel_url = trim($_POST['channel_url']);
        $channel_creat_user = trim($_SESSION["yinliubao"]);
        
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
        if(empty($channel_title) || $channel_title == '' || $channel_title == null || !isset($channel_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '渠道标题未设置'
            );
        }else if(empty($channel_rkym) || $channel_rkym == '' || $channel_rkym == null || !isset($channel_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($channel_ldym) || $channel_ldym == '' || $channel_ldym == null || !isset($channel_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '落地域名未选择'
            );
        }else if(empty($channel_dlym) || $channel_dlym == '' || $channel_dlym == null || !isset($channel_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else if(empty($channel_url) || $channel_url == '' || $channel_url == null || !isset($channel_url)){
            
            $result = array(
                'code' => 203,
                'msg' => '推广链接未填写'
            );
        }else if(is_url($channel_url) === FALSE){
            
            $result = array(
                'code' => 203,
                'msg' => '推广链接不是正确的URL格式'
            );
        }else{
            
            // 渠道ID生成
            $channel_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
        	// 数据库huoma_channel表
        	$huoma_channel = $db->set_table('huoma_channel');
        	
            // 随机生成channel_key
            function creatKey($length){
                $keyMember = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
                $keyStr = str_shuffle($keyMember);
                $keys = substr($keyStr,0,$length);
                return $keys;
            }
        
        	// 参数
            $creatChannel = [
                'channel_title'=>$channel_title,
                'channel_today_pv'=>'{"pv":0,"date":"'.date("Y-m-d").'"}',
                'channel_rkym'=>$channel_rkym,
                'channel_ldym'=>$channel_ldym,
                'channel_dlym'=>$channel_dlym,
                'channel_url'=>$channel_url,
                'channel_creat_user'=>$channel_creat_user,
                'channel_key' => creatKey(5),
                'channel_id'=>$channel_id
            ];
            
            // 执行SQL
            $creatChannelResult = $huoma_channel->add($creatChannel);
            
            // 判断执行结果
            if($creatChannelResult){
                
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
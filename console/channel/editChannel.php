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
    	$channel_title = trim($_POST['channel_title']);
    	$channel_rkym = trim($_POST['channel_rkym']);
    	$channel_ldym = trim($_POST['channel_ldym']);
    	$channel_dlym = trim($_POST['channel_dlym']);
    	$channel_url = trim($_POST['channel_url']);
    	$channel_id = trim($_POST['channel_id']);
    	$channel_beizhu_ht = trim($_POST['channel_beizhu_ht']);
        $channel_limit = trim($_POST['channel_limit']);
        $is_mzfwxz = trim($_POST['is_mzfwxz']);
        $mzfwxz_url = trim($_POST['mzfwxz_url']);
        
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
        if(empty($channel_title) || !isset($channel_title)){
            
            $result = array(
			    'code' => 203,
                'msg' => '标题未填写'
		    );
        }if($is_mzfwxz == '2' && !$mzfwxz_url){
            
            $result = array(
                'code' => 203,
                'msg' => '命中访问限制规则的时候，跳转的链接未填写'
            );
        }else if($mzfwxz_url && is_url($mzfwxz_url) === FALSE && $is_mzfwxz == '2'){
            
            $result = array(
                'code' => 203,
                'msg' => '命中规则的跳转链接不符合URL规范'
            );
        }else if(empty($channel_rkym) || !isset($channel_rkym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '入口域名未选择'
		    );
        }else if(empty($channel_ldym) || !isset($channel_ldym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '落地域名未选择'
		    );
        }else if(empty($channel_dlym) || !isset($channel_dlym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '短链域名未选择'
		    );
        }else if(empty($channel_url) || !isset($channel_url)){
            
            $result = array(
			    'code' => 203,
                'msg' => '推广链接未填写'
		    );
        }else if(empty($channel_id) || !isset($channel_id)){
            
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
        	
            // 验证用户
            $checkUser = $db->set_table('huoma_channel')->find(['channel_id' => $channel_id]);
            $channel_creat_user = json_decode(json_encode($checkUser))->channel_creat_user;
            
            // 判断操作结果
            if($channel_creat_user == $LoginUser){
                
                // 允许操作
                $updatechannelData = [
                    'channel_title' => $channel_title,
                    'channel_rkym' => $channel_rkym,
                    'channel_ldym' => $channel_ldym,
                    'channel_dlym' => $channel_dlym,
                    'channel_url' => $channel_url,
                    'channel_limit' => $channel_limit,
                    'is_mzfwxz' => $is_mzfwxz,
                    'mzfwxz_url' => $mzfwxz_url,
                    'channel_beizhu_ht' => $channel_beizhu_ht
                ];
                
                // 更新条件
                $updatechannelCondition = [
                    'channel_id' => $channel_id,
                    'channel_creat_user' => $LoginUser
                ];
                
                // 提交更新
                $updatechannel = $db->set_table('huoma_channel')->update($updatechannelCondition,$updatechannelData);
                
                // 操作结果
                if($updatechannel){
                    
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
                
                // 禁止操作
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
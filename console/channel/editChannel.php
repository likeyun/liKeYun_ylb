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
    	$channel_title = trim($_POST['channel_title']);
    	$channel_rkym = trim($_POST['channel_rkym']);
    	$channel_ldym = trim($_POST['channel_ldym']);
    	$channel_dlym = trim($_POST['channel_dlym']);
    	$channel_status = trim($_POST['channel_status']);
    	$channel_url = trim($_POST['channel_url']);
    	$channel_id = trim($_POST['channel_id']);
    	
        // 过滤参数
        if(empty($channel_title) || $channel_title == '' || $channel_title == null || !isset($channel_title)){
            
            $result = array(
			    'code' => 203,
                'msg' => '标题未填写'
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
        }else if(empty($channel_status) || $channel_status == '' || $channel_status == null || !isset($channel_status)){
            
            $result = array(
			    'code' => 203,
                'msg' => '状态未设置'
		    );
        }else if(empty($channel_url) || $channel_url == '' || $channel_url == null || !isset($channel_url)){
            
            $result = array(
			    'code' => 203,
                'msg' => '推广链接未填写'
		    );
        }else if(empty($channel_id) || $channel_id == '' || $channel_id == null || !isset($channel_id)){
            
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
        
        	// 数据库huoma_channel表
        	$huoma_channel = $db->set_table('huoma_channel');
        	
            // 验证当前要编辑的channel_id的发布者是否为当前登录的用户
            $getchannelid = ['channel_id'=>$channel_id];
            $getchannelidResult = $huoma_channel->find($getchannelid);
            $channel_creat_user = json_decode(json_encode($getchannelidResult))->channel_creat_user;
            
            // 判断操作结果
            if($channel_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                // 参数
                $updatechannelData = [
                    'channel_title' => $channel_title,
                    'channel_status' => $channel_status,
                    'channel_rkym' => $channel_rkym,
                    'channel_ldym' => $channel_ldym,
                    'channel_dlym' => $channel_dlym,
                    'channel_url' => $channel_url
                ];
                
                // 更新条件
                $updatechannelCondition = [
                    'channel_id' => $channel_id,
                    'channel_creat_user' => $LoginUser
                ];
                
                // 提交更新
                $updatechannel = $huoma_channel->update($updatechannelCondition,$updatechannelData);
                
                // 判断操作结果
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
                
                // 用户不一致：禁止操作
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
            'msg' => '未登录或登录失效'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
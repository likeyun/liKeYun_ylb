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
        
        // 接收参数
    	$zm_yz = trim($_POST['zm_yz']);
    	$zm_leader = trim($_POST['zm_leader']);
    	$zm_qrcode = trim($_POST['zm_qrcode']);
    	$zm_id = trim($_POST['zm_id']);
    	
        // 过滤参数
        if(empty($zm_yz) || !isset($zm_yz) || $zm_yz == 0){
            
            $result = array(
			    'code' => 203,
                'msg' => '请设置阈值'
		    );
        }else if(empty($zm_qrcode) || !isset($zm_qrcode)){
            
            $result = array(
			    'code' => 203,
                'msg' => '请上传二维码'
		    );
        }else if(empty($zm_id) || !isset($zm_id)){
            
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
            
            // 可能是封装的PDOClass有缺陷，所以这里使用mysqli原生对象去获取qun_creat_user
        	$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
            
            // 操作huoma_qun_zima表
            $huoma_qun_zima = $db->set_table('huoma_qun_zima');
            
            // 验证当前要删除的zm_id的发布者是否为当前登录的用户
            // 1 先获取到当前zm_id的qun_id
            $where_zminfo = ['zm_id'=>$zm_id];
            $find_zminfo = $huoma_qun_zima->find($where_zminfo);
            $qun_id = json_decode(json_encode($find_zminfo))->qun_id;
            
            // 2 根据qun_id获取到用户（可能是封装的PDOClass有缺陷，所以这里使用mysqli原生对象去获取qun_creat_user）
            $find_qun_creat_user = "SELECT * FROM huoma_qun WHERE qun_id='$qun_id'";
            $qun_creat_user = json_decode(json_encode($conn->query($find_qun_creat_user)->fetch_assoc()))->qun_creat_user;
            
            // 判断操作权限
            if($qun_creat_user && $qun_creat_user == $LoginUser){
                
                // 时间
                $zm_update_time = date('Y-m-d H:i:s');
                
                // 用户一致：允许操作
                $updateQunzm = "UPDATE huoma_qun_zima SET 
                zm_yz='$zm_yz',
                zm_leader='$zm_leader',
                zm_qrcode='$zm_qrcode',
                zm_update_time='$zm_update_time' 
                WHERE zm_id='$zm_id'";
                
                // 操作结果
                $updateQunzmResult = $huoma_qun_zima->findSql($updateQunzm);
                
                // 判断操作结果
                if(count($updateQunzmResult) == 0){
                    
                    // 操作成功
                    $result = array(
        			    'code' => 200,
                        'msg' => '更新成功',
                        'qun_id' => $qun_id  // 返回qun_id用于刷新二维码列表
        		    );
                }else{
                    
                    // 操作失败
                    $result = array(
        			    'code' => 202,
                        'msg' => '更新失败'
        		    );
                }
                
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '更新失败：无法获取到数据，原因：数据已被删除、数据不存在、获取数据失败等...'
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
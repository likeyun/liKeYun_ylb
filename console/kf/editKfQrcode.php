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
    	$zm_yz = trim($_POST['zm_yz']);
    	$zm_num = trim($_POST['zm_num']);
    	$zm_qrcode = trim($_POST['zm_qrcode']);
    	$kf_model = trim($_POST['kf_model']);
    	$zm_id = trim($_POST['zm_id']);
    	
        // 后台备注
    	$zm_beizhu_ht = trim($_POST['zm_beizhu_ht']);
    	
        // 过滤参数
        if(!isset($zm_yz) || $zm_yz == 0 && $kf_model == 1){
            
            // 循环模式=1代表选择了阈值模式
            // 则需要设置阈值
            $result = array(
			    'code' => 203,
                'msg' => '请设置阈值'
		    );
        }else if(empty($zm_num) || !isset($zm_num)){
            
            $result = array(
			    'code' => 203,
                'msg' => '客服微信号未填写'
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
            
            // 可能是封装的PDOClass有缺陷，所以这里使用mysqli原生对象去获取kf_creat_user
        	$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
            
            // 操作huoma_kf_zima表
            $huoma_kf_zima = $db->set_table('huoma_kf_zima');
            
            // 验证当前要删除的zm_id的发布者是否为当前登录的用户
            // 1 先获取到当前zm_id的kf_id
            $getKfid = ['zm_id'=>$zm_id];
            $getKfidResult = $huoma_kf_zima->find($getKfid);
            $kf_id = json_decode(json_encode($getKfidResult))->kf_id;
            
            // 2 根据kf_id获取到用户（可能是封装的PDOClass有缺陷，所以这里使用mysqli原生对象去获取kf_creat_user）
            $getCreatUser = "SELECT * FROM huoma_kf WHERE kf_id='$kf_id'";
            $kf_creat_user = json_decode(json_encode($conn->query($getCreatUser)->fetch_assoc()))->kf_creat_user;
            
            // 判断操作权限
            if($kf_creat_user && $kf_creat_user == $LoginUser){
                
                // 时间
                $zm_update_time = date('Y-m-d H:i:s');
                
                // 用户一致：允许操作
                $updatekfzm = "UPDATE huoma_kf_zima SET 
                zm_yz='$zm_yz',
                zm_num='$zm_num',
                zm_qrcode='$zm_qrcode',
                zm_beizhu_ht='$zm_beizhu_ht',
                zm_update_time='$zm_update_time' 
                WHERE zm_id='$zm_id'";
                
                // 操作结果
                $updatekfzmResult = $huoma_kf_zima->findSql($updatekfzm);
                
                // 判断操作结果
                if(count($updatekfzmResult) == 0){
                    
                    // 操作成功
                    $result = array(
        			    'code' => 200,
                        'msg' => '更新成功',
                        'kf_id' => $kf_id  // 返回kf_id用于刷新二维码列表
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
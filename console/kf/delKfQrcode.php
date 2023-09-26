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
    	$zm_id = trim($_GET['zm_id']);
    	
        // 过滤参数
        if(empty($zm_id) || $zm_id == '' || $zm_id == null || !isset($zm_id)){
            
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
        	
        	// 数据库huoma_kf_zima表
        	$huoma_kf_zima = $db->set_table('huoma_kf_zima');
        	
            // 验证当前要删除的zm_id的发布者是否为当前登录的用户
            // 1 先获取到当前zm_id的kf_id
            $where_zminfo = ['zm_id'=>$zm_id];
            $find_zminfo = $huoma_kf_zima->find($where_zminfo);
            $kf_id = json_decode(json_encode($find_zminfo))->kf_id;
            
            // 2 根据kf_id获取到用户（可能是封装的PDOClass有缺陷，所以这里使用mysqli原生对象去获取kf_creat_user）
            $find_kf_creat_user = "SELECT * FROM huoma_kf WHERE kf_id='$kf_id'";
            $kf_creat_user = json_decode(json_encode($conn->query($find_kf_creat_user)->fetch_assoc()))->kf_creat_user;
            
            // 判断操作权限
            if($kf_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                // 操作条件
                $delKfzm = ['zm_id'=>$zm_id];
                // 执行操作
                $delKfzmResult = $huoma_kf_zima->delete($delKfzm);
                
                // 操作结果
                if($delKfzmResult && $delKfzmResult == 1){
                    
                    // 删除成功
                    $result = array(
                        'code' => 200,
                        'msg' => '删除成功',
                        'kf_id' => $kf_id // 返回kf_id用于刷新二维码列表
                    );
                }else{
                    
                    // 解析报错信息
                    $errorInfo = json_decode(json_encode($delKfzmResult,true))[2];
                    if(!$errorInfo){
                        
                        // 如果没有报错信息
                        $errorInfo = '未知';
                    }
                    // 删除失败
                    $result = array(
                        'code' => 202,
                        'msg' => '删除失败，原因：'.$errorInfo
                    );
                }
                
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '删除失败：无法获取到数据，原因：数据已被删除、数据不存在、获取数据失败等...'
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
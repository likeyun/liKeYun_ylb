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
        $before_qun_id = trim($_POST['before_qun_id']);
        $later_qun_id = trim($_POST['later_qun_id']);
        
        // 过滤参数
        if(empty($before_qun_id) || !isset($before_qun_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '请输入原活码ID'
            );
        }else if(empty($later_qun_id) || !isset($later_qun_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '请输入并入活码ID'
            );
        }else{
            
            // ID生成
            $bingliu_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
        	// 验证原活码ID是否已经删除
            $check_before_qun_id_isDelete = $db->set_table('huoma_qun')->find(['qun_id' => $before_qun_id]);
            if($check_before_qun_id_isDelete) {
                
                // 如果存在
                // 代表未删除，不能并流
                $result = array(
                    'code' => 202,
                    'msg' => '添加失败！该活码未删除，不能并流。'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
        	
            // 验证原活码ID是否已经被添加到并流列表
            $check_before_qun_id = $db->set_table('ylb_qun_bingliu')->find(['before_qun_id' => $before_qun_id]);
            if($check_before_qun_id) {
                
                // 如果已存在
                // 就不允许重复添加
                $result = array(
                    'code' => 202,
                    'msg' => '添加失败！该活码已经被并流至ID：' . $check_before_qun_id['later_qun_id']
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 验证并入活码ID是否存在
            $check_later_qun_id = $db->set_table('huoma_qun')->find(['qun_id' => $later_qun_id]);
            if(!$check_later_qun_id) {
                
                // 如果不存在
                $result = array(
                    'code' => 202,
                    'msg' => '并入的活码ID不存在'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
        
        	// 插入数据库
            $addParams = [
                'bingliu_id' => $bingliu_id,
                'before_qun_id' => $before_qun_id,
                'later_qun_id' => $later_qun_id,
                'createUser' => $_SESSION["yinliubao"]
            ];
            $addBingliu = $db->set_table('ylb_qun_bingliu')->add($addParams);
            if($addBingliu){
                
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
        
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录'
        );
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
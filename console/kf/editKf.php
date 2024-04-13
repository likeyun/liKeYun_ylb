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
    	$kf_title = trim($_POST['kf_title']);
    	$kf_beizhu = trim($_POST['kf_beizhu']);
    	$kf_rkym = trim($_POST['kf_rkym']);
    	$kf_ldym = trim($_POST['kf_ldym']);
    	$kf_dlym = trim($_POST['kf_dlym']);
    	$kf_model = trim($_POST['kf_model']);
    	$kf_online = trim($_POST['kf_online']);
    	$kf_onlinetimes = trim($_POST['kf_onlinetimes']);
    	$kf_safety = trim($_POST['kf_safety']);
    	$kf_id = trim($_POST['kf_id']);
    	
        // 过滤参数
        if(empty($kf_title) || !isset($kf_title)){
            
            $result = array(
			    'code' => 203,
                'msg' => '标题未填写'
		    );
        }else if(empty($kf_rkym) || !isset($kf_rkym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '入口域名未选择'
		    );
        }else if(empty($kf_ldym) || !isset($kf_ldym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '落地域名未选择'
		    );
        }else if(empty($kf_dlym) || !isset($kf_dlym)){
            
            $result = array(
			    'code' => 203,
                'msg' => '短链域名未选择'
		    );
        }else if(empty($kf_model) || !isset($kf_model)){
            
            $result = array(
			    'code' => 203,
                'msg' => '循环模式未选择'
		    );
        }else if(empty($kf_online) || !isset($kf_online)){
            
            $result = array(
			    'code' => 203,
                'msg' => '在线状态未设置'
		    );
        }else if(empty($kf_safety) || !isset($kf_safety)){
            
            $result = array(
			    'code' => 203,
                'msg' => '顶部扫码安全提示未设置'
		    );
        }else if($kf_online == 1 && empty($kf_onlinetimes)){
            
            $result = array(
			    'code' => 203,
                'msg' => '在线时间配置不得为空'
		    );
        }else if($kf_online == 1 && !empty($kf_onlinetimes) && is_valid_time_config($kf_onlinetimes) !== true){
            
            $result = array(
			    'code' => 203,
                'msg' => '在线时间配置异常，异常位置：' . json_encode(is_valid_time_config($kf_onlinetimes),JSON_UNESCAPED_UNICODE)
		    );
        }else if(empty($kf_id) || !isset($kf_id)){
            
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
        	
            // 验证当前要编辑的kf_id的发布者是否为当前登录的用户
            $getKfidResult = $db->set_table('huoma_kf')->find(['kf_id'=>$kf_id]);
            $kf_creat_user = json_decode(json_encode($getKfidResult))->kf_creat_user;
            
            // 判断操作结果
            if($kf_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                // 参数
                $updatekfData = [
                    'kf_title' => $kf_title,
                    'kf_rkym' => $kf_rkym,
                    'kf_ldym' => $kf_ldym,
                    'kf_dlym' => $kf_dlym,
                    'kf_model' => $kf_model,
                    'kf_online' => $kf_online,
                    'kf_onlinetimes' => $kf_onlinetimes,
                    'kf_safety' => $kf_safety,
                    'kf_beizhu' => $kf_beizhu
                ];
                
                // 更新条件
                $updatekfCondition = [
                    'kf_id' => $kf_id,
                    'kf_creat_user' => $LoginUser
                ];
                
                // 提交更新
                $updateKf = $db->set_table('huoma_kf')->update($updatekfCondition,$updatekfData);
                
                // 判断操作结果
                if($updateKf){
                    
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
            'msg' => '未登录'
		);
    }
    
    // 验证在线时间JSON配置的合法性
    function is_valid_time_config($json_data) {
        
        try {
            // 解码 JSON 数据
            $time_config = json_decode($json_data, true);
    
            // 确保 JSON 解码成功，并且得到了一个数组
            if ($time_config === null || !is_array($time_config)) {
                
                // 返回当前不合法的数组
                return ['error' => '这不是一个正确的JSON格式'];
            }
    
            $invalid_keys = [];
    
            // 检查键（一周中的日期）和值（时间段）
            foreach ($time_config as $day => $time_slots) {
                
                // 检查日期是否是有效的整数
                if (!is_numeric($day) || $day < 1 || $day > 7) {
                    
                    // 返回当前不合法的星期
                    $invalid_keys[$day] = '你只能输入1-7的数值代表星期一至星期日！';
                }
    
                // 检查值是否是数组（包含时间段的关联数组）
                if (!is_array($time_slots)) {
                    
                    // 返回当前不合法的关联数组
                    $invalid_keys[$day] = '关联数组不符合规则';
                }
    
                foreach ($time_slots as $slot => $time_range) {
                    
                    // 检查时间段键是否合法
                    if (!in_array($slot, ['morning', 'afternoon', 'evening'])) {
                        
                        // 返回当前不合法的时间段键
                        $invalid_keys[$day][$slot] = '你输入的时间段键值不符合规则！正确的键值为：morning、afternoon、evening';
                    }
    
                    // 使用正则表达式检查时间格式（HH:mm-HH:mm）
                    if (!preg_match('/^(?:[01]\d|2[0-4]|00):[0-5]\d-(?:[01]\d|2[0-4]|00):[0-5]\d$/', $time_range)) {
                        
                        // 返回当前不合法的时间格式
                        $invalid_keys[$day][$slot] = '你的时间格式不符合规则！正确的格式：HH:mm-HH:mm';
                    }
    
                    // 检查时间范围的有效性
                    list($start_time, $end_time) = explode('-', $time_range);
                    if ($start_time >= $end_time && $start_time !== '00:00') {
                        
                        // 返回当前不合法的时间范围
                        $invalid_keys[$day][$slot] = '你输入的时间范围不符合规则！请确保开始时间 < 结束时间';
                    }else if($start_time >= $end_time && $end_time !== '00:00') {
                        
                        // 返回当前不合法的时间范围
                        $invalid_keys[$day][$slot] = '你输入的时间范围不符合规则！';
                    }
                }
            }
    
            return empty($invalid_keys) ? true : $invalid_keys;
        } catch (Exception $e) {
            
            // 如果在验证过程中发生任何异常，视为无效
            return ['error' => '在线时间配置验证过程中发生异常~'];
        }
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
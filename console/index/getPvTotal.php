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
        $type = trim($_GET['type']);
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    	
    	// 面向对象连接数据库
        $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
        
        // 验证是否存在huoma_dwz表
        $conn->query('SELECT * FROM huoma_dwz');
        if(preg_match("/huoma_dwz' doesn/", $conn->error)){
            
            // 不存在huoma_dwz表
            $result = array(
    			'code' => 205,
                'msg' => '点击这里进行升级'
    		);
    		echo json_encode($result,JSON_UNESCAPED_UNICODE);
    		exit;
        }
        
        // 验证是否存在huoma_dwz_apikey表
        $conn->query('SELECT * FROM huoma_dwz_apikey');
        if(preg_match("/huoma_dwz_apikey' doesn/", $conn->error)){
            
            // 不存在huoma_dwz_apikey表
            $result = array(
    			'code' => 205,
                'msg' => '点击这里进行升级'
    		);
    		echo json_encode($result,JSON_UNESCAPED_UNICODE);
    		exit;
        }
        
        // 验证huoma_count表里面的count_dwz_pv字段是否存在
        $conn->query('SELECT count_dwz_pv FROM huoma_count');
        if(preg_match("/Unknown column 'count_dwz_pv'/", $conn->error)){
            
            // 不存在count_dwz_pv字段
            $result = array(
    			'code' => 205,
                'msg' => '点击这里进行升级'
    		);
    		echo json_encode($result,JSON_UNESCAPED_UNICODE);
    		exit;
        }
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 对当天各时段访问量进行求和
        $countQunPvTotal = 'SELECT SUM(count_qun_pv),SUM(count_kf_pv),SUM(count_channel_pv),SUM(count_dwz_pv) FROM huoma_count';
        $countQunPvTotalResult = $db->set_table('huoma_count')->findSql($countQunPvTotal);
        
        // 操作结果
        if($countQunPvTotalResult){
            
            // 建一个数组用来储存这三个求和
            $pvTotalArray = array();
            foreach ($countQunPvTotalResult as $k => $v){
                
                // 遍历数组
                $pvTotalArray['qun_pvTotal'] = $countQunPvTotalResult[$k]['SUM(count_qun_pv)'];
                $pvTotalArray['kf_pvTotal'] = $countQunPvTotalResult[$k]['SUM(count_kf_pv)'];
                $pvTotalArray['channel_pvTotal'] = $countQunPvTotalResult[$k]['SUM(count_channel_pv)'];
                $pvTotalArray['dwz_pvTotal'] = $countQunPvTotalResult[$k]['SUM(count_dwz_pv)'];
            }
            
            // 检查huoma_count的访问量是不是今天的
            $checkCountData = ['id'=>1];
            $checkCountDataResult = $db->set_table('huoma_count')->find($checkCountData);
            
            // 统计表第一条数据当前的日期
            $count_date = json_decode(json_encode($checkCountDataResult))->count_date;
            
            // 判断日期是否为今天的
            if($count_date == date('Y-m-d')){
                
                // 今天
                // 根据type来获取对应活码的各时段访问量
                $getHourCount = $db->set_table('huoma_count')->findAll(
                    $conditions=['count_date'=>date('Y-m-d')],
                    $order='id asc',
                    $fields='count_'.$type.'_pv',
                    $limit=null
                );
                
                // 操作结果
                if($getHourCount){
                    
                    // 建一个数组用来储存各时段访问量
                    $hourCountArray = array();
                    foreach ($getHourCount as $k => $v){
                        
                        // 遍历数组
                        $hourCountArray[] = $getHourCount[$k]['count_'.$type.'_pv'];
                    }
                    
                    // 获取成功
                    $result = array(
            			'code' => 200,
            			'pvTotal' => $pvTotalArray,
            			'hourCount' => $hourCountArray,
            			'userTotal' => countUserTotalNum($db),
                        'msg' => '获取成功'
            		);
                }else{
                    
                    // 获取失败
                    $result = array(
            			'code' => 202,
                        'msg' => '获取失败'
            		);
                }
            }else{
                
                // 非今天
                // 将huoma_count的访问量及日期更新为今天
                $thisDate = date('Y-m-d');
                $updateDefault = 'UPDATE huoma_count SET count_qun_pv="0",count_kf_pv="0",count_channel_pv="0",count_dwz_pv="0",count_date="'.$thisDate.'"';
                $db->set_table('huoma_count')->findSql($updateDefault);
                
                // 获取成功
                $result = array(
        			'code' => 200,
        			'pvTotal' => array('qun_pvTotal' => 0,'kf_pvTotal' => 0,'channel_pvTotal' => 0,'dwz_pvTotal' => 0),
        			'hourCount' => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
        			'userTotal' => countUserTotalNum($db),
                    'msg' => '获取成功'
        		);
            }
        }
        
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录或登录过期'
		);
    }
    
    // 获取用户数量
    function countUserTotalNum($db){
        return $db->set_table('huoma_user')->getCount(['user_admin'=>2]);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>
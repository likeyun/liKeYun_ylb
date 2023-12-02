<?php

    
    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 非法参数
     */
    
    // 编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 需要获取的 hourNum_type
        $hourNum_type = trim($_GET['hourNum_type']);
        $label = trim($_GET['label']);
        $LoginUser = $_SESSION["yinliubao"];
        
        // 过滤参数
        $allowedHourNum_type = ['qun', 'kf', 'channel', 'zjy', 'dwz', 'shareCard', 'multiSPA'];
        if (!in_array($hourNum_type, $allowedHourNum_type)) {
            
            // 如果传过来的 hourNum_type 不在以上数组中
            $result = array(
                'code' => 204,
                'msg' => '非法参数'
            );
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 数据库配置
        include '../Db.php';
            
        // 实例化类
        $db = new DB_API($config);
        
        // 获取今天的数据
        $hourNumData = $db->set_table('huoma_hourNum')->findAll(['hourNum_date'=>date('Y-m-d')]);
        
        // 初始化统计结果数组
        $pvTotals = array(
            'qun_pvTotal' => 0,
            'kf_pvTotal' => 0,
            'channel_pvTotal' => 0,
            'dwz_pvTotal' => 0,
            'zjy_pvTotal' => 0,
            'shareCard_pvTotal' => 0,
            'multiSPA_pvTotal' => 0,
        );
        
        // 遍历数据进行求和
        foreach ($hourNumData as $item) {
            
             // 将pv值转换为整数
            $pv = intval($item['hourNum_pv']);
            
            switch ($item['hourNum_type']) {
                case 'qun':
                    $pvTotals['qun_pvTotal'] += $pv;
                    break;
                case 'kf':
                    $pvTotals['kf_pvTotal'] += $pv;
                    break;
                case 'channel':
                    $pvTotals['channel_pvTotal'] += $pv;
                    break;
                case 'dwz':
                    $pvTotals['dwz_pvTotal'] += $pv;
                    break;
                case 'zjy':
                    $pvTotals['zjy_pvTotal'] += $pv;
                    break;
                case 'shareCard':
                    $pvTotals['shareCard_pvTotal'] += $pv;
                    break;
                case 'multiSPA':
                    $pvTotals['multiSPA_pvTotal'] += $pv;
                    break;
                default:
                    break;
            }
        }
        
        // 初始化一个长度为24的数组，并将所有元素设置为0
        $hourNumArray = array_fill(0, 24, 0);
        
        // 遍历数据，根据hourNum_hour累计访问量
        foreach ($hourNumData as $entry) {
            
            // 筛选出当前 hourNum_type的
            if($entry['hourNum_type'] === $hourNum_type){
                
                // 将当前访问量添加到数组
                $hour = intval($entry['hourNum_hour']);
                $pv = intval($entry['hourNum_pv']);
                $hourNumArray[$hour] += $pv;
            }
            
        }
        
        // 获取当前登录账号的管理员权限
    	$user_admin = $db->set_table('huoma_user')->getField(['user_name'=>$LoginUser],'user_admin');
    	
        // 根据管理员权限返回数据
        if($user_admin == 1) {
            
            // 当前为管理员
            $result = array(
                'code' => 200,
                'msg' => '获取成功',
                'pvTotals' => $pvTotals, // 今天各渠道的总访问量
                'hourNumArray' => $hourNumArray, // 0-23时访问量
                'chartData' => array(
    			    array(
                        'label' => $label, // 当前展示的数据所属的渠道
                        'data' => $hourNumArray, // 各时段的数据
                        'backgroundColor' => ['rgba(59,94,225,1)'], // 背景颜色
                        'borderColor' => ['rgba(59,94,225,0.5)'], // 线条颜色
                        'borderWidth' => 2, // 线条宽度
                        'cubicInterpolationMode' => 'monotone',
                        'tension' => 1
    			    )
    			), // 折线图配置
            );
        }else {
            
            // 非管理员
            // $pvTotals_ = array(
            //     'qun_pvTotal' => '无权限',
            //     'kf_pvTotal' => '无权限',
            //     'channel_pvTotal' => '无权限',
            //     'dwz_pvTotal' => '无权限',
            //     'zjy_pvTotal' => '无权限',
            //     'shareCard_pvTotal' => '无权限',
            //     'multiSPA_pvTotal' => '无权限',
            // );
            $pvTotals_ = array(
                'qun_pvTotal' => '-',
                'kf_pvTotal' => '-',
                'channel_pvTotal' => '-',
                'dwz_pvTotal' => '-',
                'zjy_pvTotal' => '-',
                'shareCard_pvTotal' => '-',
                'multiSPA_pvTotal' => '-',
            );
            $hourNumArray_ = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        
            $result = array(
                'code' => 200,
                'msg' => '非管理员不展示数据',
                'pvTotals' => $pvTotals_, // 今天各渠道的总访问量
                'hourNumArray' => $hourNumArray_, // 0-23时访问量
                'chartData' => array(
    			    array(
                        'label' => $label, // 当前展示的数据所属的渠道
                        'data' => $hourNumArray_, // 各时段的数据
                        'backgroundColor' => ['rgba(59,94,225,1)'], // 背景颜色
                        'borderColor' => ['rgba(59,94,225,0.5)'], // 线条颜色
                        'borderWidth' => 2, // 线条宽度
                        'cubicInterpolationMode' => 'monotone',
                        'tension' => 1
    			    )
    			), // 折线图配置
            );
        }
    }else{
        
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录'
        );
    }
    
    // // 输出JSON
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
    
?>
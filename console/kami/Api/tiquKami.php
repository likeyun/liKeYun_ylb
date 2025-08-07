<?php

    /**
     * 状态码说明
     * 200 成功
     */
    
    // 页面编码
    header("Content-type:application/json");
    
    // 获取参数
    $openid = $_GET['openid'];
    $signKey = $_GET['signKey'];
    $kami_id = $_GET['kami_id'];
    $brand = $_GET['brand'];
    $model = $_GET['model'];
    $system = $_GET['system'];
    
    // 数据库配置
    include '../../Db.php';
    
    // 实例化类
    $db = new DB_API($config);
    
    // 查询当前openid是否被禁止提取
    $openid_ban = $db->set_table('ylb_km_openid_ban')->findAll(['openid' => $openid, 'kami_id' => $kami_id]);
    if($openid_ban) {
        
        // 禁止提取
        $result = array(
            'code' => 209,
            'msg' => '你被禁止提取'
        );
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 有参数
    if ($kami_id && $signKey && $openid) {
    
        // 获取当前卡密项目的情况
        $getKamiInfo = $db->set_table('ylb_kami')->findAll(
            $conditions = ['kami_id' => $kami_id],
            $order = null,
            $fields = 'kami_type, kami_title, km_total, kami_repeat_tiqu, kami_repeat_tiqu_interval, kami_status',
            $limit = null
        );
        
        // 获取成功
        if ($getKamiInfo) {
    
            // 解析几个需要用的参数
            $kami_status = $getKamiInfo[0]['kami_status'];
            $km_total = $getKamiInfo[0]['km_total'];
            $kami_type = $getKamiInfo[0]['kami_type'];
            $kami_title = $getKamiInfo[0]['kami_title'];
            $kami_repeat_tiqu = $getKamiInfo[0]['kami_repeat_tiqu'];
            $kami_repeat_tiqu_interval = $getKamiInfo[0]['kami_repeat_tiqu_interval'];
    
            // 获取提取记录
            $checkRepeatTiqu = $db->set_table('ylb_km_openid')->findAll(
                $conditions = ['openid' => $openid, 'kami_id' => $kami_id],
                $order = 'ID DESC',
                $fields = '*',
                $limit = '0,1'
            );
    
            // 只在开启禁止重复提取的时候验证
            if ($kami_repeat_tiqu == 2) {
    
                // 验证当前openid是否重复提取该卡密项目
                if ($checkRepeatTiqu) {
    
                    // 重复提取
                    $result = array(
                        'code' => 207,
                        'msg' => '不能重复提取'
                    );
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                    exit;
                }
            } else {
    
                // 获取上一条提取的时间
                $lastTiquTime = $checkRepeatTiqu[0]['tiqu_time'];
    
                // 计算提取间隔时间是否符合
                $current_time = date('Y-m-d H:i:s');
    
                // 将日期时间字符串转换为UNIX时间戳
                $timestamp1 = strtotime($current_time);
                $timestamp2 = strtotime($lastTiquTime);
    
                // 计算两个时间戳之间的差异
                $lastTiquTime_interval = abs($timestamp2 - $timestamp1);
    
                // 如果上一条提取时间小于等于间隔时间
                if ($lastTiquTime_interval <= $kami_repeat_tiqu_interval) {
    
                    // x秒内不能重复提取
                    $result = array(
                        'code' => 208,
                        'msg' => '提取过于频繁，请稍后再试~'
                    );
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            // 当前项目是正常的
            if ($kami_status == 1) {
    
                // 有卡密
                if ($km_total > 0) {
    
                    // 可以领取
                    // 获取当前kami_id下的km
                    $getKm = $db->set_table('ylb_kmlist')->findAll(
                        $conditions = ['kami_id' => $kami_id, 'km_status' => 1],
                        $order = 'ID DESC',
                        $fields = 'km, km_id, km_expiryDate',
                        $limit = '1'
                    );
    
                    if ($getKm) {
    
                        // 获取到Km
                        // 将当前Km状态设置为2
                        $updateKm = $db->set_table('ylb_kmlist')->update(
                            ['km_id' => $getKm[0]['km_id']],
                            ['km_status' => 2],
                        );
    
                        if ($updateKm) {
    
                            // 领取成功
                            $result = array(
                                'code' => 200,
                                'msg' => '已提取',
                                'km' => $getKm[0]['km']
                            );
                            
                            // 记录到提取表
                            $TiquJilu = [
                                'kami_id' => $kami_id,
                                'kami_title' => $kami_title,
                                'km' => $getKm[0]['km'],
                                'km_expiryDate' => $getKm[0]['km_expiryDate'],
                                'openid' => $openid,
                                'brand' => $brand,
                                'model' => $model,
                                'system' => $system,
                            ];
                            $check_isExist = $db->set_table('ylb_km_openid')->findAll(['km' =>$getKm[0]['km'],'openid' => $openid, 'kami_id' => $kami_id]);
                            if(!$check_isExist) {
                                
                                // 如果不存在，才记录
                                $db->set_table('ylb_km_openid')->add($TiquJilu);
                            }
                            
                        } else {
    
                            // 领取失败
                            $result = array(
                                'code' => 205,
                                'msg' => '提取失败',
                            );
                        }
                    } else {
    
                        // 获取失败
                        if (count($getKm) == 0) {
    
                            // 被领完了
                            $result = array(
                                'code' => 204,
                                'msg' => $kami_type . '被领完了~',
                            );
                        } else {
    
                            // 提取失败
                            $result = array(
                                'code' => 204,
                                'msg' => $kami_type . '提取失败~',
                            );
                        }
                    }
                } else {
    
                    // 无卡密
                    $result = array(
                        'code' => 201,
                        'msg' => '暂无' . $kami_type . '可提取~',
                    );
                }
            } else {
    
                // 不支持领取
                $result = array(
                    'code' => 202,
                    'msg' => '未开放提取！',
                );
            }
        } else {
    
            // 提取失败
            $result = array(
                'code' => 203,
                'msg' => '暂无项目~',
            );
        }
    } else {
    
        // 参数缺失
        $result = array(
            'code' => 203,
            'msg' => '参数缺失，系统错误！',
        );
    }
    
    // 输出JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>

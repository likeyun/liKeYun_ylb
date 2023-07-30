<?php

    // 更新当前小时的总访问量
    // 设置时区为北京时间
    date_default_timezone_set('Asia/Shanghai');
    
    // 获取当前日期和小时
    $nowDate = date('Y-m-d');
    $nowHour = date('H');
    
    // 查询是否已存在该hourNum_type的记录
    $getHourNum = $db->set_table('huoma_hourNum')->find(
        [
            'hourNum_type'=>$hourNum_type,
            'hourNum_date'=>$nowDate,
            'hourNum_hour'=>$nowHour
        ]
    );
    
    // 验证是否存在当前时间的访问次数记录
    if($getHourNum){
        
        // 获取访问次数
        $hourNum_pv = getSqlData($getHourNum,'hourNum_pv');
        $newHourNum_pv = $hourNum_pv + 1;
        
        // 更新访问次数
        $db->set_table('huoma_hourNum')->update(
            [
                'hourNum_type'=>$hourNum_type,
                'hourNum_date'=>$nowDate,
                'hourNum_hour'=>$nowHour
            ],
            ['hourNum_pv'=>$newHourNum_pv]
        );
    }else{
        
        // 新增一条记录
        $db->set_table('huoma_hourNum')->add(
            [
                'hourNum_type'=>$hourNum_type,
                'hourNum_date'=>$nowDate,
                'hourNum_hour'=>$nowHour,
                'hourNum_pv'=>1,
            ]
        );
    }
    
?>
<?php

    // 数据库配置
    include '../Db.php';
    
    // 实例化类
    $db = new DB_API($config);
    
    // 获取配置
    $getConfig = $db->set_table('huoma_shareCardConfig')->find(['id'=>1]);
    $appid = $getConfig['appid'];
    $appsecret = $getConfig['appsecret'];
    
    // 提醒文字
    function warnInfo($title,$warnText){
        
        return '
        <title>'.$title.'</title>
        <div id="warnning">
            <img src="../../static/img/warn.png" />
        </div>
        <p id="warnText">'.$warnText.'</p>';
    }
    
    // 根据sid获取shareCardInfo
    $getShareInfo = $db->set_table('huoma_shareCard')->find(['shareCard_id'=>$sid]);
    if($getShareInfo){
        
        // 标题
        $shareCard_title = $getShareInfo['shareCard_title'];
        
        // 摘要
        $shareCard_desc = $getShareInfo['shareCard_desc'];
        
        // 缩略图
        $shareCard_img = $getShareInfo['shareCard_img'];
        
        // 状态
        $shareCard_status = $getShareInfo['shareCard_status'];
        
        if ($shareCard_status == 2) {
            
            // 停用
            echo warnInfo('提示','该链接已被管理员暂停使用');
            exit;
        }
        
        // 更新当前中间页的访问量
        function updatePV($db,$sid){
            $updatePV = 'UPDATE huoma_shareCard SET shareCard_pv=shareCard_pv+1 WHERE shareCard_id="'.$sid.'"';
            $db->set_table('huoma_shareCard')->findSql($updatePV);
        }
        
        // 记录今天ip访问量
        function updateTodayIpNum($db){
            
            // 获取ip地址
            $getIP = $_SERVER['REMOTE_ADDR'];
            
            // 获取今天的ip记录数
            $getTodayIpNum = $db->set_table('huoma_ip')->find(['ip_create_time'=>date('Y-m-d')]);
            
            // 如果有记录
            if($getTodayIpNum){
                
                // 查询当前ip是否为今天首次访问
                $getThisIpISFirstTimeToday = $db->set_table('huoma_ip_temp')->find(
                    [
                        'create_date'=>date('Y-m-d'),
                        'ip'=>$getIP,
                        'from_page'=>'shareCard'
                    ]
                );
                
                // 如果没有记录
                // 说明这个ip是今天第一次访问
                if(!$getThisIpISFirstTimeToday){
                    
                    // 将当前ip添加至临时ip表
                    $db->set_table('huoma_ip_temp')->add(
                        [
                            'ip'=>$getIP,
                            'create_date'=>date('Y-m-d'),
                            'from_page'=>'shareCard'
                        ]
                    );
                    
                    // 然后更新今天的ip记录数
                    $shareCard_ip = $getTodayIpNum['shareCard_ip'];
                    $newShareCard_ip = $shareCard_ip + 1;
                    $db->set_table('huoma_ip')->update(
                        ['ip_create_time'=>date('Y-m-d')],
                        ['shareCard_ip'=>$newShareCard_ip]
                    );
                }
            }else{
                
                // 如果没有记录
                // 将当前ip添加至临时ip表并记录为今天的ip访问
                $db->set_table('huoma_ip_temp')->add(['ip'=>$getIP,'create_date'=>date('Y-m-d'),'from_page'=>'shareCard']);
                
                // 新增这个ip今天的访问次数
                $db->set_table('huoma_ip')->add(['shareCard_ip'=>1,'ip_create_time'=>date('Y-m-d')]);
            }
            
            // 昨天的日期
            $yesterdayDate = date('Y-m-d',strtotime("yesterday"));
            
            // 检查是否存在昨天的ip记录
            $getYesterdayIp = $db->set_table('huoma_ip_temp')->find(
                ['create_date'=>$yesterdayDate,'from_page'=>'shareCard']
            );
            
            // 如果有记录
            if($getYesterdayIp){
                
                // 清理昨天日期的临时ip
                $db->set_table('huoma_ip_temp')->delete(
                    ['create_date'=>$yesterdayDate,'from_page'=>'shareCard']
                );
            }
        }
        
        // 更新当前小时的总访问量
        function updateCurrentHourPageView($db,$hourNum_type){
            
            // 引入公共文件
            require '../public/updateCurrentHourPageView.php';
        }
        
        // 解析数组
        function getSqlData($result,$field){
            
            // 传入数组和需要解析的字段
            return json_decode(json_encode($result))->$field;
        }
        
        // 请求接口获取新的access_token
        function getNewToken($appid,$appsecret){
            $get_access_token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret."";
            $access_token_json =  file_get_contents($get_access_token_url);
            $access_token = json_decode($access_token_json)->access_token;
            return $access_token;
        }
        
        // 请求接口获取新的jsapi_ticket
        function getNewTicket($access_token_Str){
            
            $get_jsapi_ticket_url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token='.$access_token_Str;
            $jsapi_ticket = file_get_contents($get_jsapi_ticket_url);
            return json_decode($jsapi_ticket)->ticket;
        }
        
        // 从配置中获取access_token
        $get_access_token = $db->set_table('huoma_shareCardConfig')->find(['id'=>1]);
        
        // 判断是否有access_token
        if($get_access_token){
            
            // 获取access_token
            $access_token = json_decode(json_encode($get_access_token))->access_token;
            $access_token_expire_time = json_decode(json_encode($get_access_token))->access_token_expire_time;
            if($access_token){
                
                // 有token
                // 判断有效期
                if(time() > $access_token_expire_time){
                    
                    // 已过期
                    // 请求接口获取新的access_token
                    $access_token_Str = getNewToken($appid,$appsecret);
                    $NewToken = ['access_token'=>$access_token_Str,'access_token_expire_time'=>time()+7000];
                    $db->set_table('huoma_shareCardConfig')->update(['id'=>1],$NewToken);
                }else{
                    
                    // 未过期
                    $access_token_Str = $access_token;
                }
            }else{
                
                // 没有token
                // 请求接口获取新的access_token
                $access_token_Str = getNewToken($appid,$appsecret);
                $NewToken = ['access_token'=>$access_token_Str,'access_token_expire_time'=>time()+7000];
                $db->set_table('huoma_shareCardConfig')->update(['id'=>1],$NewToken);
            }
            
            // 获取jsapi_ticket
            // 从配置中获取access_token
            $get_jsapi_ticket = $db->set_table('huoma_shareCardConfig')->find(['id'=>1]);
            $jsapi_ticket = json_decode(json_encode($get_jsapi_ticket))->jsapi_ticket;
            $jsapi_ticket_expire_time = json_decode(json_encode($get_jsapi_ticket))->jsapi_ticket_expire_time;
            if($jsapi_ticket){
                
                // 有token
                // 判断有效期
                if(time() > $jsapi_ticket_expire_time){
                    
                    // 已过期
                    // 请求接口获取新的jsapi_ticket
                    $jsapi_ticket_Str = getNewTicket($access_token_Str);
                    $NewTicket = ['jsapi_ticket'=>$jsapi_ticket_Str,'jsapi_ticket_expire_time'=>time()+7000];
                    $db->set_table('huoma_shareCardConfig')->update(['id'=>1],$NewTicket);
                }else{
                    
                    // 未过期
                    $jsapi_ticket_Str = $jsapi_ticket;
                }
            }else{
                
                // 没有jsapi_ticket
                // 请求接口获取新的jsapi_ticket
                $jsapi_ticket_Str = getNewTicket($access_token_Str);
                $NewTicket = ['jsapi_ticket'=>$jsapi_ticket_Str,'jsapi_ticket_expire_time'=>time()+7000];
                $db->set_table('huoma_shareCardConfig')->update(['id'=>1],$NewTicket);
            }
            
            
            // 获取当前页面URL
            $protocol = (
                !empty($_SERVER['HTTPS']) && 
                $_SERVER['HTTPS'] !== 'off' || 
                $_SERVER['SERVER_PORT'] == 443
            ) ? "https://" : "http://";
            $thisPageurl = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            
            // 时间戳
            $timestamp = time();
            
            // 生成nonceStr
            $createNonceStr = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            str_shuffle($createNonceStr);
            $nonceStr = substr(str_shuffle($createNonceStr),0,16);
            
            // 按照key值ASCII码升序排序
            $signStringVal = "jsapi_ticket=$jsapi_ticket_Str&noncestr=$nonceStr&timestamp=$timestamp&url=$thisPageurl";
            
            // 按顺序排列按sha1加密生成字符串
            $signature = sha1($signStringVal);
        }
    }else{
        
        // 获取失败
        echo warnInfo('提示','页面不存在或已被管理员删除');
        exit;
    }
    
?>
<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $data_title = trim($_POST['data_title']);
        $data_limit = trim($_POST['data_limit']);
        $data_expire_time = trim($_POST['data_expire_time']);
        $data_dlym = trim($_POST['data_dlym']);
        $data_rkym = trim($_POST['data_rkym']);
        $data_ldym = trim($_POST['data_ldym']);
        $data_pic = trim($_POST['data_pic']);
        $data_jumplink = trim($_POST['data_jumplink']);
        $data_create_user = trim($_SESSION["yinliubao"]);
        $data_id = trim($_POST['data_id']);
        
        // 对到期时间进行特殊的处理
        // 因为这里的前端传过来的时间是
        // 2025-05-23T14:54 这种格式
        // 需要处理：1. 去掉T；2. 增加:00作为秒数
        $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $data_expire_time);
        $data_expire_time_formattedTime = $dateTime->format('Y-m-d H:i:s');
        
        // 过滤参数
        if(empty($data_title) || !isset($data_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($data_pic) || !isset($data_pic)){
            
            $result = array(
                'code' => 203,
                'msg' => '请上传图片'
            );
        }else if(empty($data_dlym) || empty($data_rkym) || empty($data_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '还有域名未选择'
            );
        }else if(empty($data_jumplink) || !isset($data_jumplink)){
            
            $result = array(
                'code' => 203,
                'msg' => '请填写跳转地址'
            );
        }else if(empty($data_id) || !isset($data_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '非法请求~'
            );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
            include '../../../../Db.php';
        
            // 实例化类
            $db = new DB_API($config);
            
            // 验证当前要编辑的 data_id 的创建者是否为当前登录的用户
            $getCreatUser = $db->set_table('ylbPlugin_sdk')->find(['data_id' => $data_id]);
            $data_create_user = $getCreatUser['data_create_user'];
            
            // 用户一致：允许操作
            if($data_create_user == $LoginUser){
                
                // 需更新的字段
                $updateData = [
                    'data_title' => $data_title,
                    'data_jumplink' => $data_jumplink,
                    'data_limit' => $data_limit,
                    'data_pic' => $data_pic,
                    'data_dlym' => $data_dlym,
                    'data_rkym' => $data_rkym,
                    'data_ldym' => $data_ldym,
                    'data_expire_time' => $data_expire_time_formattedTime
                ];
                
                // 执行更新
                $updateSQL = $db->set_table('ylbPlugin_sdk')->update(['data_id' => $data_id, 'data_create_user' => $data_create_user],$updateData);
                if($updateSQL){
                    
                    // 更新成功
                    $result = array(
                        'code' => 200,
                        'msg' => '已保存'
                    );
                }else{
                    
                    // 更新失败
                    $result = array(
                        'code' => 202,
                        'msg' => '更新失败'
                    );
                }
                
            }else{
                
                // 用户不一致
                $result = array(
                    'code' => 202,
                    'msg' => '非法操作'
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
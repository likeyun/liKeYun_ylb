<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $jw_title = trim($_POST['jw_title']);
        $jw_dxccym = trim($_POST['jw_dxccym']);
        $jw_icon = trim($_POST['jw_icon']);
        $jw_bgimg = trim($_POST['jw_bgimg']);
        $jw_url = trim($_POST['jw_url']);
        $jw_beizhu = trim($_POST['jw_beizhu']);
        $jw_platform = trim($_POST['jw_platform']);
        $jw_id = trim($_POST['jw_id']);
        
        // 2.3.0新增
        $jw_beizhu_msg = trim($_POST['jw_beizhu_msg']);
        $jw_expire_time = trim($_POST['jw_expire_time']);
        
        // 2.4.0新增
        $jw_common_landpage = trim($_POST['jw_common_landpage']);
        $jw_douyin_landpage = trim($_POST['jw_douyin_landpage']);
        
        // 2.4.1新增
        $jw_fwl_limit = trim($_POST['jw_fwl_limit']); // 访问量限制
        $jw_status = trim($_POST['jw_status']); // 访问量限制
        
        // 时间格式化
        $jw_expire_time = new DateTime($jw_expire_time);
        $jw_expire_time = $jw_expire_time->format("Y-m-d H:i:s");
        
        // 过滤参数
        if(empty($jw_title) || !isset($jw_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($jw_common_landpage) || !isset($jw_common_landpage)){
            
            $result = array(
                'code' => 203,
                'msg' => '通用落地页未选择'
            );
        }else if(empty($jw_douyin_landpage) || !isset($jw_douyin_landpage)){
            
            $result = array(
                'code' => 203,
                'msg' => '抖音落地页未选择'
            );
        }else if(empty($jw_icon) || !isset($jw_icon)){
            
            $result = array(
                'code' => 203,
                'msg' => '分享图未上传'
            );
        }else if(empty($jw_platform) || !isset($jw_platform)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择一个投放平台'
            );
        }else if(empty($jw_url) || !isset($jw_url)){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接未填写'
            );
        }else if($jw_fwl_limit && strlen($jw_fwl_limit) >= 10){
            
            $result = array(
                'code' => 203,
                'msg' => '访问量限制最多9位数'
            );
        }else if(empty($jw_id) || !isset($jw_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '非法请求'
            );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
            include '../../../../Db.php';
        
            // 实例化类
            $db = new DB_API($config);
            
            // 验证当前要编辑的jw_id的发布者是否为当前登录的用户
            $getJwInfoArray = $db->set_table('ylb_jumpWX')->find(['jw_id'=>$jw_id]);
            $jw_create_user = $getJwInfoArray['jw_create_user'];
            
            // 如果表单有传过来值
            // 需要验证是不是管理员操作的
            $checkUser  = $db->set_table('huoma_user')->find(['user_name'=>$LoginUser]);
            $user_admin = $checkUser['user_admin'];
            
            if((int)$user_admin !== 1){
                
                // 非管理员，不管前端传什么，全部使用旧值
                $jw_fwl_limit = $getJwInfoArray['jw_fwl_limit'];
                $jw_status = $getJwInfoArray['jw_status'];
            }
            
            // 用户一致：允许操作
            if($jw_create_user == $LoginUser || (int)$user_admin == 1){
                
                // jw_token
                $jw_token = MD5($jw_id . $jw_title . $jw_url . $jw_create_user);
                
                // 需更新的字段
                $updateData = [
                    'jw_title'=>$jw_title,
                    'jw_common_landpage' => $jw_common_landpage,
                    'jw_douyin_landpage' => $jw_douyin_landpage,
                    'jw_icon'=>$jw_icon,
                    'jw_url'=>$jw_url,
                    'jw_beizhu'=>$jw_beizhu,
                    'jw_beizhu_msg'=>$jw_beizhu_msg, // 2.3.0新增
                    'jw_expire_time'=>$jw_expire_time, // 2.3.0新增
                    'jw_fwl_limit'=>$jw_fwl_limit, // 2.4.1新增（访问量限制）
                    'jw_platform'=>$jw_platform,
                    'jw_create_user'=>$jw_create_user,
                    'jw_token'=>$jw_token,
                    'jw_status' => $jw_status // 2.4.1新增（状态）
                ];
                
                // 执行更新
                $updateSQL = $db->set_table('ylb_jumpWX')->update(['jw_id' => $jw_id],$updateData);
                if($updateSQL){
                    
                    // 更新成功
                    $result = array(
                        'code' => 200,
                        'msg' => '已更新！'
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
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
        $kami_title = trim($_POST['kami_title']);
        $kami_type = trim($_POST['kami_type']);
        $kami_status = trim($_POST['kami_status']);
        $kami_repeat_tiqu = trim($_POST['kami_repeat_tiqu']);
        $kami_repeat_tiqu_interval = trim($_POST['kami_repeat_tiqu_interval']);
        $kami_id = trim($_POST['kami_id']);
        
        // 过滤参数
        if(empty($kami_title) || !isset($kami_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '项目标题未填写'
            );
        }else if(empty($kami_type) || !isset($kami_type)){
            
            $result = array(
                'code' => 203,
                'msg' => '项目类型未选择'
            );
        }else if(empty($kami_status) || !isset($kami_status)){
            
            $result = array(
                'code' => 203,
                'msg' => '项目上架状态未选择'
            );
        }else if(empty($kami_repeat_tiqu) || !isset($kami_repeat_tiqu)){
            
            $result = array(
                'code' => 203,
                'msg' => '是否允许重复提取？'
            );
        }else if(empty($kami_repeat_tiqu) || !isset($kami_repeat_tiqu_interval)){
            
            $result = array(
                'code' => 203,
                'msg' => '请设置提取间隔时间'
            );
        }else if(empty($kami_id) || !isset($kami_id)){
            
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
            
            // 验证发布者是否为当前登录的用户
            $checkUser = $db->set_table('ylb_kami')->find(['kami_id' => $kami_id]);
            $kami_create_user = json_decode(json_encode($checkUser))->kami_create_user;
            
            // 用户一致：允许操作
            if($kami_create_user == $LoginUser){
                
                // 需更新的字段
                $updateData = [
                    'kami_title' => $kami_title,
                    'kami_type' => $kami_type,
                    'kami_repeat_tiqu' => $kami_repeat_tiqu,
                    'kami_repeat_tiqu_interval' => $kami_repeat_tiqu_interval,
                    'kami_status' => $kami_status
                ];
                
                // 执行
                $updateKamiProject = $db->set_table('ylb_kami')->update(['kami_id' => $kami_id,'kami_create_user' => $LoginUser], $updateData);
                if($updateKamiProject){
                    
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
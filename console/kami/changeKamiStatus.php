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
        
        // 获取参数
        $kami_id = trim($_GET['kami_id']);
        
        // 过滤参数
        if(empty($kami_id) || !isset($kami_id)){
            
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
            $kami_create_user = $checkUser['kami_create_user'];
            
            // 获取当前状态
            $kami_status = $checkUser['kami_status'];
            
            // 用户一致：允许操作
            if($kami_create_user == $LoginUser){
                
                // 更新条件
                $updateCondition = [
                    'kami_id' => $kami_id,
                    'kami_create_user' => $LoginUser
                ];
                
                // 执行
                if($kami_status == 1) {
                    
                    // 改为 2
                    $updateZjyResult = $db->set_table('ylb_kami')->update($updateCondition, ['kami_status' => 2]);
                    $current_status_text = '已下架';
                }else {
                    
                    // 改为 1
                    $updateZjyResult = $db->set_table('ylb_kami')->update($updateCondition, ['kami_status' => 1]);
                    $current_status_text = '已上架';
                }
                
                if($updateZjyResult){
                    
                    // 更新成功
                    $result = array(
                        'code' => 200,
                        'msg' => $current_status_text
                    );
                }else{
                    
                    // 更新失败
                    $result = array(
                        'code' => 202,
                        'msg' => '操作失败'
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
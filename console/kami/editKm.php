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
        $km = trim($_POST['km']);
        $km_id = trim($_POST['km_id']);
        $km_expiryDate = trim($_POST['km_expiryDate']);
        $km_expireDate = trim($_POST['km_expireDate']);
        $km_beizhu = trim($_POST['km_beizhu']);
        $km_status = trim($_POST['km_status']);
        
        // 过滤参数
        if(empty($km) || !isset($km)){
            
            $result = array(
                'code' => 203,
                'msg' => '卡密未填写'
            );
        }else if(empty($km_expiryDate) || !isset($km_expiryDate)){
            
            $result = array(
                'code' => 203,
                'msg' => '有效期未填写，如无需设置，请填写“-”'
            );
        }else if(empty($km_expireDate) || !isset($km_expireDate)){
            
            $result = array(
                'code' => 203,
                'msg' => '到期时间未填写，如无需设置，请填写“-”'
            );
        }else if(empty($km_beizhu) || !isset($km_beizhu)){
            
            $result = array(
                'code' => 203,
                'msg' => '备注未填写，如无需设置，请填写“-”'
            );
        }else if(empty($km_status) || !isset($km_status)){
            
            $result = array(
                'code' => 203,
                'msg' => '状态未选择'
            );
        }else if(empty($km_id) || !isset($km_id)){
            
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
            $checkUser = $db->set_table('ylb_kmlist')->find(['km_id' => $km_id]);
            $km_addUser = json_decode(json_encode($checkUser))->km_addUser;
            
            // 用户一致：允许操作
            if($km_addUser == $LoginUser){
                
                // 需更新的字段
                $updateData = [
                    'km' => $km,
                    'km_expiryDate' => $km_expiryDate,
                    'km_expireDate' => $km_expireDate,
                    'km_beizhu' => $km_beizhu,
                    'km_status' => $km_status
                ];
                
                // 更新条件
                $updateCondition = [
                    'km_id' => $km_id,
                    'km_addUser' => $LoginUser
                ];
                
                // 执行
                $updateKmInfo = $db->set_table('ylb_kmlist')->update($updateCondition, $updateData);
                if($updateKmInfo){
                    
                    // 更新成功
                    $result = array(
                        'code' => 200,
                        'msg' => '已更新'
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
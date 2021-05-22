<?php

// 元
$str_tofee = $total_fee;

if(preg_match("/^-?\d+$/",$str_tofee)){
	// 是整数，开始判断位数
	if (strlen($str_tofee) == '1') {
		// 如果只有1位数，1元转为100分
		$tofee_val = $str_tofee."00";
	}else if(strlen($str_tofee) == '2'){
		// 如果是2位数，10元转为1000分
		$tofee_val = $str_tofee."00";
	}else if(strlen($str_tofee) == '3'){
		// 如果是3位数，100元转为10000分
		$tofee_val = $str_tofee."00";
	}else if(strlen($str_tofee) == '4'){
		// 如果是4位数，1000元转为100000分
		$tofee_val = $str_tofee."00";
	}else if(strlen($str_tofee) == '5'){
		// 如果是5位数，10000元转为1000000分
		$tofee_val = $str_tofee."00";
	}
}else{
	// 不是整数，要获取小数点前面的位数
	// 获取小数点前的数
	$str_dot_left = substr($str_tofee,0,strrpos($str_tofee,"."));
	// 获取小数点后的数
	$str_dot_right = substr($str_tofee,strripos($str_tofee,".")+1);

	// 如果小数点后仅有1位数，需要加一个0
	if (strlen($str_dot_right) == '1') {
		$str_dot_right_result = $str_dot_right."0";
	}else if (strlen($str_dot_right) == '2') {
		$str_dot_right_result = $str_dot_right;
	}else if (strlen($str_dot_right) > '2') {
		$str_dot_right_result = substr($str_tofee,2,2);
	}

	// 再获取小数点前的位数
	if (strlen($str_dot_left) == '1') {
		// 1位数
		// 判断这个数是不是0
		if ($str_dot_left == '0') {
			// 如果是0，要判断.后面第一个数是不是0
			if (substr($str_tofee,2,1) == '0') {
				// 如果是0，那么可以确定这是一个0.0X格式
				$tofee_val = substr($str_tofee,3,2);
			}else{
				// 如果不是0，那么可以确定这是一个0.XX格式
				$tofee_val = substr($str_tofee,2,1).substr($str_tofee,3,2);
			}
		}else{
			$tofee_val = '不是';
		}
		// $tofee_val = $str_dot_left.$str_dot_right_result;
	}else if(strlen($str_dot_left) == '2'){
		// 2位数
		$tofee_val = $str_dot_left.$str_dot_right_result;
	}else if(strlen($str_dot_left) == '3'){
		// 3位数
		$tofee_val = $str_dot_left.$str_dot_right_result;
	}else if(strlen($str_dot_left) == '4'){
		// 4位数
		$tofee_val = $str_dot_left.$str_dot_right_result;
	}else if(strlen($str_dot_left) == '5'){
		// 5位数
		$tofee_val = $str_dot_left.$str_dot_right_result;
	}
}
?>
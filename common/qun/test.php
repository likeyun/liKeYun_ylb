<?php

$arr = array(
	"0" => array(
		'xuhao' => '1',
		"pv" => '0',
		"max" => '100',
		'status' => '1'
	),
	"1" => array(
		"xuhao" => '2',
		"pv" => '0',
		"max" => '200',
		'status' => '1'
	),
	"2" => array(
		"xuhao" => '3',
		"pv" => '0',
		"max" => '300',
		'status' => '1'
	),
	"3" => array(
		"xuhao" => '4',
		"pv" => '0',
		"max" => '210',
		'status' => '1'
	),
	"4" => array(
		"xuhao" => '5',
		"pv" => '0',
		"max" => '120',
		'status' => '1'
	)
);

echo json_encode($arr);

// $arr2 = [];

// foreach ($arr as $k=>$v){
//     if($arr[$k]['pv'] < $arr[$k]['max'] && $arr[$k]['status'] == 1){
    	
//        $arr2 = $arr[$k];


//        print_r("序号：".$arr2['xuhao']."<br/>");
//        print_r("当前访问量：".$arr2['pv']."<br/>");
//        print_r("阈值：".$arr2['max']."<br/>");
//        break;
//     }
// }


?>
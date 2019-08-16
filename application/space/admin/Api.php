<?php

// +----------------------------------------------------------------------
// | 基于ThinkPHP5开发
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | 基础框架永久免费开源
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>，开发者QQ群：*
// +----------------------------------------------------------------------

namespace app\space\admin;

use think\Db;
use app\common\controller\Common;
use app\common\model\SystemAnnex as AnnexModel;
use app\common\model\Cparam as CparamModel;
use app\space\model\Floor as FloorModel;

/**
 * 系统API控制器
 */
class Api extends Common 
{
    /**
     * 获取某楼宇下的楼层
     * @param id 消息id
     * @return json
     */
    public function getBanFloor() 
    {
    	$ban_id = input('param.ban_id');
    	//halt($ban_id);
    	$floorArr = FloorModel::where([['status','eq',1]])->field('ban_id,floor_number')->select();
    	$data= [];
    	foreach($floorArr as $f){
    		$data[$f['ban_id']][] = [
    			'id' =>$f['floor_number'],
    			'name' =>$f['floor_number'].'楼',
    		];
    	}
    	if($ban_id && isset($data[$ban_id])){
    		$data = $data[$ban_id];
    	}
    	$result = [
    		'code' => 1,
    		'msg' => '获取成功',
    		'data' => $data
    	];
    	//halt($result);
    	return json($result);
    }
}
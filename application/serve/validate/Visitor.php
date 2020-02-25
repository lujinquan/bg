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

namespace app\serve\validate;

use think\Validate;

/**
 * 访问信息管理验证器
 * @package app\system\validate
 */
class Visitor extends Validate
{
	//定义验证规则
    protected $rule = [
        'visitor_name|访客姓名'       => 'require',
        //'visitor_type|访客类型'      => 'require',
        'visitor_tel|访客电话'      => 'require|mobile',
        'visitor_time|访问时间'      => 'require',
        'member_id|访问对象'      => 'require',       
        //'__token__'      => 'require|token',
    ];

    //定义验证提示
    protected $message = [
        
    ];

     // 新增
    public function sceneAdd()
    {
        return $this->only(['visitor_name','visitor_tel','visitor_time','member_id']);
    }

    // 自定义更新场景

}
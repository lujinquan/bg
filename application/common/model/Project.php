<?php
// +----------------------------------------------------------------------
// | 基于ThinkPHP5开发
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | Motto ：No pains, no gains !
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>
// +----------------------------------------------------------------------
namespace app\common\model;

use think\Model;

/**
 * 系统配置模型
 * @package app\admin\model
 */
class project extends Model
{
    // 设置模型名称
    protected $name = 'project';
    // 定义时间戳字段名
    protected $createTime = 'ctime';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    //protected $field = ['group','sort','name','value','options','status'];

    
}

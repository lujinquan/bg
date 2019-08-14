<?php
// +----------------------------------------------------------------------
// | 基于ThinkPHP5开发
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 https://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | 基础框架永久免费开源
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>，开发者QQ群：*
// +----------------------------------------------------------------------

namespace app\system\model;

use think\Model;
use app\system\model\SystemUser as UserModel;

/**
 * 组模型
 * @package app\system\model
 */
class SystemGroup extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'ctime';
}
<?php
namespace app\space\model;

use think\Db;
use think\Model;
use app\space\model\Ban as BanModel;
use app\space\model\Floor as FloorModel;

/**
 * 工位
 */
class Site extends Model
{
	// 设置模型名称
    protected $name = 'space_site';
    // 设置主键
    protected $pk = 'site_id';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i:s',
    ];
}
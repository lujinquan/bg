<?php
namespace app\space\model;

use think\Model;

class Floor extends Model
{
	// 设置模型名称
    protected $name = 'space_floor';
    // 设置主键
    protected $pk = 'floor_id';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i:s',
    ];

    public function ban()
    {
        return $this->belongsTo('ban', 'ban_id', 'ban_id')->bind('ban_name,ban_address');
    }

    
    public function checkWhere($data)
    {
        if(!$data){
            $data = request()->param();
        }  
        // 检索楼宇名称
        if(isset($data['ban_id']) && $data['ban_id']){
            $where[] = ['b.ban_id','eq',$data['ban_id']];
        }
        // 检索地址
        if(isset($data['floor_number']) && $data['floor_number']){
            $where[] = ['a.floor_number','eq',$data['floor_number']];
        }
        $where[] = ['a.status','eq',1];
        $where[] = ['b.project_id','eq',PROJECT_ID];

        return $where;
    }
    
}
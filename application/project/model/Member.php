<?php
namespace app\project\model;

use think\Model;

class Member extends Model
{
	// 设置模型名称
    protected $name = 'member';
    // 设置主键
    protected $pk = 'member_id';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i:s',
    ];

    public function MemberGroup()
    {
        return $this->belongsTo('member_group', 'group_id', 'group_id')->bind('group_name');
    }

    
    public function checkWhere($data)
    {

        if(!$data){
            $data = request()->param();
        }  
        // 检索楼宇名称
        if(isset($data['ban_name']) && $data['ban_name']){
            $where[] = ['ban_name','like','%'.$data['ban_name'].'%'];
        }
        // 检索地址
        if(isset($data['ban_address']) && $data['ban_address']){
            $where[] = ['ban_address','like','%'.$data['ban_address'].'%'];
        }
        $where[] = ['status','eq',1];

        return $where;
    }
    
}
<?php
namespace app\project\model;

use think\Model;

class Shack extends Model
{
	// 设置模型名称
    protected $name = 'project_shack';
    // 设置主键
    protected $pk = 'id';
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

    public function member()
    {
        return $this->belongsTo('member', 'member_id', 'member_id')->bind('member_name');
    }

    public function setShackTypeAttr($value)
    {
        if (empty($value)) return '';
        return '|'.implode('|',$value).'|';
    }

    public function SystemUser()
    {
        return $this->hasOne('app\system\model\SystemUser', 'id', 'cuid')->bind('nick,role_id');
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
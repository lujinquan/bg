<?php
namespace app\space\model;

use think\Model;

class Meeting extends Model
{
	// 设置模型名称
    protected $name = 'space_meeting';
    // 设置主键
    protected $pk = 'meet_id';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i:s',
    ];

    public function setFacilityIdsAttr($value)
    {
        if (empty($value)) return '';
        return '|'.implode('|',$value).'|';
    }

    public function setDoorIdsAttr($value)
    {
        if (empty($value)) return '';
        return '|'.implode('|',$value).'|';
    }

    public function setItemIdsAttr($value)
    {
        if (empty($value)) return '';
        return '|'.implode('|',$value).'|';
    }

    public function setFloorNumberAttr($value)
    {
        if (empty($value)) return '';
        return '|'.str_replace(',','|',$value).'|';
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
        // 检索会议室名称
        if(isset($data['meet_name']) && $data['meet_name']){
            $where[] = ['a.meet_name','like','%'.$data['meet_name'].'%'];
        }
        // 检索楼层
        if(isset($data['floor_number']) && $data['floor_number']){
            $where[] = ['a.floor_name','like','%|'.$data['floor_number'].'|%'];
        }
        $where[] = ['a.status','eq',1];

        return $where;
    }
    
}
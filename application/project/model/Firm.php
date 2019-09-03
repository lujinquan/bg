<?php
namespace app\project\model;

use think\Model;

class Firm extends Model
{
	// 设置模型名称
    protected $name = 'member_firm';
    // 设置主键
    protected $pk = 'firm_id';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i:s',
        'firm_established_time' => 'timestamp:Y-m-d',
        'firm_start_time' => 'timestamp:Y-m-d H:i:s',
        'firm_end_time' => 'timestamp:Y-m-d H:i:s',  
        'firm_coupon_issue_time' => 'timestamp:Y-m-d', 
        'firm_coupon_shou_use_start_time' => 'timestamp:Y-m-d', 
        'firm_coupon_shou_use_end_time' => 'timestamp:Y-m-d', 
    ];

    public function member()
    {
        return $this->hasMany('member', 'firm_id', 'firm_id')->bind('firm_name');
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
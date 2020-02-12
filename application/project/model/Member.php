<?php
namespace app\project\model;

use think\Model;
use app\project\model\Firm as FirmModel;

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
        'member_coupon_issue_time' => 'timestamp:Y-m-d', 
        'member_coupon_shou_use_start_time' => 'timestamp:Y-m-d', 
        'member_coupon_shou_use_end_time' => 'timestamp:Y-m-d', 
        'guard' => 'json',
    ];

    public function firm()
    {
        return $this->belongsTo('firm', 'firm_id', 'firm_id')->bind('firm_name');
    }

    
    public function checkWhere($data)
    {

        if(!$data){
            $data = request()->param();
        } 
        // 检索公司名称
        if(isset($data['firm_name']) && $data['firm_name']){
            $firmid = FirmModel::where([['firm_name','like','%'.$data['firm_name'].'%']])->value('firm_id');
            $where[] = ['firm_id','eq',$firmid];
        } 
        // 检索会员名称
        if(isset($data['member_name']) && $data['member_name']){
            $where[] = ['member_name','like','%'.$data['member_name'].'%'];
        }
        // 检索地址
        if(isset($data['ban_address']) && $data['ban_address']){
            $where[] = ['ban_address','like','%'.$data['ban_address'].'%'];
        }
        if(isset($data['group'])){
            if($data['group'] == 'y'){
                $where[] = ['status','eq',1];
            }else{
                $where[] = ['status','eq',0];
            }
        }else{

        }
        
        

        return $where;
    }
    
}
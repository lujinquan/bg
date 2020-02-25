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
        'shack_start_time' => 'timestamp:Y-m-d',
        'shack_end_time' => 'timestamp:Y-m-d',
        'ctime' => 'timestamp:Y-m-d H:i:s',
        'guard' =>  'json',
        //'site_group' =>  'json',
    ];

    public function firm()
    {
        return $this->belongsTo('firm', 'firm_id', 'firm_id')->bind('firm_name,firm_tel,firm_manager,firm_credit_code,firm_industry_type,firm_registered_capital,firm_registered_address,firm_legaler,firm_established_time,firm_scope,firm_start_time,firm_end_time,firm_remark,firm_imgs,firm_coupon_num,firm_coupon_shou_num,firm_coupon_shou_use_start_time,firm_coupon_shou_use_end_time,firm_coupon_issue_time');
    }

    public function member()
    {
        return $this->belongsTo('member', 'member_id', 'member_id')->bind('member_name,member_tel,member_sex,member_card,member_remark,member_coupon_num,member_coupon_shou_num,member_coupon_shou_use_start_time,member_coupon_shou_use_end_time,member_coupon_issue_time');
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
        //检索入驻类型
        if(isset($data['shack_type']) && $data['shack_type']){
            $where[] = ['a.shack_type','like','%|'.$data['shack_type'].'|%'];
        }
        // 检索地址
        // if(isset($data['ban_address']) && $data['ban_address']){
        //     $where[] = ['ban_address','like','%'.$data['ban_address'].'%'];
        // }
        //$where[] = ['shack_status','eq',1];
        
        // 检索发布时间
        if(isset($data['ctime']) && $data['ctime']){
            $start = strtotime($data['ctime']);
            $end = strtotime($data['ctime'])+3600*24;
            $where[] = ['a.ctime','between',[$start,$end]];
        }
        if(isset($data['group'])){
            if($data['group'] == 'y'){
                $where[] = ['a.shack_status','eq',1];
            }else{
                $where[] = ['a.shack_status','eq',0];
            }

        }else{
            $where[] = ['a.shack_status','eq',1];
        }
        

        return $where;
    }
    
}
<?php
namespace app\space\model;

use think\Model;

class SiteGroup extends Model
{
	// 设置模型名称
    protected $name = 'space_site_group';
    // 设置主键
    protected $pk = 'site_group_id';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i:s',
    ];

    public function setSitesAttr($value)
    {
        if (!$value[0]) return '';
        return '|'.implode('|',$value).'|';
    }

    public function setFloorNumberAttr($value)
    {
        if (!$value) return '';
        return '|'.str_replace(',','|',$value).'|';
    }
    
    public function checkWhere($data)
    {

        if(!$data){
            $data = request()->param();
        }
        // 检索工位区名称
        if(isset($data['site_group_name']) && $data['site_group_name']){
            $where[] = ['site_group_name','like','%'.$data['site_group_name'].'%'];
        }  
        // 检索楼宇名称
        if(isset($data['ban_id']) && $data['ban_id']){
            $where[] = ['a.ban_id','eq',$data['ban_id']];
        }
        // 检索类型
        if(isset($data['site_group_type']) && $data['site_group_type']){
            $where[] = ['site_group_type','eq',$data['site_group_type']];
        }
        // 检索楼层
        if(isset($data['floor_number']) && $data['floor_number']){
            $where[] = ['floor_number','like','%|'.$data['floor_number'].'|%'];
        }
        $where[] = ['a.status','eq',1];
        $where[] = ['b.project_id','eq',PROJECT_ID];

        return $where;
    }
    
}
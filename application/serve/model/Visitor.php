<?php

namespace app\serve\model;

use think\Model;
use app\project\model\member;
use app\system\model\SystemUser as UserModel;



/**
 * 访客信息管理模型
 * @package app\system\model
 */
class Visitor extends Model
{
	// 设置模型名称
    protected $name = 'serve_visitor';
    // 设置主键
    protected $pk = 'visitor_id';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'visitor_ctime';

    protected $type = [
        'visitor_time' => 'timestamp:Y-m-d H:i',
        'sign_time' => 'timestamp:Y-m-d H:i',
        'visitor_ctime' => 'timestamp:Y-m-d H:i',
        'visitor_etime' => 'timestamp:Y-m-d H:i',
        'visitor_dtime' => 'timestamp:Y-m-d H:i',
    ];

    public function member()
    {
        return $this->hasOne('app\project\model\Member', 'member_id', 'member_id')->bind('member_name');
    }

    public function checkWhere($data)
    {
        if(!$data){
            $data = request()->param();
        }
        $where = [];
        $where[] = ['project_id','eq',PROJECT_ID];
        // 检索访客姓名
        if(isset($data['visitor_name']) && $data['visitor_name']){
            $where[] = ['visitor_name','like','%'.$data['visitor_name'].'%'];
        }
        // 检索访客类型
        if(isset($data['visitor_type']) && $data['visitor_type']){
            $where[] = ['visitor_type','eq',$data['visitor_type']];
        }
        // 检索访客目的
        if(isset($data['visitor_purpose_type']) && $data['visitor_purpose_type']){
            $where[] = ['visitor_purpose_type','eq',$data['visitor_purpose_type']];
        }
        // 检索发布时间
        if(isset($data['visitor_time']) && $data['visitor_time']){
            $start = strtotime($data['visitor_time']);
            $end = strtotime($data['visitor_time'])+3600*24;
            $where[] = ['visitor_time','between',[$start,$end]];
        }
        return $where;
    }

    public function updateReads($id)
    {
        $row = $this->get($id);
        $reads = [];
        $i = true;
        if($row['reads']){
            $reads = $row['reads'];
            foreach($reads as $r){
                if($r['uid'] == session('admin_user.uid')){
                    $i = false;
                    break;
                }
            } 
        }
        if($i){
            $tempArr = [
                'uid' => session('admin_user.uid'),
                'time' => time()
            ];
            array_unshift($reads,$tempArr);
            //dump($id);halt($reads);
            $re = self::where([['id','eq',$id]])->update(['reads'=>json_encode($reads)]);
            if($re){
                return '阅读记录更新成功！';
            }else{
                return '阅读记录更新失败！';
            }
        }
        return '阅读记录已存在！';
    }
}

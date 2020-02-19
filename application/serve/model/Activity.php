<?php

namespace app\serve\model;

use think\Model;
use app\project\model\member;
use app\system\model\SystemUser as UserModel;



/**
 * 空间活动模型
 * @package app\system\model
 */
class Activity extends Model
{
	// 设置模型名称
    protected $name = 'serve_activity';
    // 设置主键
    protected $pk = 'activity_id';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'activity_ctime';

    protected $type = [
        'activity_start_time' => 'timestamp:Y-m-d H:i',
        'activity_end_time' => 'timestamp:Y-m-d H:i',
        'activity_ctime' => 'timestamp:Y-m-d H:i',
        'activity_etime' => 'timestamp:Y-m-d H:i',
        'activity_dtime' => 'timestamp:Y-m-d H:i',
    ];

    public function getActivityCuidAttr($value){
        //halt(session('systemusers')[$value]);
        return session('systemusers')?session('systemusers')[$value]['nick']:$value;
    }

    public function setActivitySponsorAttr($value)
    {
        if (empty($value)) return '';
        return '|'.implode('|',$value).'|';
    }
    public function getActivitySponsorAttr($value)
    {
        if (empty($value)) return '';
        return explode('|',trim($value,'|'));
    }

    public function setActivityParticipantsAttr($value)
    {
        if (empty($value)) return '';
        return '|'.implode('|',$value).'|';
    }
    public function getActivityParticipantsAttr($value)
    {
        if (empty($value)) return '';
        return explode('|',trim($value,'|'));
    }

    public function checkWhere($data)
    {
        if(!$data){
            $data = request()->param();
        }
        $where = [];
        $where[] = ['activity_dtime','eq',0];
        $where[] = ['project_id','eq',PROJECT_ID];
        // 检索活动名称
        if(isset($data['activity_title']) && $data['activity_title']){
            $where[] = ['activity_title','like','%'.$data['activity_title'].'%'];
        }
        // 检索报修地点
        if(isset($data['activity_address']) && $data['activity_address']){
            $where[] = ['activity_address','like','%'.$data['activity_address'].'%'];
        }
        // 检索报修类型
        if(isset($data['activity_type']) && $data['activity_type']){
            $where[] = ['activity_type','eq',$data['activity_type']];
        }
        // 检索报修状态
        // if(isset($data['repair_status']) && $data['repair_status']){
        //     $where[] = ['repair_status','eq',$data['repair_status']];
        // }
        // 检索保修提交人
        // if(isset($data['cuid']) && $data['cuid']){
        // 	$cuid = UserModel::where([['nick','like','%'.$data['cuid'].'%']])->value('id');
	       //  if($cuid){
	       //      $where[] = ['cuid','eq',$cuid]; 
	       //  }else{
	       //      $where[] = ['cuid','eq',0];
	       //  } 
        // }
        // 检索发布时间
        if(isset($data['activity_start_time']) && $data['activity_start_time']){
            $start = strtotime($data['activity_start_time']);
            $end = strtotime($data['activity_start_time'])+3600*24;
            $where[] = ['activity_start_time','between',[$start,$end]];
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

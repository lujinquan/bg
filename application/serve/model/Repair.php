<?php

namespace app\serve\model;

use think\Model;
use app\project\model\member;
use app\system\model\SystemUser as UserModel;



/**
 * 报事报修模型
 * @package app\system\model
 */
class Repair extends Model
{
	// 设置模型名称
    protected $name = 'serve_repair';
    // 设置主键
    protected $pk = 'repair_id';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'ctime';

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i',
        'etime' => 'timestamp:Y-m-d H:i',
        'dtime' => 'timestamp:Y-m-d H:i',
        'wtime' => 'timestamp:Y-m-d H:i',
        'repair_json' =>  'json',
    ];

    public function getToUidAttr($value){
        return session('systemusers')?session('systemusers')[$value]['nick']:$value;
    }

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
        $where[] = ['dtime','eq',0];
        $where[] = ['to_uid','eq',ADMIN_ID];
        $where[] = ['project_id','eq',PROJECT_ID];
        // 检索报修单编号
        if(isset($data['repair_number']) && $data['repair_number']){
            $where[] = ['repair_number','like','%'.$data['repair_number'].'%'];
        }
        // 检索报修内容关键字
        if(isset($data['repair_content']) && $data['repair_content']){
            $where[] = ['repair_content','like','%'.$data['repair_content'].'%'];
        }
        // 检索报修类型
        if(isset($data['repair_type']) && $data['repair_type']){
            $where[] = ['repair_type','eq',$data['repair_type']];
        }
        // 检索报修状态
        if(isset($data['repair_status']) && $data['repair_status']){
            $where[] = ['repair_status','eq',$data['repair_status']];
        }
        // 检索保修提交人
        if(isset($data['cuid']) && $data['cuid']){
        	$cuid = UserModel::where([['nick','like','%'.$data['cuid'].'%']])->value('id');
	        if($cuid){
	            $where[] = ['cuid','eq',$cuid]; 
	        }else{
	            $where[] = ['cuid','eq',0];
	        } 
        }
        // 检索发布时间
        if(isset($data['ctime']) && $data['ctime']){
            $start = strtotime($data['ctime']);
            $end = strtotime($data['ctime'])+3600*24;
            $where[] = ['ctime','between',[$start,$end]];
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

<?php

namespace app\serve\model;

use think\Model;
use app\system\model\SystemUser as UserModel;


/**
 * 站内消息模型
 * @package app\system\model
 */
class Chat extends Model
{
	// 设置模型名称
    protected $name = 'serve_chat';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'ctime';

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i',
        'etime' => 'timestamp:Y-m-d H:i',
        'dtime' => 'timestamp:Y-m-d H:i',
    ];



    public function getCuidAttr($value){
        //halt(session('systemusers')[$value]);
        return session('systemusers')?session('systemusers')[$value]['nick']:$value;
    }

    public function checkWhere($data)
    {
        if(!$data){
            $data = request()->param();
        }
        $where = [];
        $where[] = ['dtime','eq',0];
        $where[] = ['project_id','eq',PROJECT_ID];
        // 检索公告标题
        if(isset($data['title']) && $data['title']){
            $where[] = ['title','like','%'.$data['title'].'%'];
        }
        // 检索公告发布人
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

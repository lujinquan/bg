<?php

namespace app\serve\model;

use think\Model;

/**
 * 系统公告模型
 * @package app\system\model
 */
class Notice extends Model
{
	// 设置模型名称
    protected $name = 'system_notice';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i',
        'etime' => 'timestamp:Y-m-d H:i',
        'dtime' => 'timestamp:Y-m-d H:i',
        'reads' => 'json'
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
        // 检索公告标题
        if(isset($data['title']) && $data['title']){
            $where[] = ['title','like','%'.$data['title'].'%'];
        }
        // 检索公告类型
        if(isset($data['type']) && $data['type']){
            $where[] = ['type','eq',$data['type']];
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

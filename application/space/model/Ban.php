<?php
namespace app\space\model;

use think\Model;

class Ban extends Model
{
	// 设置模型名称
    protected $name = 'space_ban';
    // 设置主键
    protected $pk = 'ban_id';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i:s',
    ];

    public function floor()
    {
        return $this->hasMany('floor', 'ban_id', 'ban_id')->field('floor_id,floor_number');
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
        $where[] = ['project_id','eq',PROJECT_ID];

        return $where;
    }

    /**
     * [获取楼宇楼层联动数据]
     */
    public function banFloors()
    {
        $bans = self::where([['project_id','eq',PROJECT_ID],['status','eq',1]])->select();
        $data = [];
        foreach ($bans as $k => $v) {
            $s = [
                'id' => $v->ban_id,
                'name' => $v->ban_name,  
            ];
            $s['children'] = $v->floor()->where([['status','eq',1]])->field('floor_number as id,floor_number as name')->select()->toArray(); //直接用楼层号当做id
            $data[] = $s;
        }
        //halt($data);
        return $data;
    }


    
}
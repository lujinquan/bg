<?php
namespace app\space\model;

use think\Db;
use think\Model;
use app\space\model\Ban as BanModel;
use app\space\model\Floor as FloorModel;

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

    /**
     * [getList 工位区状态主页]
     * @param  int $site_group_type [工位区的类型：1联合，2独立，3自由]
     * @param  integer $type [description]
     * @return [type]        [description]
     */
    public function getList($site_group_type = 1)
    {
        $result = [];
        $nowDayDate = date('Y-m-d',1577232000);
        $nextDayDate = date('Y-m-d',(strtotime($nowDayDate)+3600*24));
        // 获取当前项目下的所有有效楼宇数据
        $banArr = BanModel::where([['project_id','eq',PROJECT_ID],['status','eq',1]])->field('ban_id,ban_name,ban_address')->order('ctime asc')->select()->toArray();
        // 获取所有工位
        $sites = Db::name('space_site')->column('site_id,site_name');
        //halt($banArr);
        foreach($banArr as $b){
            $floorNumberArr = FloorModel::where([['status','eq',1],['ban_id','eq',$b['ban_id']]])->field('floor_number')->order('floor_number asc')->select()->toArray();

            if($site_group_type == 1){ // 联合工位区
                
                foreach($floorNumberArr as $f){

                    $lists = self::where([['ban_id','eq',$b['ban_id']],['site_group_type','eq',$site_group_type],['floor_number','eq',$f['floor_number']],['status','eq',1]])->select()->toArray();

                    if(!$lists){
                        continue;
                    }
                    
                    $result[$b['ban_id']]['baseinfo'] = [
                        'ban_name' => $b['ban_name'],
                    ];

                    $k = 0;
                    foreach($lists as $i => $l){
                        //halt($l);
                        if($l['sites'] != '||'){
                            $temp_sites = explode('|',trim($l['sites'],'|'));
                            //halt($temp_sites);
                            foreach($temp_sites as $e => $t){
                                $result[$b['ban_id']]['list'][$f['floor_number']][$l['site_group_id']]['list'][$e] = [
                                    'site_name' => $sites[$t],
                                    'site_status' => '已租',
                                    'site_info' => '测试的公司',
                                ];
                            }
                        }
                        $result[$b['ban_id']]['list'][$f['floor_number']][$l['site_group_id']]['baseinfo'] = [
                            'use_site_sum' => 100,                           
                            'all_site_sum' => 1000,
                            'site_group_name' => $l['site_group_name'],
                        ];   
                    }
                }
            }

   
        }
 
        return $result;
    }
    
}
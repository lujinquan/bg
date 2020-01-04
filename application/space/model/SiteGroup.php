<?php
namespace app\space\model;

use think\Db;
use think\Model;
use app\space\model\Ban as BanModel;
use app\space\model\Floor as FloorModel;
use app\space\model\Site as SiteModel;
use app\project\model\Shack as ShackModel;

/**
 * 工位区
 */
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

    // public function setSitesAttr($value)
    // {
    //     if (!$value[0]) return '';
    //     return '|'.implode('|',$value).'|';
    // }

    // public function setFloorNumberAttr($value)
    // {
    //     if (!$value) return '';
    //     return '|'.str_replace(',','|',$value).'|';
    // }
    
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
    public function getList($ban_name = '',$floor_number = '',$meet_name = '',$date = '',$site_group_type = 1)
    {
        $result = [];
        if($date){
            $nowDayDate = $date;
        }else{
            $nowDayDate = date('Y-m-d');
        }
        //$nowDayDate = date('Y-m-d',1577232000);
        $nextDayDate = date('Y-m-d',(strtotime($nowDayDate)+3600*24));

        $shackWhere = [];
        $shackWhere[] = ['a.shack_type','not like','%2%']; // 非退租状态
        $shackWhere[] = ['a.shack_start_time','<=',strtotime($nowDayDate)]; // 非退租状态
        $shackWhere[] = ['a.shack_end_time','>=',strtotime($nextDayDate)]; // 非退租状态

        $fields = 'b.firm_name,b.firm_coupon_num,a.sites,c.member_name,from_unixtime(a.shack_start_time,\'%Y-%m-%d\') shack_start_time,from_unixtime(a.shack_end_time,\'%Y-%m-%d\') shack_end_time,a.shack_status,a.shack_type,a.id,from_unixtime(a.ctime) ctime';

        $shackArr = Db::name('project_shack')->alias('a')->join('member_firm b','a.firm_id = b.firm_id','left')->join('member c','a.member_id = c.member_id','left')->field($fields)->select();

        //halt($shackArr);
        $sites = [];
        foreach($shackArr as $s){
            $temps = explode('|',trim($s['sites'],'|'));
            foreach($temps as $t){
                $sites[$t] = $s;
            }
        }
        //SiteModel::where([['','',]]);
        //halt($sites);

        $banWhere = [];
        $banWhere[] = ['project_id','eq',PROJECT_ID];
        $banWhere[] = ['status','eq',1];
        if($ban_name){
            $banWhere[] = ['ban_name','like','%'.$ban_name.'%'];
        }
        // 获取当前项目下的所有有效楼宇数据
        $banArr = BanModel::where($banWhere)->field('ban_id,ban_name,ban_address')->order('ctime asc')->select()->toArray();
        //halt($banArr);
        // 遍历每栋楼下的楼层
        foreach($banArr as $b){
            $floorWhere = [];
            $floorWhere[] = ['status','eq',1];
            $floorWhere[] = ['ban_id','eq',$b['ban_id']];
            if($floor_number){
                $floorWhere[] = ['floor_number','eq',$floor_number];
            }
            $floorNumberArr = FloorModel::where($floorWhere)->field('floor_number')->order('floor_number asc')->select()->toArray();

            // 联合工位区
            if($site_group_type == 1){ 
                // 遍历每层楼
                foreach($floorNumberArr as $f){ // $f是楼层
                    $sitegroupWhere = [];
                    $sitegroupWhere[] = ['status','eq',1];
                    $sitegroupWhere[] = ['ban_id','eq',$b['ban_id']];
                    $sitegroupWhere[] = ['site_group_type','eq',$site_group_type];
                    // if($floor_number){
                    //     $sitegroupWhere[] = ['floor_number','eq',$floor_number];
                    // }else{
                        $sitegroupWhere[] = ['floor_number','eq',$f['floor_number']];
                    //}
                    $lists = self::where($sitegroupWhere)->select()->toArray();
                    // 如果该楼层没有工位区
                    if(!$lists){
                        continue;
                    }     
                    $k = 0;
                    // 遍历该楼层下的所有工位区
                    foreach($lists as $i => $l){

                        // 获取当前工位区下的所有工位
                        $sitsArr = SiteModel::where([['site_group_id','eq',$l['site_group_id']]])->select()->toArray();

                        if($sitsArr){
                            //halt($sitsArr);
                            // 遍历当前工位区下的所有工位
                            foreach($sitsArr as $e => $t){
                                // 待优化
                                $result[$b['ban_id']]['list'][$f['floor_number']][$l['site_group_id']]['list'][$e] = [
                                    'site_name' => $t['site_name'],
                                    //'site_status' => '已租',
                                    'site_info' => isset($sites[$t['site_id']])?$sites[$t['site_id']]:'',
                                ];
                            }
                        }else{
                            continue;  
                        }

                        $result[$b['ban_id']]['baseinfo'] = [
                            'ban_name' => $b['ban_name'],
                        ];
                        $result[$b['ban_id']]['list'][$f['floor_number']][$l['site_group_id']]['baseinfo'] = [
                            'use_site_sum' => 100,                           
                            'all_site_sum' => 1000,
                            'site_group_name' => $l['site_group_name'],
                        ];   
                    }
                }

            // 独立工位区
            }elseif($site_group_type == 2){
                // 遍历每层楼
                foreach($floorNumberArr as $f){ // $f是楼层
                    $sitegroupWhere = [];
                    $sitegroupWhere[] = ['status','eq',1];
                    $sitegroupWhere[] = ['ban_id','eq',$b['ban_id']];
                    $sitegroupWhere[] = ['site_group_type','eq',$site_group_type];
                    // if($floor_number){
                    //     $sitegroupWhere[] = ['floor_number','eq',$floor_number];
                    // }else{
                        $sitegroupWhere[] = ['floor_number','eq',$f['floor_number']];
                    //}
                    $lists = self::where($sitegroupWhere)->select()->toArray();

                    // 如果该楼层没有工位区
                    if(!$lists){
                        continue;
                    }  
                    halt($lists);   
                    $k = 0;
                    // // 遍历该楼层下的所有工位区
                    // foreach($lists as $i => $l){

                    //     // 获取当前工位区下的所有工位
                    //     $sitsArr = SiteModel::where([['site_group_id','eq',$l['site_group_id']]])->select()->toArray();

                    //     if($sitsArr){
                    //         //halt($sitsArr);
                    //         // 遍历当前工位区下的所有工位
                    //         foreach($sitsArr as $e => $t){
                    //             // 待优化
                    //             $result[$b['ban_id']]['list'][$f['floor_number']][$l['site_group_id']]['list'][$e] = [
                    //                 'site_name' => $t['site_name'],
                    //                 //'site_status' => '已租',
                    //                 'site_info' => isset($sites[$t['site_id']])?$sites[$t['site_id']]:'',
                    //             ];
                    //         }
                    //     }else{
                    //         continue;  
                    //     }

                    //     $result[$b['ban_id']]['baseinfo'] = [
                    //         'ban_name' => $b['ban_name'],
                    //     ];
                    //     $result[$b['ban_id']]['list'][$f['floor_number']][$l['site_group_id']]['baseinfo'] = [
                    //         'use_site_sum' => 100,                           
                    //         'all_site_sum' => 1000,
                    //         'site_group_name' => $l['site_group_name'],
                    //     ];   
                    //}
                }
            }


   
        }
//halt($result);
        return $result;
    }
    
}
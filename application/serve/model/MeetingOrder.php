<?php
namespace app\serve\model;

use think\Model;
use app\space\model\Ban as BanModel;
use app\space\model\Floor as FloorModel;

class MeetingOrder extends Model
{
	// 设置模型名称
    protected $name = 'space_meeting_order';
    // 设置主键
    protected $pk = 'meet_order_id';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // protected $type = [
    //     'shack_start_time' => 'timestamp:Y-m-d',
    //     'shack_end_time' => 'timestamp:Y-m-d',
    //     'ctime' => 'timestamp:Y-m-d H:i:s',
    //     'guard' =>  'json',
    //     'site_group' =>  'json',
    // ];

    // public function firm()
    // {
    //     return $this->belongsTo('firm', 'firm_id', 'firm_id')->bind('firm_name,firm_tel,firm_manager,firm_credit_code,firm_industry_type,firm_registered_capital,firm_registered_address,firm_legaler,firm_established_time,firm_scope,firm_start_time,firm_end_time,firm_remark,firm_imgs,firm_coupon_num,firm_coupon_shou_num,firm_coupon_shou_use_start_time,firm_coupon_shou_use_end_time,firm_coupon_issue_time');
    // }

    // public function member()
    // {
    //     return $this->belongsTo('member', 'member_id', 'member_id')->bind('member_name,member_tel,member_sex,member_card,member_remark,member_coupon_num,member_coupon_shou_num,member_coupon_shou_use_start_time,member_coupon_shou_use_end_time,member_coupon_issue_time');
    // }

    // public function setShackTypeAttr($value)
    // {
    //     if (empty($value)) return '';
    //     return '|'.implode('|',$value).'|';
    // }

    // public function SystemUser()
    // {
    //     return $this->hasOne('app\system\model\SystemUser', 'id', 'cuid')->bind('nick,role_id');
    // }
    
    public function checkWhere($data)
    {
        if(!$data){
            $data = request()->param();
        }  
        $where[] = ['e.project_id','eq',PROJECT_ID];
        // 检索会议室名称
        if(isset($data['meet_name']) && $data['meet_name']){
            $where[] = ['b.meet_name','like','%'.$data['meet_name'].'%'];
        }
        // 检索公司名称
        if(isset($data['firm_name']) && $data['firm_name']){
            $where[] = ['d.firm_name','like','%'.$data['firm_name'].'%'];
        }
        // 检索预定日期
        if(isset($data['meet_start_time']) && $data['meet_start_time']){
        	$endTime = date('Y-m-d',strtotime('+1 day',strtotime($data['meet_start_time'])));
            $where[] = ['a.meet_start_time','between time',[$data['meet_start_time'],$endTime]];
        }
        // 检索会议室状态
        if(isset($data['meet_order_status']) && $data['meet_order_status']){
            $where[] = ['a.meet_order_status','eq',$data['meet_order_status']];
        }else{
        	$where[] = ['a.meet_order_status','neq',3];
        }
        return $where;
    }
    
    public function getList($ban_name = '',$floor_number = '',$meet_name = '',$date = '')
    {
    	
    	$result = [];
        if($date){
            $nowDayDate = $date;
        }else{
            $nowDayDate = date('Y-m-d');
        }
    	$nextDayDate = date('Y-m-d',(strtotime($nowDayDate)+3600*24));

        //最近7天（当前时间的最近7天，不随搜索时间变化）
        $before_seven_time = strtotime(date('Y-m-d'))-6*3600*24;
        //最近30天（当前时间的最近30天，不随搜索时间变化）
        $before_thirty_time = strtotime(date('Y-m-d'))-29*3600*24;
    	// 获取当前项目下的所有有效楼宇数据
        $banWhere = [];
        $banWhere[] = ['project_id','eq',PROJECT_ID];
        $banWhere[] = ['status','eq',1];
        if($ban_name){
            $banWhere[] = ['ban_name','like','%'.$ban_name.'%'];
        }
    	$banArr = BanModel::where($banWhere)->field('ban_id,ban_name,ban_address')->order('ctime asc')->select()->toArray();
        
		//halt($banArr);
    	foreach($banArr as $b){
            $floorWhere = [];
            $floorWhere[] = ['status','eq',1];
            $floorWhere[] = ['ban_id','eq',$b['ban_id']];
            if($floor_number){
                $floorWhere[] = ['floor_number','eq',$floor_number];
            }
    		$floorNumberArr = FloorModel::where($floorWhere)->field('floor_number')->order('floor_number asc')->select()->toArray();

    		//halt($floorNumberArr);
    		foreach($floorNumberArr as $f){
                $listsWhere = [];
                $listsWhere[] = ['a.meet_start_time','between time',[$nowDayDate,$nextDayDate]];
                $listsWhere[] = ['b.floor_number','eq',$f['floor_number']];
                $listsWhere[] = ['b.ban_id','eq',$b['ban_id']]; 
                if($meet_name){
                    $listsWhere[] = ['b.meet_name','like','%'.$meet_name.'%'];
                }
    			$fields = 'a.meet_order_id,a.meet_id,a.member_id,a.firm_id,unix_timestamp(a.order_start_time) as order_start_time,unix_timestamp(a.order_end_time) as order_end_time,unix_timestamp(a.meet_start_time) as meet_start_time,unix_timestamp(a.meet_end_time) as meet_end_time,a.meet_order_status,a.ctime,a.cancel_time,b.meet_name,b.ban_id,b.floor_number,c.member_name,c.member_tel,d.firm_name';
    			$lists = self::alias('a')->where($listsWhere)->join('space_meeting b','a.meet_id = b.meet_id','left')->join('member c','a.member_id = c.member_id','left')->join('member_firm d','a.firm_id = d.firm_id','left')->field($fields)->select()->toArray();

    			if(!$lists){
    				continue;
    			}
                //halt($lists);
    			$result[$b['ban_id']]['baseinfo'] = [
					'ban_name' => $b['ban_name'],
				];
    			$t = $lists[0]['meet_end_time'] - $lists[0]['meet_start_time'];
		    	$s = $t/(60*15);

		    	$k = 0;
                //dump($lists[0]['meet_start_time']);dump($lists[0]['meet_end_time']);
		    	while ($lists[0]['meet_start_time'] < $lists[0]['meet_end_time']) {
		    		$k++;
		    		$tmp_meet_start_time = $lists[0]['meet_start_time'];
		    		$lists[0]['meet_start_time'] += 60*15;
                    
                    //$s = false; // 定义一个标识，一旦出现已使用或已预约则，改变标识状态
		    		foreach($lists as $i => $l){
                        // if($tmp_meet_start_time == 1577235600){ // 调试：如果是9点，判断
                        //     dump(date('Y-m-d H:i:s',$l['order_start_time']));dump(date('Y-m-d H:i:s',$tmp_meet_start_time));dump(date('Y-m-d H:i:s',$l['order_end_time']));dump(date('Y-m-d H:i:s',$lists[0]['meet_start_time']));halt($l);
                        // }
                        if(!isset($result[$b['ban_id']]['list'][$f['floor_number']][$l['meet_id']]['list'][$k]['flag'])){
                            $result[$b['ban_id']]['list'][$f['floor_number']][$l['meet_id']]['list'][$k] = [
                                'member_name' => $l['member_name'],
                                'member_tel' => $l['member_tel'],
                                'firm_name' => $l['firm_name'],
                                'start_time' => date('H:i',$tmp_meet_start_time),
                                'end_time' => date('H:i',$lists[0]['meet_start_time']),
                                'meet_order_status' => 3, 
                                //'flag' => false,
                            ];
                        }

                        $result[$b['ban_id']]['list'][$f['floor_number']][$l['meet_id']]['baseinfo'] = [
                            'ban_id' => $l['ban_id'],                           
                            'meet_id' => $l['meet_id'],
                            'ban_name' => $b['ban_name'],
                            'meet_name' => $l['meet_name'],
                            'floor_number' => $l['floor_number'],
                            // 'seven_total_use_time' => 200,
                            // 'thirty_total_use_time' => 300,
                        ];

                        // 预约起始时间 <= 每一个小段的开始时间 ，且预约结束时间 >= 每一个小段的结束时间
			     		if(($l['order_start_time'] <= $tmp_meet_start_time) && ($l['order_end_time'] >= $lists[0]['meet_start_time'])){
			     			$result[$b['ban_id']]['list'][$f['floor_number']][$l['meet_id']]['list'][$k] = [
			     				'member_name' => $l['member_name'],
								'member_tel' => $l['member_tel'],
								'firm_name' => $l['firm_name'],		
			    				'start_time' => date('H:i',$tmp_meet_start_time),
			    				'end_time' => date('H:i',$lists[0]['meet_start_time']),
			    				'meet_order_status' => $l['meet_order_status'], 
                                'flag' => true,
			    			];
                            continue;
                            //$s = true;
			     		}
			     		 
			     	}

		    	}

                foreach ($result[$b['ban_id']]['list'][$f['floor_number']] as $key => &$value) { //统计最近7天的使用时长
                    $sevenTotalTimes = self::alias('a')->where([['meet_id','eq',$key],['meet_order_status','eq',2],['ctime','>=',$before_seven_time]])->field('meet_id,unix_timestamp(order_start_time) as order_start_time,unix_timestamp(order_end_time) as order_end_time')->select()->toArray();
                    $value['baseinfo']['seven_total_use_time'] = 0;
                    $value['baseinfo']['thirty_total_use_time'] = 0;
                    
                    foreach($sevenTotalTimes as $t){
                        $value['baseinfo']['seven_total_use_time'] += $t['order_end_time'] - $t['order_start_time'];
                    }
                    $value['baseinfo']['seven_total_use_time'] = $value['baseinfo']['seven_total_use_time'] / 3600;

                    $thirtyTotalTimes = self::alias('a')->where([['meet_id','eq',$key],['meet_order_status','eq',2],['ctime','>=',$before_thirty_time]])->field('meet_id,unix_timestamp(order_start_time) as order_start_time,unix_timestamp(order_end_time) as order_end_time')->select()->toArray();
                    foreach($thirtyTotalTimes as $t){
                        $value['baseinfo']['thirty_total_use_time'] += $t['order_end_time'] - $t['order_start_time'];
                    }
                    $value['baseinfo']['thirty_total_use_time'] = $value['baseinfo']['thirty_total_use_time'] / 3600;
                }
		    }	
    	}
//exit;
    	//halt($result);
    	return $result;
    }
    
}
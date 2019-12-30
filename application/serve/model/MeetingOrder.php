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
    
    public function getList($ban_id = '',$floor_number = '',$date = '')
    {
    	
    	$result = [];
    	$nowDayDate = date('Y-m-d',1577232000);
    	$nextDayDate = date('Y-m-d',(strtotime($nowDayDate)+3600*24));
    	// 获取当前项目下的所有有效楼宇数据
    	$banArr = BanModel::where([['project_id','eq',PROJECT_ID],['status','eq',1]])->field('ban_id,ban_name,ban_address')->order('ctime asc')->select()->toArray();
		//halt($banArr);
    	foreach($banArr as $b){
    		$floorNumberArr = FloorModel::where([['status','eq',1],['ban_id','eq',$b['ban_id']]])->field('floor_number')->order('floor_number asc')->select()->toArray();

    		
    		//halt($floorNumberArr);
    		foreach($floorNumberArr as $f){
    			$fields = 'a.*,b.meet_name,b.ban_id,b.floor_number,c.member_name,c.member_tel,d.firm_name';
    			$lists = self::alias('a')->where([['a.meet_start_time','between time',[$nowDayDate,$nextDayDate]],['b.ban_id','eq',$b['ban_id']],['b.floor_number','eq',$f['floor_number']]])->join('space_meeting b','a.meet_id = b.meet_id','left')->join('member c','a.member_id = c.member_id','left')->join('member_firm d','a.firm_id = d.firm_id','left')->field($fields)->select()->toArray();
    			if(!$lists){
    				continue;
    			}
    			
    			$result[$b['ban_id']]['baseinfo'] = [
					'ban_name' => $b['ban_name'],
				];
    			//halt($lists);
    			$t = $lists[0]['meet_end_time'] - $lists[0]['meet_start_time'];
		    	$s = $t/(60*15);

		    	$k = 0;
		    	while ($lists[0]['meet_start_time'] < $lists[0]['meet_end_time']) {
		    		$k++;
		    		$tmp_meet_start_time = $lists[0]['meet_start_time'];
		    		$lists[0]['meet_start_time'] += 60*15;
		    		// echo '<pre>';
		    		// dump($l['meet_start_time']);
		    		//$result[$b['ban_id']][$f['floor_number']]['list'][]
		    		foreach($lists as $i => $l){

			     		if(($l['order_start_time'] >= $tmp_meet_start_time) && ($l['order_end_time'] > $lists[0]['meet_start_time'])){
			     			$result[$b['ban_id']]['list'][$f['floor_number']][$l['meet_id']]['list'][$k] = [
			     				'member_name' => $l['member_name'],
								'member_tel' => $l['member_tel'],
								'firm_name' => $l['firm_name'],		
			    				'start_time' => date('H:i',$tmp_meet_start_time),
			    				'end_time' => date('H:i',$lists[0]['meet_start_time']),
			    				'meet_order_status' => $l['meet_order_status'], 
			    			];
			     		}else{
			     			$result[$b['ban_id']]['list'][$f['floor_number']][$l['meet_id']]['list'][$k] = [
			     				'member_name' => $l['member_name'],
								'member_tel' => $l['member_tel'],
								'firm_name' => $l['firm_name'],
			    				'start_time' => date('H:i',$tmp_meet_start_time),
			    				'end_time' => date('H:i',$lists[0]['meet_start_time']),
			    				'meet_order_status' => 3, 
			    			];
			     		}
			     		$result[$b['ban_id']]['list'][$f['floor_number']][$l['meet_id']]['baseinfo'] = [
							'ban_id' => $l['ban_id'],							
							'meet_id' => $l['meet_id'],
							'ban_name' => $b['ban_name'],
							'meet_name' => $l['meet_name'],
							'floor_number' => $l['floor_number'],
						];
			     	}

		    	}
		    }	
    	}

    	// 		foreach($lists as $i => $l){
		   //  		//halt($l);
		    		
		   //  		$k = 0;
		   //  		while ($l['meet_start_time'] < $l['meet_end_time']) {
		   //  			$k++;
		   //  			$tmp_meet_start_time = $l['meet_start_time'];
		   //  			$l['meet_start_time'] += 60*15;
		   //  			// echo '<pre>';
		   //  			//dump($l['order_start_time']);dump($tmp_meet_start_time);dump($l['order_end_time']);dump($l['meet_start_time']);exit;
		   //  			if(($l['order_start_time'] >= $tmp_meet_start_time) && ($l['order_end_time'] > $l['meet_start_time'])){
		   //  				//echo 1;
		   //  				$result[$b['ban_id']][$f['floor_number']][$i]['list'][$k] = [
			  //   				'start_time' => date('H:i',$tmp_meet_start_time),
			  //   				'end_time' => date('H:i',$l['meet_start_time']),
			  //   				'meet_order_status' => $l['meet_order_status'], 
			  //   			];
		   //  			}else{

		   //  				$result[$b['ban_id']][$f['floor_number']][$i]['list'][$k] = [
			  //   				'start_time' => date('H:i',$tmp_meet_start_time),
			  //   				'end_time' => date('H:i',$l['meet_start_time']),
			  //   				'meet_order_status' => 3, 
			  //   			];
		   //  			}
		    			
		   //  		}
		   //  		$result[$b['ban_id']][$f['floor_number']][$i]['baseinfo'] = [
					// 	'ban_id' => $l['ban_id'],
					// 	'meet_id' => $l['meet_id'],
					// 	'meet_name' => $l['meet_name'],
					// 	'floor_number' => $l['floor_number'],
					// ];
		   //  		//exit;

		   //  		//halt($result);
		   //  	}






    	


    	//foreach ($result as $value) {
    		# code...
    	//}
    	//halt($result);
    	return $result;
    }
    
}
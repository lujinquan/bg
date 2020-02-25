<?php

namespace app\system\model;

use think\Db;
use think\Model;

/**
 * 系统消息模型
 * @package app\system\model
 */
class SystemTidings extends Model
{
	// 设置模型名称
    protected $name = 'system_tidings';
    // 设置主键
    protected $pk = 'ti_id';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'ti_ctime';

    protected $type = [
        'ti_ctime' => 'timestamp:Y-m-d H:i',
    ];

    public function setTiReadUsersAttr($value)
    {
        if (empty($value)) return '';
        return '|'.implode('|',$value).'|';
    }
    
    public function getTiReadUsersAttr($value)
    {
        if (empty($value)) return '';
        return explode('|',trim($value,'|'));
    }

     
    /**
     * 功能描述：
     * =====================================
     * @author  Lucas 
     * email:   598936602@qq.com 
     * Website  address:  www.mylucas.com.cn
     * =====================================
     * 创建时间: 2020-02-24 14:40:32 
     * @example 
     * @param $ti_type int 消息类型：0全部、1系统自动推送、2报事报修
     * @param $is_read int 阅读状态：0全部、1未读、2已读
     * @return  返回值  
     * @version 版本  1.0
     */
    public function getTiList($ti_type = 0 , $is_read = 1)
    {
    	$whereSql = '';
    	if($ti_type){ $whereSql .= ' ti_type = '.$ti_type .' and'; }
    		$whereSql .= " ( ti_to_user_id like '%|" . ADMIN_ID . "|%' or ti_to_user_id = '*')";
    	if($is_read == 1){ //未读状态
    		$whereSql .= " and ti_read_users not like '%|" . ADMIN_ID . "|%'";
    	}else if($is_read == 2){ //已读状态
    		$whereSql .= " and ti_read_users like '%|" . ADMIN_ID . "|%'";
    	}
    	$sql = 'select * from bg_system_tidings where'.$whereSql;
		//halt($sql);
    	$data = Db::query($sql);
    	//halt($data);
    	return $data;
    }

    /**
     * 功能描述：标记成已读状态
     * =====================================
     * @author  Lucas 
     * email:   598936602@qq.com 
     * Website  address:  www.mylucas.com.cn
     * =====================================
     * 创建时间: 2020-02-24 15:29:55
     * @example 
     * @link    文档参考地址：
     * @return  返回值  
     * @version 版本  1.0
     */
    public function tabToAlreadyRead($ti_id)
    {
    	$tiRow = self::get($ti_id);
    	if($tiRow['ti_read_users']){
    		$tiReadUsers = $tiRow->ti_read_users;
    		if(!in_array(ADMIN_ID, $tiReadUsers)){
    			$tiReadUsers[] = ADMIN_ID;
	    		$tiRow->ti_read_users = $tiReadUsers;
	    		$tiRow->save();
    		}	
    	}else{
    		$tiRow->ti_read_users = [ADMIN_ID];
    		$tiRow->save();
    	}
    }

    public function appendReadId($id)
    {
    	$this->get($id);
    }

    /**
     * [buildAffiche description]
     * @param  [type] $title        [description]
     * @param  [type] $content      [description]
     * @param  string $to_user_id   [description]
     * @param  string $from_user_id [description]
     * @return [type]               [description]
     */
    // public function buildAffiche($title,$content,$to_user_id = '*',$from_user_id = '*')
    // {
    //     $row = [];
    //     $row['title'] = $title;
    //     $row['from_user_id'] = $from_user_id;
    //     $row['to_user_id'] = $to_user_id;
    //     $row['content'] = $content;
    //     $row['create_time'] = time();
    //     return $this->save($row);
    // }
}

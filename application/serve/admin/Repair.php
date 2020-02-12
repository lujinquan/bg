<?php

// +----------------------------------------------------------------------
// | 基于ThinkPHP5开发
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | 基础框架永久免费开源
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>，开发者QQ群：*
// +----------------------------------------------------------------------


namespace app\serve\admin;

use think\Db;
use app\system\admin\Admin;
use app\space\model\Ban as BanModel;
//use app\serve\model\Chat as ChatModel;
use app\serve\model\Repair as RepairModel;
use app\project\model\Member as MemberModel;
use app\common\model\SystemAnnex as AnnexModel;


class Repair extends Admin
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();
    }

    public function index()
    { 
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $RepairModel = new RepairModel;
            $where = $RepairModel->checkWhere($getData);
            $data = [];
            $data['data'] = $RepairModel->with('member')->where($where)->page($page)->order('ctime desc')->limit($limit)->select()->toArray();
            //halt($data['data']);
            $data['count'] = $RepairModel->where($where)->count();
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);

        }
    	return $this->fetch();
    }

    /**
     * 处理报修
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function deal()
    {
        
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if(!$data['chat_content']){
                return $this->error('回复内容不能为空');
            }
            //halt($data);
            $row = RepairModel::get($data['repair_id']);

            if(isset($data['file']) && $data['file']){
                $chatImgs = implode(',',$data['file']);
            }else{
                $chatImgs = '';
            }
            //halt($row);
            $repairArr = $row['repair_json'];
            
            $repairArr[] = [
                'chat_type' => 2, //1会员发给管理员，2管理员发给会员
                //'chat_from_uid' => ADMIN_ID,
                //'chat_to_uid' => $row['member_id'],
                'chat_content' => $data['chat_content'],
                'chat_imgs'=> $chatImgs,
                'chat_ctime' => time(),
            ];

            $row->repair_json = $repairArr;
            $row->repair_status = 2; //状态改为处理中
            $res = $row->save();
            // 入库
            if (!$res) {
                return $this->error('回复失败');
            }
            return $this->success('回复成功');
        }
    }

    /**
     * 报修单详情
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function detail()
    {
        $RepairModel = new RepairModel;
        $id = input('param.repair_id/d');
        $row = $RepairModel->find($id);
        $row['imgs'] = AnnexModel::changeFormat($row['repair_imgs']);

        $memberid = $row->getData('member_id');
        $memberInfo = MemberModel::with('firm')->where([['member_id','eq',$memberid]])->find();

        $repairArr = [];
        if($row['repair_json']){
            
            foreach ($row['repair_json'] as $k => $v) {
                if($v['chat_type'] == 1){ //用户 回复给 管理员
                    $repairArr[$k]['chat_from_role'] = '用户';
                    $repairArr[$k]['chat_from_name'] = $memberInfo['member_name'];
                    $repairArr[$k]['chat_to_name'] = $row['to_uid'];
                }
                if($v['chat_type'] == 2){ //管理员 回复给 用户
                    $repairArr[$k]['chat_from_role'] = '管理员';
                    $repairArr[$k]['chat_from_name'] = $row['to_uid'];
                    $repairArr[$k]['chat_to_name'] = $memberInfo['member_name'];
                }
                $repairArr[$k]['chat_ctime'] = date('Y-m-d H:i',$v['chat_ctime']);
                $repairArr[$k]['chat_content'] = $v['chat_content'];
                if($v['chat_imgs']){
                    $repairArr[$k]['chat_imgs'] = AnnexModel::changeFormat($v['chat_imgs']);
                }else{
                    $repairArr[$k]['chat_imgs'] = [];
                }
                
            }
        }
        //halt($repairArr);
        
        //halt($memberInfo);
        // 获取会员信息 
        $this->assign('memberInfo',$memberInfo);
        $this->assign('repairArr',$repairArr);
        //  
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    /**
     * 完结报修单
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function complete()
    { 
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if(!$data['repair_remark']){
                return $this->error('处理结果不能为空');
            }
            $row = RepairModel::get($data['repair_id']);
            $row->repair_remark = $data['repair_remark'];
            $row->repair_status = 3;
            $row->wtime = time();
            $res = $row->save();
            if (!$res) {
                return $this->error('完结失败');
            }
            return $this->success('完结成功');
        }
        $id = input('param.id/d');
        $row = $row = RepairModel::get($id);
        //halt($row);
        $this->assign('data_info',$row);
        return $this->fetch();
    }


}
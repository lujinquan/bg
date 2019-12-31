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
use app\common\model\SystemAnnex as AnnexModel;
use app\space\model\Ban as BanModel;
use app\space\model\Meeting as MeetingModel;
use app\serve\model\MeetingOrder as MeetingOrderModel;

class Meeting extends Admin
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();

        $banArr = BanModel::where([['status','eq',1],['project_id','eq',PROJECT_ID]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
    }

    public function index()
    {   
        $MeetingOrderModel = new MeetingOrderModel;
        $group = input('group','status');
        
        if($this->request->isAjax() && $group == 'record'){
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            //halt($getData);
            $where = $MeetingOrderModel->checkWhere($getData);
            //[['a.meet_start_time','between time',[$nowDayDate,$nextDayDate]],['b.ban_id','eq',$b['ban_id']],['b.floor_number','eq',$f['floor_number']]]
            $fields = 'a.meet_order_id,a.meet_order_status,from_unixtime(a.ctime,\'%Y-%m-%d\') ctime,from_unixtime(a.order_start_time,\'%H:%i\') order_start_time, from_unixtime(a.order_end_time,\'%H:%i\') order_end_time,from_unixtime(a.meet_start_time,\'%Y-%m-%d\') meet_start_time, from_unixtime(a.meet_end_time,\'%Y-%m-%d\') meet_end_time,b.meet_name,c.member_name,d.firm_name';
            $data = [];
            $data['data'] = Db::name('space_meeting_order')->alias('a')->where($where)->join('space_meeting b','a.meet_id = b.meet_id','left')->join('member c','a.member_id = c.member_id','left')->join('member_firm d','a.firm_id = d.firm_id','left')->join('space_ban e','b.ban_id = e.ban_id','left')->field($fields)->order('a.ctime desc')->page($page)->limit($limit)->select();
            // foreach ($temp as $k => &$v) {
            //     if(){

            //     }
            // }
            // = $temp;
            $data['count'] = Db::name('space_meeting_order')->alias('a')->where($where)->join('space_meeting b','a.meet_id = b.meet_id','left')->join('member c','a.member_id = c.member_id','left')->join('member_firm d','a.firm_id = d.firm_id','left')->join('space_ban e','b.ban_id = e.ban_id','left')->field($fields)->order('a.ctime desc')->count('a.meet_order_id');
            $data['code'] = 0;
            $data['msg'] = '';
            //halt($data);
            return json($data);
        }
        if($this->request->isAjax() && $group == 'status'){
            $getData = $this->request->get();
            //halt($getData);
            $data = [];
            $date = '';
            $ban_name = '';
            $meet_name = '';
            $floor_number = '';
            if(isset($getData['ban_name']) && $getData['ban_name']){
                $ban_name = $getData['ban_name'];
            }
            if(isset($getData['floor_number']) && $getData['floor_number']){
                $floor_number = $getData['floor_number'];   
            }
            if(isset($getData['meet_name']) && $getData['meet_name']){
                $meet_name = $getData['meet_name'];  
            }
            if(isset($getData['date']) && $getData['date']){
                $date = $getData['date']; 
            }
            $data['data'] = $MeetingOrderModel->getList($ban_name,$floor_number,$meet_name,$date);
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
            //$this->assign('data',$data);
        }
        
        $tabData = [];
        $tabData['menu'] = [
            [
                'title' => '会议室状态',
                'url' => '?group=status',
            ],
            [
                'title' => '会议室记录',
                'url' => '?group=record',
            ]
        ];
        $tabData['current'] = url('?group='.$group);
        $this->assign('group',$group);
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 3);
        return $this->fetch('index_'.$group);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'Meeting.add');
            if($result !== true) {
                return $this->error($result);
            }
            if(isset($data['file'])){ //附件
                $data['imgs'] = implode(',',$data['file']);
                $AnnexModel = new AnnexModel;
                $AnnexModel->updateAnnexEtime($data['file']);
            }
            $MeetingModel = new MeetingModel;
            unset($data['meet_id']);
            // 入库
            if (!$MeetingModel->allowField(true)->create($data)) {
                return $this->error('新增失败');
            }
            return $this->success('新增成功');
        }

        return $this->fetch();
    }

    public function edit()
    {
        $AnnexModel = new AnnexModel;
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'Meeting.add');
            if($result !== true) {
                return $this->error($result);
            }
            if(isset($data['file'])){ //附件
                $data['imgs'] = implode(',',$data['file']);
                $AnnexModel->updateAnnexEtime($data['file']);
            }
            $MeetingModel = new MeetingModel;
            // 入库
            if (!$MeetingModel->allowField(true)->update($data)) {
                return $this->error('编辑失败');
            }
            return $this->success('编辑成功');
        }
        $id = input('param.id/d');
        $row = MeetingModel::get($id);
        $row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function detail()
    {
        $id = input('param.id/d');
        $row = BanModel::get($id);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function del()
    {
        $ids = $this->request->param('id/a');        
        $res = MeetingModel::where([['meet_id','in',$ids]])->update(['status'=>0]);
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    public function cancel()
    {
        $id = input('param.id/d');
        halt($id);
        // $row = BanModel::get($id);
        // $this->assign('data_info',$row);
        // return $this->fetch();
    }
    
}
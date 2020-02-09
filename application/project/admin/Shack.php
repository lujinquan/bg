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


namespace app\project\admin;

use think\Db;
use app\system\admin\Admin;
use app\space\model\Ban as BanModel;
use app\project\model\Firm as FirmModel;
use app\project\model\Shack as ShackModel;
use app\space\model\Rest as RestModel;
use app\project\model\Member as MemberModel;
use app\system\model\SystemGuard as GuardModel;
use app\common\model\SystemAnnex as AnnexModel;
use app\space\model\SiteGroup as SiteGroupModel;
use app\space\model\Site as SiteModel;
include EXTEND_PATH.'phpexcel/PHPExcel.php';

/**
 * 入驻办理
 */
class Shack extends Admin
{

    public function index()
    {   
        if ($this->request->isAjax()) {
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 10);
            $getData = $this->request->get();
            $ShackModel = new ShackModel;
            $where = $ShackModel->checkWhere($getData);
            //$fields = '*';
            $data = [];
            $fields = 'b.firm_name,b.firm_coupon_num,c.member_name,from_unixtime(a.shack_start_time,\'%Y-%m-%d\') shack_start_time,from_unixtime(a.shack_end_time,\'%Y-%m-%d\') shack_end_time,a.shack_status,a.shack_type,a.id,from_unixtime(a.ctime) ctime,d.nick';
            $data = [];
            $temps = Db::name('project_shack')->alias('a')->join('member_firm b','a.firm_id = b.firm_id','left')->join('member c','a.member_id = c.member_id','left')->join('system_user d','a.cuid = d.id','left')->where($where)->field($fields)->page($page)->order('a.ctime desc')->limit($limit)->select();
            foreach ($temps as $k => &$v) {
                if($v['member_name']){
                    $v['group_name'] = $v['member_name'];
                }
                if($v['firm_name']){
                    $v['group_name'] = $v['firm_name'];
                }
                //$v['shack_type'] = explode('|',$v['shack_type']);
            }
            $data['data']  = array_slice($temps, ($page- 1) * $limit, $limit);
            //halt($where);
            $data['count'] = Db::name('project_shack')->alias('a')->join('member_firm b','a.firm_id = b.firm_id','left')->join('member c','a.member_id = c.member_id','left')->where($where)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        return $this->fetch();
    }

    /**
     * 项目管理 >入驻办理 > 企业入驻：获取企业名称接口
     * @return [type] [description]
     */
    public function getFirms()
    {
        $keywords = input('param.keywords/s');
        $where = $data = [];
        // 0为普通企业，1为入驻企业，注：初次录入企业信息状态为普通企业，企业入驻成功后状态变为入驻企业，入驻企业失效后状态变为普通企业
        $where[] = ['status','eq',0]; 
        // 查询公司名称
        if($keywords){ $where[] = ['firm_name','like','%'.$keywords.'%']; }
        $data['data'] = FirmModel::where($where)->field('firm_id as userCode,firm_name as userName,firm_name as deptName')->select();
        $data['count'] = FirmModel::where($where)->count();
        return json($data);
    }

    /**
     * 获取企业人员列表
     * @return [type] [description]
     */
    public function getMembers()
    {
        $keywords = input('param.keywords/s');
        $where = $data = [];
        $where[] = ['status','eq',1]; //正常会员
        $where[] = ['member_status','eq',0]; //未入驻
        $where[] = ['project_id','eq',PROJECT_ID]; //当前项目
        // 查询公司名称
        if($keywords){ $where[] = ['member_name','like','%'.$keywords.'%']; }
        $data['code'] = 0;
        $data['data'] = MemberModel::with('firm')->where($where)->field('firm_id ,member_id,member_name,member_tel')->select();
        $data['count'] = MemberModel::where($where)->count();
        return json($data);
    }

    /**
     * 获取企业（个人）信息
     * @return [type] [description]
     */
    public function getMemberDetail()
    {
        $member_name = input('param.member_name');
        $where = $data = [];
        $where[] = ['member_name','eq',$member_name];
        $row = MemberModel::where($where)->find();
        if($row){
            $data['data'] = $row->toArray();
            $data['code'] = 1;
            return json($data);
        }else{
            return $this->error('系统中无该人员数据');
        }
    }

    /**
     * 获取企业（公司）信息
     * @return [type] [description]
     */
    public function getFirmDetail()
    {
        $firm_name = input('param.firm_name');
        $where = $data = [];
        $where[] = ['firm_name','eq',$firm_name];
        $row = FirmModel::where($where)->find();
        if($row){
            $row['imgs'] = AnnexModel::changeFormat($row['firm_imgs']);
            $data['data'] = $row;
            $data['code'] = 1;
            return json($data);
        }else{
            return $this->error('系统中无该公司数据');
        }
    }

    /**
     * 项目管理 >入驻办理 > 企业入驻：批量导入管理员及一般员工
     * @return [type] [description]
     */
    public function import($from = 'input', $group = 'sys', $water = '', $thumb = '', $thumb_type = '', $input = 'file')
    {
        $firm_id = input('firm_id');
        halt($firm_id);
        $res = AnnexModel::upload($from, $group, $water, $thumb, $thumb_type, $input);
        //halt($res['data']['file']);
        //$fileName = "./upload/project/file/20190830/d1f12e109079fb4886b53beaee1e4c95.xls";
        $fileName = '.'.$res['data']['file'];
        $keyNames = ['member_name','member_tel','member_post','member_department','member_card'];
        $extension = strtolower( pathinfo($fileName, PATHINFO_EXTENSION) );
     
        if ($extension =='xlsx') {
            $objReader = new \PHPExcel_Reader_Excel2007();
            $objPhpExcel = $objReader ->load($fileName);
        } else if ($extension =='xls') {
            $objReader = new \PHPExcel_Reader_Excel5();
            $objPhpExcel = $objReader ->load($fileName);
        } else if ($extension=='csv') {
            $PHPReader = new \PHPExcel_Reader_CSV();
            //默认输入字符集
            $PHPReader->setInputEncoding('GBK');
            //默认的分隔符
            $PHPReader->setDelimiter(',');
            //载入文件
            $objPhpExcel = $PHPReader->load($fileName);
        }
     
        $sheet = $objPhpExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();//获得总行数
        $highestColumn = $sheet->getHighestColumn();//获得总列数
        //halt('总行数：'.$highestRow.',最大列号：'.$highestColumn);
        $to = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI'];
        $k = 0;
        $data = [];
        $hightNum = array_search($highestColumn,$to) - 1;
        if(($hightNum + 1) != count($keyNames)){
            return $this->error('导入列数与模板不一致');
        }
        //halt('总列数：'.($hightNum + 1).',模板要求列数：'.count($keyNames));
        if($hightNum <2){
            return $this->error('导入数据为空');
        }
        for($j=2;$j<=$highestRow;$j++)
        {
            for ($i=0;$i<=$hightNum;$i++){
                $data[$j][$keyNames[$i]] = $objPhpExcel->getActiveSheet()->getCell($to[$i].$j)->getValue();
            }
            $data[$j]['firm_id'] = 1;
            $data[$j]['project_id'] = PROJECT_ID;
        }
        $MemberModel = new MemberModel;
        //halt($data);
        if (!$MemberModel->allowField(true)->saveAll($data)) {
            return $this->error('导入失败');
        }
        //halt($data);
        return $this->success('导入成功');
    }

    // public function ceshi()
    // {
    //     $data = [
    //         "code"=> 0,
    //         "msg"=> "",
    //         "count"=> 100,
    //         "data"=> [
    //             [
    //                 "userName"=> "测试用户1",
    //                 "userCode"=> "170001",
    //                 "deptName"=> "技术部门1"
    //             ], [
    //                 "userName"=> "测试用户2",
    //                 "userCode"=> "170002",
    //                 "deptName"=> "行政部门1"
    //             ], [
    //                 "userName"=> "测试用户3",
    //                 "userCode"=> "170003",
    //                 "deptName"=> "测试部门2"
    //             ], [
    //                 "userName"=> "测试用户4",
    //                 "userCode"=> "170004",
    //                 "deptName"=> "测试部门2"
    //             ], [
    //                 "userName"=> "测试用户5",
    //                 "userCode"=> "170005",
    //                 "deptName"=> "测试部门3"
    //             ]
    //         ]
    //     ];
    //     return json($data);
                        
        
    // }

    /**
     * 入驻办理
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            // $result = $this->validate($data, 'Shack.add');
            // if($result !== true) {
            //     return $this->error($result);
            // }
            if(empty($data['shack_start_time']) || empty($data['shack_end_time']) || (strtotime($data['shack_start_time']) >= strtotime($data['shack_end_time']))){
                return $this->error('请选择正确的入驻时间！');
            }
            if(isset($data['file'])){ //附件
                $data['imgs'] = implode(',',$data['file']);
                $AnnexModel = new AnnexModel;
                $AnnexModel->updateAnnexEtime($data['file']);
            }
            $siteGroupID = [];
            if(isset($data['lianhe'])){ //联合工位
                $data['sites'] = '|'.implode('|',$data['lianhe']).'|';
            }
            if(isset($data['duli'])){ //独立工位
                foreach ($data['duli'] as $v1) {
                    $siteGroupID[] = $v1;
                }
            }
            if(isset($data['ziyou'])){ //自由工位
                foreach ($data['ziyou'] as $v2) {
                    $siteGroupID[] = $v2;
                }
            }
            if($siteGroupID){
                $data['site_group'] = '|'.implode('|',$siteGroupID).'|';
            }
            if(!$siteGroupID && !isset($data['lianhe'])){
                return $this->error('请分配工位区');
            }
            //halt($siteGroupID);
            if(isset($data['guard'])){ //门禁
                $data['guard'] = [
                    'ban' => $data['ban'],
                    'floor' => $data['floor'],
                    'guard' => $data['guard'],
                ];
            }else{
                return $this->error('请设置门禁');
            }
            $data['cuid'] = ADMIN_ID;
            $data['shack_type'] = [1]; // 1为承租，数组中可有多个

            $ShackModel = new ShackModel;
            
            switch ($data['shack_classify']) {
                //企业入驻
                case 'addfirm':
                    $firmStatus = FirmModel::where([['firm_id','eq',$data['firm_id']]])->value('status');
                    if($firmStatus == 1){
                        return $this->error('当前企业已入驻');
                    }
                    // 入库
                    if (!$ShackModel->allowField(true)->create($data)) {
                        return $this->error('分配失败');
                    }
                    if(isset($data['lianhe'])){
                        SiteModel::where([['site_id','in',$data['lianhe']]])->update(['status'=>1]);
                    }
                    FirmModel::where([['firm_id','eq',$data['firm_id']]])->update(['status'=>1]);
                    return $this->success('分配成功','',['firm_id'=>$data['firm_id']]);
                break; 
                //个人入驻
                case 'addpersion': 
                    $memberModel = new MemberModel;
                    if(isset($data['member_id'])){
                        $memberStatus = $memberModel->where([['member_id','eq',$data['member_id']]])->value('member_status');
                        if($memberStatus == 1){
                            return $this->error('当前会员已入驻');
                        }
                        $memSaveData = [
                            'member_name' => $data['member_name'],
                            'member_tel' => $data['member_tel'],
                            'member_sex' => $data['member_sex'],
                            'member_card' => $data['member_card'],
                            'member_remark' => $data['member_remark'],
                            'member_status' => 1, //会员状态改成已入驻
                        ];
                        $memberModel->allowField(true)->save($memSaveData,['member_id'=>$data['member_id']]);
                    }else{
                        //查到相同的手机号，代表是同一个用户
                        $memberInfo = $memberModel->where([['member_tel','eq',$data['member_tel']]])->find();
                        if($memberInfo){
                            return $this->error('当前会员已存在请点击校验');
                        }
                        $data['member_status'] = 1;
                        $memberModel->allowField(true)->create($data);
                    }
                    // 入库
                    if (!$ShackModel->allowField(true)->create($data)) {
                        return $this->error('分配失败');
                    }
                    if(isset($data['lianhe'])){
                        SiteModel::where([['site_id','in',$data['lianhe']]])->update(['status'=>1]);
                    }
                    return $this->success('入驻成功');
                    break;
                
                default:
                    # code...
                    break;
            }

            
            
            
        }


        // 获取工位区
        $siteGroupModel = new SiteGroupModel;
        $sites = Db::name('space_site_group')->alias('a')->join('space_ban b','a.ban_id = b.ban_id','left')->where([['a.status','eq',1],['b.project_id','eq',PROJECT_ID]])->field('site_group_id,site_group_type,site_group_name')->select();
        //halt($sites);
        $siteArr = [];
        $siteArr[1] = []; // 所有联合工位区
        $siteArr[2] = []; // 所有独立办公区
        $siteLianHeArr = [];
        foreach ($sites as $k => $v) {
            $siteLianHeArr[] = $v['site_group_id'];
            if($v['site_group_type'] > 1){
                $siteArr[$v['site_group_type']][] = [
                    'value' => $v['site_group_id'],
                    'title' => $v['site_group_name'],
                ];
            }
        }
        //取出非正在使用的工位且为有效工位区下的所有工位
        $siteArr[1] = Db::name('space_site')->alias('a')->join('space_site_group b','a.site_group_id = b.site_group_id','left')->where([['a.status','eq',0],['a.site_group_id','in',$siteLianHeArr]])->field('a.site_id as value,concat_ws("_",a.site_name,b.site_group_name) as title')->select();
        //halt($siteArr);
        $this->assign('siteArr',$siteArr);
        // 获取楼宇
        $banArr = BanModel::where([['status','eq',1],['project_id','eq',PROJECT_ID]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
        // 获取门禁组权限
        $GuardModel = new GuardModel;
        $guardArr = GuardModel::getAllChild();
        $this->assign('guardArr', $guardArr);

        $type = input('param.type/d');
        switch ($type) {
            case 1: // 企业入驻
                $html = 'addfirm';
                break;
            case 2: // 个人入驻
                $html = 'addpersion';
                break;
            case 3: // 自由工位区入驻
                $siteGroupModel = new SiteGroupModel;
                $siteGroupArr = $siteGroupModel->where([['status','eq',1],['site_group_type','eq',3]])->field('site_group_id,site_group_name')->select();
                $this->assign('siteGroupArr', $siteGroupArr);
                $html = 'addsitegroup';
                break;
            case 4: //其他场地入驻
                
                $RestModel = new RestModel;
                $restArr = $RestModel->where([['status','eq',1]])->field('rest_id,rest_name')->select();
                $this->assign('restArr', $restArr);
                $html = 'addrest';
                break;

            default: //企业入驻
                $html = 'addfirm';
                break;
        }
        return $this->fetch($html);
    }

    public function edit()
    {
        $AnnexModel = new AnnexModel;
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据验证
            $result = $this->validate($data, 'Rest.add');
            if($result !== true) {
                return $this->error($result);
            }
            $RestModel = new RestModel();
            // 入库
            if (!$RestModel->allowField(true)->update($data)) {
                return $this->error('编辑失败');
            }
            return $this->success('编辑成功');
        }
        $id = input('param.id/d');
        $row = ShackModel::get($id);
        $row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        $banArr = BanModel::where([['status','eq',1]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
        //halt($row);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function detail()
    {
        $id = input('param.id/d');
        $row = ShackModel::get($id);
        $shack_type = explode('|',trim($row['shack_type'],'|'));
        $this->assign('shack_type',$shack_type);

        //工位区
        $site_group_ids = explode('|',trim($row['site_group'],'|'));
        $siteGroupModel = new SiteGroupModel;
        $siteGroups = $siteGroupModel->where([['site_group_id','in',$site_group_ids]])->field('site_group_id,site_group_type,ban_id,floor_number,site_group_name')->select()->toArray();
        $floor_numbers = $ban_names =[];
        foreach ($siteGroups as $k => $s) {
            $ban_names[] = BanModel::where([['ban_id','eq',$s['ban_id']]])->value('ban_name');
            $floor_numbers[] = $s['floor_number'];
        }
        //dump($ban_names);halt($floor_numbers);
        $this->assign('banNames',array_unique($ban_names));
        $this->assign('floorNumbers',array_unique($floor_numbers));
        $this->assign('siteGroups',$siteGroups);
        //工位
        $site_ids = explode('|',trim($row['sites'],'|'));
        $siteModel = new SiteModel;
        $sites = $siteModel->where([['site_id','in',$site_ids]])->field('site_id,site_name,site_group_id')->select();
        $this->assign('sites',$sites);
        //门禁
        $GuardModel = new GuardModel;
        $guards = $GuardModel->where([['id','in',$row['guard']['guard']]])->field('id,title')->select();
        //halt($guards);
        $this->assign('guards',$guards);
        //附件
        $AnnexModel = new AnnexModel;
        $row['imgs'] = AnnexModel::changeFormat($row['imgs']);
        $banArr = BanModel::where([['status','eq',1]])->field('ban_id,ban_name')->select();
        $this->assign('banArr',$banArr);
        $this->assign('data_info',$row);
        return $this->fetch();
    }

    public function del()
    {
        $ids = $this->request->param('id/a');        
        $res = RestModel::where([['rest_id','in',$ids]])->update(['status'=>0]);
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}
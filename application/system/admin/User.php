<?php
// +----------------------------------------------------------------------
// | 基于ThinkPHP5开发
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 https://www.mylucas.com.cn
// +----------------------------------------------------------------------
// | 基础框架永久免费开源
// +----------------------------------------------------------------------
// | Author: Lucas <598936602@qq.com>，开发者QQ群：*
// +----------------------------------------------------------------------

namespace app\system\admin;

use app\system\model\SystemUser as UserModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemGuard as GuardModel;
use app\Common\model\Project;

/**
 * 后台用户、角色控制器
 * @package app\system\admin
 */
class User extends Admin
{
    public $tabData = [];
    protected $hisiTable = 'SystemUser';
    /**
     * 初始化方法
     */
    // protected function initialize()
    // {
    //     parent::initialize();

    //     $tabData['menu'] = [
    //         [
    //             'title' => '管理员角色',
    //             'url' => 'system/user/role',
    //         ],
    //         [
    //             'title' => '系统管理员',
    //             'url' => 'system/user/index',
    //         ],
    //     ];
    //     $this->tabData = $tabData;
    // }

    /**
     * 用户管理
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function index($q = '')
    {
        // $projectModel = new Project;
        // $proArr = $projectModel->where([['status','eq',1]])->column('id,project_name');
        if ($this->request->isAjax()) {
            $where      = $data = [];
            $page       = $this->request->param('page/d', 1);
            $limit      = $this->request->param('limit/d', 15);
            $username    = $this->request->param('username/s');
            $nick       = $this->request->param('nick/s');
            $status    = $this->request->param('status/d');
            $where[]    = ['id', 'neq', 1];
            $where[] = ['status', 'eq', 1];
            $where[]    = ['pro_ids', 'eq', PROJECT_ID];
            if ($username) {
                $where[] = ['username', 'like', "%{$username}%"];
            }
            if ($nick) {
                $where[] = ['nick', 'like', "%{$nick}%"];
            }
            
            if ($status) {
                if($status == 1){
                    $where[] = ['last_login_ip', 'neq', ''];
                }else{
                    $where[] = ['last_login_ip', 'eq', ''];
                }
                
            }
            
            $temp = UserModel::with('role')->where($where)->page($page)->limit($limit)->select();

            $data['data'] = $temp;
            $data['count'] = UserModel::where($where)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            //halt($temp);
            return json($data);
        }
        //$this->assign('proArr',$proArr);
        $this->assign('leadUrlExtra','user/indexManage');
        return $this->fetch();
    }

    /**
     * 一般管理员管理列表页
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function indexManage($q = '')
    {
        if(ADMIN_ROLE != 1){
            return $this->error('权限不足');
        }
        $projectModel = new Project;
        $proArr = $projectModel->where([['status','eq',1]])->column('id,project_name');
        if ($this->request->isAjax()) {
            $where      = $data = [];
            $page       = $this->request->param('page/d', 1);
            $limit      = $this->request->param('limit/d', 15);
            $username    = $this->request->param('username/s');
            $nick    = $this->request->param('nick/s');
            $username    = $this->request->param('username/s');
            $pro_ids    = $this->request->param('pro_ids/d');
            $status    = $this->request->param('status/d');
            $where[]    = ['id', 'neq', 1];
            $where[] = ['status', 'eq', 1];
            if ($username) {
                $where[] = ['username', 'like', "%{$username}%"];
            }
            if ($nick) {
                $where[] = ['nick', 'like', "%{$nick}%"];
            }
            if ($pro_ids) {
                $where[] = ['pro_ids', 'like', "%{$pro_ids}%"];
            }
            if ($status) {
                if($status == 1){
                    $where[] = ['last_login_ip', 'neq', ''];
                }else{
                    $where[] = ['last_login_ip', 'eq', ''];
                }
                
            }
            
            $temp = UserModel::with('role')->where($where)->page($page)->limit($limit)->select();
            foreach ($temp as $key => $value) {
                $value['pro_names'] = '';
                //dump($value['pro_ids']);
                if($value['pro_ids'][0]){
                    foreach ($value['pro_ids'] as $k => $v) {
                        if($value['pro_names']){
                            $value['pro_names'] .= ','.$proArr[$v];
                        }else{
                            $value['pro_names'] .= $proArr[$v];
                        }
                        
                    }
                }  
            }//exit;
            $data['data'] = $temp;
            $data['count'] = UserModel::where($where)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            //halt($temp);
            return json($data);
        }
        $this->assign('proArr',$proArr);
        $this->assign('leadUrlExtra','user/indexManage');
        return $this->fetch();
    }

    /**
     * 移除授权
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function removeUserProject()
    {
        if(ADMIN_ROLE != 1){
            return $this->error('权限不足');
        }
        $id   = $this->request->param('id/d');
        $model = new UserModel();
        $res = $model->where([['id','eq',$id]])->update(['pro_ids'=>'']);
        //halt($res);
        if ($res) {
            return $this->success('移除成功');
        }
        return $this->error($model->getError());
    }

    /**
     * 新增授权
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function addUserManage()
    {
        if(ADMIN_ROLE != 1){
            return $this->error('权限不足');
        }
        $projectModel = new Project;
        $proArr = $projectModel->where([['status','eq',1]])->column('id,project_name');
        if ($this->request->isPost()) {

            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'SystemUserManage.add');
            if($result !== true) {
                return $this->error($result);
            }
            if(!isset($data['pro_ids'])){
                return $this->error('请选择授权项目');
            }
            unset($data['id']);

            
            $data['last_login_ip'] = '';
            $data['auth'] = '';

            if (!UserModel::create($data)) {
                return $this->error('添加失败');
            }

            return $this->success('添加成功');
        }

        $this->assign('proArr',$proArr);
        $this->assign('menu_list', '');
        $this->assign('roleOptions', RoleModel::getOption());

        return $this->fetch('usermanage');
    }

    /**
     * 新增授权
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function editUserManage()
    {
        if(ADMIN_ROLE != 1){
            return $this->error('权限不足');
        }
        $projectModel = new Project;
        $proArr = $projectModel->where([['status','eq',1]])->column('id,project_name');
        if ($this->request->isPost()) {

            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'SystemUserManage.edit');
            if($result !== true) {
                return $this->error($result);
            }

            if (!UserModel::update($data)) {
                return $this->error('编辑失败');
            }

            return $this->success('编辑成功');
        }
        $id   = $this->request->param('id/d');
        $model = new UserModel();
        $row = $model->find($id);
        $this->assign('data_info',$row);
        $this->assign('proArr',$proArr);
        $this->assign('menu_list', '');
        $this->assign('roleOptions', RoleModel::getOption());

        return $this->fetch('editusermanage');
    }

    /**
     * 删除用户
     * @param int $id
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function delUserManage()
    {
        if(ADMIN_ROLE != 1){
            return $this->error('权限不足');
        }
        $id   = $this->request->param('id/d');
        $model = new UserModel();
        $pro_ids = $model->where([['id','eq',$id]])->value('pro_ids');
        if (!$pro_ids) {
            if ($model->del($id)) {
                return $this->success('删除成功');
            }
            return $this->error($model->getError());
        } else {
            return $this->error('请先移除授权');
        }
        
    }

    /**
     * 布局切换
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function iframe()
    {
        $val = UserModel::where('id', ADMIN_ID)->value('iframe');
        if ($val == 1) {
            $val = 0;
        } else {
            $val = 1;
        }
        if (!UserModel::where('id', ADMIN_ID)->setField('iframe', $val)) {
            return $this->error('切换失败');
        }
        cookie('hisi_iframe', $val);
        return $this->success('请稍等，页面切换中...', url('system/index/index'));
    }

    /**
     * 主题设置
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function setTheme()
    {
        $theme = $this->request->param('theme/d', 0);
        if (UserModel::setTheme($theme, true) === false) {
            return $this->error('设置失败');
        }
        return $this->success('设置成功');
    }

    /**
     * 添加用户
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function addUser()
    {
        if ($this->request->isPost()) {

            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'SystemUser.add');
            if($result !== true) {
                return $this->error($result);
            }
            
            
            $data['pro_ids'] = PROJECT_ID;
            $data['last_login_ip'] = '';
            $data['auth'] = '';

            $data['guard'] = [
                'ban' => $data['ban'],
                'floor' => $data['floor'],
                'guard' => $data['guard'],
            ];
            
            unset($data['id'],$data['ban'],$data['floor']);
            //halt($data);
            if (!UserModel::create($data)) {
                return $this->error('添加失败');
            }

            return $this->success('添加成功');
        }
        // 获取门禁组权限
        $GuardModel = new GuardModel;
        $guardArr = GuardModel::getAllChild();
        //halt($guardArr);
        $this->assign('guardArr', $guardArr);
        $roleArr = RoleModel::where([['status','eq',1],['id','>',2]])->column('id,name');
        $this->assign('roleArr', $roleArr);
        return $this->fetch('userform');
    }

    /**
     * 修改用户
     * @param int $id
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function editUser($id = 0)
    {
        if ($id == 1 && ADMIN_ID != $id) {
            return $this->error('禁止修改超级管理员');
        }

        
        if ($this->request->isPost()) {

            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'SystemUser.edit');
            if($result !== true) {
                return $this->error($result);
            }

            if (!UserModel::update($data)) {
                return $this->error('编辑失败');
            }

            return $this->success('编辑成功');
        }
        $roleArr = RoleModel::where([['status','eq',1],['id','>',2]])->column('id,name');
        $this->assign('roleArr', $roleArr);
        $id   = $this->request->param('id/d');
        $model = new UserModel();
        $row = $model->find($id);
        //halt($row);
        $this->assign('data_info',$row);
        return $this->fetch('edituserform');
    }

    /**
     * 修改个人信息
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function info()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['id'] = ADMIN_ID;
            // 防止伪造篡改
            unset($data['role_id'], $data['status']);

            if ($data['password']) {
                $data['password'] = md5($data['password']);
                $data['password_confirm'] = md5($data['password_confirm']);
            }

            // 验证
            $result = $this->validate($data, 'SystemUser.info');
            if($result !== true) {
                return $this->error($result);
            }

            if ($data['password']) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }

            if (!UserModel::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }

        $row = UserModel::where('id', ADMIN_ID)->field('username,nick,email,mobile')->find()->toArray();
        $this->assign('formData', $row);
        return $this->fetch();
    }

    /**
     * 删除用户
     * @param int $id
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function delUser()
    {
        $ids   = $this->request->param('id/a');
        $model = new UserModel();
        if ($model->del($ids)) {
            return $this->success('删除成功');
        }
        return $this->error($model->getError());
    }

    // +----------------------------------------------------------------------
    // | 角色
    // +----------------------------------------------------------------------

    /**
     * 角色管理
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function role()
    {
        if ($this->request->isAjax()) {
            $data = [];
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 15);

            $data['data'] = RoleModel::where('id', '<>', 1)->select();
            $data['count'] = RoleModel::where('id', '<>', 1)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        return $this->fetch();
    }

    /**
     * 添加角色
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function addRole()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'SystemRole');
            if($result !== true) {
                return $this->error($result);
            }
            unset($data['id']);
            if (!RoleModel::create($data)) {
                return $this->error('添加失败');
            }
            return $this->success('添加成功');
        }
        $tabData = [];
        $tabData['menu'] = [
            ['title' => '添加角色'],
            ['title' => '设置权限'],
        ];
        $this->assign('menu_list', MenuModel::getAllChild());
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 2);
        return $this->fetch('roleform');
    }

    /**
     * 修改角色
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function editRole($id = 0)
    {
        if ($id <= 1) {
            return $this->error('禁止编辑');
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 当前登录用户不可更改自己的分组角色
            if (ADMIN_ROLE == $data['id']) {
                return $this->error('禁止修改当前角色(原因：您不是超级管理员)');
            }

            // 验证
            $result = $this->validate($data, 'SystemRole');
            if($result !== true) {
                return $this->error($result);
            }
            if (!RoleModel::update($data)) {
                return $this->error('修改失败');
            }

            // 更新权限缓存
            cache('role_auth_'.$data['id'], $data['auth']);

            return $this->success('修改成功');
        }
        $tabData = [];
        $tabData['menu'] = [
            ['title' => '修改角色'],
            ['title' => '设置权限'],
        ];
        $row = RoleModel::where('id', $id)->field('id,name,intro,auth,status')->find()->toArray();

        $this->assign('formData', $row);
        $this->assign('menu_list', MenuModel::getAllChild());
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 2);
        return $this->fetch('roleform');
    }

    public function statusRole()
    {
        $this->hisiTable = 'SystemRole';
        parent::status();
    }

    /**
     * 删除角色
     * @param int $id
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function delRole()
    {
        $ids   = $this->request->param('id/a');
        $model = new RoleModel();
        if ($model->del($ids)) {
            return $this->success('删除成功');
        }
        return $this->error($model->getError());
    }
}

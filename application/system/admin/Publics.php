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

use think\captcha\Captcha;
use SendMessage\ServerCodeAPI;
use app\common\controller\Common;
use app\system\model\SystemUser as UserModel;
use app\common\model\PhoneUseRecord;

/**
 * 后台公共控制器
 * @package app\system\admin
 */
class Publics extends Common
{
    /**
     * 登录页面
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function index()
    {
        $model = new UserModel;
        $loginError = (int)session('admin_login_error');
        
        if ($this->request->isPost()) {
            $type = $this->request->post('type/s');
            $username   = $this->request->post('username/s');
            $password   = $this->request->post('password/s');
            switch ($type) {
                case 1: //用户名密码登录
                    if (!$model->login($username, $password)) {
                        $loginError = ($loginError+1);
                        session('admin_login_error', $loginError);
                        $data['token'] = $this->request->token();
                        //$data['captcha'] = $loginError >= 3 ? captcha_src() : '';
                        return $this->error($model->getError(), url('index'), $data);
                    }
                    //halt(1);
                    session('admin_login_error', 0);          
                    return $this->success('登录成功，页面跳转中...', url('index/index'));
                    break;

                case 2: //手机短信登录
                    $data       = [];
                    $code   = $this->request->post('code/d');

                    $row = $model->where([['username','eq',$username],['status','eq',1],['role_id','in',[1,2]]])->find();
                    if(!$row){
                        return $this->error('用户名不存在或被禁用');
                    }

                    $auth = new ServerCodeAPI();
                    
                        $res = $auth->CheckSmsYzm($username, $code);
                        $res = json_decode($res);
                        // 验证短信码是否正确
                        if($res->code == '200'){ 
                            if (!$model->loginPhone($username)) {
                                $loginError = ($loginError+1);
                                session('admin_login_error', $loginError);
                                return $this->error($model->getError(), url('index'), $data);
                            }
                            session('admin_login_error', 0);
                            $password = $model->where([['username','eq',$username]])->value('password');
                            if(!$password){
                                return $this->success('登录成功，页面跳转中...', url('index/setPassword'));
                            }
                            return $this->success('登录成功，页面跳转中...', url('index/index'));
                        } else if($res->code == '413'){
                            return $this->error('验证失败');
                        } else {
                            return $this->error('请重新获取');
                        }

                    break;

                case 3: //验证
                    $row = $model->where([['username','eq',$username],['status','eq',1]])->find();
                    if(!$row){
                        return $this->error('用户名不存在或被禁用');
                    }
                    //验证通过即发送短信
                    $auth = new ServerCodeAPI();
                    if(!isset($data['code'])){
                        $res = $auth->SendSmsCode($username);
                        $res = json_decode($res);
                        if($res->code == '416'){
                            $this->error('验证次数过多，请更换登录方式');
                        }
                    }
                    return $this->success('用户名验证成功');
                    break;
                default:
                    # code...
                    break;
            }

            
        }

        if ($model->isLogin()) {
            $this->redirect(url('index/index', '', true, true));
        }

        $this->view->engine->layout(false);
        
        $this->assign('loginError', $loginError);

        return $this->fetch();
    }

    public function firstLogin()
    {
        //验证是否可以设置密码
        if ($this->request->isPost()) {
            $model = new UserModel;
            $type = $this->request->post('type/s');
            $username   = $this->request->post('username/s');
            $data       = [];
            $code   = $this->request->post('code/d');

            $row = $model->where([['username','eq',$username],['status','eq',1],['role_id','in',[1,2]]])->find();
            if(!$row){
                return $this->error('用户名不存在或被禁用');
            }

            $auth = new ServerCodeAPI();
            
            $res = $auth->CheckSmsYzm($username, $code);
            $res = json_decode($res);
            // 验证短信码是否正确
            if($res->code == '200'){ 
                $key = str_coding($username,'ENCODE');
                return $this->success('验证码验证成功', url('publics/setPassword'),['key'=>$key]);
            } else if($res->code == '413'){
                return $this->error('验证失败');
            } else {
                return $this->error('请重新获取');
            }

        }
        return $this->fetch();
    }

    public function setPassword()
    {
        //检查是否可以进入到当前页面
        $systemUserModel = new UserModel;

        if ($this->request->isAjax()) {
            $data = $this->request->post();
            //halt($data);
            if(!$data['password']){
                return $this->error('密码不能为空！');
            }
            $data['password'] = md5($data['password']);
            $data['password_confirm'] = md5($data['password_confirm']);
        
            // 验证
            $result = $this->validate($data, 'SystemUserManage.setPassword');
            if($result !== true) {
                return $this->error($result);
            }

            $key = $data['key'];
            $key = str_replace(" ","+",$key); //加密过程中可能出现“+”号，在接收时接收到的是空格，需要先将空格替换成“+”号
            $username = str_coding($key,'DECODE');
            // dump(str_coding('18674012767','ENCODE'));
            // halt($username);
            $row = $systemUserModel->where([['username','eq',$username]])->field('password,status')->find();
            if(!$row){
                return $this->error('权限不足！');
            }else{
                if($row['status'] != 1){
                    return $this->error('权限不足！');
                }
            }
            
            unset($data['key'], $data['password_confirm'],$data['token']);

            // 入库
            
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $find = $systemUserModel->where([['username','eq',$username]])->update($data);
            //halt($res);
            if (!$find) {
                return $this->error('设置失败');
            }
            return $this->success('设置成功','publics/index');
        }
        $key = input('get.key');
        $key = str_replace(" ","+",$key); //加密过程中可能出现“+”号，在接收时接收到的是空格，需要先将空格替换成“+”号
        $username = str_coding($key,'DECODE');
        // dump(str_coding('18674012767','ENCODE'));
        // halt($username);
        $row = $systemUserModel->where([['username','eq',$username]])->field('password,status')->find();
        if(!$row){
            return $this->error('权限不足！');
        }else{
            if($row['status'] != 1){
                return $this->error('权限不足！');
            }
        }
        $this->assign('key',$key);
        return $this->fetch();
    }

    public function sendMessage()
    {
        if ($this->request->isPost()) {
            $model = new UserModel;
            $type = $this->request->post('type/s');
            $username   = $this->request->post('username/s');
            $where = [];
            if($type == 1){ //如果是首次登录则
                $where[] = ['username','eq',$username];
                $where[] = ['status','eq',1];
                $row = $model->where($where)->find();
                if(!$row){
                    return $this->error('用户名不存在或被禁用');
                }else{
                    if($row['last_login_ip']){
                        return $this->error('您的账号已登录过，请选择登录或忘记密码！');
                    }
                }
            }
            if($type == 2){ //如果是首次登录则
                $where[] = ['username','eq',$username];
                $where[] = ['status','eq',1];
                $row = $model->where($where)->find();
                if(!$row){
                    return $this->error('用户名不存在或被禁用');
                }else{
                    if(!$row['password']){
                        return $this->error('您的账号尚未设置密码，请返回选择首次登录！');
                    }
                }
            }

            $todayBeginTime = strtotime(date('Y-m-d'));
            $todayFinishTime = strtotime(date('Y-m-d',strtotime('+ 1 day')));
            //dump($todayBeginTime);halt($todayFinishTime);
            $phoneWhere = [];
            $phoneWhere[] = ['phone','eq',$username];
            $phoneWhere[] = ['phone_use_type','eq',$type];
            $phoneWhere[] = ['ctime','between',[$todayBeginTime,$todayFinishTime]];
            $phoneModel = new PhoneUseRecord;
            $phoneUseTimes = $phoneModel->where($phoneWhere)->count('id');
           //halt($phoneUseTimes);
            if($phoneUseTimes > 4){
                return $this->error('当前账号本次操作使用短信，今日次数已达到上限5次！');
            }
            //通过类型判断是否超出当日短信发送限额！
            
            //验证通过即发送短信
            $auth = new ServerCodeAPI();
            $res = json_decode($auth->SendSmsCode($username));
            //halt($res);
            if($res->code == '416'){
                $this->error('验证次数过多，请更换登录方式');
            }
            $filtData = [
                'phone' => $username,
                'phone_use_type' => $type,
                'code' => $res->obj
            ];

            $phoneModel->allowField(true)->create($filtData);
            return $this->success('用户名验证成功');
        }
    }

    public function forget()
    {
        //验证是否可以设置密码
        if ($this->request->isPost()) {
            $model = new UserModel;
            $type = $this->request->post('type/s');
            $username   = $this->request->post('username/s');
            $data       = [];
            $code   = $this->request->post('code/d');

            $row = $model->where([['username','eq',$username],['status','eq',1],['role_id','in',[1,2]]])->find();
            if(!$row){
                return $this->error('用户名不存在或被禁用');
            }else{
                if(!$row['password']){
                   return $this->error('当前账号尚未登录，请点击首次登录！'); 
                }
            }


            $auth = new ServerCodeAPI();
            
            $res = $auth->CheckSmsYzm($username, $code);
            $res = json_decode($res);
            // 验证短信码是否正确
            if($res->code == '200'){ 
                $key = str_coding($username,'ENCODE');
                return $this->success('验证码验证成功', url('publics/setPassword'),['key'=>$key]);
            } else if($res->code == '413'){
                return $this->error('验证失败');
            } else {
                return $this->error('请重新获取');
            }

        }
        return $this->fetch();
    }

    /**
     * 登录页面
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function index_old()
    {
        $model = new UserModel;
        $loginError = (int)session('admin_login_error');
        
        if ($this->request->isPost()) {
            $type = $this->request->post('type/s');
            $username   = $this->request->post('username/s');
            $password   = $this->request->post('password/s');

            //$captchaObj = new Captcha();
            // $username   = $this->request->post('username/s');
            // $password   = $this->request->post('password/s');
            //$captcha    = $this->request->post('captcha/s');
            $data       = [];

            /* if ($loginError >= 3) {

                if (empty($captcha)) {
                    return $this->error('请输入验证码');
                }

                if (!captcha_check($captcha)) {
                    return $this->error('验证码错误');
                }
            } */
            
            if (!$model->login($username, $password)) {

                $loginError = ($loginError+1);
                session('admin_login_error', $loginError);

                $data['token'] = $this->request->token();
                //$data['captcha'] = $loginError >= 3 ? captcha_src() : '';

                return $this->error($model->getError(), url('index'), $data);

            }

            session('admin_login_error', 0);
            
            return $this->success('登录成功，页面跳转中...', url('index/index'));

        }

        if ($model->isLogin()) {
            $this->redirect(url('index/index', '', true, true));
        }

        $this->view->engine->layout(false);
        
        $this->assign('loginError', $loginError);

        return $this->fetch();
    }

    /**
     * 退出登录
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function logout(){
        model('SystemUser')->logout();
        $this->redirect(ROOT_DIR);
    }


    /**
     * 图标选择
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function icon() {
        return $this->fetch();
    }

    /**
     * 解锁屏幕
     * @author Lucas <598936602@qq.com>
     * @return mixed
     */
    public function unlocked()
    {
        $_pwd = $this->request->post('password/s');
        $model = model('SystemUser');
        $login = $model->isLogin();
        
        if (!$login) {
            return $this->error('登录信息失效，请重新登录！');
        }

        $password = $model->where('id', $login['uid'])->value('password');
        if (!$password) {
            return $this->error('登录异常，请重新登录！');
        }

        if (!password_verify($_pwd, $password)) {
            return $this->error('密码错误，请重新输入！');
        }

        return $this->success('解锁成功');
    }

}

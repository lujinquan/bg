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
namespace app\system\model;

use think\Model;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemLog as LogModel;

/**
 * 后台用户模型
 * @package app\system\model
 */
class SystemUser extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = 'mtime';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $type = [
        'ctime' => 'timestamp:Y-m-d H:i',
        'mtime' => 'timestamp:Y-m-d H:i',
        'guard' =>  'json',
        //'jsondata' =>  'json',
    ];

    public function setAuthAttr($value)
    {
        if (empty($value)) return '';
        return json_encode($value);
    }

    public function getAuthAttr($value)
    {
        if (empty($value)) return [];
        return json_decode($value, 1);
    }

    // 获取最后登录ip
    // public function setLastLoginIpAttr($value)
    // {
    //     return get_client_ip();
    // }

    // 格式化最后登录时间
    public function getLastLoginTimeAttr($value)
    {
        return date('Y-m-d H:i', $value);
    }

    // 格式化授权项目
    public function getCtimeAttr($value)
    {
        return date('Y-m-d H:i', $value);
    }

    // 格式化授权时间
    public function getProIdsAttr($value)
    {
        return explode(',', $value);
    }

    // 格式化授权项目
    public function setProIdsAttr($value)
    {
        //return (strpos($value,',') === false) ? $value : implode(',', $value);
        return implode(',', $value);
    }

    // 权限
    public function role()
    {
        return $this->hasOne('SystemRole', 'id', 'role_id');
    }

    // 权限
    public function group()
    {
        return $this->belongsTo('SystemGroup', 'group_id', 'group_id')->bind('group_name');
    }

    /**
     * 删除用户
     * @param string $id 用户ID
     * @author Lucas <598936602@qq.com>
     * @return bool
     */
    public function del($id = 0) 
    {
        $menu_model = new MenuModel();
        if (is_array($id)) {
            $error = '';
            foreach ($id as $k => $v) {
                if ($v == ADMIN_ID) {
                    $error .= '不能删除当前登录的用户['.$v.']！<br>';
                    continue;
                }

                if ($v == 1) {
                    $error .= '不能删除超级管理员['.$v.']！<br>';
                    continue;
                }

                if ($v <= 0) {
                    $error .= '参数传递错误['.$v.']！<br>';
                    continue;
                }

                $map = [];
                $map['id'] = $v;
                // 删除用户
                self::where($map)->delete();
                // 删除关联表;
                $menu_model->delUser($v);
            }

            if ($error) {
                $this->error = $error;
                return false;
            }
        } else {
            $id = (int)$id;
            if ($id <= 0) {
                $this->error = '参数传递错误！';
                return false;
            }

            if ($id == ADMIN_ID) {
                $this->error = '不能删除当前登录的用户！';
                return false;
            }

            if ($id == 1) {
                $this->error = '不能删除超级管理员！';
                return false;
            }

            $map = [];
            $map['id'] = $id;
            // 删除用户
            self::where($map)->delete();
            // 删除关联表
            $menu_model->delUser($id);
        }

        return true;
    }

    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $remember 记住登录 TODO
     * @author Lucas <598936602@qq.com>
     * @return bool|mixed
     */
    public function login($username = '', $password = '', $remember = false)
    {
        $username = trim($username);
        $password = trim($password);
        //dump($password);halt(md5(''));
        if($password == md5('')){
            $this->error = '请输入密码！';
            return false;
        }
        
        $map = [];
        $map['status'] = 1;
        $map['username'] = $username;
       
        $validate = new \app\system\validate\SystemUser;
        
        if ($validate->scene('login')->check(input('post.')) !== true) {
            $this->error = $validate->getError();
            return false;
        }

        $user = self::with('group')->where($map)->find();
        //halt($user);
        if (!$user) {
            $this->error = '账号不存在，请联系您所属企业管理员！';
            //$this->error = '用户不存在或被禁用！';
            return false;
        } 
        // 账号未设置过密码，则为首次登录
        if(!($user->password)){
            $this->error = '账号未激活，请点击首次登录！';
            return false;
        }
        // 密码校验
        if (!password_verify($password, $user->password)) {
            $this->error = '密码错误，请重新输入！';
            return false;
        }
        // 检查是否分配角色
        if ($user->role_id == 0) {
            $this->error = '禁止访问(原因：未分配角色)！';
            return false;
        }
        // 角色信息
        $role = RoleModel::where('id', $user->role_id)->find()->toArray();
        if (!$role || $role['status'] == 0) {
            $this->error = '禁止访问(原因：角色分组可能被禁用)！';
            return false;
        }
        
        // 账号是否被授予项目
        if(!($user->getData('pro_ids')) && ($user->role_id != 1)){
            $this->error = '账号未授权，请联系您所属企业管理员！';
            return false;
        }
        // 自动清除过期的系统日志
        LogModel::where('ctime', '<', strtotime('-'.(int)config('sys.system_log_retention').' days'))->delete();

        // 更新登录信息
        $user->last_login_time = time();
        $user->last_login_ip   = get_client_ip();
        if ($user->save()) {
            // 执行登录
            $login = [];
            $login['uid'] = $user->id;
            $login['role_id'] = $user->role_id;
            $login['pro_ids'] = $user->pro_ids;
            $login['group_id'] = $user->group_id;
            $login['group_name'] = $user->group_name;
            $login['role_name'] = $role['name'];
            $login['post'] = $user->post;
            $login['nick'] = $user->nick;
            cookie('hisi_iframe', (int)$user->iframe);
            // 主题设置
            self::setTheme(isset($user->theme) ? $user->theme : 0);
            self::getThemes(true);
            // 缓存角色权限
            session('role_auth_'.$user->role_id, $user->auth ? $user->auth : $role['auth']);
            // 缓存登录信息
            session('admin_user', $login);
            session('admin_user_sign', $this->dataSign($login));
            // 缓存用户表基础数据
            $users = $this->with('role')->where([['status','eq','1']])->select();
            $usersArr = [];
            foreach($users as $v){
                $usersArr[$v['id']] = $v;
            }
            //halt($users);
            session('systemusers',$usersArr);
            
            return $user->id;
        }
        return false;
    }

    /**
     * 短信登录
     * @param string $username 用户名
     * @author Lucas <598936602@qq.com>
     * @return bool|mixed
     */
    public function loginPhone($username = '')
    {
        $username = trim($username);
        $map = [];
        $map['status'] = 1;
        $map['username'] = $username;

        $validate = new \app\system\validate\SystemUser;
        
        // if ($validate->scene('login')->check(input('post.')) !== true) {
        //     $this->error = $validate->getError();
        //     return false;
        // }
        
        $user = self::where($map)->find();
        if (!$user) {
            $this->error = '账号不存在，请联系您所属企业管理员！';
            //$this->error = '用户不存在或被禁用！';
            return false;
        }

        // 密码校验
        // if (!password_verify($password, $user->password)) {
        //     $this->error = '登录密码错误！';
        //     return false;
        // }

        // 检查是否分配角色
        if ($user->role_id == 0) {
            $this->error = '禁止访问(原因：未分配角色)！';
            return false;
        }

        // 角色信息
        $role = RoleModel::where('id', $user->role_id)->find()->toArray();
        if (!$role || $role['status'] == 0) {
            $this->error = '禁止访问(原因：角色分组可能被禁用)！';
            return false;
        }

        // 自动清除过期的系统日志
        LogModel::where('ctime', '<', strtotime('-'.(int)config('sys.system_log_retention').' days'))->delete();

        // 更新登录信息
        $user->last_login_time = time();
        $user->last_login_ip   = get_client_ip();
        if ($user->save()) {
            // 执行登录
            $login = [];
            $login['uid'] = $user->id;
            $login['role_id'] = $user->role_id;
            $login['role_name'] = $role['name'];
            $login['group_id'] = $user->group_id;
            $login['nick'] = $user->nick;
            $login['pro_ids'] = $user->pro_ids;
            $login['post'] = $user->post;
            cookie('hisi_iframe', (int)$user->iframe);
            // 主题设置
            self::setTheme(isset($user->theme) ? $user->theme : 0);
            self::getThemes(true);
            // 缓存角色权限
            session('role_auth_'.$user->role_id, $user->auth ? $user->auth : $role['auth']);
            // 缓存登录信息
            session('admin_user', $login);
            session('admin_user_sign', $this->dataSign($login));
            return $user->id;
        }
        return false;
    }

    /**
     * 获取主题列表
     * @author Lucas <598936602@qq.com>
     * @return bool
     */
    public static function getThemes($cache = false)
    {
        $themeFile = '.'.config('view_replace_str.__ADMIN_CSS__').'/theme.css';
        $themes = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        if (is_file($themeFile)) {
            $content = file_get_contents($themeFile);
            preg_match_all("/\/\*{6}(.+?)\*{6}\//", $content, $diyTheme);
            if (isset($diyTheme[1]) && count($diyTheme[1]) > 0) {
                foreach ($diyTheme[1] as $v) {
                    if (preg_match("/^[A-Za-z0-9\-\_]+$/", trim($v))) {
                        array_push($themes, trim($v));
                    }
                }
                $themes = array_unique($themes);
            }
        }
        if ($cache) {
            session('hisi_admin_themes', $themes);
        }
        return $themes;
    }

    /**
     * 设置主题
     * @author Lucas <598936602@qq.com>
     * @return bool
     */
    public static function setTheme($name = 'default', $update = false)
    {
        cookie('hisi_admin_theme', $name);
        $result = true;
        if ($update && defined('ADMIN_ID')) {
            $result = self::where('id', ADMIN_ID)->setField('theme', $name);
        }
        return $result;
    }

    /**
     * 判断是否登录
     * @author Lucas <598936602@qq.com>
     * @return bool|array
     */
    public function isLogin() 
    {
        $user = session('admin_user');
        if (isset($user['uid'])) {
            if (!self::where('id', $user['uid'])->find()) {
                return false;
            }
            return session('admin_user_sign') == $this->dataSign($user) ? $user : false;
        }
        return false;
    }

    /**
     * 退出登录
     * @author Lucas <598936602@qq.com>
     * @return bool
     */
    public function logout() 
    {
        session('admin_user', null);
        session('admin_user_sign', null);
        session('curr_project_id', null); 
    }

    /**
     * 数据签名认证
     * @param array $data 被认证的数据
     * @author Lucas <598936602@qq.com>
     * @return string 签名
     */
    public function dataSign($data = [])
    {
        if (!is_array($data)) {
            $data = (array) $data;
        }
        ksort($data);
        $code = http_build_query($data);
        $sign = sha1($code);
        return $sign;
    }

    // /**
    //  * 用户状态设置
    //  * @param string $id 用户ID
    //  * @return bool
    //  */
    // public function status($id = '', $val = 0) {
    //     if (is_array($id)) {
    //         $error = '';
    //         foreach ($id as $k => $v) {
    //             $v = (int)$v;
    //             if ($v == 1) {
    //                 $error .= '禁止更改超级管理员状态['.$v.']<br>';
    //                 continue;
    //             }

    //             $map = [];
    //             $map['id'] = $v;
    //             // 删除用户
    //             self::where($map)->setField('status', $val);
    //         }

    //         if ($error) {
    //             $this->error = $error;
    //             return false;
    //         }
    //     } else {
    //         $id = (int)$id;
    //         if ($id <= 0) {
    //             $this->error = '参数传递错误';
    //             return false;
    //         }

    //         if ($id == 1) {
    //             $this->error = '禁止更改超级管理员状态';
    //             return false;
    //         }

    //         $map = [];
    //         $map['id'] = $id;
    //         // 删除用户
    //         self::where($map)->setField('status', $val);
    //     }

    //     return true;
    // }
}

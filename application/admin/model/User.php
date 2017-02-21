<?php
namespace app\admin\model;
use think\Model;
use think\Request;
class User extends Model{
    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    //设置当前模型对应的完整数据表名称
    protected $table = 'wx_user';
    //指定主键
    protected $pk = 'user_id';
    /**
     * 登录
     * @param  [type]  $loginName [登录名]
     * @param  [type]  $password  [密码]
     * @param  integer $type      [类型 1-用户名，2-用户邮箱，3-用户电话]
     * @return [type]             [description]
     */
    public function login($loginName, $password, $type = 1)
    {
        $map = array();
        switch ($type) {
            case 1:
                $map['user_name'] = $loginName;
                break;
            case 2:
                $map['email'] = $loginName;
                break;
            case 3:
                $map['mobile'] = $loginName;
                break;
            default:
                return 0; //参数错误
        }
        /* 获取用户数据 */
        $user = $this->get($map);
        if($user){
            /* 验证用户密码 */
            if(wxEncrypt($password) === $user->password){
                $this->updateLogin($user->user_id); //更新用户登录信息
                return $user->user_id; //登录成功，返回用户ID
            } else {
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }
    }
    /**
     * 更新用户登录信息
     * @param  [type] $userId [description]
     * @return [type]         [description]
     */
    public function updateLogin($userId)
    {
        $data = array(
            'user_id'              => $userId,
            'last_login_time' => time(),
            'last_login_ip'   => Request::instance()->ip(),
        );
        $this->update($data);
    }
    /**
     * 注册
     * @param  [type] $userName [description]
     * @param  [type] $password [description]
     * @param  [type] $email    [description]
     * @param  [type] $mobile   [description]
     * @return [type]           [description]
     */
    public function register($userName, $password, $email, $mobile, $regIp='')
    {
        $data = array(
            'user_name' => $userName,
            'password' => $password,
            'email'    => $email,
            'mobile'   => $mobile,
            'reg_ip'   => $regIp
        );
        /* 添加用户 */
        if($this->validate(true)->save($data)){
            $userId = $this->user_id;
            return $userId ? $userId : 0; //0-未知错误，大于0-注册成功
        } else {
            return $this->getError(); //错误详情见自动验证注释
        }
    }
    /**
     * 获取用户列表
     * @return [type] [description]
     */
    public function getUserList()
    {   
        return $this->field('user_id,user_name')->paginate(10);
    }
    /**
     * 不要密码更新信息
     * @param  [type] $userId [description]
     * @param  [type] $data   [description]
     * @return [type]         [description]
     */
    public function updateUserFieldsNotCheck($userId,$data)
    {
        if(empty($userId) || empty($data)){
            $this->error = '参数错误';
            return false;
        }
        // 更新用户信息
        if($this->validate('User.edit')->save($data,['user_id'=>$userId])){
            return true;
        }else{
            return false;
        }
    }
}
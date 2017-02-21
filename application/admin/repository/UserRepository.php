<?php 
namespace app\admin\repository;
use app\admin\model\User;
use think\Request;
class UserRepository{

    protected $model;

    public function __construct()
    {
        $this->model = new User;
    }
    /**
     * 用户登录认证
     * @param  string  $userName 用户名
     * @param  string  $password 用户密码
     * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function login($userName, $password, $type = 1)
    {
        return $this->model->login($userName, $password, $type);
    }
    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @param  string $email    用户邮箱
     * @param  string $mobile   用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function register($userName, $password, $email = '', $mobile = ''){
        $regIp=Request::instance()->ip();
        $password=wxEncrypt($password);
        return $this->model->register($userName, $password, $email, $mobile, $regIp);
    }
    /**
     * 不要密码更新信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function updateInfoNotCheck($userId,$data)
    {
        if($this->model->updateUserFieldsNotCheck($userId, $data) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $return['info'] = $this->model->getError();
        }
        return $return;
    }

}
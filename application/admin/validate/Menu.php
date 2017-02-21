<?php
namespace app\admin\validate;
use think\Validate;

class Menu extends Validate{
    protected $rule = [
        'title'=>'require',
    ];

    protected $message = [
        'title.require'=>'菜单名称不能为空',
    ];

}
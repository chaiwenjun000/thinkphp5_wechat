<?php

namespace app\admin\model;

use think\Model;
/**
 * 基础模型
 */
class Base extends Model
{
    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
}

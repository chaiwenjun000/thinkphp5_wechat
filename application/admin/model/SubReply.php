<?php

namespace app\admin\model;

use think\Model;
/**
 * 关注回复
 */
class SubReply extends Base
{
    /**
     * 获取最新的内容
     * @return [type] [description]
     */
    public function getLast()
    {   
        return $this->order('id desc')->value('content');
    }
}

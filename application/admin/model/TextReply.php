<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class TextReply extends Base
{
    use SoftDelete;
    public function getText($id)
    {
        return $this->where('id',$id)->value('text');
    }
}

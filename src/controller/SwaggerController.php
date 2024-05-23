<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace dfer\controller;

class SwaggerController
{
    public function index()
    {
        if (is_dir(WEB_ROOT . 'plugins/swagger')) {
            return redirect(dfer_plugin_url('Swagger://Index/index'));
        } else {
            return 'please install swagger plugin!';
        }
    }

}

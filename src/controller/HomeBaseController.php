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

use think\facade\Db;
use app\admin\model\ThemeModel;

class HomeBaseController extends BaseController
{

    protected function initialize()
    {
        // 监听home_init
        hook('home_init');
        parent::initialize();
    }

    protected function _initializeView()
    {
        $cmfThemePath    = config('template.dfer_theme_path');
        $cmfDefaultTheme = dfer_get_current_theme();
        $root            = dfer_get_root();
        $themePath       = "{$cmfThemePath}{$cmfDefaultTheme}";
        //使cdn设置生效
        $cdnSettings = dfer_get_option('cdn_settings');
        if (empty($cdnSettings['cdn_static_root'])) {
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$root}/{$themePath}",
                '__STATIC__'   => "{$root}/static",
                '__WEB_ROOT__' => $root
            ];
        } else {
            $cdnStaticRoot  = rtrim($cdnSettings['cdn_static_root'], '/');
            $viewReplaceStr = [
                '__ROOT__'     => $root,
                '__TMPL__'     => "{$cdnStaticRoot}/{$themePath}",
                '__STATIC__'   => "{$cdnStaticRoot}/static",
                '__WEB_ROOT__' => $cdnStaticRoot
            ];
        }

        $this->view->engine()->config([
            'view_base'          => WEB_ROOT . $themePath . '/',
            'tpl_replace_string' => $viewReplaceStr
        ]);

//        $themeErrorTmpl = "{$themePath}/error.html";
//        if (file_exists_case($themeErrorTmpl)) {
//            config('dispatch_error_tmpl', $themeErrorTmpl);
//        }
//
//        $themeSuccessTmpl = "{$themePath}/success.html";
//        if (file_exists_case($themeSuccessTmpl)) {
//            config('dispatch_success_tmpl', $themeSuccessTmpl);
//        }


    }

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $config = [])
    {
        $template = $this->parseTemplate($template);

        $content        = $this->view->fetch($template, $vars, $config);
        $designingTheme = cookie('dfer_design_theme');

        if ($designingTheme) {
            $app        = $this->app->http->getName();
            $controller = $this->request->controller();
            $action     = $this->request->action();

            $output = <<<hello
<script>
var _themeDesign=true;
var _themeTest="test";
var _app='{$app}';
var _controller='{$controller}';
var _action='{$action}';
var _themeFile='{$more['_theme_file']}';
if(parent && parent.simulatorRefresh){
  parent.simulatorRefresh();
}
</script>
hello;

            $pos = strripos($content, '</body>');
            if (false !== $pos) {
                $content = substr($content, 0, $pos) . $output . substr($content, $pos);
            } else {
                $content = $content . $output;
            }
        }

        return $content;
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    protected function parseTemplate($template)
    {
        $siteInfo = dfer_get_site_info();
        $this->view->assign('site_info', $siteInfo);

        // 分析模板文件规则
        $request = $this->request;
        // 获取视图根目录
        if (strpos($template, '@')) {
            // 跨模块调用
            list($module, $template) = explode('@', $template);
        }

        $cmfThemePath    = config('template.dfer_theme_path');
        $cmfDefaultTheme = dfer_get_current_theme();
        $themePath       = WEB_ROOT . "{$cmfThemePath}{$cmfDefaultTheme}/";

        // 基础视图目录
        $module = isset($module) ? $module : $this->app->http->getName();
        $path   = $themePath . ($module ? $module . DIRECTORY_SEPARATOR : '');

        $depr = config('view.view_depr');
        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = dfer_parse_name($request->controller());
            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认规则定位
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . dfer_parse_name($request->action(true));
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }

        return $path . ltrim($template, '/') . '.' . ltrim(config('view.view_suffix'), '.');
    }

    public function checkUserLogin($isreurl = false)
    {
        $refer  = $this->request->server('HTTP_REFERER');
        $userId = dfer_get_current_user_id();
        if (empty($userId)) {
            if ($isreurl !== false) {
                $tourl = dfer_url('user/Login/index', ['redirect' => $refer]);
            } else {
                $tourl = dfer_url('user/Login/index');
            }
            if ($this->request->isAjax()) {
                $this->error('您尚未登录', $tourl);
            } else {
                $this->redirect($tourl);
            }
        }
    }

}

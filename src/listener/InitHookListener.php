<?php
// +---------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +---------------------------------------------------------------------
namespace dfer\listener;

use think\db\Query;
use think\facade\Event;
use think\facade\Db;
use think\facade\Route;

class InitHookListener
{
    private $app;
    // 行为扩展的执行入口必须是run
    public function handle($param)
    {
        Route::any('plugin/:_plugin/[:_controller]/[:_action]', "\\dfer\\controller\\PluginController@index");
        Route::get('new_captcha', "\\dfer\\controller\\CaptchaController@index");
        if (APP_DEBUG) {
            Route::get('swagger', "\\dfer\\controller\\SwaggerController@index");
        }

        if (!dfer_is_installed()) {
            return;
        }

        $systemHookPlugins = cache('init_hook_plugins_system_hook_plugins');
        if (empty($systemHookPlugins)) {
            try {
                $systemHooks = Db::name('hook')->where(function (Query $query) {
                    $query->where(function (Query $query) {
                        $query->where('app', '=', '')->whereOr('app', '=', 'cmf');
                    })->where('type', 3);
                })->whereOr('type', 1)->column('hook', 'id');

                $systemHookPlugins = Db::name('hook_plugin')->field('hook,plugin')->where('status', 1)
                    ->where('hook', 'in', $systemHooks)
                    ->order('list_order ASC')
                    ->select()->toArray();

                if (!empty($systemHookPlugins)) {
                    cache('init_hook_plugins_system_hook_plugins', $systemHookPlugins, null, 'init_hook_plugins');
                }
            } catch (\Exception $e) {
            }
        }

        if (!empty($systemHookPlugins)) {
            foreach ($systemHookPlugins as $hookPlugin) {
                $hookMethod  = dfer_parse_name($hookPlugin['hook'], 1, false);
                $eventName   = ucfirst($hookMethod);
                $pluginClass = dfer_get_plugin_class($hookPlugin['plugin']);
                Event::listen($eventName, [$pluginClass, $hookMethod]);
            }
        }

    }
}

<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------

$commands = [];
$app_path=app()->getAppPath();
if (PHP_SAPI == 'cli') {

    $commands = [
        'cli' => \dfer\console\command\Cli::class
    ];

    $apps = dfer_scan_dir($app_path . '*', GLOB_ONLYDIR);

    foreach ($apps as $app) {
        $commandFile = $app_path . $app . '/command.php';

        if (file_exists($commandFile)) {
            $mCommands = include $commandFile;
            if (is_array($mCommands)) {
                $commands = array_merge($commands, $mCommands);
            }
        }
    }

    $plugins = dfer_scan_dir(WEB_ROOT . '/plugins/*', GLOB_ONLYDIR);

    foreach ($plugins as $plugin) {
        $commandFile = WEB_ROOT . "/plugins/$plugin/command.php";

        if (file_exists($commandFile)) {
            $mCommands = include $commandFile;
            if (is_array($mCommands)) {
                $commands = array_merge($commands, $mCommands);
            }
        }
    }
}

return [
    // 指令定义
    'commands' => $commands,
];

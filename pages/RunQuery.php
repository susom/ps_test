<?php

namespace Stanford\PSTest;

/** @var \Stanford\PSTest\PSTest $module */

$module->emDebug($_POST);



if (isset($_POST['get_plugins'])) {
    if (!$_POST['plugin_name']) {
        die("No plugin_name set.");
    }
    $plugin_name = $_POST['plugin_name'];
    $module->emDebug("getting occurences of $plugin_name");
    $module->getPlugins($plugin_name);
    exit;
}

if (isset($_POST['check_det'])) {
    $module->emDebug("Checking DET");
    $module->getCountPlugins($file, $origin_pid);
    exit;
}

if (isset($_POST['check_an_det'])) {
    $module->emDebug("Checking DET in autonotify triggers");
    $module->getAutonotifyPlugins($file, $origin_pid, $first_ct);
    exit;
}
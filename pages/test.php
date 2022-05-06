<?php

namespace Stanford\PSTest;
/** @var \Stanford\PSTest\PSTest $module */

use REDCap;

$runUrl = $module->getUrl('pages/RunQuery.php', false, true);
?>
<form enctype="multipart/form-data" action="<?php echo $runUrl ?>" method="post" name="plugin-check" id="plugin-check">
    <h2>Run this</h2>
    <input type="text" name="plugin_name" id="plugin_name"  placeholder="Plugin Name">
    <input type="submit" id="get_plugins" name="get_plugins" value="Get plugin in Customization DET">
    <br><br>

    <input type="submit" id="check_det" name="check_det" value="Count of DET in Customizations">
    <br><br>
    <input type="submit" id="check_an_det" name="check_an_det" value="Get Autonotify Pre/Post Triggers">
</form>

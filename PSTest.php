<?php
namespace Stanford\PSTest;

use Stanford\PSTest\emLoggerTrait;

require_once "emLoggerTrait.php";

class PSTest extends \ExternalModules\AbstractExternalModule {

    use emLoggerTrait;



    public function getPlugins($plugin_name) {
        $return_array = null;
        $this->emDebug("Starting getPlugins");
        //echo "Starting getPlugins";

        $p_sql = sprintf("select '%s' as type,project_id, data_entry_trigger_url ".
                         "from redcap_projects where data_entry_trigger_url like '%%%s%%' ".
                         "and status != 2 and completed_time is null order by 2 desc;",
        prep($plugin_name),
        prep($plugin_name));

        $this->emDebug($p_sql);
        $q = db_query($p_sql);

        while ($row = db_fetch_assoc($q)) {
            $this->emDebug($row);
            $return_array[] = array(
                'type'                   => $row['type'],
                'project_id'             => $row['project_id'],
                'data_entry_trigger_url'=>$row['data_entry_trigger_url']
            );
        }

        $header = array(
            'type',
            'project_id',
            'data_entry_trigger_url'
        );
        if (!empty($return_array)) {
            $this->downloadCSVFile("plugin_by_name.csv", $header, $return_array);
        }


        $this->emDebug("Done with getPlugins");

    }

    public function getCountPlugins() {
        $this->emDebug("Starting getCountPlugins");
        //echo "Starting getCountPlugins";

        $gp_sql = "select distinct(data_entry_trigger_url), ".
            "count(data_entry_trigger_url) as count ".
            "from redcap_projects ".
            "where data_entry_trigger_url is not null ".
            "and status != 2 ".
            "and completed_time is null ".
            "group by data_entry_trigger_url ".
            "order by 2 desc";

        $this->emDebug($gp_sql);
        $q = db_query($gp_sql);

        while ($row = db_fetch_assoc($q)) {
            $this->emDebug($row);
            $return_array[] = array(
                'data_entry_trigger_url'=>$row['data_entry_trigger_url'],
                'count'                 =>$row['count']
            );
        }


        $header = array(
            'data_entry_trigger_url',
            'count'
        );
        $this->downloadCSVFile("CountPlugins.csv", $header, $return_array);
        $this->emDebug("Done with getCountPlugins");

    }

     function getAutonotifyPlugins() {
         $return_array = null;
        $this->emDebug("Starting getAutonotifyPlugins");
        //echo "Starting getAutonotifyPlugins";

        $an_sql = "SELECT l.project_id,rp.app_title, l.sql_log, l.description,max(l.ts) ".
            "FROM redcap_log_event l ".
            "inner join redcap_projects rp ".
            "on l.project_id = rp.project_id ".
            "where rp.status != 2 ".
            "and rp.completed_time is null ".
            "and l.description like '%Config%' and ".
            "l.sql_log  like'\{\"triggers%' and ".
            "(l.sql_log like  '%plugins%' OR ".
            "l.sql_log like  '\%data_edit\%') ".
            "and rp.data_entry_trigger_url is not null ".
            "and l.project_id in (".
            "'81', '73', '70','65',".
            "'238','1419','3547','3606','3619','3928','4046','4288','4304','4698','4730','4821','4864','5242', ".
            "'5581','5726','5733','5928','5987','6020','6136','6372','6440','6537','6700','6703','6723','6802','6896', ".
            "'6955','7154','7309','7351','7405','7476','7527','7565','7566','7606','7756','7768','7785','7884','7954', ".
            "'8158','8167','8216','8228','8229','8280','8339','8384','8443','8449','8606','8625','8670','8947','9026', ".
            "'9040','9044','9046','9055','9088','9206','9223','9263','9280','9322','9390','9465','9520','9548','9569', ".
            "'9605','9665','9666','9667','9674','9717','9797','9837','9871','9903','10010','10056','10170','10171', ".
            "'10214','10263','10301','10309','10392','10466','10495','10505','10518','10584','10589','10600','10682', ".
            "'10687','10767','10770','10778','11000','11059','11333','11367','11403','11416','11435','11464','11576', ".
            "'11583','11585','11586','11587','11704','11838','11916','12040','12091','12092','12385','12701','12713', ".
            "'12771','12851','12965','13150','13199','13344','13358','13368','13467','13532','13869','13871','13886', ".
            "'13887','13898','13914','13926','13972','14060','14124','14217','14228','14230','14240','14296','14315', ".
            "'14407','14424','14466','14467','14509','14530','14532','14579','14582','14600','14603','14622','14623', ".
            "'14667','14685','14688','14748','14786','14891','14979','15060','15063','15088','15134','15138','15198', ".
            "'15206','15288','15297','15304','15305','15365','15472','15649','15746','15778','15782','15794','15813','15823','15840','15848','15894','15913','15923','15925','15939','16030','16057','16185','16272','16444','16464') ".
            "group by l.project_id;";


         $this->emDebug($an_sql);
         $q = db_query($an_sql);

         while ($row = db_fetch_assoc($q)) {
             $pre = null;
             $post = null;

             $this->emDebug($row);
             // get json and spit out the pre and post
             if (isset($row['sql_log'])) {
                 $jarray = json_decode($row['sql_log'], true);
                 $pre = $jarray['pre_script_det_url'];
                 $post = $jarray['post_script_det_url'];

                 if((!empty($pre)) OR (!empty($post))) {
                     //we have a pre or post trigger set in autonotify, so add to return array
                     $return_array[] = array(
                         'project_id' => $row['project_id'],
                         'app_title'  => $row['app_title'],
                         'description'  => $row['description'],
                         'max_ts'  => $row['max(1.ts)'],
                         'pre_script_det_url'  => $pre,
                         'post_script_det_url'  => $post
                     );

                 }
             }
         }

         $header = array(
             'project_id',
             'app_title',
             'description',
             'max_ts',
             'pre_script_det_url',
             'post_script_det_url'
         );

         $this->downloadCSVFile("AutonotifyPlugins.csv", $header, $return_array);

        $this->emDebug("Done with getAutonotifyPlugins");

    }

    public function downloadCSVFile($filename='plugins.csv', $header, $return_array)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $header);
        foreach ($return_array as $row) {
            fputcsv($fp, $row);//, "\t", '"' );
        }

        fclose($fp);
    }
}


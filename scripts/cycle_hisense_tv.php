<?php
chdir(dirname(__FILE__) . '/../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES . 'hisense_tv/hisense_tv.class.php');
//include_once(DIR_MODULES . 'hisense_tv/lib/phpMQTT.php');
include_once(DIR_MODULES . 'hisense_tv/lib/MQTTClient.php');
$hisense_tv_module = new hisense_tv();
$hisense_tv_module->getConfig();
$total = $hisense_tv_module->initCycle();
if ($total == 0)
   exit; // no devices added -- no need to run this cycle
echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

$latest_check=0;
$checkEvery=0; // poll every 5 seconds
while (1)
{
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   if ((time()-$latest_check)>$checkEvery) {
    $latest_check=time();
    //echo date('Y-m-d H:i:s')." Polling devices...\n";
    $hisense_tv_module->processCycle();
   }
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }
   sleep(1);
}
$total = $hisense_tv_module->closeCycle();
$db->Disconnect();
DebMes("Unexpected close of cycle: " . basename(__FILE__));

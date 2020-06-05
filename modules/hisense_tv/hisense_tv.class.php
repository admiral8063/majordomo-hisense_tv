<?php
/**
* Hisense TV 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 21:10:20 [Oct 13, 2019])
*/
//
//
include_once(DIR_MODULES . 'hisense_tv/lib/MQTTClient.php');

class hisense_tv extends module {
/**
* hisense_tv
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="hisense_tv";
  $this->title="Hisense TV";
  $this->client_name = "Majordomo";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function api($params) {
    if ($_REQUEST['topic']) {
        $this->processMessage($_REQUEST['id'],$_REQUEST['topic'], $_REQUEST['msg']);
    }
}


function admin(&$out) {
 $this->getConfig();
 if (!gg('cycle_hisense_tvRun')) {
   setGlobal('cycle_hisense_tvRun',1);
 }
 if ((time() - gg('cycle_hisense_tvRun')) < 60 ) {
   $out['CYCLERUN'] = 1;
 } else {
   $out['CYCLERUN'] = 0;
 } 
 $out['API_URL']=$this->config['API_URL'];
 if (!$out['API_URL']) {
  $out['API_URL']='http://';
 }
 $out['API_KEY']=$this->config['API_KEY'];
 $out['API_USERNAME']=$this->config['API_USERNAME'];
 $out['API_PASSWORD']=$this->config['API_PASSWORD'];
 if ($this->view_mode=='update_settings') {
   global $api_url;
   $this->config['API_URL']=$api_url;
   global $api_key;
   $this->config['API_KEY']=$api_key;
   global $api_username;
   $this->config['API_USERNAME']=$api_username;
   global $api_password;
   $this->config['API_PASSWORD']=$api_password;
   $this->saveConfig();
   $this->redirect("?");
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='hisense_device' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_hisense_device') {
   $this->search_hisense_device($out);
  }
  if ($this->view_mode=='edit_hisense_device') {
   $this->edit_hisense_device($out, $this->id);
  }
  if ($this->view_mode=='delete_hisense_device') {
   $this->delete_hisense_device($this->id);
   $this->redirect("?data_source=hisense_device");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='hisense_device_data') {
  if ($this->view_mode=='' || $this->view_mode=='search_hisense_device_data') {
   $this->search_hisense_device_data($out);
  }
  if ($this->view_mode=='edit_hisense_device_data') {
   $this->edit_hisense_device_data($out, $this->id);
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* hisense_device search
*
* @access public
*/
 function search_hisense_device(&$out) {
  require(DIR_MODULES.$this->name.'/hisense_device_search.inc.php');
 }
/**
* hisense_device edit/add
*
* @access public
*/
 function edit_hisense_device(&$out, $id) {
  require(DIR_MODULES.$this->name.'/hisense_device_edit.inc.php');
 }
/**
* hisense_device delete record
*
* @access public
*/
 function delete_hisense_device($id) {
  $rec=SQLSelectOne("SELECT * FROM hisense_device WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM hisense_device WHERE ID='".$rec['ID']."'");
 }
/**
* hisense_device_data search
*
* @access public
*/
 function search_hisense_device_data(&$out) {
  require(DIR_MODULES.$this->name.'/hisense_device_data_search.inc.php');
 }
/**
* hisense_device_data edit/add
*
* @access public
*/
 function edit_hisense_device_data(&$out, $id) {
  require(DIR_MODULES.$this->name.'/hisense_device_data_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
  $this->getConfig();
   DebMes($object.".".$property."=".$value, 'hisense_tv'); 
   $table='hisense_device_data';
   $properties=SQLSelect("SELECT * FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
      $device_id = $properties[$i]["DEVICE_ID"];
      $table='hisense_device';
      $device=SQLSelectOne("SELECT * FROM $table WHERE ID=$device_id"); 
      
      $new = $value;
      $old = $properties[$i]['VALUE'];
      DebMes($device['IP']." ".$properties[$i]['TITLE']."=".$old, 'hisense_tv'); 
        
      if ($new != $old)
      {
          $name = "Majordomo";
                    
          if ($properties[$i]['TITLE'] == 'state')
          {
              if ($value == '1')
                  $this->wakeOnLan('192.168.0.255',$device['MAC']);
              else
                  $this->sendCommand($device['IP'],'/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_POWER');
          }
          if ($properties[$i]['TITLE'] == 'channel_num')
          {
                if ($new - $old == 1)
                    $this->sendCommand($device['IP'],'/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_CHANNELUP'); //ch+
                else if ($old - $new == 1)
                    $this->sendCommand($device['IP'],'/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_CHANNELDOWN'); //ch+
                else
                {
                    $client = $this->createClient($device['IP']);
                    $success = $client->sendConnect($name);  
                    if ($success)
                    {
                        $ch = strval($new);
                        $a = str_split($ch);
                        foreach ($a as $v) {
                            if ($v == 0) $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_0');// 0
                            else if ($v == 1) $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_1');// 1
                            else if ($v == 2) $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_2');// 2
                            else if ($v == 3) $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_3');// 3
                            else if ($v == 4) $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_4');// 4
                            else if ($v == 5) $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_5');// 5
                            else if ($v == 6) $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_6');// 6
                            else if ($v == 7) $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_7');// 7
                            else if ($v == 8) $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_8');// 8
                            else if ($v == 9) $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_9');// 9
                        }
                        $client->sendPublish('/remoteapp/tv/remote_service/'.$name.'/actions/sendkey','KEY_OK');
                    }
                   
                }
          }
          if ($properties[$i]['TITLE'] == 'volume_value')
          {
            $this->sendCommand($device['IP'],'/remoteapp/tv/platform_service/'.$name.'/actions/changevolume',$value);
          }
          if ($properties[$i]['TITLE'] == 'source')
          {
              if ($new == 'tv') $this->sendCommand($device['IP'],'/remoteapp/tv/ui_service/'.$name.'/actions/changesource','{"sourceid" : "0","sourcename" : "TV"}');
              else if ($new == 'av') $this->sendCommand($device['IP'],'/remoteapp/tv/ui_service/'.$name.'/actions/changesource','{"sourceid" : "1","sourcename" : "AV"}');
              else if ($new == 'hdmi1') $this->sendCommand($device['IP'],'/remoteapp/tv/ui_service/'.$name.'/actions/changesource','{"sourceid" : "4","sourcename" : "HDMI 1"}');
              else if ($new == 'hdmi2') $this->sendCommand($device['IP'],'/remoteapp/tv/ui_service/'.$name.'/actions/changesource','{"sourceid" : "5","sourcename" : "HDMI 2"}');
              else if ($new == 'youtube') $this->sendCommand($device['IP'],'/remoteapp/tv/ui_service/'.$name.'/actions/launchapp','{"name" : "YouTube","urlType" : 37,"storeType" : 0,"url" : "youtube"}');
              else if ($new == 'netflix') $this->sendCommand($device['IP'],'/remoteapp/tv/ui_service/'.$name.'/actions/launchapp','{"name" : "Netflix","urlType" : 37,"storeType" : 0,"url" : "netflix"}');
              else DebMes("Unknown source - ".$new, 'hisense_tv');
            
            $properties[$i]["UPDATED"] = date('Y-m-d H:i:s');
            $properties[$i]["VALUE"] = $new;
            SQLUpdate('hisense_device_data', $properties[$i]);
          }
      
      }
    }
   }
 }
 
 function sendCommand($host, $topic, $msg)
 {
    $client = $this->createClient($host);
    $name = "Majordomo";
    $success = $client->sendConnect($name);  // set your client ID
    if ($success)
    {
        DebMes("Send:".$host."->".$topic."=".$msg, 'hisense_tv');
        $client->sendPublish($topic, $msg);
    }
 }
 
 function initCycle()
 {
    $devices = SQLSelect("SELECT * FROM hisense_device");
    $total=count($devices);
    $this->mqtt_clients = array();
    for($i=0;$i<$total;$i++) {
        $id = $devices[$i]["ID"]; 
        $host = $devices[$i]["IP"]; 
        $client = $this->createClient($host);
        $this->mqtt_clients[$id]=$client;
    }
    return $total;
 }
 
  function closeCycle()
 {
    foreach ($this->mqtt_clients as $k => $mqtt_client)
    {
        $mqtt_client->close();
    }
 }
 
 function processCycle() {
 $this->getConfig();
  foreach ($this->mqtt_clients as $k => $mqtt_client)
    {
        if ($mqtt_client->isConnected())
        {
            //$mqtt_client->sendPublish('/remoteapp/tv/ui_service/AutoHTPC/actions/gettvstate',"");
            $messages = $mqtt_client->getPublishMessages();
            foreach ($messages as $message) {
                print_r($message);
                $this->procmsg($k, $message['topic'] , $message['message']);
                if ($message['qos']==1)
                    $mqtt_client->sendPubAck($message['packetId']);
            }
            if (count($messages)==0)
            {
                echo "Send ping\n";
                $mqtt_client->sendPing();
            }
        }
        else
        {
            echo date('Y-m-d H:i:s')." Connect...\n";
            $name = $this->client_name.substr(md5(rand()),0,5);
            $success = $mqtt_client->sendConnect($name);
            if ($success)
            {
                $data = array('state' => "1");
                $this->updateData($k, $data);
                $mqtt_client->sendSubscribe('#');
            }
            else
            {
                $data = array('state' => "0");
                $this->updateData($k, $data);
            }
        }
    }
 }
 
 function procmsg($id, $topic, $msg) {
    if (!isset($topic) || !isset($msg)) return false;

    echo date("Y-m-d H:i:s") . " Client:{$id} Topic:{$topic} $msg\n";
    //if (function_exists('callAPI')) {
    //    callAPI('/api/module/hisense_tv','GET',array('id' => $id, 'topic'=>$topic, 'msg'=>$msg));
    //} else {
        $this->processMessage($id, $topic, $msg);
    //}
}
 
 function updateData($id, $data)
 {
        $table_name='hisense_device_data';
        $values=SQLSelect("SELECT * FROM $table_name WHERE DEVICE_ID='$id'");
        foreach ($data as $key => $val)
        {
            echo $key."-".$val."\n";
            $value_ind = array_search($key, array_column($values, 'TITLE'));
            if ($value_ind !== False)
                $value = $values[$value_ind];
            else
                $value = array();
            //print_r($value);
            $value["TITLE"] = $key;
            $value["DEVICE_ID"] = $id;
            $value["UPDATED"] = date('Y-m-d H:i:s');
            if ($value['ID']) {
                if ($value["VALUE"] != $val)
                {   
                    $value["VALUE"] = $val;
                    if ($value['LINKED_OBJECT'] && $value['LINKED_PROPERTY']) 
                        setGlobal($value['LINKED_OBJECT'] . '.' . $value['LINKED_PROPERTY'], $val, array($this->name => '0'));
                    SQLUpdate($table_name, $value);
                }
            }
            else{
                $value["VALUE"] = $val;
                SQLInsert($table_name, $value);
            }
        }
 } 
 
function processMessage($id, $path, $value)
    {
        DebMes("Recv:$id<-$path=$value", 'hisense_tv'); 
            
        if ($path == '/remoteapp/mobile/broadcast/ui_service/state')
        {
            $tmp = json_decode($value, true);
            print_r ($tmp);
            if ($tmp["statetype"] == 'livetv')
            {
                $data['channel_num'] = $tmp['channel_num'];
                $data['channel_name'] = $tmp['channel_name'];
                $data['progname'] = $tmp['progname'];
                $data['detail'] = $tmp['detail'];
                $data['starttime'] = $tmp['starttime'];
                $data['endtime'] = $tmp['endtime'];
                $data['source'] = 'tv';
            }
            if ($tmp["statetype"] == 'app')
            {
                $data['app_name'] = $tmp['name'];
                $data['url'] = $tmp['url'];
                $data['source'] = $tmp['name'];
            }
            if ($tmp["statetype"] == 'mediadmp')
            {
                $data['media_name'] = $tmp['name'];
                $data['mediatype'] = $tmp['mediatype'];
                $data['playstate'] = $tmp['playstate'];
                $data['starttime'] = $tmp['starttime'];
                $data['curtime'] = $tmp['curtime'];
                $data['totaltime'] = $tmp['totaltime'];
                $data['source'] = 'media';
            }
            if ($tmp["statetype"] == 'sourceswitch')
            {
                $data['source_name'] = $tmp['displayname'];
                $data['sourceid'] = $tmp['sourceid'];
                $data['source'] = strtolower($tmp['sourcename']);
            }
            $data['statetype'] = $tmp['statetype'];
            $data['state'] = 1;
            $this->updateData($id,$data);
        }
            
        if ($path == '/remoteapp/mobile/broadcast/platform_service/actions/volumechange')
        {
            $tmp = json_decode($value, true);
            $data['volume_value'] = $tmp['volume_value'];
            $this->updateData($id,$data);
        }
        
        
        if ($path == '/remoteapp/mobile/broadcast/platform_service/actions/tvsleep')
        {
            $data['state'] = '0';
            $this->updateData($id,$data);
        }
        
        if ($path == '/remoteapp/tv/ui_service/Majordomo/actions/changesource')
        {
            //{"sourceid" : "0","sourcename" : "TV"}
            $data['sourceid'] = $tmp['sourceid'];
            $data['source'] = strtolower($tmp['sourcename']);
            $this->updateData($id,$data);
        }
            
    }
    
//     
function wakeOnLan($broadcast, $mac)
{
    DebMes("Wake on lan - ".$broadcast ." ". $mac, 'hisense_tv'); 
    $hwaddr = pack('H*', preg_replace('/[^0-9a-fA-F]/', '', $mac));
    
    // Create Magic Packet
    $packet = sprintf(
        '%s%s',
        str_repeat(chr(255), 6),
        str_repeat($hwaddr, 16)
    );

    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

    if ($sock !== false) {
        $options = socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, true);

        if ($options !== false) {
            socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, 7);
            socket_close($sock);
        }
    }
} 

function createClient($host)
{
    $client_name = "MajorDoMo";
    $username = 'hisenseservice';
    $password = 'multimqttservice';
    $port = 36669;
    
    $client = new karpy47\PhpMqttClient\MQTTClient($host, $port);
    //$client->setDebug(true); 
    $client->setAuthentication($username, $password);
    return $client;
}

/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS hisense_device');
  SQLExec('DROP TABLE IF EXISTS hisense_device_data');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
hisense_device - 
hisense_device_data - 
*/
  $data = <<<EOD
 hisense_device: ID int(10) unsigned NOT NULL auto_increment
 hisense_device: TITLE varchar(100) NOT NULL DEFAULT ''
 hisense_device: IP varchar(255) NOT NULL DEFAULT ''
 hisense_device: MAC varchar(255) NOT NULL DEFAULT ''
 hisense_device_data: ID int(10) unsigned NOT NULL auto_increment
 hisense_device_data: TITLE varchar(100) NOT NULL DEFAULT ''
 hisense_device_data: VALUE varchar(1024) NOT NULL DEFAULT ''
 hisense_device_data: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 hisense_device_data: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 hisense_device_data: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 hisense_device_data: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 hisense_device_data: UPDATED datetime
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgT2N0IDEzLCAyMDE5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/

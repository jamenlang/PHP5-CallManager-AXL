<?php

require_once "./vendor/autoload.php";

$ip = '192.168.128.50';

$URL    = "https://$ip:8443/axl"; // Prod CUCM

//downloaded from the administration portal of 192.168.128.50

$SCHEMA = "./schema/10.5/AXLAPI.wsdl";

//RIS is used for realtime registration status among other things.
$RISURL = "https://$ip:8443/realtimeservice2/services/RISService70";
$RISSCHEMA = "./schema/10.5/RISPOST.wsdl";

$USER   = 'SuperUser';
$PASS   = 'Don\'tShareMe';

try {
    $CUCM = new \CallmanagerAXL\Callmanager($URL, $SCHEMA, $USER, $PASS);
    $RISCUCM = new \CallmanagerAXL\Callmanager($RISURL, $RISSCHEMA, $USER, $PASS);
    $devices = $RISCUCM->get_registration_by_type('Any');
    //force a sync from 'AD Directory'
    $ldap = $CUCM->do_ldap_sync('AD Directory',true);
    //more data is available from list_all_phones_summary_by_site() below
    /*
    $phones = $CUCM->get_phone_names();
    foreach($phones as $pkid => $phone){
        $pkid = substr(substr($pkid, 1), 0, -1);
        var_dump($phone);
    }
    */
    $device_pools = $CUCM->get_device_pool_names();
    foreach($device_pools as $site){
            //get all devices for this site.
            $phone_data_array[] = $CUCM->list_all_phones_summary_by_site($site);
    }
    foreach($phone_data_array as $id => $sub_data){
        foreach($sub_data as $sub_id => $sub_sub_data){
            //var_dump($sub_id);
            if($sub_sub_data['name']){
                $name = $sub_sub_data['name'];
                $description = $sub_sub_data['description'];
                $model = $sub_sub_data['product'];
                $css = $sub_sub_data['callingSearchSpaceName']['_'];
                $device_pool = $sub_sub_data['devicePoolName']['_'];
                $device_location = $sub_sub_data['locationName']['_'];
                $owner = $sub_sub_data['ownerUserName']['_'];
                $pkid = substr(substr($sub_sub_data['uuid'], 1), 0, -1);
            }
            //var_dump($sub_sub_data);
        }
    }
} catch (\Exception $E) {
    echo "Error communicating with callmanager: {$E->getMessage()}".PHP_EOL;
}

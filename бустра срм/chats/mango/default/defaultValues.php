<?php

if (!isset($data['phoneId'])) {
    $data['phoneId'] = false;
}
if (!isset($data['phonesType'])) {
    $data['phonesType'] = false;
}
if (!isset($data['phone'])) {
    $data['phone'] = false;
}
if (!isset($data['phonesComment'])) {
    $data['phonesComment'] = false;
}
if (!isset($data['phonesExt'])) {
    $data['phonesExt'] = false;
}
if (!isset($data['phonesDefault'])) {
    $data['phonesDefault'] = false;
}
if (!isset($data['name'])) {
    $data['name'] = false;
}
if (!isset($data['office'])) {
    $data['office'] = false;
}
if (!isset($data['site'])) {
    $data['site'] = false;
}
if (!isset($data['org'])) {
    $data['org'] = false;
}
if (!isset($data['importance'])) {
    $data['importance'] = false;
}
if (!isset($data['comment'])) {
    $data['comment'] = false;
}
if (!isset($data['birthday'])) {
    $data['birthday'] = false;
}
if (!isset($data['sex'])) {
    $data['sex'] = false;
}
if (!isset($data['groupId'])) {
    $data['groupId'] = false;
}
if (!isset($data['groupName'])) {
    $data['groupName'] = false;
}
if (!isset($data['netId'])) {
    $data['netId'] = false;
}
if (!isset($data['net'])) {
    $data['net'] = false;
}
if (!isset($data['netUname'])) {
    $data['netUname'] = false;
}
if (!isset($data['mgrId'])) {
    $data['mgrId'] = false;
}
if (!isset($data['mgrId'])) {
    $data['mgrId'] = false;
}
if (!isset($data['mgr'])) {
    $data['mgr'] = false;
}
if (!isset($data['mgrUname'])) {
    $data['mgrUname'] = false;
}
if (!isset($data['inFavorites'])) {
    $data['inFavorit'] = false;
} else {
    foreach ($data['inFavorites'] as $inFovorite) {
        $data['inFavorit'][] = $inFovorite;
    }
}
if (!isset($data['customValueId'])) {
    $data['customValueId'] = false;
}
if (!isset($data['customFieldId'])) {
    $data['customFieldId'] = false;
}
if (!isset($data['customType'])) {
    $data['customType'] = false;
}
if (!isset($data['customText'])) {
    $data['customText'] = false;
}
if (!isset($data['customListEnumId'])) {
    $data['customListEnumId'] = false;
}
if (!isset($data['customListOrder'])) {
    $data['customListOrder'] = false;
}
if (!isset($data['customListName'])) {
    $data['customListName'] = false;
}
if (!isset($data['onError'])) {
    $data['contactOnError'] = 'skip';
} else {
    $data['contactOnError'] = 'duplicate';
}
if (!isset($data['contactId'])) {
    $data['contactId'] = false;
}















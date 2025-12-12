<?php

$cashierinfo->password = "";
$base_url = base_url();
$jsonstoreinfo = json_encode(compact(
    'base_url',
    'storeinfo',
    'orderinfo',
    'customerinfo',
    'iteminfo',
    'billinfo',
    'cashierinfo',
    'tableinfo',
    'settinginfo',
    'currency',
    'taxinfos',
    'page'
));

echo $jsonstoreinfo;

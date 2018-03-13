<?php
namespace tools;

/**根据身份证算出地址****/
class InfoValid {

    public function checkIdcard($idcard, $name) {
        $host = "http://jisusfzsm.market.alicloudapi.com";
        $path = "/idcardverify/verify";
        $method = "GET";
        $appcode = "130dd8e6d2f64ae8a70f710af3814185";
        $headers = [];
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "idcard={$idcard}&realname={$name}";
        $url = $host . $path . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $result = curl_exec($curl);
        $result = json_decode($result, true);
        $rdata = [];
        if($result['status']==0) {

        } else {
            
        }
    }

}
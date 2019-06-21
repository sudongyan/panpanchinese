<?php

namespace sdk;

class SASRsdk {

    static function http_curl_exec($url, $data, &$rsp_str, &$http_code, $method = 'POST', $timeout = 10, $cookie = array(), $headers = array())
    {
        $ch = curl_init();
        if (!curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1)) {
            return -1;
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (count($headers) > 0) {
            if (!curl_setopt($ch, CURLOPT_HTTPHEADER, $headers)) {
                return -1;
            }
        }

        if ($method != 'GET') {
            $data = is_array($data) ? http_build_query($data) : $data;
            if (!curl_setopt($ch, CURLOPT_POSTFIELDS, $data)) {
                echo 'http_curl_ex set postfields failed';
                return -1;
            }
        } else {
            $data = is_array($data) ? http_build_query($data) : $data;
            echo 'data (GET method) : ' . $data . "\n";
            if (strpos($url, '?') === false) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= $data;
        }

        if (!curl_setopt($ch, CURLOPT_URL, $url)) {
            return -1;
        }

        if (!curl_setopt($ch, CURLOPT_TIMEOUT, $timeout)) {
            return -1;
        }

        if (!curl_setopt($ch, CURLOPT_HEADER, 0)) {
            return -1;
        }

        $rsp_str = curl_exec($ch);
        if ($rsp_str === false) {
            var_dump(curl_error($ch));
            curl_close($ch);
            return -2;
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return 0;
    }

    static function formatSignString($host, $uri, $param, $requestMethod)
    {
        $tmpParam = array();
        ksort($param);
        foreach ($param as $key => $value) {
            array_push($tmpParam, str_replace("_", ".", $key) . "=" . $value);
        }
        $strParam = join("&", $tmpParam);
        $signStr = strtoupper($requestMethod) . $host . $uri . "?" . $strParam;
        return $signStr;
    }

    static function randstr($num)
    {
        $str = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
        str_shuffle($str);
        $name = substr(str_shuffle($str), 26, $num);
        return $name;
    }

    static function sendvoice($secretKey, $SecretId, $EngSerViceType, $SourceType, $URI, $VoiceFormat)
    {
        if (empty($secretKey)) {
            echo "secretKey can not be empty";
            return -1;
        }
        if (empty($SecretId)) {
            echo "SecretId can not be empty";
            return -1;
        }
        if (empty($EngSerViceType) || ($EngSerViceType != "8k" && $EngSerViceType != "16k")) {
            echo "EngSerViceType is not right";
            return -1;
        }
        if (empty($URI)) {
            echo "URI can not be empty";
            return -1;
        }
        if (empty($VoiceFormat) || ($VoiceFormat != "mp3" && $VoiceFormat != "wav")) {
            echo "VoiceFormat is not right";
            return -1;
        }
        $secret_key = $secretKey;
        $query_arr = array();
        $query_arr['Action'] = 'SentenceRecognition';
        $query_arr['SecretId'] = $SecretId;
        $query_arr['Timestamp'] = time();
        $query_arr['Nonce'] = substr($query_arr['Timestamp'], 0, 4);
        $query_arr['Version'] = '2018-05-22';
        $query_arr['ProjectId'] = 0;
        $query_arr['SubServiceType'] = 2;
        $query_arr['EngSerViceType'] = $EngSerViceType;
        $query_arr['SourceType'] = $SourceType;
        if ($query_arr['SourceType'] == 0) {
            $voice = $URI;
            $voice = urlencode($voice);
            $query_arr['Url'] = $voice;
        } else if ($query_arr['SourceType'] == 1) {
            $file_path = $URI;
            if (file_exists($file_path)) {
                //echo $file_path;
                $handle = fopen($file_path, "rb");
                $str = fread($handle, filesize($file_path));
                fclose($handle);
                $strlen = strlen($str);
                $str = base64_encode($str);
                $query_arr["Data"] = $str;
                $query_arr["DataLen"] = $strlen;
            } else {
                return -3;
            }
        }
        $query_arr['VoiceFormat'] = $VoiceFormat;
        $query_arr['UsrAudioKey'] = static::randstr(16);

        ksort($query_arr);

        $signStr = static::formatSignString('aai.tencentcloudapi.com', '/', $query_arr, 'POST');

        $sign = base64_encode(hash_hmac('sha1', $signStr, $secret_key, true));

        $query_arr['Signature'] = $sign;

        $url = 'https://aai.tencentcloudapi.com';
        $headers = array("Host:aai.tencentcloudapi.com", "Content-Type:application/x-www-form-urlencoded", "charset=UTF-8");
        $http_code = -1;
        $rsp_str = "";
        //$starttime = time();
        static::http_curl_exec($url, $query_arr, $rsp_str, $http_code, 'POST', 10, array(), $headers);
        //echo "ret : ".$ret."\n";
        //echo "http_code : ".$http_code."\n";
        //echo "rsp_str : ".$rsp_str."\n";
        //echo $rsp_str . "\n";
        //$endtime = time();
        //$cost = $endtime - $starttime;
        //echo "cost time: ".$cost."\n";
        $ret = \json_decode($rsp_str, 1);
        return $ret['Response'];
    }
}




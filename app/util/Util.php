<?php
declare(strict_types=1);

namespace Mcp\Util;

use Exception;

class Util
{
    public static function fillString($string, $targetlength)
    {
        while(strlen($string) < $targetlength)
        {
            $string = "0".$string;
        }
    
        return $string;
    }
    
    public static function left($str, $length)
    {
        return substr($str, 0, $length);
    }
    
    public static function right($str, $length)
    {
        return substr($str, -$length);
    }
    
    public static function generateToken($length): string
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $res = "";
        for($i = 0; $i < $length; $i++) {
            $index = random_int(0, strlen($chars) - 1);
            $res = $res.substr($chars, $index, 1);
        }
        return $res;
    }
    
    public static function getDataFromHTTP($url, $content = "", $requestTyp = "application/text")
    {
        try {
            if ($content != "") {
                return file_get_contents($url, true, stream_context_create(array('http' => array('header'  => 'Content-type: '.$requestTyp, 'method' => 'POST', 'timeout' => 0.5, 'content' => $content))));
            } else {
                return file_get_contents($url);
            }
        } catch (Exception $e) {
            echo "(HTTP REQUEST) error while conntect to remote server. : ".$url;
        }
    }
    
    public static function sendInworldIM($fromUUID, $toUUID, $fromName, $targetURL, $text)
    {
        $rawXML    =    "<?xml version=\"1.0\" encoding=\"utf-8\"?><methodCall><methodName>grid_instant_message</methodName><params><param><value><struct><member><name>position_x</name><value><string>0</string></value></member><member><name>position_y</name><value><string>0</string></value></member><member><name>position_z</name><value><string>0</string></value></member><member><name>to_agent_id</name><value><string>".$toUUID."</string></value></member><member><name>from_agent_session</name><value><string>00000000-0000-0000-0000-000000000000</string></value></member><member><name>im_session_id</name><value><string>".$fromUUID."</string></value></member><member><name>from_agent_name</name><value><string>".$fromName."</string></value></member><member><name>from_agent_id</name><value><string>".$fromUUID."</string></value></member><member><name>binary_bucket</name><value><string>AA==</string></value></member><member><name>region_handle</name><value><i4>0</i4></value></member><member><name>region_id</name><value><string>00000000-0000-0000-0000-000000000000</string></value></member><member><name>parent_estate_id</name><value><string>1</string></value></member><member><name>timestamp</name><value><string>".time()."</string></value></member><member><name>dialog</name><value><string>AA==</string></value></member><member><name>offline</name><value><string>AA==</string></value></member><member><name>from_group</name><value><string>FALSE</string></value></member><member><name>message</name><value><string>".$text."</string></value></member></struct></value></param></params></methodCall>";
        Util::getDataFromHTTP($targetURL, $rawXML, "text/xml");
    }
}

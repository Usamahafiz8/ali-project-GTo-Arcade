<?php
namespace VanguardLTE\Lib;
class Network
{
    public static function post($uri, $headers, $fields){

        $request = curl_init($uri);
        curl_setopt_array($request, array(
            CURLOPT_URL => $uri,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => $headers)
        );

        $response = curl_exec($request);
        curl_close($request);

        return $response;
    }

    public static function get($uri, $headers){

        $request = curl_init($uri);
        curl_setopt_array($request, array(
           CURLOPT_URL => $uri,
           CURLOPT_RETURNTRANSFER => 1,
           CURLOPT_HEADER => 0,
           CURLOPT_HTTPHEADER => $headers)
        );
        $response = curl_exec($request);
        curl_close($request);

        return $response;
    }

    public static function delete($uri, $headers, $fields){

        $request = curl_init($uri);
        curl_setopt_array($request, array(
           CURLOPT_URL => $uri,
           CURLOPT_RETURNTRANSFER => 1,
           CURLOPT_HEADER => 0,
           CURLOPT_HTTPHEADER => $headers,
           CURLOPT_POSTFIELDS => $fields,
           CURLOPT_CUSTOMREQUEST, "DELETE")
        );
        $response = curl_exec($request);
        curl_close($request);

        return $response;
    }
}

?>
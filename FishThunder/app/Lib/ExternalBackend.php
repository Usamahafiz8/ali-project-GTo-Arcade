<?php

namespace VanguardLTE\Lib;

class ExternalBackend {

    private static $ROOT_DOMAIN = "https://phantomcashier.com";
    private static $ROOT_URL = "/mobile/v1";

    private static $ROUTE_GET_USER_INFO = "/users/find/";
    private static $ROUTE_BET = "/bet";
    private static $ROUTE_WIN = "/win";
    private static $ROUTE_WHEEL = "/users/wheel";
    private static $ROUTE_WHEEL_WIN = "/users/wheel/win";
    private static $ROUTE_WIN_TO_BALANCE = "/win_to_balance";
    private static $ROUTE_JACKPOTSE = "/users/jackpots";


    public static function getUserInfo($user_id, $token) {

        $uri = self::$ROOT_DOMAIN.self::$ROOT_URL.self::$ROUTE_GET_USER_INFO.$user_id;

        return Network::get($uri, array('Authorization: '.$token, 'Content-Type: application/json'));
	}

    public static function bet($uuid, $user_id, $game_id, $game_unique_code, $amount, $token) {

        $uri = self::$ROOT_DOMAIN.self::$ROOT_URL.self::$ROUTE_BET;

        $data = array("uuid" => $uuid, "user_id" => $user_id, "game_id" => $game_id, "game_unique_code" => $game_unique_code, "amount" => $amount );

        $json_data = json_encode($data);

        return Network::post($uri, array('Authorization: '.$token, 'Content-Type: application/json'), $json_data);
	}

    public static function win($uuid, $user_id, $game_id, $game_unique_code, $amount, $event_id, $token) {

        $uri = self::$ROOT_DOMAIN.self::$ROOT_URL.self::$ROUTE_WIN;

        $data = array("uuid" => $uuid, "user_id" => $user_id, "game_id" => $game_id, "game_unique_code" => $game_unique_code, "amount" => $amount, "event_id" => $event_id );

        $json_data = json_encode($data);

        return Network::post($uri, array('Authorization: '.$token, 'Content-Type: application/json'), $json_data);
	}

    public static function wheel($token) {

        $uri = self::$ROOT_DOMAIN.self::$ROOT_URL.self::$ROUTE_WHEEL;

        return Network::get($uri, array('Authorization: '.$token, 'Content-Type: application/json'));
	}

    public static function wheelWin($token, $value){

        $uri = self::$ROOT_DOMAIN.self::$ROOT_URL.self::$ROUTE_WHEEL_WIN;

        $data = array("value" => $value);

        $json_data = json_encode($data);

        return Network::post($uri, array('Authorization: '.$token, 'Content-Type: application/json'), $json_data);
    }

    public static function winToBalance($uuid, $user_id, $id, $created_at, $token) {

        $uri = self::$ROOT_DOMAIN.self::$ROOT_URL.self::$ROUTE_WIN_TO_BALANCE;

        $data = array("uuid" => $uuid, "user_id" => $user_id, "id" => $id, "created_at" => $created_at );

        $json_data = json_encode($data);

        return Network::post($uri, array('Authorization: '.$token, 'Content-Type: application/json'), $json_data);
	}

    public static function jackpots($token) {

        $uri = self::$ROOT_DOMAIN.self::$ROOT_URL.self::$ROUTE_JACKPOTSE;

        return Network::get($uri, array('Authorization: '.$token, 'Content-Type: application/json'));
	}
}

?>
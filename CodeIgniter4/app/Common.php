<?php

use Config\App;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('getUserIdByToken')) {
    // 根据request head Authorization 获取用户ID
    function getUserIdByToken($Authorization)
    {
        try {
            list($Token) = sscanf($Authorization, 'Bearer %s');
            $appConfig = config(App::class); // 获取app/Config/App.php文件夹里变量
            $decoded = JWT::decode($Token, new Key($appConfig->jwt_key, 'HS256')); //HS256方式，这里要和签发的时候对应
            return $decoded->user_id;
        } catch (Exception $e) {  //其他错误
            log_message('error', $e->getMessage()); // 记录错误日志在 writable/logs 目录下，并根据配置文件中的设置进行轮转和管理。
            var_dump($e->getMessage());
        }
    }
}

if (!function_exists('array_diff_assoc2')) {
    /**
     * 指定格式两个二维数组比较差集, 只存在于array1,不存在于array2
     * @param $array1
     * @param $array2
     * @return array
     */
    // $arr1 = [
    // ['role_id'=>1,'perm_id'=>1],
    // ['role_id'=>1,'perm_id'=>2]
    // ];
    function array_diff_assoc2($array1, $array2)
    {
        $ret = array();
        foreach ($array1 as $k => $v) {
            # var_dump($v);
            $isExist = false;
            foreach ($array2 as $k2 => $v2) {
                if (empty(array_diff_assoc($v, $v2))) {
                    $isExist = true;
                    break;
                }
            }
            if (!$isExist) array_push($ret, $v);
        }
        return $ret;
    }
}

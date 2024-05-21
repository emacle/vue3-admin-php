<?php

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

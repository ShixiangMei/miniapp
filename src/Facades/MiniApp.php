<?php
/**
 * Created by PhpStorm.
 * User: mei
 * Date: 2018/3/14
 * Time: 15:28
 */
namespace Msx\MiniApp\Facades;

use Illuminate\Support\Facades\Facade;

class MiniApp extends Facade {
    protected static function getFacadeAccessor ()
    {
        return 'miniapp';
    }
}
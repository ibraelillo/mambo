<?php
/**
 * Created by PhpStorm.
 * User: ibra
 * Date: 21/07/2016
 * Time: 14:05
 */

namespace Mambo\Tests;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class Util
{
    /**
     * @param $name
     * @return Logger
     */
    static function createLogger($name)
    {
        $logger = new Logger($name);
        $handler = new StreamHandler(sprintf(__DIR__.'/../logs/%s.logs', $name));
        $logger->pushHandler($handler);

        return $logger;
    }

}
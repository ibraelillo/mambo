<?php
/**
 * Created by PhpStorm.
 * User: ibra
 * Date: 21/07/2016
 * Time: 13:34
 */

namespace Mambo\Loader;


interface JSLoaderInterface
{
    /**
     * Called to resolve module in filesystem
     *
     * @param $base
     * @param module $
     * @return mixed
     */
    public function resolve($base, $module);

    /**
     * Load module
     *
     * @param $module
     * @return mixed
     */
    public function load($module);
}
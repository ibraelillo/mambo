<?php
/**
 * Created by PhpStorm.
 * User: iespinosa
 * Date: 22/07/2016
 * Time: 14:50
 */

namespace Mambo\Loader;


class NullLoader implements JSLoaderInterface
{

    /**
     * Called to resolve module in filesystem
     *
     * @param $base
     * @param module $
     * @return mixed
     */
    public function resolve($base, $module)
    {
        // TODO: Implement resolve() method.
    }

    /**
     * Load module
     *
     * @param $module
     * @return mixed
     */
    public function load($module)
    {
        // TODO: Implement load() method.
    }
}
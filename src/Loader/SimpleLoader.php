<?php
/**
 * Created by PhpStorm.
 * User: iespinosa
 * Date: 22/07/2016
 * Time: 15:11
 */

namespace Mambo\Loader;


class SimpleLoader implements JSLoaderInterface
{
    
    protected $path;

    /**
     * Called to resolve module in filesystem
     *
     * @param $base
     * @param module $
     * @return mixed
     */
    public function resolve($base, $module)
    {
        dump($base, $module);

        return [ __DIR__, 'test.js'];
    }

    /**
     * Load module
     *
     * @param $module
     * @return mixed
     */
    public function load($module)
    {
        return 'print(__basePath)';
    }
    
    public function setPath($path)
    {
        $this->path = $path;
    }
}
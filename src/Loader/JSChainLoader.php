<?php
/**
 * Created by PhpStorm.
 * User: ibra
 * Date: 21/07/2016
 * Time: 13:36
 */

namespace Mambo\Loader;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class JSChainLoader implements JSLoaderInterface, LoggerAwareInterface
{
    /**
     * @var
     */
    protected $basePath;

    /**
     * @var array
     */
    protected $srcPath = null;

    /**
     * @var array
     */
    protected $autoload = [];


    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * JSChainLoader constructor.
     * @param $paths
     * @param $logger
     */
    public function __construct($basePath, $srcPath = null, array $vendors = [])
    {
        $this->basePath = $basePath;
        $this->srcPath = $srcPath;
        $this->vendors = $vendors;

        $this->scanAndResolve($this->srcPath, $this->srcPath);
        $keys = array_keys($this->autoload);

        foreach ($keys as &$mod) {
            $mod = '.'.$mod;
        }

        $this->autoload = array_combine($keys, $this->autoload);

        foreach($vendors as $path)
        {
            $files =  glob($path.'/*', GLOB_MARK);

            $this->addSlashes($path);

            foreach ($files as $file) {
                $this->scanAndResolve($file, $path);
            }
        }

        $this->logger = new NullLogger();
    }

    /**
     * @param $dir
     * @param string $removePrefix
     */
    public function scanAndResolve($dir, $removePrefix = '')
    {
        $files = glob($dir.'/*');

        foreach($files as $file)
        {
            if(in_array($file, ['.', '..']))
                continue;

            if(is_dir($file)){
                $this->scanAndResolve($file, $removePrefix);
            }

            if(is_file($file)){
                    $namespace = str_replace($removePrefix,'', str_replace('.js', '', realpath($file)));


                    if(basename($file) === 'index.js'){
                        $namespace = str_replace('/index', '', $namespace);
                    }

                    $this->registerNamespace($namespace, realpath($file));

            }

        }
    }

    public function registerNamespace($namespace, $resolve_to)
    {
        $this->autoload[$namespace] = $resolve_to;
    }


    /**
     * Called to resolve module in filesystem
     *
     * @param $base
     * @param module $
     * @return mixed
     */
    public function resolve($base, $module)
    {
        $results = [];

        $file = $module;

        if(preg_match('/\.js$/', $module))
            $module = str_replace('.js', '', $module);

        $path = null;

        if(isset($this->autoload[$module]))
            $path = realpath(dirname($this->autoload[$module]));

        return [ $path, basename($this->autoload[$module]) ];
    }

    /**
     * Load module
     *
     * @param $module
     * @return mixed
     */
    public function load($module)
    {
        $this->logger->info($module);

        return file_get_contents($module);
    }

    /**
     * @param $path
     * @return string
     */
    protected function addSlashes(&$path)
    {
        if(!in_array(substr($path, -1,1), ['/', '\\', DIRECTORY_SEPARATOR]))
            $path .= '/';

        return $path;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
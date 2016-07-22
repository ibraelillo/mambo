<?php
/**
 * Created by PhpStorm.
 * User: ibra
 * Date: 06/07/2016
 * Time: 07:31
 */

namespace Mambo;

use Mambo\helpers\Process;
use Mambo\Loader\JSLoaderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class JS
 * @package Mambo
 */
class JS implements LoggerAwareInterface
{
    /**
     * @var \V8Js
     */
    protected $v8;

    /**
     * @var JSLoaderInterface
     */
    protected $loader;

    /**
     *
     * Path where to find modules
     *
     * @var string
     *
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $cache_dir;

    /**
     * @var array
     */
    protected $helpers = [];

    /**
     * Dependencies array of scripts to execute when creating blob binary context
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * Application name. Used in context of v8 to pipe php objects
     * Ex:
     *     $v8 = new \V8Js('MyApp')
     *     $v8->logger = new Monolog\Logger();
     *
     *      then in javascript when can use:
     *
     *     MyApp.logger.info("test");
     *
     * @var string
     */
    protected $appName = 'PHP';


    /**
     * JS constructor.
     *
     *
     * @param $appName
     * @param $basePath
     * @param JSLoaderInterface $loader
     * @param null $cache_dir
     */
    public function __construct($appName, $basePath, JSLoaderInterface $loader, $cache_dir = null)
    {
        $this->setAppName($appName);
        $this->setBasePath($basePath);
        $this->setLoader($loader);
        $this->setCachedir($cache_dir);

        $this->logger = new NullLogger();

        $this->dependencies = [
            'process' => [
                'code' => file_get_contents(__DIR__.'/extensions/process.js'),
                'enabled' => true,
                'dependencies' => []
            ],
            'path' => [
                'code' => file_get_contents(__DIR__.'/extensions/path.js'),
                'enabled' => true,
                'dependencies' => []
            ],
            'executionContext' => [
                'code' => sprintf("
                    var ExecutionContext = '%s';
                    ", json_encode($this->appName)
                ),
                'dependencies' => [],
                'enabled' => true
            ],
            '__basePath' => [
                'code' => sprintf("
                    var __basePath = '%s';
                    ", json_encode($this->basePath)
                ),
                'dependencies' => [],
                'enabled' => true
            ],
            'require' => [
                'code' => file_get_contents(__DIR__.'/extensions/require.js'),
                'dependencies' => [ '__basePath' ],
                'enabled' => true
            ]
        ];
    }

    /**
     *
     *
     * @param $jscode
     * @return string
     * @throws \Exception
     */
    public function execute($jscode, $scriptname = null)
    {
        if(!$this->v8)
            $this->createEngine();

        try{
            $result = null;
            ob_start(function($buffer) use (&$result){
                $result = $buffer;
                return null;
            });

            $this->v8->executeString($jscode, $scriptname, \V8Js::FLAG_FORCE_ARRAY);
            ob_end_clean();


            $e = $this->v8->getPendingException();

            if($e){
                throw $e;
            }

            return $result;
        }

        catch(\Exception $e){
            $this->logger->error($e->getMessage(), $e->getTrace());
            try{
                ob_end_clean();
            }
            catch(\Exception $x){
                $this->logger->error("Error: {$e->getMessage()}");
            }
            throw $e;
        }

    }

    /**
     *
     */
    public function iniContext()
    {
        /**
         * add extensions
         */
        foreach ($this->dependencies as $name => $dep)
        {
            \V8Js::registerExtension($name, $dep['code'], $dep['dependencies'], $dep['enabled']);
        }
    }

    /**
     * Create the v8 engine
     */
    protected function createEngine()
    {
        $this->iniContext();

        $this->v8 = new \V8Js(
            'global',
            [],
            array_keys($this->dependencies),
            true,
            $this->cache_dir ? file_get_contents(sprintf('%s/%s.bin', $this->cache_dir, $this->appName)): ''
        );

        $this->v8->process = new Process($this);

        $bp = $this->basePath;
        $logger = $this->logger;

        foreach ($this->getHelpers() as $name => $helper) {
            $this->v8->{$name} = $helper;
        }

        // plug logger
        $this->v8->logger = $this->logger;

        $this->v8->setModuleNormaliser(function($base, $module){
            return $this->loader->resolve($base, $module);
        });

        /*$this->v8->setModuleNormaliser(function($base, $module) {

            if($base == "")
                $base = $this->getBasePath();

            if(!in_array(substr($base, -1,1), ['/', '\\', DIRECTORY_SEPARATOR]))
                $base .= DIRECTORY_SEPARATOR;

            if(!preg_match('/\.js$/', $module))
                $module .= '.js';

            $dir = dirname($base.$module);
            $file = basename($module);
            $path =  realpath($dir);

            if(!$path){
                $this->logger->error("Module $module not found");
                throw new \Exception("Module $module not found");
            }

            $this->logger->info("Normalize $module", [
                'base'  => $base,
                'dir'   => $path,
                'file'  => $file
            ]);

            return [ $path, $file ];
        });
        */

        $this->v8->setModuleLoader(function($module) use($bp, $logger) {

            $this->logger->info("Loding module $module", [ 'loader' => true ]);

            return $this->loader->load($module);
        });



        $this->created = true;
    }

    /**
     * @param $name
     * @param $helper
     * @return $this
     */
    public function addHelper($name, $helper)
    {
        $this->helpers[$name] = $helper;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function generateBinaryContext()
    {
        $inicode = '';
        foreach($this->getDependencies() as $dep)
        {
            $inicode .= $dep['code'];
        }

        $this->generateBinaryContextFromSource($inicode);
    }


    /**
     * @param $source
     * @throws \Exception
     */
    public function generateBinaryContextFromSource($source)
    {

        try{

            //$this->iniContext();

            $binaryContent = \V8Js::createSnapshot($source);

            file_put_contents(sprintf('%s/%s.bin', $this->getCachedir(), $this->getAppName()), $binaryContent);
        }
        catch(\Exception $e){
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    /**
     * @return string
     */
    public function getCachedir()
    {
        return $this->cache_dir;
    }

    /**
     * @param string $cache_dir
     * @return JS
     */
    public function setCachedir($cache_dir)
    {
        $this->cache_dir = $cache_dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param string $basePath
     * @return JS
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * @return array
     */
    public function getHelpers()
    {
        return $this->helpers;
    }

    /**
     * @param array $helpers
     * @return JS
     */
    public function setHelpers($helpers)
    {
        $this->helpers = $helpers;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @param mixed $appName
     * @return JS
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;

        return $this;
    }


    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param array $dependencies
     * @return JS
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies += $dependencies;

        return $this;
    }

    /**
     * @return JSLoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @param $loader
     * @return $this
     */
    public function setLoader($loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * @return NullLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: iespinosa
 * Date: 22/07/2016
 * Time: 15:47
 */

namespace Mambo\helpers;


use Mambo\JS;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Process
 * @package Mambo\helpers
 */
class Process implements LoggerAwareInterface
{
    /**
     * @var
     */
    protected $app;

    /**
     * @var ProcessStdout
     */
    protected $stdout;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Process constructor.
     * @param JS $app
     */
    public function __construct(JS $app)
    {
        $this->app = $app;
        
        $this->logger = $app->getLogger();
        
        $this->stdout = (new ProcessStdout())->setLogger($this->logger);
    }


    public function getArchiInfo()
    {
        //$this->ar
    }

    /**
     * @return mixed
     */
    public function cwd()
    {
        return $this->app->getBasePath(); 
    }



    /**
     * @return mixed
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param mixed $app
     * @return Process
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }


    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger =  logger;
    }
}
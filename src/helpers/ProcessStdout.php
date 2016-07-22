<?php
/**
 * Created by PhpStorm.
 * User: iespinosa
 * Date: 22/07/2016
 * Time: 16:16
 */

namespace Mambo\helpers;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class ProcessStdout implements LoggerAwareInterface
{

    protected $logger;

    /**
     *
     */
    public function write()
    {
        foreach (func_get_args() as $arg) {
            try{
                $this->logger->info($arg);
            }catch (\Exception $e){
                dump($arg);
            }
        }
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

        return $this;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ibra
 * Date: 06/07/2016
 * Time: 07:33
 */

namespace Mambo\Tests;


use Mambo\JS;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class JSTest extends \PHPUnit_Framework_TestCase
{
    private function createLogger($name)
    {
        $logger = new Logger($name);
        $handler = new StreamHandler(__DIR__.'/../logs/test.logs');
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * @test
     */
    public function testBinaryContext()
    {

        $app  = new JS('PHP', __DIR__, [], [
            __DIR__.'/app.js'
        ]);

        $app->setCachedir(
            __DIR__
        );

        $app->generateBinaryContext();

        $this->assertFileExists(
            __DIR__.'/php.bin'
        );
    }

    /**
     *
     */
    public function testFailedBinaryContext()
    {
        $app  = new JS('PHP', __DIR__, [], [
            __DIR__.'/scripts/failed.js'
        ]);

        $app->setLogger($this->createLogger('PHP'));

        $app->setCachedir(
            __DIR__.'/failed'
        );

        try{

            $app->generateBinaryContext();
        }
        catch(\Exception $e){
            $this->assertFileNotExists(
                __DIR__.'/failed/php.bin'
            );
        }
    }


    /**
     * @depends testBinaryContext
     *
     * */
    public function testCreateEngine()
    {
        $app  = new JS('PHP', __DIR__, [], [
            __DIR__.'/app.js'
        ], __DIR__);

        $result = $app->execute("console.log('test');");

        $this->assertEquals('test', $result);
    }

    /**
     * @throws \Exception
     */
    public function testExecute()
    {
        $app = new JS('App', __DIR__);
        $app->setLogger(
            $this->createLogger($app->getAppName())
        );
        $res = null;

        try{
            $res = $app->execute("console.log('this fail');");
        }
        catch(\V8JsScriptException $e){
            $this->assertContains('ReferenceError: console is not defined', $e->getMessage());
            $this->assertNull($res);
        }
    }

    public function testCommonJsRequire()
    {
        $app  = new JS('PHP', __DIR__.'/scripts', [], []);

        $logger = new Logger($app->getAppName());
        $handler = new StreamHandler(__DIR__.'/../logs/test.logs');
        $logger->pushHandler($handler);

        $app->setLogger($logger);

        $result = $app->execute("require('test1')('test1');");

        $this->assertEquals('test1', $result);

        $app->execute('require("../app.js");');

        $result = $app->execute('require("./test2.js")("test2");');
        $this->assertEquals('test2', $result);

        $result = $app->execute('require("./internal/file1.js");');

        $this->assertEquals('file1', $result);


    }


    /**
     * @throws \Exception
     */
    public function testHelpers()
    {
        $logfile = __DIR__.'/../logs/test1.logs';

        $logger = new Logger('test');
        $handler = new StreamHandler($logfile);
        $logger->pushHandler($handler);

        $helper = function($text) use ($logger){
            $logger->info($text);
        };

        $app = new JS('Test', __DIR__);
        $app->addHelper('customLogger', $helper);
        $app->execute("Test.customLogger('test custom logger')");

        $this->assertFileExists($logfile);
        $this->assertContains('test custom logger', file_get_contents($logfile));

        // cleaning
        unlink($logfile);
    }


    public function testReactApp()
    {
        $app = new JS('PHP', __DIR__.'/scripts/react', []);
        $app->setLogger($this->createLogger('PHP'));

        $result = $app->execute("require('./server');");

        dump($result);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ibra
 * Date: 21/07/2016
 * Time: 13:40
 */

namespace Loader;


use Mambo\JS;
use Mambo\Loader\JSChainLoader;
use Mambo\Tests\Util;

class JSChainLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testResolve()
    {
        $basePath = __DIR__.'/scripts/app';
        $src = $basePath.'/src';
        $node_modules = $basePath.'/node_modules';
        $customLib = $basePath.'/lib';

        self::removeDirectory($basePath);
        mkdir($src, 0777, true);
        mkdir($node_modules, 0777, true);
        mkdir($customLib, 0777, true);


        $paths= [];
        $names = [];
        $files = [];

        for($i = 0; $i < 10; $i++)
        {
            if(mkdir($loaderPath = $node_modules.'/module'.$i, 0777, true)){

                $randomName = $loaderPath.'/'.sha1($i.'test').'.js';

                if(touch(sprintf('%s/index.js', $loaderPath)) and touch($randomName)){

                    file_put_contents($loaderPath.'/index.js', sprintf("print('%s')", $loaderPath.'/index.js'));
                    file_put_contents($randomName, sprintf("print('%s');", $randomName));


                    $files[] = $loaderPath.'/index.js';
                    $files[] = $randomName;

                    $names[] = basename($loaderPath);
                    $names[] = basename($loaderPath).'/'.basename($randomName);
                    $paths[] = $loaderPath;
                }
            }
        }

        $app = $src.'/app.js';
        file_put_contents($app, implode("\n", array_map(function(&$name){ return sprintf("require('%s');", $name); }, $names)));

        $chainLoader = new JSChainLoader($basePath, $src, [
            $node_modules,
            $customLib,
        ]);

        $app = new JS('Loader', __DIR__);
        $app->setLoader($chainLoader);

        $result = $app->execute("require('./app');");

        $this->assertEquals(implode("", $files), $result);

        self::removeDirectory($basePath);
    }

    public static function removeDirectory($path) {
        if(file_exists($path)){

            $files = glob($path . '/*');
            foreach ($files as $file) {
                is_dir($file) ? self::removeDirectory($file) : unlink($file);
            }
            rmdir($path);
            return;
        }
    }
}

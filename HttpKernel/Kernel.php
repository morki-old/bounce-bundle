<?php

namespace Morki\BounceBundle\HttpKernel;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

abstract class Kernel extends BaseKernel
{
    public static function handleRequest($environment = 'dev', $debug = true)
    {
        if (true === $debug)
            Debug::enable();

        $kernel = new static($environment, $debug);
        $kernel->loadClassCache();

        $request = Request::createFromGlobals();
        $response = $kernel->handle($request);
        $response->send();

        $kernel->terminate($request, $response);
    }

    public static function runCLI($environment = 'dev', $debug = true)
    {
        set_time_limit(0);
        $input = new ArgvInput;

        $environment = $input->getParameterOption(array('--env', '-e'), $environment);
        $debug = !$input->hasParameterOption(array('--no-debug', '')) && $environment !== 'prod';

        if ($debug)
            Debug::enable();

        $kernel = new static($environment, $debug);
        $application = new Application($kernel);
        $application->run($input);
    }

    public static function enableAPC($prefix, $loader)
    {
        $apc = new ApcClassLoader($prefix, $loader);

        $loader->unregister();
        $apc->register(true);
    }

    public function getConfigDir()
    {
        return $this->rootDir.'/config';
    }

    public function getVarDir()
    {
        return $this->rootDir.'/var';
    }

    public function getCacheDir()
    {
        return $this->getVarDir().'/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->getVarDir().'/log';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        foreach ($this->getConfigurationFiles() as $file)
        {
            $loader->load($file);
        }
    }

    public function getConfigurationFiles()
    {
        return array(
            $this->getConfigDir().'/config_'.$this->getEnvironment().'.yml'
        );
    }

    protected function getKernelParameters()
    {
        return array_merge(
            array(
                'kernel.var_dir' => $this->getVarDir(),
                'kernel.config_dir' => $this->getConfigDir(),
            ),
            parent::getKernelParameters()
        );
    }
}
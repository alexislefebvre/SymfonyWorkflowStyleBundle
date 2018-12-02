<?php

declare(strict_types=1);

namespace Acme\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles(): array
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \AlexisLefebvre\Bundle\SymfonyWorkflowStyleBundle\SymfonyWorkflowStyleBundle(),
            new \Acme\App\AcmeBundle(),
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config.yaml');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/AlexisLefebvre/SymfonyWorkflowStyleBundle/Acme/App/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/AlexisLefebvre/SymfonyWorkflowStyleBundle/Acme/App/logs';
    }
}

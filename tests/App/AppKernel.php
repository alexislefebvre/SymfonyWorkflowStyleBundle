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
            new \AlexisLefebvre\Bundle\SymfonyWorflowStyleBundle\SymfonyWorflowStyleBundle(),
            new \Acme\App\AcmeBundle(),
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/AlexisLefebvre/SymfonyWorflowStyleBundle/Acme/App/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/AlexisLefebvre/SymfonyWorflowStyleBundle/Acme/App/logs';
    }
}

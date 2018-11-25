<?php

declare(strict_types=1);

namespace Acme\App;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
    }
}

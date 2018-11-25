<?php

namespace Acme\Command;

use AlexisLefebvre\Bundle\SymfonyWorflowStyleBundle\Command\WorkflowDumpStyleCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class WorkflowDumpStyleCommandTest extends WebTestCase
{
    /** @var CommandTester $commandTester */
    public $commandTester;

    /** @var Application */
    public $application;

    public function setUp()
    {
        parent::setUp();

        $kernel = $this->createKernel();
        $kernel->boot();

        $this->application = new Application($kernel);

        $this->application->add(new WorkflowDumpStyleCommand());

        $command = $this->application->find('workflow:dump-with-style');

        $this->commandTester = new CommandTester($command);
    }

    public function testDump()
    {
        $this->commandTester->execute([]);

        $this->assertContains('Current page: 1', $this->commandTester->getDisplay());
    }
}

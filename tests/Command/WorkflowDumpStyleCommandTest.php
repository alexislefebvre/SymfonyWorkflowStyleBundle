<?php

namespace Acme\Command;

use AlexisLefebvre\Bundle\SymfonyWorflowStyleBundle\Command\WorkflowDumpStyleCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class WorkflowDumpStyleCommandTest extends WebTestCase
{
    /** @var CommandTester $commandTester */
    private $commandTester;

    /** @var Application */
    private $application;

    /** @var string */
    private $fixturesDirectory;

    public function setUp()
    {
        parent::setUp();

        $kernel = $this->createKernel();
        $kernel->boot();

        $this->application = new Application($kernel);

        $this->application->add(new WorkflowDumpStyleCommand());

        $command = $this->application->find('workflow:dump-with-style');

        $this->commandTester = new CommandTester($command);

        $this->fixturesDirectory = __DIR__.'/../fixtures/';
    }

    public function testDumpPuml()
    {
        $this->commandTester->execute([
            'name' => 'pull_request',
            '--dump-format' => 'puml',
        ]);

        $output = $this->commandTester->getDisplay();

        $expectedOutput = $this->getFixtureFileContent('puml/arrow/complex-state-machine-nomarking.puml');

        $this->assertSame($expectedOutput, $output);
    }

    public function testDumpDot()
    {
        $this->commandTester->execute([
            'name' => 'pull_request',
            '--dump-format' => 'dot',
        ]);

        $this->markTestSkipped('Dot is not implemented yet.');

        $output = $this->commandTester->getDisplay();

        $this->assertContains('Current page: 1', $output);
    }

    protected function getFixtureFileContent($filePath): string
    {
        $fullPath = $this->fixturesDirectory.$filePath;

        if (!file_exists($fullPath)) {
            $this->markTestIncomplete(
                sprintf(
                    'File “%s” doesn\'t exist.',
                    $fullPath
                )
            );
        }

        if (!is_readable($fullPath)) {
            $this->markTestIncomplete(
                sprintf(
                    'File “%s” isn\'t readable.',
                    $fullPath
                )
            );
        }

        return file_get_contents($fullPath);
    }
}

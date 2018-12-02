<?php

namespace Acme\Command;

use AlexisLefebvre\SymfonyWorkflowStyleBundle\Command\WorkflowDumpStyleCommand;
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

    public function testDumpDotSingleState()
    {
        $this->commandTester->execute([
            'name' => 'pull_request',
            '--dump-format' => 'dot',
        ]);

        $output = $this->commandTester->getDisplay();

        $expectedOutput = $this->getFixtureFileContent('dot/complex-state-machine-nomarking.gv');

        $this->assertSame($expectedOutput, $output);
    }

    public function testDumpDotMultipleState()
    {
        $this->commandTester->execute([
            'name' => 'article',
            '--dump-format' => 'dot',
        ]);

        $output = $this->commandTester->getDisplay();

        $expectedOutput = $this->getFixtureFileContent('dot/complex-multiple-state-machine-nomarking.gv');

        $this->assertSame($expectedOutput, $output);
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

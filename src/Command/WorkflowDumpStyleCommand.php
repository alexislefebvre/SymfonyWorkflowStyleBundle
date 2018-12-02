<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AlexisLefebvre\Bundle\SymfonyWorkflowStyleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\WorkflowDumpCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AlexisLefebvre\Bundle\SymfonyWorkflowStyleBundle\Symfony\Component\Workflow\Dumper\GraphvizDumper;
use AlexisLefebvre\Bundle\SymfonyWorkflowStyleBundle\Symfony\Component\Workflow\Dumper\PlantUmlDumper;
use AlexisLefebvre\Bundle\SymfonyWorkflowStyleBundle\Symfony\Component\Workflow\Dumper\StateMachineGraphvizDumper;
use Symfony\Component\Workflow\Marking;

/**
 * @final
 */
class WorkflowDumpStyleCommand extends WorkflowDumpCommand
{
    protected static $defaultName = 'workflow:dump-with-style';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Dump a workflow with style')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getKernel()->getContainer();
        $serviceId = $input->getArgument('name');

        if ($container->has('workflow.'.$serviceId)) {
            $workflow = $container->get('workflow.'.$serviceId);
            $type = 'workflow';
        } elseif ($container->has('state_machine.'.$serviceId)) {
            $workflow = $container->get('state_machine.'.$serviceId);
            $type = 'state_machine';
        } else {
            throw new InvalidArgumentException(sprintf('No service found for "workflow.%1$s" nor "state_machine.%1$s".', $serviceId));
        }

        if ('puml' === $input->getOption('dump-format')) {
            $transitionType = 'workflow' === $type ? PlantUmlDumper::WORKFLOW_TRANSITION : PlantUmlDumper::STATEMACHINE_TRANSITION;
            $dumper = new PlantUmlDumper($transitionType);
        } elseif ('workflow' === $type) {
            $dumper = new GraphvizDumper();
        } else {
            $dumper = new StateMachineGraphvizDumper();
        }

        $marking = new Marking();

        foreach ($input->getArgument('marking') as $place) {
            $marking->mark($place);
        }

        $options = array(
            'name' => $serviceId,
            'nofooter' => true,
            'graph' => array(
                'label' => $input->getOption('label'),
            ),
        );
        $output->writeln($dumper->dump($workflow->getDefinition(), $marking, $options));
    }
}

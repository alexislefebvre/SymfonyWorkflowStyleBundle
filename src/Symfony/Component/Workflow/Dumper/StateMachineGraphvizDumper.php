<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AlexisLefebvre\SymfonyWorkflowStyleBundle\Symfony\Component\Workflow\Dumper;

use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Metadata\GetMetadataTrait;
use Symfony\Component\Workflow\Metadata\MetadataStoreInterface;

class StateMachineGraphvizDumper extends GraphvizDumper
{
    use GetMetadataTrait;

    /** @var MetadataStoreInterface */
    protected $workflowMetadata;

    /**
     * {@inheritdoc}
     *
     * Dumps the workflow as a graphviz graph.
     *
     * Available options:
     *
     *  * graph: The default options for the whole graph
     *  * node: The default options for nodes (places)
     *  * edge: The default options for edges
     */
    public function dump(Definition $definition, Marking $marking = null, array $options = array())
    {
        $this->workflowMetadata = $definition->getMetadataStore();

        $places = $this->findPlaces($definition, $marking);
        $edges = $this->findEdges($definition);

        $options = array_replace_recursive(self::$defaultOptions, $options);

        return $this->startDot($options)
            .$this->addPlaces($places)
            .$this->addEdges($edges)
            .$this->endDot()
            ;
    }

    /**
     * @internal
     */
    protected function findEdges(Definition $definition)
    {
        $edges = array();

        foreach ($definition->getTransitions() as $transition) {
            $attributes = [];

            $transitionStyle = $this->getTransitionStyle($transition);
            $transitionName = $transitionStyle['label'] ?? $transition->getName();

            if (isset($transitionStyle['label_color'])) {
                $attributes['fontcolor'] = $transitionStyle['label_color'];
            }
            if (isset($transitionStyle['arrow_color'])) {
                $attributes['color'] = $transitionStyle['arrow_color'];
            }

            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    $edge = array(
                        'name' => $transitionName,
                        'to' => $to,
                        'attributes' => $attributes,
                    );
                    $edges[$from][] = $edge;
                }
            }
        }

        return $edges;
    }

    /**
     * @internal
     */
    protected function addEdges(array $edges)
    {
        $code = '';

        foreach ($edges as $id => $edges) {
            foreach ($edges as $edge) {
                $code .= sprintf(
                    "  place_%s -> place_%s [label=\"%s\" style=\"%s\"%s];\n",
                    $this->dotize($id),
                    $this->dotize($edge['to']),
                    $this->escape($edge['name']),
                    'solid',
                    $this->addAttributes($edge['attributes'])
                );
            }
        }

        return $code;
    }
}

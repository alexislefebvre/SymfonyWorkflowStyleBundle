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
use Symfony\Component\Workflow\Dumper\GraphvizDumper as BaseGraphvizDumper;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Metadata\MetadataStoreInterface;
use Symfony\Component\Workflow\Transition;

/**
 * GraphvizDumper dumps a workflow as a graphviz file.
 *
 * You can convert the generated dot file with the dot utility (http://www.graphviz.org/):
 *
 *   dot -Tpng workflow.dot > workflow.png
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class GraphvizDumper extends BaseGraphvizDumper
{
    protected static $defaultOptions = array(
        'graph' => array('ratio' => 'compress', 'rankdir' => 'LR'),
        'node' => array('fontsize' => 9, 'fontname' => 'Arial', 'color' => '#333333', 'fillcolor' => 'lightblue', 'fixedsize' => 'false', 'width' => 1),
        'edge' => array('fontsize' => 9, 'fontname' => 'Arial', 'color' => '#333333', 'arrowhead' => 'normal', 'arrowsize' => 0.5),
    );

    /**
     * {@inheritdoc}
     *
     * Dumps the workflow as a graphviz graph.
     *
     * Available options:
     *
     *  * graph: The default options for the whole graph
     *  * node: The default options for nodes (places + transitions)
     *  * edge: The default options for edges
     */
    public function dump(Definition $definition, Marking $marking = null, array $options = array())
    {
        $places = $this->findPlaces($definition, $marking);
        $transitions = $this->findTransitions($definition);
        $edges = $this->findEdges($definition);

        $options = array_replace_recursive(self::$defaultOptions, $options);

        return $this->startDot($options)
            .$this->addPlaces($places)
            .$this->addTransitions($transitions)
            .$this->addEdges($edges)
            .$this->endDot();
    }

    /**
     * @internal
     */
    protected function findPlaces(Definition $definition, Marking $marking = null)
    {
        $workflowMetadata = $definition->getMetadataStore();

        $places = array();

        foreach ($definition->getPlaces() as $place) {
            $attributes = array();
            if ($place === $definition->getInitialPlace()) {
                $attributes['style'] = 'filled';
            }
            if ($marking && $marking->has($place)) {
                $attributes['color'] = '#FF0000';
                $attributes['shape'] = 'doublecircle';
            }
            $style = $this->getPlaceStyle($place, $workflowMetadata);
            if (isset($style['background_color'])) {
                $attributes['style'] = 'filled';
                $attributes['fillcolor'] = $style['background_color'];
            }
            if (isset($style['label'])) {
                $attributes['name'] = $style['label'];
            }
            $places[$place] = array(
                'attributes' => $attributes,
            );
        }

        return $places;
    }

    /**
     * @internal
     */
    protected function findTransitions(Definition $definition)
    {
        $workflowMetadata = $definition->getMetadataStore();

        $transitions = array();

        foreach ($definition->getTransitions() as $transition) {
            $attributes = array('shape' => 'box', 'regular' => true);

            $style = $this->getTransitionStyle($transition, $workflowMetadata);
            if (isset($style['background_color'])) {
                $attributes['style'] = 'filled';
                $attributes['fillcolor'] = $style['background_color'];
            }
            $name = $style['label'] ?? $transition->getName();

            $transitions[] = array(
                'attributes' => $attributes,
                'name' => $name,
            );
        }

        return $transitions;
    }

    /**
     * @internal
     */
    protected function addPlaces(array $places)
    {
        $code = '';

        foreach ($places as $id => $place) {
            if (isset($place['attributes']['name'])) {
                $placeName = $place['attributes']['name'];
                unset($place['attributes']['name']);
            } else {
                $placeName = $id;
            }

            $code .= sprintf("  place_%s [label=\"%s\", shape=circle%s];\n", $this->dotize($id), $this->escape($placeName), $this->addAttributes($place['attributes']));
        }

        return $code;
    }

    /**
     * @internal
     */
    protected function addTransitions(array $transitions)
    {
        $code = '';

        foreach ($transitions as $place) {
            $code .= sprintf("  transition_%s [label=\"%s\",%s];\n", $this->dotize($place['name']), $this->escape($place['name']), $this->addAttributes($place['attributes']));
        }

        return $code;
    }

    /**
     * @internal
     */
    protected function findEdges(Definition $definition)
    {
        $workflowMetadata = $definition->getMetadataStore();

        $dotEdges = array();

        foreach ($definition->getTransitions() as $transition) {
            $transitionStyle = $this->getTransitionStyle($transition, $workflowMetadata);
            $transitionName = $transitionStyle['label'] ?? $transition->getName();

            foreach ($transition->getFroms() as $from) {
                $dotEdges[] = array(
                    'from' => $from,
                    'to' => $transitionName,
                    'direction' => 'from',
                );
            }
            foreach ($transition->getTos() as $to) {
                $dotEdges[] = array(
                    'from' => $transitionName,
                    'to' => $to,
                    'direction' => 'to',
                );
            }
        }

        return $dotEdges;
    }

    /**
     * @internal
     */
    protected function addEdges(array $edges)
    {
        $code = '';

        foreach ($edges as $edge) {
            $code .= sprintf("  %s_%s -> %s_%s [style=\"solid\"];\n",
                'from' === $edge['direction'] ? 'place' : 'transition',
                $this->dotize($edge['from']),
                'from' === $edge['direction'] ? 'transition' : 'place',
                $this->dotize($edge['to'])
            );
        }

        return $code;
    }

    /**
     * @internal
     */
    protected function startDot(array $options)
    {
        return sprintf("digraph workflow {\n  %s\n  node [%s];\n  edge [%s];\n\n",
            $this->addOptions($options['graph']),
            $this->addOptions($options['node']),
            $this->addOptions($options['edge'])
        );
    }

    /**
     * @internal
     */
    protected function endDot()
    {
        return "}\n";
    }

    /**
     * @internal
     */
    protected function dotize($id)
    {
        return hash('sha1', $id);
    }

    /**
     * @internal
     */
    protected function escape(string $string): string
    {
        return addslashes($string);
    }

    protected function addAttributes(array $attributes): string
    {
        $code = array();

        foreach ($attributes as $k => $v) {
            $code[] = sprintf('%s="%s"', $k, $this->escape($v));
        }

        return $code ? ' '.implode(' ', $code) : '';
    }

    private function addOptions(array $options): string
    {
        $code = array();

        foreach ($options as $k => $v) {
            $code[] = sprintf('%s="%s"', $k, $v);
        }

        return implode(' ', $code);
    }

    private function getPlaceStyle(string $place, MetadataStoreInterface $workflowMetadata): array
    {
        return $workflowMetadata->getMetadata('dump_style', $place) ?? array();
    }

    protected function getTransitionStyle(Transition $transition, MetadataStoreInterface $workflowMetadata): array
    {
        return $workflowMetadata->getMetadata('dump_style', $transition) ?? array();
    }
}

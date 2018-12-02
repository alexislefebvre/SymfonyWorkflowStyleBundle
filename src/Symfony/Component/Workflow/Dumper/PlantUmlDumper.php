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
use Symfony\Component\Workflow\Dumper\PlantUmlDumper as BasePlantUmlDumper;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Metadata\GetMetadataTrait;
use Symfony\Component\Workflow\Metadata\MetadataStoreInterface;
use Symfony\Component\Workflow\Transition;

/**
 * PlantUmlDumper dumps a workflow as a PlantUML file.
 *
 * You can convert the generated puml file with the plantuml.jar utility (http://plantuml.com/):
 *
 * php bin/console workflow:dump pull_request travis --dump-format=puml | java -jar plantuml.jar -p  > workflow.png
 *
 * @author Sébastien Morel <morel.seb@gmail.com>
 */
class PlantUmlDumper extends BasePlantUmlDumper
{
    use GetMetadataTrait;

    private const INITIAL = '<<initial>>';
    private const MARKED = '<<marked>>';

    private $transitionType = self::STATEMACHINE_TRANSITION;
    /** @var MetadataStoreInterface */
    private $workflowMetadata;

    public function __construct(string $transitionType = null)
    {
        parent::__construct($transitionType);
    }

    public function dump(Definition $definition, Marking $marking = null, array $options = array()): string
    {
        $options = array_replace_recursive(self::DEFAULT_OPTIONS, $options);

        $this->workflowMetadata = $definition->getMetadataStore();

        $code = $this->initialize($options, $definition);
        foreach ($definition->getPlaces() as $place) {
            $code[] = $this->getState($place, $definition, $marking);
        }
        if ($this->isWorkflowTransitionType()) {
            foreach ($definition->getTransitions() as $transition) {
                $transitionEscaped = $this->escape($transition->getName());
                $code[] = "agent $transitionEscaped";
            }
        }
        foreach ($definition->getTransitions() as $transition) {
            $transitionEscaped = $this->escape($transition->getName());
            foreach ($transition->getFroms() as $from) {
                $fromEscaped = $this->escape($from);
                foreach ($transition->getTos() as $to) {
                    $style = $this->getTransitionStyle($transition);

                    $toEscaped = $this->escape($to);

                    $transitionEscaped = $this->getStyledEscapedTransition($transitionEscaped, $style);

                    $transitionColor = $style['arrow_color'] ?? '';
                    if (!empty($transitionColor)) {
                        $transitionColor = sprintf('[%s]', $transitionColor);
                    }
                    if ($this->isWorkflowTransitionType()) {
                        $lines = array(
                            "$fromEscaped -${transitionColor}-> $transitionEscaped",
                            "$transitionEscaped -${transitionColor}-> $toEscaped",
                        );
                        foreach ($lines as $line) {
                            if (!\in_array($line, $code)) {
                                $code[] = $line;
                            }
                        }
                    } else {
                        $code[] = "$fromEscaped -${transitionColor}-> $toEscaped: $transitionEscaped";
                    }
                }
            }
        }

        return $this->startPuml($options).$this->getLines($code).$this->endPuml($options);
    }

    private function isWorkflowTransitionType(): bool
    {
        return self::WORKFLOW_TRANSITION === $this->transitionType;
    }

    private function startPuml(array $options): string
    {
        $start = '@startuml'.PHP_EOL;
        $start .= 'allow_mixing'.PHP_EOL;

        return $start;
    }

    private function endPuml(array $options): string
    {
        return PHP_EOL.'@enduml';
    }

    private function getLines(array $code): string
    {
        return implode(PHP_EOL, $code);
    }

    private function initialize(array $options, Definition $definition): array
    {
        $code = array();
        if (isset($options['title'])) {
            $code[] = "title {$options['title']}";
        }
        if (isset($options['name'])) {
            $code[] = "title {$options['name']}";
        }

        // Add style from nodes
        foreach ($definition->getPlaces() as $place) {
            $style = $this->getPlaceStyle($place);

            if (isset($style['background_color'])) {
                $backgroundColor = $style['background_color'];

                $key = 'BackgroundColor<<'.$this->getColorId($backgroundColor).'>>';

                $options['skinparams']['state'][$key] = $backgroundColor;
            }
        }

        if (isset($options['skinparams']) && \is_array($options['skinparams'])) {
            foreach ($options['skinparams'] as $skinparamKey => $skinparamValue) {
                if (!$this->isWorkflowTransitionType() && 'agent' === $skinparamKey) {
                    continue;
                }
                if (!\is_array($skinparamValue)) {
                    $code[] = "skinparam {$skinparamKey} $skinparamValue";
                    continue;
                }
                $code[] = "skinparam {$skinparamKey} {";
                foreach ($skinparamValue as $key => $value) {
                    $code[] = "    {$key} $value";
                }
                $code[] = '}';
            }
        }

        return $code;
    }

    private function escape(string $string): string
    {
        // It's not possible to escape property double quote, so let's remove it
        return '"'.str_replace('"', '', $string).'"';
    }

    private function getState(string $place, Definition $definition, Marking $marking = null): string
    {
        $placeEscaped = $this->escape($place);

        $stateStyle = $this->getPlaceStyle($place);

        $output = "state $placeEscaped".
            ($definition->getInitialPlace() === $place ? ' '.self::INITIAL : '').
            ($marking && $marking->has($place) ? ' '.self::MARKED : '');

        if (isset($stateStyle['background_color'])) {
            $output .= ' <<'.$this->getColorId($stateStyle['background_color']).'>>';
        }

        if (isset($stateStyle['description'])) {
            $output .= ' as '.$place.
                PHP_EOL.
                $place.' : '.$stateStyle['description'];
        }

        return $output;
    }

    private function getStyledEscapedTransition(string $to, ?array $style): string
    {
        $label = $style['label'] ?? $to;

        $color = $style['label_color'] ?? null;

        if (!is_null($color)) {
            $to = sprintf(
                '<font color=%1$s>%2$s</font>',
                $color,
                $label
            );
        }

        return $this->escape($to);
    }

    private function getPlaceStyle(string $place): array
    {
        return $this->workflowMetadata->getMetadata('style', $place) ?? [];
    }

    private function getTransitionStyle(Transition $transition): array
    {
        return $this->workflowMetadata->getMetadata('style', $transition) ?? [];
    }

    /**
     * Remove “#“ from start of the color name so it can be used as an identifier.
     */
    private function getColorId(string $color): string
    {
        if ('#' === substr($color, 0, 1)) {
            $color = substr($color, 1);
        }

        return $color;
    }
}

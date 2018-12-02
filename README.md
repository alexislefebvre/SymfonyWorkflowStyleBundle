# symfony-workflow-style-bundle

Add style to Symfony workflow dumps

[Packagist ![Latest Stable Version][Packagist Stable Image] ![Latest Unstable Version][Packagist Unstable Image]][Packagist]

Builds: [![Build status][Travis Master image]][Travis Master]

## Install

```
composer require alexislefebvre/symfony-workflow-style
```

Register bundle in `Kernel` (if not already done automatically by Symfony Flex):

```
new \AlexisLefebvre\Bundle\SymfonyWorflowStyleBundle\SymfonyWorflowStyleBundle(),
```

Add style to your workflow, see [examples][YAML example].

Call command `workflow:dump-with-style`, it behaves as `workflow:dump` but it add colors to output.

## Examples

YAML configuration used in examples: [workflows.yaml][YAML example]

### `pull_request` workflow: `marking_store.type` is `single_state`

Style is added to the first transition:
- arrow has custom color
- label has custom text
- label has custom color

And to travis and closed states:
- background color is changed for travis and closed states
- description is added to travis node (only for PUML format)

#### PUML
[![Example][PUML example image]][PUML example image]

#### Dot
[![Example][Dot example image]][Dot example image]

### `article` workflow: `marking_store.type` is `multiple_state`

Labels and colors of one transition and one state are changed.

[![Example][Dot multiple state example image]][Dot multiple state example image]


[Packagist]: https://packagist.org/packages/alexislefebvre/symfony-workflow-style
[Packagist Stable Image]: https://poser.pugx.org/alexislefebvre/symfony-workflow-style/v/stable.svg
[Packagist Unstable Image]: https://poser.pugx.org/alexislefebvre/symfony-workflow-style/v/unstable.svg

[Travis Master image]: https://travis-ci.org/alexislefebvre/SymfonyWorkflowStyleBundle.svg?branch=master
[Travis Master]: https://travis-ci.org/alexislefebvre/SymfonyWorkflowStyleBundle

[YAML example]: ./tests/App/workflows.yaml
[PUML example image]: ./tests/fixtures/puml/arrow/complex-state-machine-nomarking.png
[Dot example image]: ./tests/fixtures/dot/complex-state-machine-nomarking.png
[Dot multiple state example image]: ./tests/fixtures/dot/complex-multiple-state-machine-nomarking.png

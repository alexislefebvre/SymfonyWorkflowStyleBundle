# symfony-workflow-style-bundle

Add style to Symfony workflow dumps

Builds: 
[![Build status][Travis Master image]][Travis Master]

## Examples

YAML configuration used in examples: [workflows.yaml][PUML example]

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

[Travis Master image]: https://travis-ci.org/alexislefebvre/SymfonyWorkflowStyleBundle.svg?branch=master
[Travis Master]: https://travis-ci.org/alexislefebvre/SymfonyWorkflowStyleBundle

[PUML example]: ./tests/App/workflows.yaml
[PUML example image]: ./tests/fixtures/puml/arrow/complex-state-machine-nomarking.png
[Dot example image]: ./tests/fixtures/dot/complex-state-machine-nomarking.png
[Dot multiple state example image]: ./tests/fixtures/dot/complex-multiple-state-machine-nomarking.png

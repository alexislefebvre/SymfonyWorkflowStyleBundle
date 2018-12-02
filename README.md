# symfony-workflow-style-bundle

Add style to Symfony workflow dumps

Builds: 
[![Build status][Travis Master image]][Travis Master]

## Example

Example of YAML configuration: [workflows.yaml][PUML example]

Style is added to the first transition:
- arrow has custom color
- label has custom text
- label has custom color

And to travis and closed states:
- background color is changed for travis and closed states
- description is added to travis node

[![Example][PUML example image]][PUML example image]

[Travis Master image]: https://travis-ci.org/alexislefebvre/SymfonyWorkflowStyleBundle.svg?branch=master
[Travis Master]: https://travis-ci.org/alexislefebvre/SymfonyWorkflowStyleBundle

[PUML example]: ./tests/App/workflows.yaml
[PUML example image]: ./tests/fixtures/puml/arrow/complex-state-machine-nomarking.png

## TODO

- Add more options
- Implement Dot format

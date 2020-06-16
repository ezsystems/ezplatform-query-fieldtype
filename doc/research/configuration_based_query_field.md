# Configuration based query fields

An evolution of the query field where the query can be configured using semantic configuration.

## Refactoring

### Field settings abstraction
Abstract the field's settings (returned type, pagination settings, query type, parameters) so that
multiple providers can be implemented

- Interface: `QueryFieldSettingsProvider`
- Implementations:
  - `FieldDefinitionFieldSettingsProvider`: reads settings from a FieldDefinition
  - `ConfigurationFieldSettingsProvider`: reads settings from configuration
  - `ChainFieldSettingsProvider`

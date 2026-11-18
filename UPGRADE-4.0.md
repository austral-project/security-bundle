# Upgrade from 3.1 to 4.0

## Requirements

- PHP 8.4 minimum
- Symfony 7.4
- Doctrine ORM 3

## Update the dependencies

```diff
- "austral/security-bundle": "^3.1"
+ "austral/security-bundle": "^4.0"
```

All `austral/*` packages must be upgraded to `^4.0` at the same time.

## Breaking changes

<!--
For each break: what changes, why, and a before / after example.

### Annotations replaced by attributes

Before:
    /** @Austral\Field(...) */
After:
    #[Austral\Field(...)]
-->

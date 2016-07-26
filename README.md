# Schemata

> Facilitate generation of schema definitions of Drupal data models.

A schema is a declarative definition of an entity's makeup. A way of describing
the different pieces that make up the entity, much like an interface defines a
class and exactly like an XML DTD describes an XML document. This project uses
Drupal's new Typed Data system to faciliate the creation of schemas for your
site.

This is especially powerful in conjunction with the Drupal REST system, as your
content model schemata can help with testing, client code generation,
documentation generation, and more, especially in conjunction with external
tools that process schemas.

## What has a Schema?

All Entity Types and Entity Bundles have a schema automatically via the Schemata
module.

## Where is the Schema?

Schemata are accessed via REST endpoints. Once enabled, Schemata resources are
found at `/schemata/{entity_type}/{bundle?}`. These resources are dynamically
generated based on the Typed Data Definition system in Drupal core, which means
any change to fields on an Entity will automatically be reflected in the schema.

## Special Requirements

Schema will only apply if [Issue #2751325: Specifically-typed properties in json output](https://www.drupal.org/node/2751325)
is fixed or patched in your Drupal instance. Otherwise your serialized API
output for JSON and HAL will only produce string values.

## Architecture

The Schemata project contains the Schemata module. The module provides REST
endpoints to retrieve a schema object. The Schema is assembled by Drupal based
on the [Typed Data API](https://www.drupal.org/node/1794140), configuration,
and in the future, router introspection. The resulting Schema object can then
be requested via a GET request using a `_format` parameter to select a
particular serializer.

In order to serialize the Schema Object, the serializer must be able to support
implementations of the Drupal\schemata\Schema\SchemaInterface class. At this
time, the only serializer support for Schemata is within this project, you can
see an example of this in the packaged submodule **JSON Schema**.

## Configuration

* Enable the Schemata module and a Schemata Serializer. For now, that means the
  JSON Schema module.
* Enable both Schemata resources with the json_schema format.
* Grant permission to access the schemata resources to roles that need it.
* Visit a URL such as `/schemata/user?_format=json_schema` to see it.

## Security

* Bug reports should follow [Drupal.org security reporting procedures](https://www.drupal.org/node/101494).

## URLs

* **Homepage:** https://www.drupal.org/project/schemata
* **Development:** https://github.com/phase2/schemata

## Maintainers

Adam Ross a.k.a. Grayside

## Contributors

Fubhy's work on [GraphQL](https://www.drupal.org/project/graphql) was a great
help in early architecture of this project. Thank you to [Fubhy](https://www.drupal.org/u/fubhy)
and the GraphQL sponsors.

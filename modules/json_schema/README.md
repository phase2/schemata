# JSON Schema

> Provides a Schemata Serializer to generate JSON Schema v4.

This module is a serializer that will transform Schemata REST resources into
[JSON Schema v4](json-schema.org) schemata.

This can be used to provide clean, machine-readable definitions of entities,
useful for synchronizing their definitions between systems. Used to define
REST resources, this can be used to validate REST response payloads do not
contain surprises from custom code, as well as allow auto-generation of
strongly-typed client libraries for interacting with your API.

## Configuration

* Enable both Schemata resources with the `json_schema` and/or `hal_json_schema`
  format. The former describes the output of the JSON serializer, the latter
  describes the output of the HAL serializer.
* Grant permission to access the schemata resources to roles that need it.
* Visit a URL such as `/schemata/user?_format=json_schema` to see what happens.

## Security

* Bug reports should follow [Drupal.org security reporting procedures](https://www.drupal.org/node/101494).

## Maintainers

Adam Ross a.k.a. Grayside

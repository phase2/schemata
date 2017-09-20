<?php

namespace Drupal\schemata_json_schema\Plugin\schemata\schema_type;

use Drupal\schemata\Plugin\SchemaTypeInterface;

/**
 * Describe JSON Schema as a supported Schema Type.
 *
 * @SchemaType(
 *   id = "schema_json",
 *   label = @Translation("JSON Schema v4"),
 *   documentation_url = "http://json-schema.org",
 *   describes = {
 *     "json" = "JSON"
 *   }
 * )
 */
class JsonSchema implements SchemaTypeInterface {

}

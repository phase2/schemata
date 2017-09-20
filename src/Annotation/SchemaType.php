<?php

namespace Drupal\schemata\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Schemata Schema Type annotation object.
 *
 * Plugin Namespace: Plugin\schemata\schema_type
 *
 * For a working example, see \Drupal\schemata_json_schema\Plugin\schemata\schema_type\JsonSchema
 *
 * @see \Drupal\schemata\Plugin\Type\SchemaTypePluginManager
 * @see \Drupal\schemata\Plugin\SchemaTypeInterface
 * @see plugin_api
 *
 * @ingroup third_party
 *
 * @Annotation
 */
class SchemaType extends Plugin {

  /**
   * The Schema Type plugin ID.
   *
   * Must match the identifier expected by the related normalizers in the
   * _format querystring.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the Schema Type plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * URL to the documentation of this schema type.
   *
   * Use of this is not yet implemented.
   *
   * @var string (optional)
   */
  public $documentation_url;

  /**
   * List of serialization formats this schema type currently supports.
   *
   * Must match the identifier expected by the related normalizers in the
   * _describes querystring.
   *
   * @var string[]
   */
  public $describes;

}

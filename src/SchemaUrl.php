<?php

namespace Drupal\schemata;

use Drupal\schemata\schema\SchemaInterface;
use Drupal\Core\Url;

/**
 * Provides additional URL factory methods for linking to Schema.
 *
 * If internal methods or properties of the Url class seem valuable, this class
 * could be made a child class. For now the forced isolation is used to keep it
 * clean.
 */
class SchemaUrl {

  /**
   * Generate a URI for the Schema instance.
   *
   * @param string $format
   *   The format or type of schema.
   *
   * @return \Drupal\Core\Url
   *   The schema resource Url object.
   */
  public static function fromSchema($format, SchemaInterface $schema) {
    return static::fromOptions(
      $format,
      $schema->getEntityTypeId(),
      $schema->getBundleId()
    );
  }

  /**
   * Build a URI to a schema resource.
   *
   * @param string $format
   *   The format or type of schema.
   * @param string $entity_type_id
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   *
   * @return \Drupal\Core\Url
   *   The schema resource Url object.
   */
  public static function fromOptions($format, $entity_type_id, $bundle = NULL) {
    $route = sprintf('schemata.%s:%s', $entity_type_id, $bundle);
    $parameters = ['entity_type' => $entity_type_id];
    if (empty($bundle)) {
      $route = sprintf('schemata.%s', $entity_type_id);
      $parameters['bundle'] = $bundle;
    }

    return Url::fromRoute($route, $parameters, [
      'query' => [
        '_format' => $format,
      ],
      'absolute' => TRUE,
    ]);
  }

}

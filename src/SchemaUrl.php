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
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   *
   * @return \Drupal\Core\Url
   *   The schema resource Url object.
   */
  public static function fromOptions($format, $entity_type, $bundle = NULL) {
    $route = static::getRouteName($format, $bundle);

    $parameters = [
      'entity_type' => $entity_type,
    ];
    if (isset($bundle)) {
      $parameters['bundle'] = $bundle;
    }

    return Url::fromRoute($route, $parameters, [
      'query' => [
        '_format' => $format,
      ],
      'absolute' => TRUE,
    ]);
  }

  /**
   * Determine the route name.
   *
   * @param string $format
   *   The route format.
   * @param string $bundle
   *   The bundle name.
   *
   * @return string
   *   The route name.
   */
  public static function getRouteName($format, $bundle = '') {
    return empty($bundle) ? 'rest.schemata_entity_base.GET.' . $format
      : 'rest.schemata_entity_bundle.GET.' . $format;
  }

}

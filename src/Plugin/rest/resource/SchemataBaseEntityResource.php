<?php

namespace Drupal\schemata\Plugin\rest\resource;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "schemata_entity_base",
 *   label = @Translation("Schemata Base Entity"),
 *   serialization_class = "Drupal\schemata\Schema\Schema",
 *   uri_paths = {
 *     "canonical" = "/schemata/{entity_type}",
 *     "https://www.drupal.org/link-relations/describes" = "/entity/{entity_type}"
 *   }
 * )
 */
class SchemataBaseEntityResource extends SchemataResourceBase {

}

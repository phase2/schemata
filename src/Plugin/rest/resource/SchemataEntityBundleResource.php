<?php

namespace Drupal\schemata\Plugin\rest\resource;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "schemata_entity_bundle",
 *   label = @Translation("Schemata Entity Bundle"),
 *   serialization_class = "Drupal\schemata\Schema\Schema",
 *   uri_paths = {
 *     "canonical" = "/schemata/{entity_type}/{bundle}",
 *     "https://www.drupal.org/link-relations/describes" = "/entity/{entity_type}/{bundle}"
 *   }
 * )
 */
class SchemataEntityBundleResource extends SchemataResourceBase {

}

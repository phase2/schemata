<?php

namespace Drupal\json_schema\Normalizer;

use Drupal\json_schema\Normalizer\DataDefinitionNormalizer;
use Drupal\schemata\SchemaUrl;

/**
 * Normalizer for Entity References.
 *
 * DataReferenceDefinitions are embedded inside ComplexDataDefinitions, and
 * represent a type property. The key for this is usually "entity", and it is
 * found alongside a "target_id" value which refers to the specific entity
 * instance for the reference. The target_id is not normalized by this class,
 * instead it comes through the DataDefinitionNormalizer as a scalar value.
 */
class DataReferenceDefinitionNormalizer extends DataDefinitionNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = '\Drupal\Core\TypedData\DataReferenceDefinitionInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = array()) {
    /* @var $entity \Drupal\Core\TypedData\DataReferenceDefinitionInterface */
    // We do not support config entities.
    // @todo properly identify and exclude ConfigEntities.
    if ($entity->getDataType() == 'language_reference'
      || $entity->getConstraint('EntityType') == 'node_type'
      || $entity->getConstraint('EntityType') == 'user_role') {

      return [];
    }

    // DataDefinitionNormalizer::normalize() results in extraneous structures
    // added to the schema for this field element (e.g., entity)
    return $this->extractPropertyData($entity, $context);
  }

}

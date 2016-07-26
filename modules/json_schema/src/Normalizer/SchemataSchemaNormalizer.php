<?php

namespace Drupal\json_schema\Normalizer;

use Drupal\json_schema\Normalizer\NormalizerBase;
use Drupal\schemata\Schema\SchemaInterface;
use Drupal\schemata\SchemaUrl;
use Drupal\Component\Utility\NestedArray;

/**
 * Primary normalizer for SchemaInterface objects.
 */
class SchemataSchemaNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\schemata\Schema\SchemaInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = array()) {
    // Create the array of normalized fields, starting with the URI.
    /* @var $entity \Drupal\schemata\Schema\SchemaInterface */
    $normalized = [
      '$schema' => 'http://json-schema.org/draft-04/schema#',
      'id' => SchemaUrl::fromSchema($format, $entity)->toString(),
      'type' => 'object',
    ];
    $normalized = array_merge($normalized, $entity->getMetadata());

    // Stash schema request parameters.
    $context['entityTypeId'] = $entity->getEntityTypeId();
    $context['bundleId'] = $entity->getBundleId();

    // Retrieve 'properties' and possibly 'required' nested arrays.
    $properties = $this->normalizeProperties(
      $this->getProperties($entity, $format, $context),
      $format,
      $context
    );
    $normalized = NestedArray::mergeDeep($normalized, $properties);

    return $normalized;
  }

  /**
   * Identify properties of the data definition to normalize.
   *
   * This allow subclasses of the normalizer to build white or blacklisting
   * functionality on what will be included in the serialized schema. The JSON
   * Schema serializer already has logic to drop any properties that are empty
   * values after processing, but this allows cleaner, centralized logic.
   *
   * @param \Drupal\schemata\Schema\SchemaInterface $entity
   *   The Schema object whose properties the serializer will present.
   * @param string $format
   *   The serializer format. Defaults to NULL.
   * @param array $context
   *   The current serializer context.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface[]
   *   The DataDefinitions to be processed.
   */
  protected static function getProperties(SchemaInterface $entity, $format = NULL, $context = []) {
    return $entity->getProperties();
  }

}

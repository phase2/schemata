<?php

namespace Drupal\json_schema\Normalizer;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\serialization\Normalizer\NormalizerBase as SerializationNormalizerBase;
use Drupal\Component\Utility\NestedArray;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Base class for JSON Schema Normalizers.
 */
abstract class NormalizerBase extends SerializationNormalizerBase implements DenormalizerInterface {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = array('json_schema');

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    return in_array($format, $this->formats) && parent::supportsNormalization($data, $format);
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDenormalization($data, $type, $format = NULL) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = array()) {
    return FALSE;
  }

  /**
   * Normalize an array of data definitions.
   *
   * This normalization process gets an array of properties and an array of
   * properties that are required by name. This is needed by the
   * SchemataSchemaNormalizer, otherwise it would have been placed in
   * DataDefinitionNormalizer.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $items
   *   An array of data definition properties to be normalized.
   * @param string $format
   *   Format identifier of the current serialization process.
   * @param array $context
   *   Operating context of the serializer.
   *
   * @return array
   *   Array containing one or two nested arrays.
   *   - properties: The array of all normalized properties.
   *   - required: The array of required properties by name.
   */
  protected function normalizeProperties($items, $format, $context = []) {
    $normalized = [];
    foreach ($items as $name => $property) {
      $context['name'] = $name;
      $item = $this->serializer->normalize($property, $format, $context);
      if (!empty($item)) {
        $normalized = NestedArray::mergeDeep($normalized, $item);
      }
    }

    return $normalized;
  }

  /**
   * Determine if the given property is a required element of the schema.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $property
   *   The data property to be evaluated.
   *
   * @return bool
   *   Whether the property should be treated as required for schema
   *   purposes.
   */
  protected function requiredProperty(DataDefinitionInterface $property) {
    return $property->isRequired();
  }

}

<?php

namespace Drupal\json_schema\Normalizer;

use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Normalizer for DataDefinitionInterface instances.
 *
 * DataDefinitionInterface is the ultimate parent to all data definitions. This
 * service must always be low priority for data definitions, otherwise the
 * simpler normalization process it supports will take precedence over all the
 * complexities most entity properties contain before reaching this level.
 *
 * DataDefinitionNormalizer produces scalar value definitions.
 *
 * Unlike the other Normalizer services in the JSON Schema module, this one is
 * used by the hal_json_schema normalizer. It is unlikely divergent requirements
 * will develop.
 *
 * All the TypedData normalizers extend from this class.
 */
class DataDefinitionNormalizer extends NormalizerBase {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = array('json_schema', 'hal_json_schema');

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = '\Drupal\Core\TypedData\DataDefinitionInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = array()) {
    /* @var $entity \Drupal\Core\TypedData\DataDefinitionInterface */
    // `text source` and `date source` produce objects not supported in the API.
    // It is not clear how the API excludes them.
    // @todo properly identify and exclude this class of computed objects.
    if ($entity->getSetting('text source')
      || $entity->getSetting('date source')) {

      return [];
    }

    $property = $this->extractPropertyData($entity, $context);
    if (!empty($context['parent']) && $context['name'] == 'value') {
      if ($maxLength = $context['parent']->getSetting('max_length')) {
        $property['maxLength'] = $maxLength;
      }
    }

    $normalized = ['properties' => []];
    $normalized['properties'][$context['name']] = $property;
    if ($this->requiredProperty($entity)) {
      $normalized['required'][] = $context['name'];
    }

    return $normalized;
  }

  /**
   * Extracts property details from a data definition.
   *
   * This method includes mapping primitive types in Drupal to JSON Schema
   * type and format descriptions. This method is invoked by several of the
   * normalizers.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $property
   *   The data definition from which to extract values.
   * @param array $context
   *   Serializer context.
   *
   * @return array
   *   Discrete values of the property definition.
   *
   * @todo identify how to cleanly inject the plugin manager without requiring
   *   updates to many of the normalizers.
   */
  protected function extractPropertyData(DataDefinitionInterface $property, array $context = []) {
    $type_mapper_manager = \Drupal::service('plugin.manager.json_schema.type_mapper');
    $data = $type_mapper_manager->createInstance($property->getDataType())
      ->getMappedValue($property);

    if (isset($context['parent']) && $context['parent']->getDataType() == 'field_item:uuid') {
      $data['format'] = 'uuid';
    }

    return $data;
  }

}

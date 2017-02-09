<?php

namespace Drupal\schemata_json_schema\Normalizer\json;

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
 * used by the hal_schemata normalizer. It is unlikely divergent requirements
 * will develop.
 *
 * All the TypedData normalizers extend from this class.
 */
class DataDefinitionNormalizer extends JsonNormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = '\Drupal\Core\TypedData\DataDefinitionInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
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
   *   Discrete values of the property definition
   */
  protected function extractPropertyData(DataDefinitionInterface $property, array $context = []) {
    $data = [
      // 'constraints' => print_r($property->getConstraints(), TRUE),
      // 'settings' => print_r($property->getSettings(), TRUE),
      // 'class' => get_class($Property),
      // 'computed' => $property->isComputed(),
    ];

    if ($item = $property->getLabel()) {
      $data['title'] = $item;
    }
    if ($item = $property->getDescription()) {
      $data['description'] = $item;
    }

    $type = $property->getDataType();
    switch ($type) {
      case 'email':
        $data['type'] = 'string';
        $data['format'] = 'email';
        break;

      case 'datetime_iso8601':
        $data['type'] = 'string';
        $data['format'] = 'date';
        break;

      case 'timestamp':
        $data['type'] = 'number';
        $data['format'] = 'utc-millisec';
        break;

      case 'filter_format':
        // @todo machine_name format or regex validation.
        $data['type'] = 'string';
        break;

      case 'entity_reference':
        $data['type'] = 'object';
        break;

      default:
        $data['type'] = $type;

    }

    if (isset($context['parent']) && $context['parent']->getDataType() == 'field_item:uuid') {
      $data['format'] = 'uuid';
    }

    return $data;
  }

}

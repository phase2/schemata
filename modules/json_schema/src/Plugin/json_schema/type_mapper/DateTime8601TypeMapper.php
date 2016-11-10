<?php

namespace Drupal\json_schema\Plugin\json_schema\type_mapper;

use Drupal\json_schema\Plugin\TypeMapperBase;
use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Converts Data Definition properties of the datetime_iso8601 to JSON Schema.
 *
 * @TypeMapper(
 *  id = "datetime_iso8601"
 * )
 */
class DateTime8601TypeMapper extends TypeMapperBase {

  /**
   * {@inheritdoc}
   */
  public function getMappedValue(DataDefinitionInterface $property) {
    $value = parent::getMappedValue($property);
    $value['type'] = 'string';
    $value['format'] = 'date';
    return $value;
  }

}

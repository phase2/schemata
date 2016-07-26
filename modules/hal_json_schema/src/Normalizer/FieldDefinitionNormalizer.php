<?php

namespace Drupal\hal_json_schema\Normalizer;

use Drupal\json_schema\Normalizer\FieldDefinitionNormalizer as JsonFieldDefinitionNormalizer;

/**
 * HAL normalizer for FieldDefinition objects.
 */
class FieldDefinitionNormalizer extends JsonFieldDefinitionNormalizer {

  use ReferenceListTrait;

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = array('hal_json_schema');

}

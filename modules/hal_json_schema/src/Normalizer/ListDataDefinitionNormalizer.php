<?php

namespace Drupal\hal_json_schema\Normalizer;

use Drupal\json_schema\Normalizer\ListDataDefinitionNormalizer as JsonListDataDefinitionNormalizer;

/**
 * HAL normalizer for ListDataDefinitionInterface objects.
 */
class ListDataDefinitionNormalizer extends JsonListDataDefinitionNormalizer {

  use ReferenceListTrait;

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = array('hal_json_schema');

}

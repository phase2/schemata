<?php


namespace Drupal\schemata_json_schema\Normalizer\hal;

use Drupal\schemata_json_schema\Normalizer\json\DataDefinitionNormalizer as JsonDataDefinitionNormalizer;

class DataDefinitionNormalizer extends JsonDataDefinitionNormalizer {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $format = 'schema_json';

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $describedFormat = 'hal_json';

}

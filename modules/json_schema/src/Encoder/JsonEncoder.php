<?php

namespace Drupal\json_schema\Encoder;

use Drupal\serialization\Encoder\JsonEncoder as DrupalJsonEncoder;

/**
 * Encodes JSON Schema data in JSON.
 *
 * Simply respond to json_schema format requests using the JSON encoder.
 */
class JsonEncoder extends DrupalJsonEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var string
   */
  protected static $format = array('json_schema', 'hal_json_schema');

  /**
   * {@inheritdoc}
   */
  public function supportsDecoding($format) {
    return FALSE;
  }

}

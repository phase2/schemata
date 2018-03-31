<?php

namespace Drupal\schemata;

use Drupal\schemata\Plugin\Type\SchemaTypePluginManager;

/**
 * Manage available Schema Formats.
 */
class SchemaTypeManager {

  /**
   * SchemaTypePluginManager.
   *
   * @var \Drupal\schemata\Plugin\Type\SchemaTypePluginManager
   */
  protected $pluginManager;

  /**
   * The available serialization formats.
   *
   * @var array
   */
  protected $serializerFormats = [];

  /**
   * SchemaFormatHelper constructor.
   *
   * @param \Drupal\schemata\Plugin\Type\SchemaTypePluginManager $plugin_manager
   *   SchemaTypePluginManager.
   * @param array $serializer_formats
   *   The available serializer formats.
   */
  public function __construct(SchemaTypePluginManager $plugin_manager, array $serializer_formats = []) {
    $this->pluginManager = $plugin_manager;
    $this->serializerFormats = $serializer_formats;
  }

  /**
   * Retrieve a list of the unsupported Schema Types.
   *
   * This is primarily used for error reporting.
   *
   * @see schemata_requirements().
   *
   * @return \Drupal\schemata\Plugin\Type\SchemaTypeInterface[]
   *  Each plugin definition is keyed on its ID.
   */
  public function getUnsupportedTypeList() {
    $plugins = $this->pluginManager->getDefinitions();
    $plugins = array_filter($plugins, function($plugin) {
      return !$this->isSerializationFormat($plugin['id']);
    });

    return $plugins;
  }

  /**
   * Retrieve a list of supported Schema Types.
   *
   * "Supported" in this case means there are identified normalizers for the
   * defined type.
   *
   * @return \Drupal\schemata\Plugin\Type\SchemaTypeInterface[]
   *  Each plugin definition is keyed on its ID.
   */
  public function getSupportedTypeList() {
    $plugins = $this->pluginManager->getDefinitions();
    $plugins = array_filter($plugins, function($plugin) {
      return $this->isSerializationFormat($plugin['id']);
    });

    return $plugins;
  }

  /**
   * Checks if a given type exists, with the implication it is supported.
   *
   * @param string $name
   *   Name of a Schema Type.
   *
   * @return bool
   *   TRUE if the Schema Type is available, FALSE otherwise.
   */
  public function schemaTypeExists($name) {
    return array_key_exists($name, $this->getSupportedTypeList());
  }

  /**
   * Identify if the named plugin supports the described serialization format.
   *
   * @param string $name
   *   Name of a Schema Type.
   * @param string $described_format
   *   Name of a serialization format.
   *
   * @return bool
   *   TRUE if the schema type exists and can support the named format.
   */
  public function schemaTypeSupportsFormat($name, $described_format) {
    return $this->schemaTypeExists($name) &&
      array_key_exists($described_format, $this->pluginManager->getDefinition($name)['describes']);
  }

  /**
   * Checks if a given type is a supported serialization format.
   *
   * @param string $name
   *   Name of a serializer format.
   *
   * @return bool
   *   TRUE if the serialize format is available, FALSE otherwise.
   */
  public function isSerializationFormat($name) {
    return array_key_exists($name, array_flip($this->serializerFormats));
  }

}

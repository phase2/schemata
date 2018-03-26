<?php

namespace Drupal\schemata\Plugin\Type;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages SchemaType plugins.
 *
 * SchemaType provides metadata about a schemata schema format.
 * Formats are otherwise only defined by the collection of normalizers that
 * are invoked for actual use.
 *
 * @see \Drupal\schemata\Annotation\SchemaType
 * @see \Drupal\schemata\Plugin\schemata\SchemaTypePluginBase
 * @see \Drupal\schemata\Plugin\schemata\\SchemaTypeInterface
 * @see plugin_api
 */
class SchemaTypePluginManager extends DefaultPluginManager {

  /**
   * Constructs a new SchemaTypePluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/schemata/schema_type', $namespaces, $module_handler, 'Drupal\schemata\Plugin\SchemaTypeInterface', 'Drupal\schemata\Annotation\SchemaType');

    $this->setCacheBackend($cache_backend, 'schemata_schema_type_plugins');
    $this->alterInfo('schemata_schema_type');
  }
}

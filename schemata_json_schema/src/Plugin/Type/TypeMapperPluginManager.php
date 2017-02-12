<?php

namespace Drupal\schemata_json_schema\Plugin\Type;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Plugin\FallbackPluginManagerInterface;
/**
 * Manages TypeMapper plugins.
 *
 * TypeMappers are used to adapt Drupal TypedData types to JSON Schema specs.
 *
 * @see \Drupal\json_schema\Annotation\TypeMapper
 * @see \Drupal\json_schema\Plugin\TypeMapperBase
 * @see \Drupal\json_schema\Plugin\TypeMapperInterface
 * @see plugin_api
 */
class TypeMapperPluginManager extends DefaultPluginManager implements FallbackPluginManagerInterface {

  /**
   * The TypeMapper to use if there's a miss.
   *
   * @param string
   */
  const FALLBACK_TYPE_MAPPER = 'fallback';

  /**
   * Constructs a new \Drupal\rest\Plugin\Type\ResourcePluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/json_schema/type_mapper', $namespaces, $module_handler, 'Drupal\json_schema\Plugin\TypeMapperInterface', 'Drupal\json_schema\Annotation\TypeMapper');
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = array()) {
    return static::FALLBACK_TYPE_MAPPER;
  }

}

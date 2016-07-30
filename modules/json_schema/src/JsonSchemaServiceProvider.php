<?php

namespace Drupal\json_schema;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Adds application/schema+json as a known format for json_schema.
 */
class JsonSchemaServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('http_middleware.negotiation') && is_a($container->getDefinition('http_middleware.negotiation')->getClass(), '\Drupal\Core\StackMiddleware\NegotiationMiddleware', TRUE)) {
      $container->getDefinition('http_middleware.negotiation')->addMethodCall('registerFormat', ['json_schema', ['application/schema+json']]);
    }
  }

}

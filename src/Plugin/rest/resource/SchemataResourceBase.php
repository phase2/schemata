<?php

namespace Drupal\schemata\Plugin\rest\resource;

use Drupal\schemata\SchemaFactory;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides the basic resource behavior for a Schema resource.
 *
 * Extensions of this class are imagined primarily to vary the annotations for
 * different purposes, such as whether or not entity bundle is a required part
 * of the path.
 */
class SchemataResourceBase extends ResourceBase {

  /**
   * EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * EntityTypeBundleInfo.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfo
   */
  protected $entityTypeBundleInfo;

  /**
   * The Schemata SchemaFactory.
   *
   * @var \Drupal\schemata\SchemaFactory
   */
  protected $schemaFactory;

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The EntityTypeManager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfo $entity_type_bundle_info
   *   The EntityTypeBundleInfo helper.
   * @param \Drupal\schemata\SchemaFactory $schema_factory
   *   The Schemata Schema Loader.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    EntityTypeManager $entity_type_manager,
    EntityTypeBundleInfo $entity_type_bundle_info,
    SchemaFactory $schema_factory,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->schemaFactory = $schema_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('schemata.schema_factory'),
      $container->get('logger.factory')->get('schemata'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response for the REST request.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Throws access denied if permission not available.
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws not found if resource not available.
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Throws bad request if the request is not valid.
   */
  public function get($entity_type, $bundle = NULL) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Validating entity type and bundle validity here allows us to throw
    // HTTP-centric errors.
    $entity_type_plugin = $this->entityTypeManager->getDefinition($entity_type, FALSE);
    if (empty($entity_type_plugin)) {
      throw new NotFoundHttpException('Requested Entity Type not found.');
    }
    if (!($entity_type_plugin->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface'))) {
      throw new BadRequestHttpException('Only Content Entities are supported.');
    }

    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
    if (!empty($bundle) && !array_key_exists($bundle, $bundles)) {
      throw new NotFoundHttpException('Requested Entity Bundle not found.');
    }

    // As a general-purpose resource response, REST does not handle this without
    // the specification of a _format.
    $schema = $this->schemaFactory->create($entity_type, $bundle);
    $response = new ResourceResponse($schema);
    // Avoid the error thrown in line 154 of
    // core/lib/Drupal/Core/EventSubscriber/EarlyRenderingControllerWrapperSubscriber.php.
    // This is an error complaining of short-circuiting the rendering of cache
    // metadata.
    $response->addCacheableDependency($schema);

    return $response;
  }

}

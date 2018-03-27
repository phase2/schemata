<?php

namespace Drupal\schemata\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\schemata\SchemaFactory;
use Drupal\schemata\SchemaTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Contains callback methods for dynamic routes.
 */
class Controller extends ControllerBase {

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * The schema factory.
   *
   * @var \Drupal\schemata\SchemaFactory
   */
  protected $schemaFactory;

  /**
   * SchemaTypeManager.
   *
   * @var \Drupal\schemata\SchemaTypeManager
   */
  protected $typeManager;

  /**
   * The cacheable response.
   *
   * @var \Drupal\Core\Cache\CacheableResponseInterface
   */
  protected $response;

  /**
   * Controller constructor.
   *
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer service.
   * @param \Drupal\schemata\SchemaFactory $schema_factory
   *   The schema factory.
   * @param \Drupal\schemata\SchemaTypeManager $type_manager
   *   SchemaTypeManager.
   * @param \Drupal\Core\Cache\CacheableResponseInterface $response
   *   The cacheable response.
   */
  public function __construct(SerializerInterface $serializer, SchemaFactory $schema_factory, SchemaTypeManager $type_manager, CacheableResponseInterface $response) {
    $this->serializer = $serializer;
    $this->schemaFactory = $schema_factory;
    $this->typeManager = $type_manager;
    $this->response = $response;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('serializer'),
      $container->get('schemata.schema_factory'),
      $container->get('schemata.type_manager'),
      new CacheableResponse()
    );
  }

  /**
   * Serializes a entity type or bundle definition.
   *
   * We have 2 different data formats involved. One is the schema format (for
   * instance JSON Schema) and the other one is the format that the schema is
   * describing (for instance jsonapi, json, hal+json, …). We need to provide
   * both formats. Something like: ?_format=schema_json&_describes=api_json.
   *
   * @param string $entity_type_id
   *   The entity type ID to describe.
   * @param string $bundle
   *   The (optional) bundle to describe.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\Core\Cache\CacheableResponse
   *   The response object.
   *
   * @todo Handle an empty _format query parameter. https://www.drupal.org/node/2773009
   * @todo Handle an empty _describes query parameter. https://www.drupal.org/node/2910018
   * @todo Handle MIME type not associated with the given schema type.
   */
  public function serialize($entity_type_id, Request $request, $bundle = NULL) {
    list ($schema_type, $described_format) = $this->extractFormatNames($request);

    // Load the schema data to serialize from the request route information.
    /** @var \Drupal\schemata\Schema\SchemaInterface $schema */
    $schema = $this->schemaFactory->create($entity_type_id, $bundle);
    $format = "{$schema_type}:{$described_format}";
    $mime_type = $request->getMimeType($schema_type);

    try {
      if (!$this->typeManager->schemaTypeExists($schema_type)) {
        $error = [
          'title' => t('Unrecognized Schema Type'),
          'details' => t('Your requested schema type format @type is not supported.', [
            '@type' => $schema_type
          ]),
          'status' => 400,
          'instance' => "#{$schema_type}",
        ];
        $mime_type = 'application/problem+json';
        $content = $this->serializer->serialize($error, 'json');
        $this->response->setStatusCode($error['status']);
      }
      elseif (!$this->typeManager->schemaTypeSupportsFormat($schema_type, $described_format)) {
        $error = [
          'title' => t('Unrecognized Format for Schema Description'),
          'details' => t('The format you requested to be described by @type is not available for @described.', [
            '@type' => $schema_type,
            '@described' => $described_format,
          ]),
          'status' => 400,
          'instance' => "#{$schema_type}-{$described_format}",
        ];
        $mime_type = 'application/problem+json';
        $content = $this->serializer->serialize($error, 'json');
        $this->response->setStatusCode($error['status']);
      }
      else {
        // Serialize the entity type/bundle definition.
        $content = $this->serializer->serialize($schema, $format);
      }
    }
    catch(\Exception $e) {
      $error = [
        'title' => t('Error Assembling Schema'),
        'details' => t('Something went wrong building your schema after collecting the data and during the process of arrranging it for your selected schema format.'),
        'status' => 500,
        // @todo Log this error instead of returning it in the message.
        'error' => $e->getMessage(),
      ];
      $mime_type = 'application/problem+json';
      $content = $this->serializer->serialize($error, 'json');
      $this->response->setStatusCode($error['status']);
    }

    // Finally, set the contents of the response and return it.
    $this->response->addCacheableDependency($schema);
    $cacheable_dependency = (new CacheableMetadata())
      ->addCacheContexts(['url.query_args:_describes']);
    $this->response->addCacheableDependency($cacheable_dependency);
    $this->response->setContent($content);
    $this->response->headers->set('Content-Type', $mime_type);

    return $this->response;
  }

  /**
   * Helper function that inspects the request to extract the formats.
   *
   * Extracts the format of the response and media type being described.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array
   *   An array containing the format of the output and the media type being
   *   described.
   */
  protected function extractFormatNames(Request $request) {
    return [
      $request->getRequestFormat(),
      $request->query->get('_describes', ''),
    ];
  }

}

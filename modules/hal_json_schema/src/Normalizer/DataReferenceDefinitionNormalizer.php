<?php

namespace Drupal\hal_json_schema\Normalizer;

use Drupal\json_schema\Normalizer\DataReferenceDefinitionNormalizer as JsonDataReferenceDefinitionNormalizer;
use Drupal\schemata\SchemaUrl;
use Drupal\rest\LinkManager\LinkManagerInterface;

/**
 * Normalizer for Entity References in HAL+JSON style.
 */
class DataReferenceDefinitionNormalizer extends JsonDataReferenceDefinitionNormalizer {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = array('hal_json_schema');

  /**
   * The hypermedia link manager.
   *
   * @var \Drupal\rest\LinkManager\LinkManagerInterface
   */
  protected $linkManager;

  /**
   * Constructs an DataReferenceDefinitionNormalizer object.
   *
   * @param \Drupal\rest\LinkManager\LinkManagerInterface $link_manager
   *   The hypermedia link manager.
   */
  public function __construct(LinkManagerInterface $link_manager) {
    $this->linkManager = $link_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = array()) {
    /* @var $entity \Drupal\Core\TypedData\DataReferenceDefinitionInterface */
    // We do not support config entities.
    // @todo properly identify and exclude ConfigEntities.
    if ($entity->getDataType() == 'language_reference'
      || $entity->getConstraint('EntityType') == 'node_type'
      || $entity->getConstraint('EntityType') == 'user_role') {

      return [];
    }

    // Collect data about the reference field.
    $parentProperty = $this->extractPropertyData($context['parent'], $context);
    $property = $this->extractPropertyData($entity, $context);
    $target_type = $entity->getConstraint('EntityType');
    $target_bundles = $context['settings']['handler_settings']['target_bundles'];

    // Build the relation URI, which is used as the property key.
    $field_uri = $this->linkManager->getRelationUri(
      $context['entityTypeId'],
      $context['bundleId'],
      $context['name'],
      $context
    );

    // From the root of the schema object, build out object references.
    $normalized = [
      '_links' => [
        $field_uri => [
          '$ref' => '#/definitions/linkArray',
        ],
      ],
      '_embedded' => [
        $field_uri => [
          'type' => 'array',
          'items' => [],
        ],
      ],
    ];

    // Add title and description to relation definition.
    if (isset($parentProperty['title'])) {
      $normalized['_links'][$field_uri]['title'] = $parentProperty['title'];
      $normalized['_embedded'][$field_uri]['title'] = $parentProperty['title'];
    }
    if (isset($parentProperty['description'])) {
      $normalized['_links'][$field_uri]['description'] = $parentProperty['description'];
    }

    // Add Schema resource references.
    $item = &$normalized['_embedded'][$field_uri]['items'];
    if (!isset($target_bundles)) {
      $item['$ref'] = SchemaUrl::fromOptions($format, $target_type)->toString();
    }
    elseif (count($target_bundles) == 1) {
      $item['$ref'] = SchemaUrl::fromOptions($format, $target_type, $target_bundles[0])->toString();
    }
    elseif (count($target_bundles) > 1) {
      $refs = [];
      foreach ($target_bundles as $bundle) {
        $refs[] = [
          '$ref' => SchemaUrl::fromOptions($format, $target_type, $bundle)
            ->toString(),
        ];
      }

      $item['anyOf'] = $refs;
    }

    return ['properties' => $normalized];
  }

}

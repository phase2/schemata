<?php

namespace Drupal\hal_json_schema\Normalizer;

use Drupal\json_schema\Normalizer\DataReferenceDefinitionNormalizer as JsonDataReferenceDefinitionNormalizer;
use Drupal\schemata\SchemaUrl;
use Drupal\rest\LinkManager\LinkManagerInterface;
use Drupal\Core\Entity\EntityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The Entity Type Manager.
   * @param \Drupal\rest\LinkManager\LinkManagerInterface $link_manager
   *   The hypermedia link manager.
   */
  public function __construct(EntityTypeManager $entity_type_manager, LinkManagerInterface $link_manager) {
    parent::__construct($entity_type_manager);
    $this->linkManager = $link_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = array()) {
    /* @var $entity \Drupal\Core\TypedData\DataReferenceDefinitionInterface */
    if (!$this->validateEntity($entity)) {
      return [];
    }

    // Collect data about the reference field.
    $parentProperty = $this->extractPropertyData($context['parent'], $context);
    $property = $this->extractPropertyData($entity, $context);
    $target_type = $entity->getConstraint('EntityType');
    $target_bundles = isset($context['settings']['handler_settings']['target_bundles']) ?
      $context['settings']['handler_settings']['target_bundles'] : [];

    // Build the relation URI, which is used as the property key.
    $field_uri = $this->linkManager->getRelationUri(
      $context['entityTypeId'],
      // Drupal\Core\Entity\Entity::bundle() returns Entity Type ID by default.
      isset($context['bundleId']) ? $context['bundleId'] : $context['entityTypeId'],
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
    if (empty($target_bundles)) {
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

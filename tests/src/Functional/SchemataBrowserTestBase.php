<?php

namespace Drupal\Tests\schemata\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\BrowserTestBase;

/**
 * Sets up functional testing for Schemata.
 */
class SchemataBrowserTestBase extends BrowserTestBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'field',
    'filter',
    'text',
    'node',
    'taxonomy',
    'serialization',
    'hal',
    'schemata',
    'schemata_json_schema',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    if (!NodeType::load('camelids')) {
      // Create a "Camelids" node type.
      NodeType::create([
        'name' => 'Camelids',
        'type' => 'camelids',
      ])->save();
    }

    // Create a "Camelids" vocabulary.
    $vocabulary = Vocabulary::create([
      'name' => 'Camelids',
      'vid' => 'camelids',
    ]);
    $vocabulary->save();

    $entity_types = ['node', 'taxonomy_term'];
    foreach ($entity_types as $entity_type) {
      // Add access-protected field.
      FieldStorageConfig::create([
        'entity_type' => $entity_type,
        'field_name' => 'field_test_' . $entity_type,
        'type' => 'text',
      ])
        ->setCardinality(1)
        ->save();
      FieldConfig::create([
        'entity_type' => $entity_type,
        'field_name' => 'field_test_' . $entity_type,
        'bundle' => 'camelids',
      ])
        ->setLabel('Test field')
        ->setTranslatable(FALSE)
        ->save();
    }
    $this->container->get('router.builder')->rebuild();
    $this->drupalLogin($this->drupalCreateUser(['access schemata data models']));
  }

  /**
   * Requests a Schema via HTTP.
   *
   * @param string $format
   *   The described format.
   * @param string $entity_type_id
   *   Then entity type.
   * @param string|null $bundle_name
   *   The bundle name or NULL.
   */
  protected function requestSchema($format, $entity_type_id, $bundle_name = NULL) {
    $options = [
      'query' => [
        '_format' => 'schema_json',
        '_describes' => $format,
      ],
    ];
    return $this->drupalGet("schemata/$entity_type_id/$bundle_name", $options);
  }

}

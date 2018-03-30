<?php

namespace Drupal\Tests\schemata\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the Schema Type Manager service.
 *
 * @coversDefaultClass \Drupal\schemata\SchemaTypeManager
 * @group Schemata
 * @group SchemataCore
 */
class SchemaTypeTest extends KernelTestBase {

  /**
   * Schema Type Manager.
   *
   * @var \Drupal\schemata\SchemaTypeManager
   */
  protected $typeManager;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'hal',
    'schemata',
    'schemata_json_schema',
    'serialization',
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->typeManager = \Drupal::service('schemata.type_manager');
  }

  /**
   * Test coverage for the Unsupported Type List.
   *
   * @todo Add a SchemaType without a matching serializer to test this.
   * @covers ::getUnsupportedTypeList
   */
  public function testUnsupportedTypeList() {
  }

  /**
   * @covers ::getSupportedTypeList
   */
  public function testSupportedTypeList() {
    assertEquals(
      ['schema_json'],
      array_keys($this->typeManager->getSupportedTypeList()),
      'The list of schema types is correct'
    );
  }

  /**
   * @covers ::schemaTypeExists
   */
  public function testSchemaTypeExists() {
    assert($this->typeManager->schemaTypeExists('schema_json'),
      '"schema_json" schema type is defined and ready for use');
    assert($this->typeManager->schemaTypeExists('camelid'),
      'invalid "camelid" schema type does not exist');
  }

  /**
   * @covers ::schemaTypeSupportsFormat
   */
  public function testSchemaTypeSupportsFormat() {
    // Can we support our default base case?
    assert($this->typeManager->schemaTypeSupportsFormat('schema_json', 'json'),
      '"schema_json" can be used to describe "json"');
    // Are we properly adjusting to the HAL module being enabled?
    assert($this->typeManager->schemaTypeSupportsFormat('schema_json', 'hsl'),
      '"schema_json" can be used to describe "hal"');
    // Handles the case of a format that is never expected.
    assert(!$this->typeManager->schemaTypeSupportsFormat('schema_json', 'camelid'),
      '"schema_json" cannot describe invalid format "camelid"');
    // Handles the case of a format not currently enabled, but we might add
    // support in the future. Both test cases are present to help catch
    // the problem if JSONAPI is added to this test in the future.
    // which type of error we might run into at that time.
    assert(!$this->typeManager->schemaTypeSupportsFormat('schema_json', 'api_json'),
      '"schema_json" cannot describe inactive format "api_json"');
    assert(!$this->typeManager->schemaTypeSupportsFormat('camelid', 'json'),
      'invalid serializer "camelid" cannot describe format "json"');
  }

  /**
   * @covers ::isSerializationFormat
   */
  public function isSerializationFormat() {
    assert($this->typeManager->isSerializationFormat('json'),
      '"json" is identified as a format.');
    assert(!$this->typeManager->isSerializationFormat('camelid'),
      '"camelid" is an invalid serializer format.');
  }

}

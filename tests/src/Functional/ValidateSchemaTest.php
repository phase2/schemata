<?php

namespace Drupal\Tests\schemata\Functional;

use League\JsonGuard\Validator;

/**
 * Tests that generated JSON Schemas are valid as JSON Schema.
 *
 * Without this test, we do not know that the entire schema conforms to the
 * rules that constrain the structure of schemas.
 *
 * @group Schemata
 * @group SchemataJsonSchema
 */
class ValidateSchemaTest extends SchemataBrowserTestBase {

  /**
   * Test the generated schemas are valid JSON Schema.
   */
  public function testSchemataAreValidJsonSchema() {
    foreach (['json', 'hal_json', 'api_json'] as $described_format) {
      foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
        $this->validateSchemaAsJsonSchema($described_format, $entity_type_id);
        if ($bundle_type = $entity_type->getBundleEntityType()) {
          $bundles = $entity_type_manager->getStorage($bundle_type)->loadMultiple();
          foreach ($bundles as $bundle) {
            $this->validateSchemaAsJsonSchema($described_format, $entity_type_id, $bundle->id());
          }
        }
      }
    }
  }

  /**
   * Confirm a schema is inherently valid as a JSON Schema.
   *
   * @param string $format
   *   The described format.
   * @param string $entity_type_id
   *   Then entity type.
   * @param string|null $bundle_id
   *   The bundle name or NULL.
   *
   * @todo how do you handle tests that should stop processing on a failure.
   */
  protected function validateSchemaAsJsonSchema($format, $entity_type_id, $bundle_id = NULL) {
    $json = $this->getRawSchemaByOptions($format, $entity_type_id, $bundle_id);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertTrue(!empty($schema), 'Schema request should provide response content instead of a NULL value.');

    try {
      $data = json_decode($json);
    }
    catch (Exception $e) {
      $this->assertTrue(FALSE, "Could not decode JSON from schema response. Error: " . $e->getMessage());
    }
    $this->assertNotEmpty($data->{'$schema'}, 'JSON Schema should include a $schema reference to a defining schema.');

    // Prepare the schema for validation.
    // By definition of the JSON Schema spec, schemas use a top-level '$schema'
    // key to identify the schema specification with which they conform.
    $schema = $this->getDereferencedSchema($data->{'$schema'});
    $this->assertTrue(!empty($schema), 'The schema specification must be retrieved and dereferenced for use.');

    // Validate our schema is a correct schema.
    $validator = new Validator($data, $schema);
    if ($validator->fails()) {
      $bundle_label = empty($bundle_id) ? 'no-bundle' : $bundle_id;
      $message = "Schema ($entity_type_id:$bundle_label) failed validation for $format:\n";
      $errors = $validator->errors();
      foreach ($errors as $error) {
        $message .= $error->getMessage() . "\n";
      }
      $this->assertTrue(FALSE, $message);
    }

    // Now that the schema has validated correctly, let's confirm an invalid
    // schema will fail validation.
    $data->properties = '';
    $validator = new Validator($data, $schema);
    if (!$validator->fails()) {
      $bundle_label = empty($bundle_id) ? 'no-bundle' : $bundle_id;
      $message = "Schema ($entity_type_id:$bundle_label) should fail validation if it is wrong.\n";
      $this->assertTrue(FALSE, $message);
    }
  }

}

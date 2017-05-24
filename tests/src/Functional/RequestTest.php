<?php

namespace Drupal\Tests\schemataFunctional;

use Behat\Mink\Driver\BrowserKitDriver;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\BrowserTestBase;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests requests schemata routes.
 */
class RequestTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['text', 'node', 'taxonomy', 'serialization', ];

  protected function setUp() {
    parent::setUp();

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



    // @todo Schemata Routes aren't rebuilt after new content type.
    $this->container->get('module_installer')->install(['schemata', 'schemata_json_schema'], TRUE);
    $this->drupalLogin($this->drupalCreateUser(['access schemata data models']));
  }


  /**
   *
   */
  public function testRequests() {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $options = [
      'query' => [
        '_format' => 'schema_json',
        '_describes' => 'json',
      ],
    ];
    foreach ($entity_type_manager->getDefinitions() as $entity_type_id => $entity_type) {

      $response = $this->request('GET', Url::fromRoute("schemata.$entity_type_id", [], $options), []);
      //print $response->getBody()->getContents();
      $this->checkExpectResponse($response, $entity_type_id);
      if ($entity_type_id == 'taxonomy_term') {
        //var_export(json_decode($response->getBody()->getContents(), TRUE));
        //return;
      }
      if ($bundle_type = $entity_type->getBundleEntityType()) {
        $bundles = $entity_type_manager->getStorage($bundle_type)->loadMultiple();
        foreach ($bundles as $bundle) {
          $response = $this->request('GET', Url::fromRoute("schemata.$entity_type_id:{$bundle->id()}", [], $options), []);
          $this->checkExpectResponse($response, $entity_type_id, $bundle->id());
        }
      }
    }
  }

  /**
   * Performs a HTTP request. Wraps the Guzzle HTTP client.
   *
   * Why wrap the Guzzle HTTP client? Because we want to keep the actual test
   * code as simple as possible, and hence not require them to specify the
   * 'http_errors = FALSE' request option, nor do we want them to have to
   * convert Drupal Url objects to strings.
   *
   * We also don't want to follow redirects automatically, to ensure these tests
   * are able to detect when redirects are added or removed.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   *
   * @param string $method
   *   HTTP method.
   * @param \Drupal\Core\Url $url
   *   URL to request.
   * @param array $request_options
   *   Request options to apply.
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  protected function request($method, Url $url, array $request_options) {
    $request_options[RequestOptions::HTTP_ERRORS] = FALSE;
    $request_options[RequestOptions::ALLOW_REDIRECTS] = FALSE;
    $request_options = $this->decorateWithXdebugCookie($request_options);
    $client = $this->getSession()->getDriver()->getClient()->getClient();
    return $client->request($method, $url->setAbsolute(TRUE)->toString(), $request_options);
  }

  /**
   * Adds the Xdebug cookie to the request options.
   *
   * @param array $request_options
   *   The request options.
   *
   * @return array
   *   Request options updated with the Xdebug cookie if present.
   */
  protected function decorateWithXdebugCookie(array $request_options) {
    $session = $this->getSession();
    $driver = $session->getDriver();
    if ($driver instanceof BrowserKitDriver) {
      $client = $driver->getClient();
      foreach ($client->getCookieJar()->all() as $cookie) {
        if (isset($request_options[RequestOptions::HEADERS]['Cookie'])) {
          $request_options[RequestOptions::HEADERS]['Cookie'] .= '; ' . $cookie->getName() . '=' . $cookie->getValue();
        }
        else {
          $request_options[RequestOptions::HEADERS]['Cookie'] = $cookie->getName() . '=' . $cookie->getValue();
        }
      }
    }
    return $request_options;
  }

  protected function checkExpectResponse(ResponseInterface $response, $entity_type_id, $bundle_name = NULL) {
    $this->assertEquals('200', $response->getStatusCode());
    $key = $entity_type_id . ($bundle_name? ":$bundle_name" : '');
    $expected_responses = $this->getExpectResponses();
    if (isset($expected_responses[$key])) {
      //$expected = $expected_responses[$key];
      $this->assertEquals($expected_responses[$key], json_decode($response->getBody()->getContents(), TRUE));
    }
  }

  protected function getExpectResponses() {

    $expected['node:camelids'] = array (
      '$schema' => 'http://json-schema.org/draft-04/schema#',
      'id' => "{$this->baseUrl}/schemata/node/camelids?_format=schema_json&_describes=json",
      'type' => 'object',
      'title' => 'node:camelids Schema',
      'description' => 'Describes the payload for \'node\' entities of the \'camelids\' bundle.',
      'properties' =>
        array (
          'nid' =>
            array (
              'type' => 'array',
              'title' => 'ID',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'integer',
                          'title' => 'Integer value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'minItems' => 1,
              'maxItems' => 1,
            ),
          'uuid' =>
            array (
              'type' => 'array',
              'title' => 'UUID',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text value',
                          'maxLength' => 128,
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'minItems' => 1,
              'maxItems' => 1,
            ),
          'vid' =>
            array (
              'type' => 'array',
              'title' => 'Revision ID',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'integer',
                          'title' => 'Integer value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'minItems' => 1,
              'maxItems' => 1,
            ),
          'langcode' =>
            array (
              'type' => 'array',
              'title' => 'Language',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'string',
                          'title' => 'Language code',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'maxItems' => 1,
            ),
          'type' =>
            array (
              'type' => 'array',
              'title' => 'Content type',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'target_id' =>
                        array (
                          'type' => 'string',
                          'title' => 'Content type ID',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'target_id',
                    ),
                ),
              'minItems' => 1,
              'maxItems' => 1,
            ),
          'revision_timestamp' =>
            array (
              'type' => 'array',
              'title' => 'Revision create time',
              'description' => 'The time that the current revision was created.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'number',
                          'title' => 'Timestamp value',
                          'format' => 'utc-millisec',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'maxItems' => 1,
            ),
          'revision_uid' =>
            array (
              'type' => 'array',
              'title' => 'Revision user',
              'description' => 'The user ID of the author of the current revision.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'target_id' =>
                        array (
                          'type' => 'integer',
                          'title' => 'User ID',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'target_id',
                    ),
                  'title' => 'User',
                  'description' => 'The referenced entity',
                ),
              'maxItems' => 1,
            ),
          'revision_log' =>
            array (
              'type' => 'array',
              'title' => 'Revision log message',
              'description' => 'Briefly describe the changes you have made.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'default' =>
                array (
                  0 =>
                    array (
                      'value' => '',
                    ),
                ),
              'maxItems' => 1,
            ),
          'status' =>
            array (
              'type' => 'array',
              'title' => 'Publishing status',
              'description' => 'A boolean indicating the published state.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'boolean',
                          'title' => 'Boolean value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'default' =>
                array (
                  0 =>
                    array (
                      'value' => true,
                    ),
                ),
              'maxItems' => 1,
            ),
          'title' =>
            array (
              'type' => 'array',
              'title' => 'Title',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text value',
                          'maxLength' => 255,
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'minItems' => 1,
              'maxItems' => 1,
            ),
          'uid' =>
            array (
              'type' => 'array',
              'title' => 'Authored by',
              'description' => 'The username of the content author.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'target_id' =>
                        array (
                          'type' => 'integer',
                          'title' => 'User ID',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'target_id',
                    ),
                  'title' => 'User',
                  'description' => 'The referenced entity',
                ),
              'maxItems' => 1,
            ),
          'created' =>
            array (
              'type' => 'array',
              'title' => 'Authored on',
              'description' => 'The time that the node was created.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'number',
                          'title' => 'Timestamp value',
                          'format' => 'utc-millisec',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'maxItems' => 1,
            ),
          'changed' =>
            array (
              'type' => 'array',
              'title' => 'Changed',
              'description' => 'The time that the node was last edited.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'number',
                          'title' => 'Timestamp value',
                          'format' => 'utc-millisec',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'maxItems' => 1,
            ),
          'promote' =>
            array (
              'type' => 'array',
              'title' => 'Promoted to front page',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'boolean',
                          'title' => 'Boolean value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'default' =>
                array (
                  0 =>
                    array (
                      'value' => true,
                    ),
                ),
              'maxItems' => 1,
            ),
          'sticky' =>
            array (
              'type' => 'array',
              'title' => 'Sticky at top of lists',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'boolean',
                          'title' => 'Boolean value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'default' =>
                array (
                  0 =>
                    array (
                      'value' => false,
                    ),
                ),
              'maxItems' => 1,
            ),
          'revision_translation_affected' =>
            array (
              'type' => 'array',
              'title' => 'Revision translation affected',
              'description' => 'Indicates if the last edit of a translation belongs to current revision.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'boolean',
                          'title' => 'Boolean value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'minItems' => 1,
              'maxItems' => 1,
            ),
          'default_langcode' =>
            array (
              'type' => 'array',
              'title' => 'Default translation',
              'description' => 'A flag indicating whether this is the default translation.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'boolean',
                          'title' => 'Boolean value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'default' =>
                array (
                  0 =>
                    array (
                      'value' => true,
                    ),
                ),
              'maxItems' => 1,
            ),
          'field_test_node' =>
            array (
              'type' => 'array',
              'title' => 'Test field',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text',
                          'maxLength' => 255,
                        ),
                      'format' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text format',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'maxItems' => 1,
            ),
        ),
      'required' =>
        array (
          0 => 'nid',
          1 => 'uuid',
          2 => 'vid',
          3 => 'type',
          4 => 'title',
          5 => 'revision_translation_affected',
        ),
    );

    $expected['taxonomy_term:camelids'] = array (
      '$schema' => 'http://json-schema.org/draft-04/schema#',
      'id' => "{$this->baseUrl}/schemata/taxonomy_term/camelids?_format=schema_json&_describes=json",
      'type' => 'object',
      'title' => 'taxonomy_term:camelids Schema',
      'description' => 'Describes the payload for \'taxonomy_term\' entities of the \'camelids\' bundle.',
      'properties' =>
        array (
          'tid' =>
            array (
              'type' => 'array',
              'title' => 'Term ID',
              'description' => 'The term ID.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'integer',
                          'title' => 'Integer value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'minItems' => 1,
              'maxItems' => 1,
            ),
          'uuid' =>
            array (
              'type' => 'array',
              'title' => 'UUID',
              'description' => 'The term UUID.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text value',
                          'maxLength' => 128,
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'minItems' => 1,
              'maxItems' => 1,
            ),
          'langcode' =>
            array (
              'type' => 'array',
              'title' => 'Language',
              'description' => 'The term language code.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'string',
                          'title' => 'Language code',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'maxItems' => 1,
            ),
          'vid' =>
            array (
              'type' => 'array',
              'title' => 'Vocabulary',
              'description' => 'The vocabulary to which the term is assigned.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'target_id' =>
                        array (
                          'type' => 'string',
                          'title' => 'Taxonomy vocabulary ID',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'target_id',
                    ),
                ),
              'minItems' => 1,
              'maxItems' => 1,
            ),
          'name' =>
            array (
              'type' => 'array',
              'title' => 'Name',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text value',
                          'maxLength' => 255,
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'minItems' => 1,
              'maxItems' => 1,
            ),
          'description' =>
            array (
              'type' => 'array',
              'title' => 'Description',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text',
                        ),
                      'format' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text format',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'maxItems' => 1,
            ),
          'weight' =>
            array (
              'type' => 'array',
              'title' => 'Weight',
              'description' => 'The weight of this term in relation to other terms.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'integer',
                          'title' => 'Integer value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'default' =>
                array (
                  0 =>
                    array (
                      'value' => 0,
                    ),
                ),
              'maxItems' => 1,
            ),
          'parent' =>
            array (
              'type' => 'array',
              'title' => 'Term Parents',
              'description' => 'The parents of this term.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'target_id' =>
                        array (
                          'type' => 'integer',
                          'title' => 'Taxonomy term ID',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'target_id',
                    ),
                  'title' => 'Taxonomy term',
                  'description' => 'The referenced entity',
                ),
            ),
          'changed' =>
            array (
              'type' => 'array',
              'title' => 'Changed',
              'description' => 'The time that the term was last edited.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'number',
                          'title' => 'Timestamp value',
                          'format' => 'utc-millisec',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'maxItems' => 1,
            ),
          'default_langcode' =>
            array (
              'type' => 'array',
              'title' => 'Default translation',
              'description' => 'A flag indicating whether this is the default translation.',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'boolean',
                          'title' => 'Boolean value',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'default' =>
                array (
                  0 =>
                    array (
                      'value' => true,
                    ),
                ),
              'maxItems' => 1,
            ),
          'field_test_taxonomy_term' =>
            array (
              'type' => 'array',
              'title' => 'Test field',
              'items' =>
                array (
                  'type' => 'object',
                  'properties' =>
                    array (
                      'value' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text',
                          'maxLength' => 255,
                        ),
                      'format' =>
                        array (
                          'type' => 'string',
                          'title' => 'Text format',
                        ),
                    ),
                  'required' =>
                    array (
                      0 => 'value',
                    ),
                ),
              'maxItems' => 1,
            ),
        ),
      'required' =>
        array (
          0 => 'tid',
          1 => 'uuid',
          2 => 'vid',
          3 => 'name',
        ),
    );

    $expected['taxonomy_term'] = array (
      '$schema' => 'http://json-schema.org/draft-04/schema#',
      'id' => "{$this->baseUrl}/schemata/taxonomy_term?_format=schema_json&_describes=json",
      'type' => 'object',
      'title' => 'taxonomy_term Schema',
      'description' => 'Describes the payload for \'taxonomy_term\' entities.',
    ) + $expected['taxonomy_term:camelids'];
    unset($expected['taxonomy_term']['properties']['field_test_taxonomy_term']);

    $expected['node'] = array (
        '$schema' => 'http://json-schema.org/draft-04/schema#',
        'id' => "{$this->baseUrl}/schemata/node?_format=schema_json&_describes=json",
        'type' => 'object',
        'title' => 'node Schema',
        'description' => 'Describes the payload for \'node\' entities.',
      ) + $expected['node:camelids'];
    unset($expected['node']['properties']['field_test_node']);
    return $expected;
  }

}

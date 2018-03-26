# CONTRIBUTING

## Add a New Schema Type Format

Before submitting a change for a new Schema Type, [create an issue](https://www.drupal.org/project/issues/schemata)
to discuss the new format. In most cases you will be asked to create this as a
separate project dependent on Schemata, but this issue will also serve for
preliminary guidance and early notice for a new project link from the Schemata
Project page and README.

### Technical Steps

To add a new Schema Type format, you will take the following steps:

* Create a set of Normalizers that can process a SchemaInterface.
* Create a SchemaType plugin that declares your Schema Type and what formats it supports.
* Implement Drupal\Core\DependencyInjection\ServiceModifierInterface to
  associate a MIME Type with your Schema Type. In the future this may be done
  automatically by Schemata by adding a MIME Type key to the SchemaType plugin.

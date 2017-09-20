# CONTRIBUTING

## Add a New Schema Type Format

Before submitting a change for a new Schema Type, [create an issue](https://www.drupal.org/project/issues/schemata)
to discuss the new format. In most cases you will be asked to create this as a
separate project dependent on Schemata, but this issue will also serve as a good
place to receive preliminary guidance and notice of a new project to link from
the Schemata README and Project page.

### Technical Steps

If you want to add a new Schema Type format, you will need to take the following steps:

* Create a set of Normalizers that can converted a SchemaInterface into your format.
* Create a SchemaType plugin that declares what your Schema Type is and what it supports.
* Implement Drupal\Core\DependencyInjection\ServiceModifierInterface to
  associated a MIME Type with your Schema Type. In the future this may be done
  automatically by Schemata by adding a MIME Type key to the SchemaType plugin.

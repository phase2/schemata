#!/bin/bash

# Run either PHPUnit tests or CODE_QUALITY tests on Travis CI, depending
# on the passed in parameter.
#
# Adapted from https://github.com/Gizra/og/blob/8.x-1.x/scripts/travis-ci/run-test.sh

case "$1" in
    CODE_QUALITY)
        cd $MODULE_DIR
        composer install
        composer run-script quality
        exit $?
        ;;
    *)
        ln -s $MODULE_DIR $DRUPAL_DIR/modules/schemata
        cd $DRUPAL_DIR
        ./vendor/bin/phpunit -c ./core/phpunit.xml.dist $MODULE_DIR/tests
        exit $?
esac

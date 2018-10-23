# Magento 2 Composer Plugin

This composer plugin adds a check after `composer install` to see whether all modules that are defined in Magento 2's `app/etc/config.php` are actually present in the codebase.

If an extension is defined in `app/etc/config.php` and it is not available in the codebase, Magento 2 will just deploy and will give a warning in production. This plugin ensures the build step will fail due to a non-zero exit code this extension throws if it finds something wrong.

## Installation
```
composer require elgentos/magento2-composer-plugin
```

## Optional configuration
You can add an array to `composer.json` under `extra` to ignore some extensions;

```json
{
  "extra": {
      "magento2-ignore-extensions": [
          "Mageplaza_Core",
          "Mageplaza_DeleteOrders"
      ]
  }
}
```
## Overview
This project was developed to present how to add new content type programmatically.

The module create new content type **Product**.

Each time a new product is added a notification is sent to a configurable email.

## Installing the module
You can install the module from the Drupal admin. But it's better to use Drupal console. First copy the folder product_manager on your modules folder. than run the command:

$ drupal module:install product_manager

To change the recipient email move to _admin/config/product_manager/config_. than enter a valid email

To uninstall it:

$ drupal module:uninstall product_manager

This will remove the content type Product and unistall the module

## Dev Env.
- Widnows 7 Pro
- Ampps Stack
- git
- Drupal 8.3
- PhpStorm
- Papercut for emulating SMTP server
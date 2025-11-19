=== Multi Purpose Plugin ===
Contributors: servetech
Tags: multipurpose, stripe, fluentforms, admin tools
Requires at least: 5.0
Tested up to: 6.x
Requires PHP: 7.4+
License: GPLv2 or later

== Plugin Structure Overview ==

The Multi Purpose Plugin is a modular WordPress plugin built using an extensible and organized architecture.  
It supports separate admin modules, public modules, third-party integrations, and asset management under one unified plugin framework.

Below is the complete folder structure used inside the plugin.

multi-purpose-plugin/
│
├─ assets/
│   ├─ js/
│   │   └─ Contains all JavaScript files used across the plugin.
│   ├─ css/
│       └─ Contains all CSS stylesheets for both admin and frontend.
│
├─ src/
│   ├─ Plugin.php
│   │   └─ Core bootstrap class responsible for initializing all plugin modules,
│           loading dependencies, registering hooks, and defining constants.
│
│   ├─ Includes/
│   │   └─ GivingLink.php
│   │        → Contains business logic and backend functionality such as
│              FluentForms donation processing, Stripe integration, and
│              helper functions that run outside the admin UI.
│
│   ├─ Admin/
│   │   ├─ Submenus/
│   │   │   └─ StripeSettings.php
│   │   │        → Admin submenu page for configuring Stripe API keys,
│   │   │          mode selection, and other settings.
│   │   └─ Admin.php
│   │        → Registers admin menu pages, submenus, and loads admin scripts.
│
│   └─ Public/
│       └─ PublicScripts.php
│            → Handles all frontend script and style loading, and public-facing hooks.
│
├─ vendor/
│   → Contains Composer-installed libraries such as Stripe PHP SDK and autoloaded classes.
│
├─ composer.json
│   → Defines PHP dependencies, namespaces, and PSR-4 autoloading structure.
│
├─ composer.lock
│   → Locked dependency versions for production stability.
│
└─ multi-purpose-plugin.php
    → Main plugin entry file. Loads the autoloader, registers activation hooks,
      and initializes the core Plugin class from /src/Plugin.php.

== Libraries & Dependencies ==

This plugin uses the following libraries via Composer:

- **Stripe/stripe-php**  
  Provides full Stripe API functionality used for payments, customers,
  subscriptions, checkout sessions, and financial workflows.

- **Composer Autoloader**  
  Handles PSR-4 class autoloading for:
  - `/src/Plugin.php`
  - `/src/Includes/`
  - `/src/Admin/`
  - `/src/Public/`

This ensures clean class namespacing and automatic loading of PHP classes.

== Summary ==

The Multi Purpose Plugin is built using a clean, modern plugin architecture with:
- PSR-4 autoloading  
- Organized folder separation (Admin, Public, Includes)  
- Composer dependency management  
- Modular structure for adding new features without affecting existing ones  
- Built-in Stripe and FluentForms integration (via GivingLink.php)

This structure makes the plugin easy to extend, maintain, test, and scale.



STRUCTURE

multi-purpose-plugin/
├─ assets/
│  ├─ js
│  │   └─ All Java Script Files
│  ├─ css
│  │   └─ All Css Files
├─ src/
│  ├─ Plugin.php
│  ├─ Includes/
│  │   └─ GivingLink.php
│  ├─ Admin/
│  │   └─ Submenus
│  │   │   └─ StripeSettings.php
│  │   └─ Admin.php
│  └─ Public/
│      └─ PublicScripts.php
├─ vendor/
├─ composer.json
├─ composer.lock
└─ multi-purpose-plugin.php
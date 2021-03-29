<p align="center"><img src="./src/icon.svg" width="100" height="100" alt="Azure Blog Storage for Craft CMS icon"></p>

<h1 align="center">Azure Blob Storage for Craft CMS</h1>

This plugin provides an [Azure Blob Storage](https://azure.microsoft.com/en-us/services/storage/blobs/) integration for [Craft CMS](https://craftcms.com/).

## Requirements

This plugin requires Craft CMS 3.1.5 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Azure Blob Storage”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/azure-blob

# tell Craft to install the plugin
./craft install/plugin craftcms/azure-blob
```

## Setup

To create a new asset volume for your Azure container, go to **Settings** → **Assets**, create a new volume, and set the Volume Type setting to “Azure Blob Storage”.

> **Tip:** The Connection String, Base URL, Container, and Subfolder settings can be set to environment variables. See [Environmental Configuration](https://docs.craftcms.com/v3/config/environments.html) in the Craft docs to learn more about that.

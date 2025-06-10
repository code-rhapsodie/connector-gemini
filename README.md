# Code Rhapsodie Connector Gemini Bundle

The code-rhapsodie/connector-gemini bundle integrates Google Gemini into Ibexa DXP, enabling AI-assisted content generation and editing capabilities directly from the Ibexa Back Office.

## Installation

### Step 1: Install the bundle via composer
```bash
  composer require code-rhapsodie/connector-gemini
```

### Step 2: Enable the bundle
````php
// config/bundles.php

return [
    // ...
    CodeRhapsodie\Bundle\ConnectorGemini\CRConnectorGeminiBundle::class => ['all' => true],
];
````

### Step 3: Configure your api key
```dotenv
#.env

GEMINI_API_KEY=your-google-gemini-api-key-here
```

### Step 4: Import generic IA Action migration
```bash
  php bin/console ibexa:migrations:import vendor/code-rhapsodie/connector-gemini/src/bundle/Resources/migrations/action_configurations.yaml
```

### Step 5: Execute Ibexa migration
```bash
  php bin/console ibexa:migrations:migrate
```
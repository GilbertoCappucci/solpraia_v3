# Contexto para Gemini CLI

Este arquivo contém informações importantes para o assistente Gemini CLI, visando fornecer contexto e parâmetros consistentes para todas as interações.

## Informações do Ambiente

- **Data Atual:** 20 de dezembro de 2025
- **Sistema Operacional:** win32
- **Diretório Temporário do Projeto:** `C:\Users\gilbe\.gemini\tmp\e5677433834bf166bd8bc24adec085928af6522ec3a8d3ccc1ebb2b0c1ba189d`
- **Diretório de Trabalho Atual:** `E:\Projects\solpraia`

## Estrutura de Pastas

A estrutura de pastas do projeto (última atualização):

```
E:\Projects\solpraia\
├───.editorconfig
├───.env.example
├───.gitattributes
├───.gitignore
├───artisan
├───composer.json
├───composer.lock
├───COPILOT_GUIDELINES.md
├───package-lock.json
├───package.json
├───phpunit.xml
├───vite.config.js
├───.git\...
├───.github\
│   └───workflows\
│       ├───lint.yml
│       └───tests.yml
├───app\
│   ├───helpers.php
│   ├───Actions\
│   │   └───Fortify\
│   │       ├───CreateNewUser.php
│   │       ├───PasswordValidationRules.php
│   │       └───ResetUserPassword.php
│   ├───Console\
│   │   └───Commands\
│   ├───Enums\
│   │   ├───CheckStatusEnum.php
│   │   ├───DepartamentEnum.php
│   │   ├───OrderStatusEnum.php
│   │   ├───RoleEnum.php
│   │   └───TableStatusEnum.php
│   ├───Events\
│   ├───Filament\
│   │   └───Resources\
│   ├───Http\
│   │   ├───Controllers\
│   │   └───Middleware\
│   ├───Livewire\
│   │   ├───Check.php
│   │   ├───Menu.php
│   │   ├───Orders.php
│   │   ├───Tables.php
│   │   ├───Actions\
│   │   └───Settings\
│   ├───Models\
│   │   ├───Category.php
│   │   ├───Check.php
│   │   ├───Departament.php
│   │   ├───GlobalSetting.php
│   │   ├───Invoice.php
│   │   ├───Menu.php
│   │   ├───MenuItem.php
│   │   ├───Order.php
│   │   ├───OrderStatusHistory.php
│   │   ├───Product.php
│   │   ├───Setting.php
│   │   ├───Stock.php
│   │   ├───Table.php
│   │   ├───User.php
│   │   └───UserPreference.php
│   ├───Observers\
│   │   └───UserObserver.php
│   ├───Providers\
│   │   ├───AppServiceProvider.php
│   │   ├───FortifyServiceProvider.php
│   │   └───Filament\
│   └───Services\
│       ├───CheckService.php
│       ├───GlobalSettingService.php
│       ├───MenuService.php
│       ├───OrderService.php
│       ├───PixService.php
│       ├───StockService.php
│       ├───TableService.php
│       └───UserPreferenceService.php
├───bootstrap\
│   ├───app.php
│   ├───providers.php
│   └───cache\
│       ├───.gitignore
│       └───filament\...
├───config\
│   ├───app.php
│   ├───auth.php
│   ├───cache.php
│   ├───database.php
│   ├───filesystems.php
│   ├───fortify.php
│   ├───logging.php
│   ├───mail.php
│   ├───queue.php
│   ├───restaurant.php
│   ├───sanctum.php
│   ├───services.php
│   ├───session.php
│   └───simulator.php
├───database\
│   ├───.gitignore
│   ├───factories\
│   │   ├───CategoryFactory.php
│   │   ├───CheckFactory.php
│   │   ├───DepartamentFactory.php
│   │   ├───GlobalSettingFactory.php
│   │   ├───MenuFactory.php
│   │   ├───MenuItemFactory.php
│   │   ├───OrderFactory.php
│   │   ├───ProductFactory.php
│   │   ├───SettingFactory.php
│   │   ├───StockFactory.php
│   │   ├───TableFactory.php
│   │   ├───UserFactory.php
│   │   └───UserPreferenceFactory.php
│   ├───migrations\
│   │   ├───0001_01_01_000000_create_users_table.php
│   │   ├───0001_01_01_000001_create_cache_table.php
│   │   ├───0001_01_01_000002_create_jobs_table.php
│   │   ├───0001_01_01_000003_create_personal_access_tokens_table.php
│   │   ├───2025_09_22_145432_add_two_factor_columns_to_users_table.php
│   │   ├───2025_10_27_145432_create_menus_table.php
│   │   ├───2025_10_28_120600_create_categories_table.php
│   │   ├───2025_10_28_120623_create_products_table.php
│   │   ├───2025_10_28_120624_create_stocks_table.php
│   │   ├───2025_11_10_150446_create_menu_items_table.php
│   │   ├───2025_11_11_204303_create_tables_table.php
│   │   ├───2025_11_13_205726_create_checks_table.php
│   │   ├───2025_11_13_210631_create_orders_table.php
│   │   ├───2025_12_04_124140_create_order_status_history_table.php
│   │   ├───2025_12_13_160556_create_user_preferences_table.php
│   │   └───2025_12_13_160642_create_global_settings_table.php
│   └───seeders\
│       ├───CategorySeeder.php
│       ├───CheckSeeder.php
│       ├───DatabaseSeeder.php
│       ├───GlobalSettingSeeder.php
│       ├───MenuItemSeeder.php
│       ├───MenuSeeder.php
│       ├───OrderSeeder.php
│       ├───ProductSeeder.php
│       ├───SettingSeeder.php
│       ├───StockSeeder.php
│       ├───TableSeeder.php
│       ├───UserPreferenceSeeder.php
│       └───UserSeeder.php
├───node_modules\...
├───public\
│   ├───.htaccess
│   ├───apple-touch-icon.png
│   ├───favicon.ico
│   ├───favicon.svg
│   ├───index.php
│   ├───robots.txt
│   ├───css\
│   │   └───filament\
│   ├───fonts\
│   │   └───filament\
│   ├───images\
│   │   └───hero-bg.png
│   └───js\
│       └───filament\
├───resources\
│   ├───css\
│   │   └───app.css
│   ├───js\
│   │   └───app.js
│   └───views\
│       ├───dashboard.blade.php
│       ├───welcome.blade.php
│       ├───components\
│       ├───flux\
│       ├───livewire\
│       └───partials\
├───routes\
│   ├───api.php
│   ├───console.php
│   └───web.php
├───storage\
│   ├───app\
│   │   ├───.gitignore
│   │   ├───private\...
│   │   └───public\...
│   ├───framework\
│   │   ├───.gitignore
│   │   ├───cache\
│   │   ├───sessions\
│   │   ├───testing\
│   │   └───views\
│   └───logs\
│       └───.gitignore
├───tests\
│   ├───Pest.php
│   ├───TestCase.php
│   ├───Feature\
│   │   ├───DashboardTest.php
│   │   ├───ExampleTest.php
│   │   ├───Auth\
│   │   └───Settings\
│   └───Unit\
│       └───ExampleTest.php
└───vendor\...
```

## Diretrizes Adicionais

- **Idioma:** Português/Brasil
- **Convenções de Código:** Adotar as convenções existentes no projeto Laravel.


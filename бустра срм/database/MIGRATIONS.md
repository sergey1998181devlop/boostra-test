# Документация по миграциям Phinx

## Обзор

В проекте используется [Phinx](https://phinx.org/) для управления миграциями базы данных. Phinx позволяет создавать, применять и откатывать изменения в структуре базы данных.

## Конфигурация

Файл конфигурации: `phinx.php` (в корне проекта)

Путь к миграциям: `database/migrations/`

## Основные команды

### Создание новой миграции

```bash
docker exec -it boostra-crm ./vendor/bin/phinx create -c phinx.php ИмяМиграции
```

**Пример:**
```bash
docker exec -it boostra-crm ./vendor/bin/phinx create -c phinx.php CreateUsersTable
```

Создает файл: `database/migrations/YYYYMMDDHHMMSS_create_users_table.php`

### Применение миграций

```bash
docker exec -it boostra-crm ./vendor/bin/phinx migrate -c phinx.php
```

**Опции:**
- `-v` - подробный вывод
- `--target=VERSION` - применить до конкретной версии
- `--dry-run` - показать что будет выполнено без применения

**Примеры:**
```bash
# Применить все миграции
docker exec -it boostra-crm ./vendor/bin/phinx migrate -c phinx.php

# С подробным выводом
docker exec -it boostra-crm ./vendor/bin/phinx migrate -c phinx.php -v

# Применить до конкретной версии
docker exec -it boostra-crm ./vendor/bin/phinx migrate -c phinx.php --target=20250729123703
```

### Откат миграций

```bash
docker exec -it boostra-crm ./vendor/bin/phinx rollback -c phinx.php
```

**Опции:**
- `-t VERSION` - откатить до конкретной версии
- `-d DATE` - откатить до конкретной даты
- `-f` - принудительный откат

**Примеры:**
```bash
# Откатить последнюю миграцию
docker exec -it boostra-crm ./vendor/bin/phinx rollback -c phinx.php

# Откатить до конкретной версии
docker exec -it boostra-crm ./vendor/bin/phinx rollback -c phinx.php -t 20250729123703

# Откатить до конкретной даты
docker exec -it boostra-crm ./vendor/bin/phinx rollback -c phinx.php -d 2025-07-29
```

### Просмотр статуса миграций

```bash
docker exec -it boostra-crm ./vendor/bin/phinx status -c phinx.php
```

Показывает:
- Статус каждой миграции (up/down)
- Время начала и завершения
- Имя миграции

## Сидеры (Seeders)

Сидеры используются для заполнения базы данных тестовыми или начальными данными.

### Создание сидера

```bash
docker exec -it boostra-crm ./vendor/bin/phinx seed:create -c phinx.php ИмяСидера
```

**Пример:**
```bash
docker exec -it boostra-crm ./vendor/bin/phinx seed:create -c phinx.php TestDataSeeder
```

Создает файл: `database/seeds/YYYYMMDDHHMMSS_test_data_seeder.php`

### Запуск сидеров

```bash
docker exec -it boostra-crm ./vendor/bin/phinx seed:run -c phinx.php
```

**Опции:**
- `-s SEEDER` - запустить конкретный сидер
- `-v` - подробный вывод

**Примеры:**
```bash
# Запустить все сидеры
docker exec -it boostra-crm ./vendor/bin/phinx seed:run -c phinx.php

# Запустить конкретный сидер
docker exec -it boostra-crm ./vendor/bin/phinx seed:run -c phinx.php -s TestDataSeeder

# С подробным выводом
docker exec -it boostra-crm ./vendor/bin/phinx seed:run -c phinx.php -v
```

### Структура файла сидера

```php
<?php

use Phinx\Seed\AbstractSeed;

class ИмяКласса extends AbstractSeed
{
    public function run(): void
    {
        // Код для заполнения данных
    }
}
```

### Примеры сидеров

#### Простое заполнение таблицы

```php
public function run(): void
{
    $data = [
        [
            'name' => 'Тест 1',
            'created_at' => date('Y-m-d H:i:s'),
        ],
        [
            'name' => 'Тест 2',
            'created_at' => date('Y-m-d H:i:s'),
        ],
    ];

    $table = $this->table('test_table');
    $table->insert($data)->save();
}
```

#### Заполнение с использованием Faker

```php
public function run(): void
{
    $faker = Faker\Factory::create('ru_RU');
    
    $data = [];
    for ($i = 0; $i < 10; $i++) {
        $data[] = [
            'name' => $faker->name,
            'email' => $faker->email,
            'created_at' => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
        ];
    }

    $table = $this->table('users');
    $table->insert($data)->save();
}
```

## Структура файла миграции

```php
<?php

use Phinx\Migration\AbstractMigration;

final class ИмяКласса extends AbstractMigration
{
    public function change(): void
    {
        // Код миграции
    }
}
```

## Примеры миграций

### Создание таблицы

```php
public function change(): void
{
    $table = $this->table('users');
    
    $table->addColumn('name', 'string', [
        'limit' => 255,
        'null' => false,
    ]);
    
    $table->addColumn('email', 'string', [
        'limit' => 255,
        'null' => false,
    ]);
    
    $table->addColumn('created_at', 'datetime', [
        'null' => false,
        'default' => 'CURRENT_TIMESTAMP',
    ]);
    
    $table->create();
}
```

### Добавление колонки

```php
public function change(): void
{
    $table = $this->table('users');
    
    $table->addColumn('phone', 'string', [
        'limit' => 20,
        'null' => true,
        'after' => 'email',
    ]);
    
    $table->update();
}
```

### Создание индекса

```php
public function change(): void
{
    $table = $this->table('users');
    
    $table->addIndex(['email'], ['unique' => true]);
    $table->addIndex(['name']);
    
    $table->update();
}
```

### Создание внешнего ключа

```php
public function change(): void
{
    $table = $this->table('posts');
    
    $table->addColumn('user_id', 'integer', [
        'signed' => false,
        'null' => false,
    ]);
    
    $table->addForeignKey('user_id', 'users', 'id', [
        'delete' => 'CASCADE',
        'update' => 'CASCADE',
    ]);
    
    $table->update();
}
```

## Важные моменты

### Автоматическая колонка ID

Phinx автоматически добавляет колонку `id` (INT AUTO_INCREMENT PRIMARY KEY) к любой создаваемой таблице. **Не добавляйте её вручную!**

### Обратимость миграций

Используйте метод `change()` для создания обратимых миграций. Phinx автоматически создаст код для отката.

### Имена классов

- Используйте CamelCase для имен классов
- Имя класса должно соответствовать имени файла
- **ВАЖНО**: Название класса в файле миграции должно совпадать с частью в названии миграции
- Пример: `CreateUsersTable` для файла `create_users_table.php`
- Пример: `AddPhoneToUsers` для файла `add_phone_to_users.php`

### Типы данных

Основные типы:
- `string` - VARCHAR
- `text` - TEXT
- `integer` - INT
- `biginteger` - BIGINT
- `boolean` - BOOLEAN
- `datetime` - DATETIME
- `timestamp` - TIMESTAMP
- `decimal` - DECIMAL

## Устранение проблем

### Миграция не видна

1. Проверьте путь в конфигурации: `database/migrations/`
2. Убедитесь, что файл имеет правильное расширение `.php`
3. Проверьте синтаксис PHP в файле

### Ошибка "Column already exists"

- Phinx автоматически добавляет колонку `id`
- Не добавляйте колонку `id` вручную в миграции

### Ошибка "Table already exists"

- Удалите таблицу вручную или используйте `DROP TABLE IF EXISTS`
- Проверьте статус миграций: `phinx status`

### Проблемы с подключением к БД

- Проверьте настройки в `api/Simpla.php`
- Убедитесь, что Docker контейнер запущен
- Проверьте доступность базы данных

## Полезные команды

```bash
# Показать все доступные команды
docker exec -it boostra-crm ./vendor/bin/phinx list

# Показать информацию о миграции
docker exec -it boostra-crm ./vendor/bin/phinx info -c phinx.php

# Показать SQL без выполнения
docker exec -it boostra-crm ./vendor/bin/phinx migrate -c phinx.php --dry-run
```
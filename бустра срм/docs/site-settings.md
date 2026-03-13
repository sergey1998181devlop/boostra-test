# Система настроек сайтов

## Обзор

Система настроек позволяет управлять конфигурацией на двух уровнях:

- **Глобальные настройки** - применяются ко всем сайтам (`site_id IS NULL`)
- **Специфичные настройки** - только для конкретного сайта (`site_id = 'soyaplace'`)

Одна база данных → множество сайтов с разными настройками.

📖 **Быстрый старт:** [Как работать с настройками](#работа-с-настройками) | [Примеры использования](#примеры-использования) | [Добавление новых настроек](#добавление-новых-настроек)

---

## Работа с настройками

**Файл:** `api/Settings.php`

```php
// Загрузить настройки сайта
$this->settings->setSiteId('soyaplace'); // Настройки сайта
$this->settings->setSiteId(null);        // Глобальные настройки

// Получить список настроек
$names = $this->settings->getVisibleSettingNames();

// Чтение/запись через магические методы
$email = $this->settings->header_email;
$this->settings->header_email = 'new@example.com';

// Массивы сериализуются автоматически
$this->settings->utm_sources = ['source1', 'source2'];
```

**Важно:**
- Настройки НЕ объединяются - либо глобальные, либо сайта
- Массивы автоматически сериализуются/десериализуются
- При записи несуществующей настройки она создается автоматически

---

## Добавление новых настроек

### 1. Создание миграции

```bash
vendor/bin/phinx create AddNewSiteSettings
```

```php
final class AddNewSiteSettings extends AbstractMigration
{
    public function up(): void
    {
        // Для конкретного сайта
        $this->execute("INSERT INTO s_settings (site_id, name, value)
                       VALUES ('soyaplace', 'new_feature_enabled', '1');");

        // Глобальная настройка
        $this->execute("INSERT INTO s_settings (site_id, name, value)
                       VALUES (NULL, 'global_timeout', '3600');");
    }

    public function down(): void
    {
        $this->execute("DELETE FROM s_settings
                       WHERE site_id = 'soyaplace' AND name = 'new_feature_enabled';");
        $this->execute("DELETE FROM s_settings
                       WHERE site_id IS NULL AND name = 'global_timeout';");
    }
}
```

```bash
vendor/bin/phinx migrate
```

### 2. Добавление PHPDoc в Settings.php

```php
/**
 * @property string $new_feature_enabled Включена ли новая функция
 * @property int $global_timeout Глобальный таймаут в секундах
 */
class Settings extends Simpla { }
```

### 3. Добавление в интерфейс админки (опционально)

Если нужно управлять настройкой через веб-интерфейс:

1. Выберите подходящую секцию в `design/manager/html/site_settings/sections/` или создайте новый файл
2. Добавьте поле формы с именем настройки
3. **Обязательно добавьте условную проверку** видимости: `$current_site_id` и `$site_setting_names`
4. При создании новой секции подключите её в `site_settings.tpl` через `{include}`

### 4. Кастомная обработка (опционально)

Если настройка требует преобразования, добавьте в `processCustomSettings()` файла `view/SiteSettingsView.php`:

```php
// Для UTM-настроек с CSV → массив
$simpleUtmSettings = [
    'autoconfirm_flow_utm_sources',
    'my_new_utm_setting', // <-- Добавить здесь
];
```

---

## Настройка конфигурации

Для корректной работы с настройками добавьте секцию `[site]` в конфигурационном файле сайта:

**Файл:** `{site}/config/config.php`

```ini
[site]
; ID сайта из таблицы s_sites
; Пустое значение или отсутствие секции = только глобальные настройки
site_id = "soyaplace";
```

---

## Примеры использования

### Проверка существования настройки

```php
$names = $this->settings->getVisibleSettingNames();

if (in_array('new_feature_enabled', $names)) {
    $value = $this->settings->new_feature_enabled;
} else {
    $value = false; // Значение по умолчанию
}
```

---

## FAQ

**В: Какие настройки загружены?**
```php
var_dump($this->settings->getVisibleSettingNames());
```

**В: Настройка не найдена?**
```php
$value = $this->settings->non_existent; // null
```

**В: Как удалить настройку?**
```php
$this->execute("DELETE FROM s_settings
               WHERE site_id = 'soyaplace' AND name = 'setting_name';");
```

---

## Структура базы данных

### Таблица: `s_settings`

| Поле      | Тип          | Описание                                |
|-----------|--------------|----------------------------------------|
| `id`      | INT          | Первичный ключ                         |
| `site_id` | VARCHAR(50)  | ID сайта (NULL = глобальные)           |
| `name`    | VARCHAR(255) | Название настройки                     |
| `value`   | TEXT         | Значение (строка или сериализованный массив) |

---

## Полезные ссылки

- [Документация Phinx](https://book.cakephp.org/phinx/0/en/index.html)
- [Структура проекта](../README.md)
- [Git Workflow](../CLAUDE.md#git-workflow)

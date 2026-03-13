<?PHP

require_once('View.php');

/**
 * Шаблон для страниц с простыми таблицами.
 *
 * Пример использования можно посмотреть в view/LeadPriceView.php
 */
class TableView extends View
{
    /**
     * @var string Заголовок таблицы
     */
    public const PAGE_TITLE = 'Таблица';

    /**
     * @var string Название класса для работы с таблицей бд в Simpla
     */
    protected const TABLE_CLASS = '';

    /**
     * @var array Колонки в таблице
     */
    public const COLUMNS = [
        'example' => [ // Название столбца в БД
            'name' => 'Пример', // Отображаемое имя
            // Необязательные параметры:
            'type' => 'integer', // Тип - integer/float/string/boolean
            'required' => true, // Обязательно ли для заполнения
            'editable' => true, // Можно ли редактировать (Влияет только в фронт части)
        ],
    ];

    /**
     * @var string Колонка, по которой выполняются update и delete
     */
    public const ID_COLUMN = 'id';

    function fetch()
    {
        if ($action = $this->request->post('action')) {
            $response = 'Unknown action';
            switch ($action) {
                case 'add':
                    $response = $this->addAction();
                    break;
                case 'update':
                    $response = $this->updateAction();
                    break;
                case 'delete':
                    $response = $this->deleteAction();
                    break;
            }
            $this->json_output($response);
            exit();
        }

        $this->design->assign('pageTitle', $this::PAGE_TITLE);
        $rows = $this->{$this::TABLE_CLASS}->getAll();
        $this->design->assign('rows', $rows);
        $this->design->assign('columns', $this::COLUMNS);
        $this->design->assign('id', $this::ID_COLUMN);

        return $this->design->fetch('general_table.tpl');
    }

    /**
     * @return array
     */
    function getFields()
    {
        $fields = [];
        foreach ($this::COLUMNS as $key => $column) {
            $value = $this->request->post($key);
            if (!isset($value)) {
                if ($column['required'] && !empty($column['editable']))
                    return ['error' => "Поле \"{$column['name']}\" не должно быть пустым."];
                else
                    continue;
            }
            $value = trim($value);

            $type = $column['type'] ?? 'string';
            if ($column['required']) {
                if ($value == '')
                    return ['error' => "Поле \"{$column['name']}\" не должно быть пустым."];

                switch ($type) {
                    case 'integer':
                        $value = (int)$value;
                        break;
                    case 'float':
                        $value = (float)$value;
                        break;
                    case 'boolean':
                        $value = (bool)$value;
                        break;
                }
            }

            $fields[$key] = $value;
        }
        return $fields;
    }

    function addAction()
    {
        $fields = $this->getFields();
        if (!empty($fields['error']))
            return $fields;

        $this->{$this::TABLE_CLASS}->add($fields);
        return true;
    }

    function updateAction()
    {
        $fields = $this->getFields();
        if (!empty($fields['error']))
            return $fields;

        $this->{$this::TABLE_CLASS}->update($fields[$this::ID_COLUMN], $fields);
        $fields['success'] = 'Ок';
        return $fields;
    }

    function deleteAction()
    {
        $id = trim($this->request->post($this::ID_COLUMN));
        if (empty($id))
            return ['error' => 'Произошла ошибка, обновите страницу.'];

        $this->{$this::TABLE_CLASS}->delete($id);
        return [
            $this::ID_COLUMN => $id,
            'success' => 'Ок'
        ];
    }
}

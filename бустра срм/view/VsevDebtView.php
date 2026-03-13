<?php

require_once 'View.php';

class VsevDebtView extends View
{
    private $upload_dir = __DIR__ . '/../files/vsev_debt/';

    public function fetch()
    {
        if ($this->request->method('post') && !empty($_FILES['file']['name'])) {
            $file = $_FILES['file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->design->assign('error', 'Ошибка загрузки файла');
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($ext !== 'csv') {
                    $this->design->assign('error', 'Неверный формат файла. Допускаются только .csv');
                } else {
                    if (!is_dir($this->upload_dir)) {
                        mkdir($this->upload_dir, 0777, true);
                    }

                    $filename = md5(uniqid(rand(), true)) . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $this->upload_dir . $filename)) {
                        $this->vsev_debt_task->add_task([
                            'filename' => $filename,
                            'original_filename' => $file['name'],
                            'status' => 'pending',
                        ]);
                    } else {
                        $this->design->assign('error', 'Ошибка сохранения файла');
                    }
                }
            }
            header('Location: ' . $this->request->url());
        }

        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));

        $sort = $this->request->get('sort', 'string');
        if (!empty($sort)) {
            $filter['sort'] = $sort;
        }

        $original_filename = $this->request->get('original_filename', 'string');
        if (!empty($original_filename)) {
            $filter['original_filename'] = $original_filename;
        }

        $items_per_page = 20;
        $filter['limit'] = $items_per_page;

        $tasks_count = $this->vsev_debt_task->count_tasks($filter);
        $pages_count = ceil($tasks_count / $items_per_page);
        $this->design->assign('pages_count', $pages_count);
        $this->design->assign('current_page', $filter['page']);

        $tasks = $this->vsev_debt_task->get_tasks($filter);
        $this->design->assign('tasks', $tasks);
        $this->design->assign('sort', $sort);

        return $this->design->fetch('vsev_debt.tpl');
    }
}
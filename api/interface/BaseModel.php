<?php

/**
 * Interface BaseModel
 * Базовая модель
 */
interface BaseModel
{
    /**
     * @param array $data
     * @return mixed
     */
    public function addItem(array $data);

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateItem(int $id, array $data);

    /**
     * @param int $id
     * @return mixed
     */
    public function deleteItem(int $id);

    /**
     * @param int $id
     * @return mixed
     */
    public function selectItemById(int $id);

    /**
     * @param array $filter_data
     * @param bool $return_all
     * @return mixed
     */
    public function selectAll(array $filter_data, bool $return_all);
}

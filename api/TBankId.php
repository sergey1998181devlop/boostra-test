<?php

require_once 'Simpla.php';

/**
 * Класс для работы с БД и TBankId
 */
class TBankId extends Simpla
{
    /**
     * Тип входа ПК
     */
    public const T_ID_AUTH_OLD_USER_TYPE = 't_id_old_user';

    /**
     * Тригер, если пользователь есть в 1С, но не был в нашей базе
     */
    public const T_ID_AUTH_FROM_1C = 't_id_from_1c';

    /**
     * Сохраняет для свертки sub
     * @param int $user_id
     * @param string $sub
     * @return mixed
     */
   public function saveSubId(int $user_id, string $sub)
   {
       $query = $this->db->placehold("INSERT INTO __tbank_id SET user_id = ?, sub = ?", $user_id, $sub);

       $this->db->query($query);
       return $this->db->insert_id();
   }
}

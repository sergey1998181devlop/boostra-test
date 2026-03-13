<?php

namespace App\Models;

use App\Core\Models\BaseModel;

class VoxUser extends BaseModel
{
    public string $table = 's_vox_users';

    /**
     * Проверяет, включен ли оператор для отправки звонков на анализ
     *
     * @param int $voxUserId ID оператора в Voximplant
     * @return bool
     */
    public function isEnabledForCallAnalysis(int $voxUserId): bool
    {
        $result = $this->get(
            ['is_call_analysis'],
            ['vox_user_id' => $voxUserId]
        )->getData();

        return !empty($result) && (bool)$result['is_call_analysis'] === true;
    }
}

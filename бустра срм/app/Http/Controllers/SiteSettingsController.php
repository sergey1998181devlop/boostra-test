<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Models\Setting;
use Exception;

class SiteSettingsController
{
    private Setting $settingModel;

    private array $accessablesSettings = [
        'voximplant_ai_enabled'
    ];

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    public function index(Request $request): Response
    {
        $name = $request->input('name');
        if (!in_array($name, $this->accessablesSettings)) {
            throw  new \InvalidArgumentException('Неизвестный параметр!');
        }
        $value = $this->settingModel->get(['value'], ['name' => $name])->getData();

        return response()->json([
            'success' => true,
            $name => $value['value']
        ]);
    }

    /**
     * Toggle voximplant AI enabled setting
     *
     * @param Request $request
     * @return Response
     */
    public function toggleVoximplantAI(Request $request): Response
    {
        $enabled = $request->input('enabled');

        if ($enabled === null) {
            return response()->json([
                'success' => false,
                'message' => 'Параметр enabled обязателен'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $value = filter_var($enabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($value === null) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректное значение параметра enabled'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->settingModel->update(
                ['value' => (int)$value],
                ['name' => 'voximplant_ai_enabled']
            );

            return response()->json([
                'success' => true,
                'message' => 'Настройка voximplant_ai_enabled успешно обновлена',
                'value' => (int)$value
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении настройки: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 
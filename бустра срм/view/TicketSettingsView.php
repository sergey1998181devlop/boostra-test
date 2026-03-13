<?php

require_once 'View.php';

use App\Core\Application\Application;
use App\Modules\TicketAssignment\Services\CompetencyService;
use App\Modules\TicketAssignment\Enums\CompetencyLevel;
use App\Modules\TicketAssignment\Enums\TicketType;

class TicketSettingsView extends View
{
    private const SOUND_TICKET_NOTICE_OPTIONS = ['', 'COLLECTION', 'EXTRAS_AND_OTHERS', 'ALL'];
    /** @var CompetencyService */
    private $competencyService;

    public function __construct()
    {
        parent::__construct();

        $app = Application::getInstance();
        $this->competencyService = $app->make(CompetencyService::class);
    }

    public function fetch()
    {
        if ($this->request->method('post')) {
            switch ($this->request->post('action', 'string')) {
                case 'save_authorized_managers':
                    $this->saveAuthorizedManagers();
                    break;
                case 'save_competencies':
                    $this->saveCompetencies();
                    break;
                case 'save_sla_settings':
                    $this->saveSLASettings();
                    break;
                case 'save_sla_managers':
                    $this->saveSLAManagers();
                    break;
                case 'save_sound_ticket_notice':
                    $this->saveSoundTicketNotice();
                    break;
                default:
                    $this->response->json_output([
                        'success' => false,
                        'message' => 'Неизвестное действие'
                    ]);
            }
        } else {
            return $this->showSettingsView();
        }
    }

    /**
     * Сохранение настройки звукового уведомления
     */
    private function saveSoundTicketNotice(): void
    {
        $value = $this->request->post('sound_ticket_notice', 'string');
        if ($value === null) {
            $value = '';
        }

        if (!in_array($value, self::SOUND_TICKET_NOTICE_OPTIONS, true)) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Некорректное значение настройки'
            ]);
            return;
        }

        $checkInterval = (int)$this->request->post('check_interval_sec', 'integer');
        $remindInterval = (int)$this->request->post('remind_interval_min', 'integer');

        if ($checkInterval < 1 || $remindInterval < 1) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Интервалы должны быть больше 0'
            ]);
            return;
        }

        $this->settings->sound_ticket_notice = $value;

        $soundSettings = json_encode([
            'check_interval_sec' => $checkInterval,
            'remind_interval_min' => $remindInterval
        ]);
        $this->saveSetting('ticket_sound_settings', $soundSettings);

        $this->response->json_output([
            'success' => true,
            'message' => 'Настройки звукового уведомления сохранены'
        ]);
    }

    /**
     * Получить настройки звукового уведомления
     */
    private function getTicketSoundSettings(): array
    {
        $jsonSettings = $this->getSetting('ticket_sound_settings', '{}');
        $settings = json_decode($jsonSettings, true) ?: [];

        return [
            'check_interval_sec' => (int)($settings['check_interval_sec'] ?? 10),
            'remind_interval_min' => (int)($settings['remind_interval_min'] ?? 15),
        ];
    }

    /**
     * Отображение страницы настроек
     */
    private function showSettingsView(): string
    {
        // Получаем всех менеджеров
        $all_managers = $this->managers->get_managers();

        // Настройки доступа
        $this->prepareAccessSettings($all_managers);

        // Настройки компетенций
        $this->prepareCompetencySettings($all_managers);

        // Настройки SLA
        $this->prepareSLASettings($all_managers);

        // Настройки звукового уведомления
        $this->prepareSoundSettings();

        return $this->design->fetch('contact_center/settings.tpl');
    }

    /**
     * Подготовка настроек звукового уведомления для шаблона
     */
    private function prepareSoundSettings(): void
    {
        $ticketSoundSettings = $this->getTicketSoundSettings();
        $this->design->assign('ticket_sound_settings', $ticketSoundSettings);
    }

    /**
     * Подготовка данных для настроек доступа
     */
    private function prepareAccessSettings(array $all_managers): void
    {
        // Получаем ID менеджеров с доступом к обоим типам тикетов
        $authorized_dopy_manager_ids = $this->settings->authorized_dopy_managers ?? [];
        $authorized_collection_manager_ids = $this->settings->authorized_collection_managers ?? [];
        $auto_assign_ticket_manager_ids = $this->settings->auto_assign_ticket_managers ?? [];

        // Проверяем, что все значения являются массивами
        if (!is_array($authorized_dopy_manager_ids)) {
            $authorized_dopy_manager_ids = [];
        }
        if (!is_array($authorized_collection_manager_ids)) {
            $authorized_collection_manager_ids = [];
        }
        if (!is_array($auto_assign_ticket_manager_ids)) {
            $auto_assign_ticket_manager_ids = [];
        }

        $authorized_dopy_managers = [];
        $authorized_collection_managers = [];
        $auto_assign_ticket_managers = [];
        $available_managers = [];
        $available_for_auto_assign = [];

        foreach ($all_managers as $manager) {
            $has_dopy_access = in_array($manager->id, $authorized_dopy_manager_ids);
            $has_collection_access = in_array($manager->id, $authorized_collection_manager_ids);
            $has_auto_assign = in_array($manager->id, $auto_assign_ticket_manager_ids);

            // Менеджеры с доступом к допам
            if ($has_dopy_access) {
                $authorized_dopy_managers[] = $manager;
            }

            // Менеджеры с доступом к взысканию
            if ($has_collection_access) {
                $authorized_collection_managers[] = $manager;
            }

            // Менеджеры для автоназначения
            if ($has_auto_assign) {
                $auto_assign_ticket_managers[] = $manager;
            }

            // Доступные для автоназначения - только те, кто имеет права на тикеты
            if (($has_dopy_access || $has_collection_access) && !$has_auto_assign) {
                $available_for_auto_assign[] = $manager;
            }

            // Обычные доступные менеджеры (без прав на тикеты)
            if (!$has_dopy_access && !$has_collection_access) {
                $available_managers[] = $manager;
            }
        }

        $this->design->assign_array([
            'authorized_dopy_managers' => $authorized_dopy_managers,
            'authorized_collection_managers' => $authorized_collection_managers,
            'auto_assign_ticket_managers' => $auto_assign_ticket_managers,
            'available_for_auto_assign' => $available_for_auto_assign,
            'available_managers' => $available_managers
        ]);
    }

    /**
     * Подготовка данных для настроек компетенций
     */
    private function prepareCompetencySettings(array $all_managers): void
    {
        // Получаем менеджеров с доступом к каждому типу тикетов
        $authorized_dopy_ids = $this->settings->authorized_dopy_managers ?? [];
        $authorized_collection_ids = $this->settings->authorized_collection_managers ?? [];

        if (!is_array($authorized_dopy_ids)) {
            $authorized_dopy_ids = [];
        }
        if (!is_array($authorized_collection_ids)) {
            $authorized_collection_ids = [];
        }

        // Получаем текущие компетенции для каждого типа и уровня
        $dopy_competencies = [
            'soft' => $this->competencyService->getManagersByLevel(TicketType::ADDITIONAL_SERVICES, CompetencyLevel::SOFT),
            'middle' => $this->competencyService->getManagersByLevel(TicketType::ADDITIONAL_SERVICES, CompetencyLevel::MIDDLE),
            'hard' => $this->competencyService->getManagersByLevel(TicketType::ADDITIONAL_SERVICES, CompetencyLevel::HARD)
        ];

        $collection_competencies = [
            'soft' => $this->competencyService->getManagersByLevel(TicketType::COLLECTION, CompetencyLevel::SOFT),
            'middle' => $this->competencyService->getManagersByLevel(TicketType::COLLECTION, CompetencyLevel::MIDDLE),
            'hard' => $this->competencyService->getManagersByLevel(TicketType::COLLECTION, CompetencyLevel::HARD)
        ];

        // Формируем списки доступных и выбранных менеджеров для каждого уровня
        $dopy_managers = $this->prepareManagerLists($all_managers, $authorized_dopy_ids, $dopy_competencies);
        $collection_managers = $this->prepareManagerLists($all_managers, $authorized_collection_ids, $collection_competencies);

        $this->design->assign_array([
            'dopy_managers' => $dopy_managers,
            'collection_managers' => $collection_managers
        ]);
    }

    /**
     * Подготовка списков менеджеров для каждого уровня
     */
    private function prepareManagerLists(array $all_managers, array $authorized_ids, array $competencies): array
    {
        $result = [];
        $all_competency_ids = array_merge(
            $competencies['soft'],
            $competencies['middle'],
            $competencies['hard']
        );

        // Для каждого уровня
        foreach (['soft', 'middle', 'hard'] as $level) {
            $selected = [];
            $available = [];

            // Получаем менеджеров этого уровня
            foreach ($all_managers as $manager) {
                if (in_array($manager->id, $competencies[$level])) {
                    $selected[] = $manager;
                } elseif (
                    in_array($manager->id, $authorized_ids) && // Имеет доступ к типу тикетов
                    !in_array($manager->id, $all_competency_ids) // Не назначен на другой уровень
                ) {
                    $available[] = $manager;
                }
            }

            $result[$level] = [
                'selected' => $selected,
                'available' => $available
            ];
        }

        return $result;
    }



    /**
     * Сохранение настроек доступа
     */
    private function saveAuthorizedManagers(): void
    {
        $type = $this->request->post('type', 'string');
        $manager_ids = $this->request->post('manager_ids');
        
        if (empty($type)) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Не указан тип настроек'
            ]);
            return;
        }

        // Преобразуем входные данные в массив целых чисел
        $manager_ids = !empty($manager_ids) ? (array)$manager_ids : [];
        $manager_ids = array_map('intval', $manager_ids);
        
        if ($type === 'dopy') {
            $setting_key = 'authorized_dopy_managers';
            $notification_title = 'Допы и прочее';
        } elseif ($type === 'collection') {
            $setting_key = 'authorized_collection_managers';
            $notification_title = 'Взыскание';
        } elseif ($type === 'auto_assign_ticket') {
            $setting_key = 'auto_assign_ticket_managers';
            $notification_title = 'Автоматическое назначение (все тикеты)';
        } else {
            $this->response->json_output([
                'success' => false,
                'message' => 'Неверный тип настроек'
            ]);
            return;
        }

        // Получаем текущие значения
        $current_manager_ids = $this->settings->$setting_key ?? [];
        if (!is_array($current_manager_ids)) {
            $current_manager_ids = [];
        }

        $this->saveSetting($setting_key, serialize($manager_ids));

        $this->sendAccessChangeNotifications($current_manager_ids, $manager_ids, $notification_title);

        $this->response->json_output([
            'success' => true,
            'message' => 'Настройки доступа успешно сохранены'
        ]);
    }

    /**
     * Сохранение настроек компетенций
     */
    private function saveCompetencies(): void
    {
        $type = $this->request->post('type', 'string');
        $competencies = $this->request->post('competencies');

        if ($competencies === null) {
            $competencies = [
                'soft' => [],
                'middle' => [],
                'hard' => []
            ];
        }

        if (empty($type)) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Не указан тип'
            ]);
            return;
        }

        if (!is_array($competencies)) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Competencies не является массивом: ' . gettype($competencies)
            ]);
            return;
        }

        $requiredKeys = ['soft', 'middle', 'hard'];
        foreach ($requiredKeys as $key) {
            if (!isset($competencies[$key])) {
                $competencies[$key] = [];
            }
        }

        try {
            // Преобразуем тип в формат из enum
            $ticketType = $type === 'additional_services' ? TicketType::ADDITIONAL_SERVICES : TicketType::COLLECTION;

            // Получаем текущие компетенции для логирования изменений
            $oldCompetencies = [
                'soft' => $this->competencyService->getManagersByLevel($ticketType, CompetencyLevel::SOFT),
                'middle' => $this->competencyService->getManagersByLevel($ticketType, CompetencyLevel::MIDDLE),
                'hard' => $this->competencyService->getManagersByLevel($ticketType, CompetencyLevel::HARD)
            ];

            // Сохраняем новые компетенции
            foreach ($competencies as $level => $managerIds) {
                // Удаляем старые компетенции этого уровня
                foreach ($oldCompetencies[$level] as $managerId) {
                    $this->competencyService->removeManagerCompetency($managerId, $ticketType);
                }

                // Устанавливаем новые
                foreach ($managerIds as $managerId) {
                    $this->competencyService->setManagerCompetency(
                        (int)$managerId,
                        $ticketType,
                        $level
                    );
                }
            }

            // Логируем изменения
            $this->logCompetencyChanges($type, $oldCompetencies, $competencies);

            $this->response->json_output([
                'success' => true,
                'message' => 'Настройки компетенций успешно сохранены'
            ]);
        } catch (Exception $e) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Ошибка при сохранении компетенций: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Логирование изменений компетенций
     */
    private function logCompetencyChanges(string $type, array $oldCompetencies, array $newCompetencies): void
    {
        $changes = [];
        $typeTitle = $type === 'additional_services' ? 'Допы и прочее' : 'Взыскание';

        foreach (['soft', 'middle', 'hard'] as $level) {
            $added = array_diff($newCompetencies[$level], $oldCompetencies[$level]);
            $removed = array_diff($oldCompetencies[$level], $newCompetencies[$level]);

            if (!empty($added) || !empty($removed)) {
                $levelTitle = ucfirst($level);
                $changes[] = "Уровень $levelTitle:";

                if (!empty($added)) {
                    $changes[] = "- Добавлены: " . $this->formatManagerNames($added);
                }
                if (!empty($removed)) {
                    $changes[] = "- Удалены: " . $this->formatManagerNames($removed);
                }
            }
        }

        if (!empty($changes)) {
            $message = "Изменения компетенций для \"$typeTitle\":\n" . implode("\n", $changes);
            
            // Отправляем уведомление руководителям
            $notification_recipients = $this->getCompetencyChangeNotificationRecipients();
            foreach ($notification_recipients as $recipient_id) {
                $this->notificationsManagers->sendNotification([
                    'from_user' => $this->getManagerId() ?: 1,
                    'to_user' => $recipient_id,
                    'subject' => "Изменение компетенций ($typeTitle)",
                    'message' => $message,
                    'is_read' => false
                ]);
            }
        }
    }

    /**
     * Форматирование списка имен менеджеров
     */
    private function formatManagerNames(array $managerIds): string
    {
        $names = [];
        foreach ($managerIds as $managerId) {
            $manager = $this->managers->get_manager($managerId);
            $names[] = $manager ? trim($manager->name) : "ID: $managerId";
        }
        return implode(', ', $names);
    }

    /**
     * Отправка уведомлений об изменении доступа сотрудников
     * @param array $old_manager_ids
     * @param array $new_manager_ids
     * @param string $type_title
     * @return void
     */
    private function sendAccessChangeNotifications(array $old_manager_ids, array $new_manager_ids, string $type_title): void
    {
        // ID менеджеров, которые должны получить уведомления
        $notification_recipients = $this->getCompetencyChangeNotificationRecipients();

        $initiator_id = $this->getManagerId();
        $initiator_name = 'Неизвестный пользователь';

        if ($initiator_id) {
            $initiator = $this->managers->get_manager($initiator_id);
            if ($initiator) {
                $initiator_name = trim($initiator->name);
            }
        }

        $added_managers = array_diff($new_manager_ids, $old_manager_ids);
        $removed_managers = array_diff($old_manager_ids, $new_manager_ids);

        if (empty($added_managers) && empty($removed_managers)) {
            return;
        }

        $changes_message = $this->formatChangesMessage($added_managers, $removed_managers, $type_title);

        $subject = "Изменение доступа в тикет системе ({$type_title})";
        $message = "Инициатор изменения: {$initiator_name}\n\n{$changes_message}";

        foreach ($notification_recipients as $recipient_id) {
            $this->notificationsManagers->sendNotification([
                'from_user' => $initiator_id ?: 1,
                'to_user' => $recipient_id,
                'subject' => $subject,
                'message' => $message,
                'is_read' => false
            ]);
        }
    }

    /**
     * Формирует сообщение об изменениях доступа
     * @param array $added_managers
     * @param array $removed_managers
     * @param string $type_title
     * @return string
     */
    private function formatChangesMessage(array $added_managers, array $removed_managers, string $type_title): string
    {
        $message = "Произведены следующие изменения доступа к тикетам \"{$type_title}\":<br><br>";

        if (!empty($added_managers)) {
            $message .= "<strong>Добавлены сотрудники:</strong><br>";
            foreach ($added_managers as $manager_id) {
                $manager = $this->managers->get_manager($manager_id);
                $manager_name = $manager ? trim($manager->name) : "ID: {$manager_id}";
                $message .= "- {$manager_name}<br>";
            }
            $message .= "<br>";
        }

        if (!empty($removed_managers)) {
            $message .= "<strong>Удалены сотрудники:</strong><br>";
            foreach ($removed_managers as $manager_id) {
                $manager = $this->managers->get_manager($manager_id);
                $manager_name = $manager ? trim($manager->name) : "ID: {$manager_id}";
                $message .= "- {$manager_name}<br>";
            }
        }

        return $message;
    }

    /**
     * Подготовка данных для настроек SLA
     */
    private function prepareSLASettings(array $all_managers): void
    {
        // Получаем настройки SLA из JSON
        $slaSettings = $this->getSLASettings();

        // Получаем менеджеров с компетенциями и их SLA уровни
        $slaManagers = $this->getSLAManagersData($all_managers);

        $this->design->assign_array([
            'sla_settings' => $slaSettings,
            'sla_managers' => $slaManagers
        ]);
    }

    /**
     * Сохранение настроек SLA
     */
    private function saveSLASettings(): void
    {
        $timeoutLevel1 = (int)$this->request->post('timeout_level_1', 'integer');
        $timeoutLevel2 = (int)$this->request->post('timeout_level_2', 'integer');
        $slaEnabled = $this->request->post('sla_enabled', 'boolean');

        // Валидация
        if ($timeoutLevel1 <= 0 || $timeoutLevel2 <= 0) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Время SLA должно быть больше 0'
            ]);
            return;
        }

        if ($timeoutLevel1 >= $timeoutLevel2) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Время SLA должно увеличиваться с каждым уровнем'
            ]);
            return;
        }

        try {
            // Сохраняем настройки в JSON
            $this->saveSLASettingsToDatabase([
                'timeout_level_1' => $timeoutLevel1,
                'timeout_level_2' => $timeoutLevel2,
                'sla_enabled' => $slaEnabled
            ]);

            $this->response->json_output([
                'success' => true,
                'message' => 'Настройки SLA успешно сохранены'
            ]);
        } catch (\Exception $e) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Ошибка при сохранении настроек SLA: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Получить настройку из БД
     */
    private function getSetting(string $key, $default = null)
    {
        $this->db->query("SELECT value FROM s_settings WHERE name = ? AND site_id IS NULL", $key);
        $result = $this->db->result();
        return $result ? $result->value : $default;
    }

    /**
     * Получить получателей уведомлений об изменении компетенций
     * (те же, кто настроен на SLA эскалацию уровня 2 - руководители)
     */
    private function getCompetencyChangeNotificationRecipients(): array
    {
        // Получаем всех менеджеров, настроенных на SLA уровень 2 (руководители)
        $collectionManagers = $this->competencyService->getSLAEscalationManagers('collection', 2);
        $additionalServicesManagers = $this->competencyService->getSLAEscalationManagers('additional_services', 2);
        
        return array_unique(array_merge($collectionManagers, $additionalServicesManagers));
    }

    /**
     * Сохранить настройку в БД
     */
    private function saveSetting(string $key, $value): void
    {
        $this->db->query("UPDATE s_settings SET value = ? WHERE name = ?", $value, $key);
    }

    /**
     * Получить SLA настройки
     */
    private function getSLASettings(): array
    {
        $jsonSettings = $this->getSetting('sla_settings', '{}');
        $settings = json_decode($jsonSettings, true) ?: [];
        
        return [
            'timeout_level_1' => $settings['timeout_level_1'] ?? 4,
            'timeout_level_2' => $settings['timeout_level_2'] ?? 8,
            'sla_enabled' => $settings['enabled'] ?? true
        ];
    }

    /**
     * Сохранить SLA настройки
     */
    private function saveSLASettingsToDatabase(array $settings): void
    {
        $jsonSettings = json_encode([
            'timeout_level_1' => $settings['timeout_level_1'],
            'timeout_level_2' => $settings['timeout_level_2'],
            'enabled' => $settings['sla_enabled']
        ]);
        
        $this->db->query("UPDATE s_settings SET value = ? WHERE name = ?", $jsonSettings, 'sla_settings');
        if ($this->db->affected_rows() === 0) {
            $this->db->query("INSERT INTO s_settings (name, value) VALUES (?, ?)", 'sla_settings', $jsonSettings);
        }
    }

    /**
     * Получить данные менеджеров для SLA настроек
     */
    private function getSLAManagersData(array $all_managers): array
    {
        $slaManagers = [
            'additional_services' => [],
            'collection' => []
        ];

        // Получаем ID менеджеров с доступом к типам тикетов
        $authorized_dopy_ids = $this->settings->authorized_dopy_managers ?? [];
        $authorized_collection_ids = $this->settings->authorized_collection_managers ?? [];

        if (!is_array($authorized_dopy_ids)) {
            $authorized_dopy_ids = [];
        }
        if (!is_array($authorized_collection_ids)) {
            $authorized_collection_ids = [];
        }

        // Обрабатываем всех менеджеров
        foreach ($all_managers as $manager) {
            // Проверяем доступ к "Допы и прочее"
            if (in_array($manager->id, $authorized_dopy_ids)) {
                $competencies = $this->competencyService->getFullManagerCompetencies($manager->id);
                $competency = $competencies['additional_services'] ?? null;
                
                $managerData = (object)[
                    'id' => $manager->id,
                    'name' => $manager->name,
                    'competency_level' => $competency['level'] ?? 'none',
                    'competency_level_name' => $competency ? $this->getCompetencyLevelName($competency['level']) : 'Нет компетенции',
                    'competency_level_color' => $competency ? $this->getCompetencyLevelColor($competency['level']) : 'secondary',
                    'sla_level' => $competency['sla_level'] ?? null
                ];
                
                $slaManagers['additional_services'][] = $managerData;
            }

            // Проверяем доступ к "Взыскание"
            if (in_array($manager->id, $authorized_collection_ids)) {
                $competencies = $this->competencyService->getFullManagerCompetencies($manager->id);
                $competency = $competencies['collection'] ?? null;
                
                $managerData = (object)[
                    'id' => $manager->id,
                    'name' => $manager->name,
                    'competency_level' => $competency['level'] ?? 'none',
                    'competency_level_name' => $competency ? $this->getCompetencyLevelName($competency['level']) : 'Нет компетенции',
                    'competency_level_color' => $competency ? $this->getCompetencyLevelColor($competency['level']) : 'secondary',
                    'sla_level' => $competency['sla_level'] ?? null
                ];
                
                $slaManagers['collection'][] = $managerData;
            }
        }

        return $slaManagers;
    }

    /**
     * Получить название уровня компетенции
     */
    private function getCompetencyLevelName(string $level): string
    {
        $names = [
            'soft' => 'Soft (1-7 дней)',
            'middle' => 'Middle (8-30 дней)',
            'hard' => 'Hard (>30 дней)'
        ];
        
        return $names[$level] ?? $level;
    }

    /**
     * Получить цвет для уровня компетенции
     */
    private function getCompetencyLevelColor(string $level): string
    {
        $colors = [
            'soft' => 'success',
            'middle' => 'warning',
            'hard' => 'danger'
        ];
        
        return $colors[$level] ?? 'secondary';
    }

    /**
     * Сохранение SLA настроек менеджеров
     */
    private function saveSLAManagers(): void
    {
        $slaData = $this->request->post('sla_data');
        
        if (!is_array($slaData)) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Некорректные данные SLA'
            ]);
            return;
        }

        try {
            // Устанавливаем новые SLA уровни (без предварительной очистки)
            foreach ($slaData as $item) {
                if (!empty($item['manager_id']) && !empty($item['type'])) {
                    // Проверяем, есть ли у менеджера компетенция для этого типа
                    $existingCompetency = $this->competencyService->getManagerCompetency(
                        (int)$item['manager_id'], 
                        $item['type']
                    );
                    
                    // Если компетенции нет, создаем базовую (soft)
                    if (!$existingCompetency) {
                        $this->competencyService->setManagerCompetency(
                            (int)$item['manager_id'],
                            $item['type'],
                            'soft'
                        );
                    }
                    
                    // Устанавливаем или очищаем SLA уровень
                    if (!empty($item['sla_level'])) {
                        $this->competencyService->setSLAEscalationLevel(
                            (int)$item['manager_id'],
                            $item['type'],
                            (int)$item['sla_level']
                        );
                    } else {
                        // Если sla_level пустой, очищаем его
                        $this->competencyService->removeSLAEscalationLevel(
                            (int)$item['manager_id'],
                            $item['type']
                        );
                    }
                }
            }

            $this->response->json_output([
                'success' => true,
                'message' => 'SLA настройки менеджеров успешно сохранены'
            ]);
        } catch (\Exception $e) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Ошибка при сохранении SLA настроек: ' . $e->getMessage()
            ]);
        }
    }
}
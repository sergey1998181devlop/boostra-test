<?php

use App\Modules\Faq\Enums\FaqBlockType;
use App\Modules\Faq\Repositories\FaqRepository;

/**
 * Страница FAQ с табами для всех сайтов из s_sites
 */
class FaqView extends View
{
    protected FaqRepository $faqRepository;
    protected string $templatePath;
    protected string $ajaxUrl;
    protected string $pageTitle;
    protected array $breadcrumbs;
    private ?string $currentSiteId = null;

    public function __construct()
    {
        parent::__construct();

        // Получаем site_id из запроса (GET для отображения, POST для CRUD)
        $this->currentSiteId = $this->request->get('site_id', 'string')
            ?: $this->request->post('site_id', 'string');

        // Если site_id не передан, используем первый активный сайт
        if (!$this->currentSiteId) {
            $sites = $this->getActiveSites();
            $this->currentSiteId = !empty($sites) ? $sites[0]->site_id : 'boostra';
        }

        // Инициализируем репозиторий с site_id напрямую
        $this->faqRepository = new FaqRepository($this->db, $this->currentSiteId);

        $this->ajaxUrl = '/faq';
        $this->pageTitle = 'FAQ';
        $this->breadcrumbs = [
            ['url' => '/', 'title' => 'Главная'],
            ['url' => null, 'title' => 'FAQ']
        ];
        $this->templatePath = 'faq/list.tpl';
    }

    public function fetch()
    {
        if (!in_array('cc_tasks', $this->manager->permissions)) {
            return $this->design->fetch('403.tpl');
        }

        if ($this->request->method('post')) {
            $action = $this->request->post('action', 'string');

            if ($action) {
                switch ($action) {
                    case 'create':
                        $this->create();
                        break;
                    case 'update':
                        $this->update();
                        break;
                    case 'delete':
                        $this->delete();
                        break;
                    case 'createBlock':
                        $this->createBlock();
                        break;
                    case 'updateBlock':
                        $this->updateBlock();
                        break;
                    case 'deleteBlock':
                        $this->deleteBlock();
                        break;
                    case 'createSection':
                        $this->createSection();
                        break;
                    case 'updateSection':
                        $this->updateSection();
                        break;
                    case 'deleteSection':
                        $this->deleteSection();
                        break;
                    case 'reorderSections':
                        $this->reorderSections();
                        break;
                }
            }
        }

        return $this->getAll();
    }

    private function getAll(): string
    {
        $blocks = $this->faqRepository->getAllBlocksWithFaq();

        $structuredBlocks = [];

        foreach ($blocks as $row) {
            if (!isset($row->block_id)) {
                continue;
            }

            $blockId = $row->block_id;
            $sectionId = $row->section_id;

            if (!isset($structuredBlocks[$blockId])) {
                $blockType = $row->type ?? '';
                $structuredBlocks[$blockId] = [
                    'block_id' => $blockId,
                    'name' => $row->block_name ?? 'Unknown Block',
                    'type' => $blockType,
                    'type_label' => FaqBlockType::getLabel($blockType),
                    'block_yandex_goal_id' => $row->block_yandex_goal_id ?? '',
                    'sections' => []
                ];
            }

            if ($sectionId && !isset($structuredBlocks[$blockId]['sections'][$sectionId])) {
                $structuredBlocks[$blockId]['sections'][$sectionId] = [
                    'section_id' => $sectionId,
                    'section_name' => $row->section_name ?? 'Без названия',
                    'faqs' => []
                ];
            }

            if (!empty($row->faq_id)) {
                $structuredBlocks[$blockId]['sections'][$sectionId]['faqs'][] = [
                    'faq_id' => $row->faq_id,
                    'question' => $row->question ?? '',
                    'answer' => $row->answer ?? '',
                    'yandex_goal_id' => $row->yandex_goal_id ?? ''
                ];
            }
        }

        $blockTypes = [];
        foreach (FaqBlockType::all() as $type) {
            $blockTypes[] = [
                'value' => $type,
                'label' => FaqBlockType::getLabel($type)
            ];
        }

        $sites = $this->getActiveSites();

        $this->design->assign('structuredBlocks', $structuredBlocks);
        $this->design->assign('blockTypes', $blockTypes);
        $this->design->assign('ajaxUrl', $this->ajaxUrl);
        $this->design->assign('pageTitle', $this->pageTitle);
        $this->design->assign('breadcrumbs', $this->breadcrumbs);
        $this->design->assign('sites', $sites);
        $this->design->assign('currentSiteId', $this->currentSiteId);

        return $this->design->fetch($this->templatePath);
    }

    private function create()
    {
        $data['question'] = $this->request->post('question', 'string');
        $data['answer'] = $this->request->post('answer');
        $data['yandex_goal_id'] = $this->request->post('yandex_goal_id', 'string');
        $data['section_id'] = $this->request->post('section_id', 'integer');

        $this->faqRepository->create($data);

        $this->response->json_output(['status' => true]);
    }

    private function update()
    {
        $data['id'] = $this->request->post('id', 'integer');
        $data['question'] = $this->request->post('question', 'string');
        $data['answer'] = $this->request->post('answer');
        $data['yandex_goal_id'] = $this->request->post('yandex_goal_id', 'string');

        $this->faqRepository->update($data);

        $this->response->json_output(['status' => true]);
    }

    private function delete()
    {
        $id = $this->request->post('id', 'integer');

        $this->faqRepository->delete($id);

        $this->response->json_output(['status' => true]);
    }

    private function createBlock()
    {
        $data['name'] = $this->request->post('block_title', 'string');
        $data['type'] = $this->request->post('block_type', 'string');

        $this->faqRepository->createBlock($data);

        $this->response->json_output(['status' => true]);
    }

    private function updateBlock()
    {
        $data['id'] = $this->request->post('block_id', 'integer');
        $data['name'] = $this->request->post('block_title', 'string');
        $data['type'] = $this->request->post('block_type', 'string');
        $data['yandex_goal_id'] = $this->request->post('block_yandex_goal_id', 'string');

        if (empty($data['name']) || empty($data['type'])) {
            $this->response->json_output(['status' => false, 'error' => 'Missing Block ID, Name, or Type']);
            return;
        }

        $this->faqRepository->updateBlock($data);

        $this->response->json_output(['status' => true]);
    }

    private function deleteBlock()
    {
        $id = $this->request->post('id', 'integer');

        if (empty($id)) {
            $this->response->json_output(['status' => false, 'error' => 'Missing Block ID']);
            return;
        }

        $this->faqRepository->deleteBlock($id);

        $this->response->json_output(['status' => true]);
    }

    private function createSection()
    {
        $data['name'] = $this->request->post('name', 'string');
        $data['block_id'] = $this->request->post('block_id', 'integer');
        $this->faqRepository->createSection($data);
        $this->response->json_output(['status' => true]);
    }

    private function updateSection()
    {
        $data['id'] = $this->request->post('id', 'integer');
        $data['name'] = $this->request->post('name', 'string');
        $data['block_id'] = $this->request->post('block_id', 'integer');
        $this->faqRepository->updateSection($data);
        $this->response->json_output(['status' => true]);
    }

    private function deleteSection()
    {
        $id = $this->request->post('id', 'integer');
        $this->faqRepository->deleteSection($id);
        $this->response->json_output(['status' => true]);
    }

    private function reorderSections()
    {
        $order = $this->request->post('order');

        if (empty($order) || !is_array($order)) {
            $this->response->json_output(['status' => false, 'error' => 'Invalid order data']);
            return;
        }

        foreach ($order as $item) {
            if (!empty($item['id']) && isset($item['sequence'])) {
                $this->faqRepository->updateSectionSequence((int)$item['id'], (int)$item['sequence']);
            }
        }

        $this->response->json_output(['status' => true]);
    }

    /**
     * Получает список активных сайтов из s_sites
     */
    private function getActiveSites(): array
    {
        require_once 'api/Sites.php';
        $sites = (new Sites())->getActiveSites();

        return is_array($sites) ? $sites : [];
    }
}

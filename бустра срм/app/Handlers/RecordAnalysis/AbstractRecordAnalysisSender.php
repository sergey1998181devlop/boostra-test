<?php

namespace App\Handlers\RecordAnalysis;

use App\Models\Comment;
use Exception;

abstract class AbstractRecordAnalysisSender
{
    protected Comment $commentModel;

    public function __construct()
    {
        $this->commentModel = new Comment();
    }

    public function sendBatch(string $dateFrom, string $dateTo, ?string $tag = null, int $pageSize = 30): int
    {
        try {
            $total = 0;
            $columns = $this->getColumns();
            $where = $this->getWhere($dateFrom, $dateTo);
            $join = $this->getJoin();

            foreach ($this->commentModel->eachChunk($pageSize, $columns, $where, $join) as $comment) {
                $comment = (array)$comment;
                if (!$this->matchTag($comment, $tag)) {
                    continue;
                }
                $handled = $this->handle($comment);
                $total = $handled ? $total + 1 : $total;
            }

            return $total;
        } catch (Exception $e) {
            error_log('Ошибка в ' . static::class . '::sendBatch: ' . $e->getMessage());
            return 0;
        }
    }

    protected function getColumns(): array
    {
        return [
            's_comments.id',
            's_comments.user_id',
            's_comments.block',
            's_comments.created',
            's_comments.text',
        ];
    }

    abstract protected function getWhere(string $dateFrom, string $dateTo): array;

    protected function getJoin(): array
    {
        return [
            '[>]s_comment_record_analysis' => ['id' => 'comment_id'],
        ];
    }

    protected function matchTag(array $comment, ?string $tag): bool
    {
        return true; // по умолчанию не фильтруем по тегу
    }

    abstract public function handle(array $comment): bool;
}

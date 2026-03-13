<?php

namespace App\Handlers\RecordAnalysis;

use App\Enums\CommentBlocks;
use App\Handlers\BoostraSendRecordAnalysisHandler;

class BoostraRecordAnalysisSender extends AbstractRecordAnalysisSender
{
    protected function getWhere(string $dateFrom, string $dateTo): array
    {
        return [
            's_comments.created[<>]' => [$dateFrom, $dateTo],
            's_comments.block' => [CommentBlocks::INCOMING_CALL, CommentBlocks::OUTGOING_CALL],
            's_comment_record_analysis.comment_id' => null,
            's_comments.text[~]' => '%"is_sent_analysis":false%',
        ];
    }

    protected function matchTag(array $comment, ?string $tag): bool
    {
        if ($tag === null) {
            return true;
        }
        $call = json_decode($comment['text'], true);
        return isset($call['tag']) && $call['tag'] === $tag;
    }

    public function handle(array $comment): bool
    {
        return (new BoostraSendRecordAnalysisHandler())->handle($comment);
    }
}

<?php

error_reporting(1);
ini_set('display_errors', 'on');
ini_set('max_execution_time', '600');
require_once dirname(__FILE__).'/../api/Simpla.php';

class SendVoxStatus extends Simpla
{
    public function run()
    {
        $comments = $this->comments->get_comments([
            'block' => 'vox_status',
            'created' => date('Y-m-d', strtotime("-1 days"))
        ]);
        $chunkSize = 100;
        $commentsChunks = array_chunk($comments, $chunkSize);
        $manager = $this->managers->get_manager(50);
        foreach ($commentsChunks as $chunk) {
            $items = [];
            foreach ($chunk as $comment) {
                $item = [];
                $item['manager'] = $manager->name_1c;
                $item['user_uid'] = $this->users->getUserUidById($comment->user_id);
                $item['created'] = $comment->created;
                $item['text'] = $comment->text;
                $items[] = $item;
            }
            $this->soap->send_comment($items);
            sleep(1);
        }
    }

}

(new SendVoxStatus())->run();

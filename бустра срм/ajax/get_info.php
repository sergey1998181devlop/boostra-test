<?php

chdir('..');

require 'api/Simpla.php';

class GetInfoAjax extends Simpla
{
    private $response = array();
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
        
        $this->json_output();
                
    }
    
    
    public function run()
    {
    	$action = $this->request->get('action', 'string');
        
        switch ($action):
            
            case 'comments':
                if (($uid = $this->request->get('uid')) && ($site_id = $this->request->get('site_id')))
                {
                    // получаем комменты из 1С
                    if ($comments_1c_response = $this->soap->get_comments($uid, $site_id))
                    {

                        $comments_1c = array();
                        if (!empty($comments_1c_response->Комментарии))
                        {
                            foreach ($comments_1c_response->Комментарии as $comm)
                            {
                                $comment_1c_item = new StdClass();
                                
                                $comment_1c_item->created = date('Y-m-d H:i:s', strtotime($comm->Дата));
                                $comment_1c_item->text = $comm->Комментарий;
                                $comment_1c_item->block = $comm->Блок;
                                
                                $comments_1c[] = $comment_1c_item;
                            }
                        }
                        
                        usort($comments_1c, function($a, $b){
                            return strtotime($b->created) - strtotime($a->created);
                        });
                        
                        $this->response['comments'] = $comments_1c;
                        
                        $blacklist_comments = array();
                        if (!empty($comments_1c_response->ЧС))
                        {
                            foreach ($comments_1c_response->ЧС as $comm)
                            {
                                $blacklist_comment = new StdClass();
                                
                                $blacklist_comment->created = date('d.m.Y H:i:s', strtotime($comm->Дата));
                                $blacklist_comment->text = $comm->Комментарий;
                                $blacklist_comment->block = $comm->Блок;
                                
                                $blacklist_comments[] = $blacklist_comment;
                            }
                        }
                        
                        usort($blacklist_comments, function($a, $b){
                            return strtotime($b->created) - strtotime($a->created);
                        });
                        
                        $this->response['blacklist'] = $blacklist_comments;
                    }

                }
                else
                {
                    $this->response['error'] = 'Пользователь не найден';
                }
                
            break;
            
            case 'movements':
                
                $number = $this->request->get('number');
                
                $response = $this->soap->get_movements($number);
                $data = array();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response);echo '</pre><hr />';
                foreach ($response as &$item)
                {
                    $data_item = new StdClass();
                    
                    $data_item->date = date('d.m.Y', strtotime($item->Дата));
                    $data_item->start_total_summ = $item->НачальныйОстаток;
                    $data_item->added_body_summ = $item->НачисленоОД;
                    $data_item->paid_body_summ = $item->ОплаченоОД;
                    $data_item->added_percents_summ = $item->НачисленоПроцент;
                    $data_item->paid_percents_summ = $item->ОплаченоПроцент;
                    $data_item->added_peni_summ = $item->НачисленоПени;
                    $data_item->paid_peni_summ = $item->ОплаченоПени;
                    $data_item->added_charge_summ = $item->НачисленоОтветственность;
                    $data_item->paid_charge_summ = $item->ОплаченоОтветственность;
                    $data_item->finish_total_summ = $item->КонечныйОстаток;
                    $data_item->conditional = (int)$item->УсловнаяОплата;
                    
                    $data[] = $data_item;
                }
                
                
                $this->response = array_values(array_filter($data, function($var){
                    return !empty($var->added_body_summ)
                        || !empty($var->paid_body_summ)
                        || !empty($var->paid_percents_summ)
                        || !empty($var->paid_charge_summ);
                }));
                
            break;
            
        endswitch;
    
    }
    
    private function json_output()
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");	
        
        echo json_encode($this->response);
    }
}
new GetInfoAjax();
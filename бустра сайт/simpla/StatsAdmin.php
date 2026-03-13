<?PHP

require_once('api/Simpla.php');


class StatsAdmin extends Simpla
{
 
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            $filter = array();
            
            $period = $this->request->post('period', 'string');
            $from = $this->request->post('from');
            $to = $this->request->post('to');

            $this->design->assign('period', $period);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            switch ($period):
                
                case 'today':
                    $filter['date_from'] = date('Y-m-d');
                    $filter['date_to'] =  date('Y-m-d');
                break;
                
                case 'yesterday':
                    $filter['date_from'] = date('Y-m-d', time() - 86400);
                    $filter['date_to'] =  date('Y-m-d', time() - 86400);
                break;
                
                case 'week':
                    $filter['date_from'] = date('Y-m-d', strtotime('last Monday'));
                    $filter['date_to'] =  date('Y-m-d');
                break;
                
                case 'month':
                    $filter['date_from'] = date('Y-m-d', strtotime(date('Y-m-').'01'));
                    $filter['date_to'] =  date('Y-m-d');
                break;
                
                case 'optional':
                    $filter['date_from'] = date('Y-m-d', strtotime($from));
                    $filter['date_to'] = date('Y-m-d', strtotime($to));
                break;
                
            endswitch;
                
            $stats = $this->referrals->get_stats($filter);

            if (!($sort = $this->request->post('sort')))
                $sort = 'source_asc';
            $this->design->assign('sort', $sort);
            
            switch ($sort):
                
                case 'source_asc':
                    usort($stats, function($a, $b){
                        return strcasecmp($a->utm_source, $b->utm_source);
                    });
                break;
                
                case 'source_desc':
                    usort($stats, function($a, $b){
                        return strcasecmp($b->utm_source, $a->utm_source);
                    });
                break;
                
                case 'webmaster_asc':
                    usort($stats, function($a, $b){
                        return intval($a->webmaster_id) - intval($b->webmaster_id);
                    });                    
                break;
                
                case 'webmaster_desc':
                    usort($stats, function($a, $b){
                        return intval($b->webmaster_id) - intval($a->webmaster_id);
                    });                                        
                break;
                
                case 'referral_asc':
                    usort($stats, function($a, $b){
                        return intval($a->count) - intval($b->count);
                    });                                                            
                break;
                
                case 'referral_desc':
                    usort($stats, function($a, $b){
                        return intval($b->count) - intval($a->count);
                    });                                                            
                break;
                
                case 'order_asc':
                    usort($stats, function($a, $b){
                        return intval($a->count_orders) - intval($b->count_orders);
                    });                                                            
                break;
                
                case 'order_desc':
                    usort($stats, function($a, $b){
                        return intval($b->count_orders) - intval($a->count_orders);
                    });                                                            
                break;
                
                case 'amount_asc':
                    usort($stats, function($a, $b){
                        return intval($a->amount) - intval($b->amount);
                    });                                                            
                break;
                
                case 'amount_desc':
                    usort($stats, function($a, $b){
                        return intval($b->amount) - intval($a->amount);
                    });                                                                                
                break;
                
                case 'conversion_asc':
                    usort($stats, function($a, $b){
                        return intval($a->conversion) - intval($b->conversion);
                    });                                                                                
                break;
                
                case 'conversion_desc':
                    usort($stats, function($a, $b){
                        return intval($b->conversion) - intval($a->conversion);
                    });                                                                                
                break;
                
            endswitch;
            
            $this->design->assign('stats', $stats);
            
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($filter, $stats);echo '</pre><hr />';
        }
        
        
        return $this->design->fetch('stats.tpl');
    }
}

<?PHP

require_once('View.php');

class ApprovedLoans extends View
{
    function fetch()
    {
        //$limit = 1000;
        //$page = 1;
        //
        //if (isset($filter['limit'])){
        //    $limit = max(1, intval($filter['limit']));
        //}
        //
        //if(isset($filter['page'])) {
        //    $page = max(1, intval($filter['page']));
        //}
        //
        //$sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $daterange = $this->request->get('daterange');
        list($from, $to) = explode('-', $daterange);
            
        $date_from = date('Y-m-d', strtotime($from));
        $date_to = date('Y-m-d', strtotime($to));
        
        $this->design->assign('date_from', $date_from);
        $this->design->assign('date_to', $date_to);
        $this->design->assign('from', $from);
        $this->design->assign('to', $to);

        $approved_loans = $this->approveds->approvedLoans($date_from, $date_to);

        foreach ($approved_loans as $key => $loan) {
            $loan->pay_result = unserialize($loan->pay_result) ?? '';
            if ($loan->pay_result) {
                $loan->pay_result = $loan->pay_result['return'];
            }
        }

        $this->design->assign('approved_loans', $approved_loans);

        return $this->design->fetch('approved_loans/list.tpl');
    }
}

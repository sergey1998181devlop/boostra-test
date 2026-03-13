<?php

/**
 * Extension for Simpla
 * @author Alexander Urov
 */

require_once 'Simpla.php';
namespace api\traits;
use api\traits\setPages;


class NewTickets extends Simpla
{


 /**
  * Получаем выбранный тикет
   * @param int $id
   * @return mixed
 */
public function getTicketById(int $id){
	
	$query = $this->db->placehold("
            SELECT * 
            FROM __newtickets
            WHERE id = ?
        ", (int)$id);
     $this->db->query($query);
    $result = $this->db->result();	
  return $result;
}


 /**
  * Получаем список тикетов по User Id
   * @param int $user_id
   * @return mixed
 */
public function getTicketByUserId(int $user_id){
    
    $query = $this->db->placehold("
            SELECT * 
            FROM __newtickets
            WHERE user_id = ?
        ", (int)$user_id);
     $this->db->query($query);
    $result = $this->db->result();  
  return $result;
}

 /**
  * Поиск тикетов по ключевым полям 
    * @param array $filter
   * @return mixed
 */

public function searchTicket($filter = array()){
        $search_filter = '';
        $limit = 1000;
        $page = 1;

    if (isset($filter['search']))
        {
            if (!empty($filter['search']['chanel']))
            {
                
                $search_filter .= $this->db->placehold(" AND chanel LIKE '%".$this->db->escape($filter['search']['chanel'])."%'");
            }

            if (!empty($filter['search']['subject']))
            {
               
                $search_filter .= $this->db->placehold(" AND subject LIKE '%".$this->db->escape($filter['search']['subject'])."%'");
            }

            if (isset($filter['search']['status']))
            {
                $search_filter .= $this->db->placehold(" AND status = ?", (int)$filter['search']['status']);
            }

            if (isset($filter['search']['department']))
            {
                $search_filter .= $this->db->placehold(" AND department = ?", (int)$filter['search']['department']);
            }

      if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);


      $query = $this->db->placehold("
            SELECT *
            FROM __newtickets
            WHERE 1
            $keyword_filter
            ORDER BY id DESC
            $sql_limit
        ");

        $this->db->query($query);
        $results = $this->db->results();
        return $results;
        }

}

 /**
  * Добавляем новый комментарий к тикету
   * @param array $data
   * @return mixed
 */
public function addMyTicketComment($data) {
        $query = $this->db->placehold("
            INSERT INTO __newticketcomments SET comment='" .
                $data['comment'] . "', user_id='" .
                $data['user_id'] . "', tiсket_id='" . $data['tiсketId'] . "'");
        $this->db->query($query);
     $result = $this->db->result();  
  return $result; 
}

 /**
  * Обновляем выбранный тикет
   * @param int $id
   * @param array $data
   * @return mixed
 */
public function updateMyTicket($data, $id) {
        $query = $this->db->placehold("
            UPDATE __newtickets SET ?% WHERE id = ?
        ", (array) $data, (int) $id);
        $this->db->query($query);
   return $id;
 }

 /**
  * Считаем количество тикетов
   * @param array $filter
   * @return mixed
 */
public function countTickets($filter = array()) {
        $id_filter = '';
        $order_id_filter = '';
        $keyword_filter = '';
        $where = [];
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
      
      if (!empty($filter['client_id']))
            $order_id_filter = $this->db->placehold("AND client_id  = ?", (int)$filter['client_id']);
        
        if(isset($filter['status']))
      {
         $keywords = explode(' ', $filter['status']);
         foreach($keywords as $keyword)
            $keyword_filter .= $this->db->placehold('AND (status LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
      }
      
      $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM ___newtickets
            WHERE 1
                $id_filter
                $order_id_filter
                $keyword_filter
            -- {{where}}
        ");

        $query = strtr($query, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);
    return $this->db->result('count');

}

 /**
  * Получаем общий список статусов тикетов
   * @param array $filter
   * @return mixed
 */
public function getListStatuses($filter = array()) {
        $id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id'])) {
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array) $filter['id']));
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
            }
        }

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT *
            FROM __newticket_statuses
            WHERE 1
                $id_filter
            $keyword_filter
            ORDER BY id DESC
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
    return $results;
    }

 /**
  * Считаем количество статусов тикетов
   * @param array $filter
   * @return mixed
 */
public function countStatuses($filter = array()) {
        $id_filter = '';
        $keyword_filter = '';

        if (!empty($filter['id'])) {
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array) $filter['id']));
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= $this->db->placehold('AND (status LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
            }
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __newticket_statuses
            WHERE 1
                $id_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
 return $count;
}

 /**
  * Получаем общий список статусов subject
   * @param array $filter
   * @return mixed
 */
public function getListSubjects($filter = array()) {
        $id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id'])) {
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array) $filter['id']));
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= $this->db->placehold('AND (subject LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
            }
        }

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT *
            FROM __newticket_subjects
            WHERE 1
                $id_filter
            $keyword_filter
            ORDER BY id DESC
            $sql_limit
        ");

        $this->db->query($query);
        $results = $this->db->results();
    return $results;
}

 /**
  * Получаем последний комментарий  по ID клиента
   * @param int $client_id
   * @param array $data
   * @return mixed
 */
public function getLastComment($ticket_id) {
        $query = $this->db->placehold("
            SELECT *
            FROM __newtickets_comments
            WHERE ticket_id = ?
            ORDER BY date_create DESC
            LIMIT 1
        ", (int) $ticket_id);
        $this->db->query($query);
        $result = $this->db->result('comment_body');
     return $result;
}

 /**
  * Получаем выбранный тикет по ID клиента
   * @param int $client_id
   * @param array $data
   * @return mixed
 */
public function getTicketByClientId($client_id) {
        $query = $this->db->placehold("
            SELECT *
            FROM __newtickets
            WHERE client_id = ?
            ORDER BY id DESC 
        ", (int) $client_id);
        $query .= $this->getLimit($query);
        $this->db->query($query);
        $result = $this->db->results();
    return $result;
}

 /**
  * Создает новый тикет
   * @param array $data
   * @return mixed
 */
 public function createTicket(array $data) {

 $query = $this->db->placehold("
            INSERT INTO __newtickets SET ?%
        ", (array) $data);
        $this->db->query($query);
    return $this->db->insert_id();

  }

 /**
  * Статистика количества клиентов по ID клиента
   * @param int $client_id
   * @return mixed
 */
 public function getClientIDCount($client_id) {

 $query = $this->db->placehold("
            SELECT count(client_id)
            FROM __newtickets
            WHERE client_id = ?
            ORDER BY id DESC 
        ", (int) $client_id);
       $result =  $this->db->query($query);
    return $result;

  }

 /**
  * Статистика  по ID клиента
   * @param int $client_id
   * @return mixed
 */
 public function getStatisticClientID($client_id) {

 $query = $this->db->placehold("
            SELECT registration_date, subject,status,count(status) as total_status,count(client_id) as total_clients
            FROM __newtickets
            WHERE client_id = ?
            ORDER BY id DESC 
        ", (int) $client_id);
        $result = $this->db->results();
    return $result;
  }

/**
  * Статистика  по ID клиента и избранной дате
   * @param int $client_id
   * @param  string $date
   * @return mixed
 */
 public function getStatisticByDate($client_id,$date) {
 $seldate=date('Y-m-d',strtotime($date));
 $query = $this->db->placehold("
            SELECT registration_date, subject,status,count(status) as total_status,count(client_id) as total_clients
            FROM __newtickets WHERE registration_date = ? 
            AND WHERE client_id = ?
        ",  $seldate, (int) $client_id);
        $result = $this->db->results();
    return $result;
  }


/**
  * Статистика  по ID клиента и в промежутках между двумя датами
   * @param int $client_id
   * @param string $start_date
   * @param string $end_date
   * @return mixed
 */
 public function getStatisticByDateRange($client_id,$start_date,$end_date) {
 $start_seldate=date('m-d-Y',strtotime($start_date));
 $end_seldate=date('m-d-Y',strtotime($end_date));
 $query = $this->db->placehold("
            SELECT registration_date, subject,status,count(status) as total_status,count(client_id) as total_clients
            FROM __newtickets WHERE registration_date =".$end_seldate." AND 
            registration_date =".$end_date." AND WHERE client_id =". (int) $client_id);
        $result = $this->db->results();
    return $result;
  }


}

?>
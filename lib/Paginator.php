<?php

namespace plugins\riResultList;
class Paginator{
    protected 
        $record_per_page = 10,
        $total_page = 0,
        $total_record = 0,
        $current_page = 1,
        $count_key = '*',
        $query;
    
    /**
     * set the query to get results
     * @param string $query
     * @return Paginator
     */
    public function setQuery($query, $count_key = '*'){
        $this->query = $query;
        $this->count_key = $count_key;
        return $this;
    }
    
    /**
     * set the current page
     * @param int $current_page
     * @return Paginator
     */
    public function setCurrentPage($current_page){
        $current_page = (int)$current_page;
        $this->current_page = $current_page > 0 ? $current_page : 1;
        return $this;
    }

    /**
     * return the current page
     * @return int
     */
    public function getCurrentPage(){
        return $this->current_page;
    }
    
    /**
     * set the total number of record to be shown
     * @param integer $record_per_page
     * @return Paginator
     */
    public function setRecordPerPage($record_per_page){
        $this->record_per_page = (int)$record_per_page;
        return $this;
    }

    /**
     * return the total record per page
     * @return int
     */
    public function getRecordPerPage(){
        return $this->record_per_page;
    }

    /**
     * process the query with all the provide info
     * @return Paginator
     */
    public function proccess(){
        global $db;
        // taken from split_page_results.php of Zencart
        // we will attempt to build the count query from the original query
        $pos_to = strlen($this->query);
        $query_lower = strtolower($this->query);
        $pos_from = strpos($query_lower, ' from', 0);

        $pos_group_by = strpos($query_lower, ' group by', $pos_from);
        if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

        $pos_having = strpos($query_lower, ' having', $pos_from);
        if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

        $pos_order_by = strpos($query_lower, ' order by', $pos_from);
        if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

        if (strpos($query_lower, 'distinct') || strpos($query_lower, 'group by')) {
            $count_string = 'distinct ' . zen_db_input($this->count_key);
        } else {
            $count_string = zen_db_input($this->count_key);
        }
        $count_query = "select count(" . $count_string . ") as total " .
            substr($this->query, $pos_from, ($pos_to - $pos_from));

        $result = $db->Execute($count_query);

        $this->total_record = $result->fields['total'];

        $this->total_page = ceil($this->total_record / $this->record_per_page);

        return $this;
    }

    /**
     * return the record query with limit
     * @return string
     */
    public function getRecordsQuery(){
        $offset = ($this->record_per_page * ($this->current_page - 1));

        return $this->query . ' LIMIT ' . $offset . ', ' . $this->record_per_page;
    }

    /**
     * return the database result object
     * @return bool|null|\queryFactoryResult
     */
    public function getRecords(){
        global $db;

        return $db->Execute($this->getRecordsQuery());
    }

    /**
     * return the total number of record
     * @return int
     */
    public function getTotalRecord(){
        return $this->total_record;
    }

    /**
     * return the total number of page
     * @return int
     */
    public function getTotalPage(){
        return $this->total_page;
    }
}
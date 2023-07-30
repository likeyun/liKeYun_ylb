<?php
class DB_API
{
    // 数据库表名
    protected $table;
 
    // 数据库主键
    protected $primary = 'id';
 
    // 表前缀
    protected $prefix = '';
 
    // WHERE和ORDER拼装后的条件
    private $filter = array();
 
    // PDO
    private $pdo;
    
    // PDOStatement
    private $Statement;
    
    // PDO链接数据库
    public function __construct($config){
        class_exists('PDO') or exit("not found PDO");
        
        try{
            $this->pdo = new PDO("mysql:host=".$config['db_host'].";port=".$config['db_port'].";dbname=".$config['db_name'],$config['db_user'], $config['db_pass']);
        }catch(PDOException $e){
            // 数据库无法链接，如果您是第一次使用，请先配置数据库！
            exit($e->getMessage());
        }
        $this->prefix = $config['db_prefix'];
        $this->pdo->exec("SET NAMES UTF8");
        
        
    }
    // 配置表信息
    public function set_table($table=null,$primary='id'){
        if($table==null){ exit('Not found Table');}
        
        $this->primary = $primary;
        $this->table = $this->prefix.$table;
        return $this;
    }
    
    
    // 获取数据
    public function getData($sql)
    {
        if(!$result = $this->query($sql))return array();
        if(!$this->Statement->rowCount())return array();
        $rows = array();
        while($rows[] = $this->Statement->fetch(PDO::FETCH_ASSOC)){}
        $this->Statement=null;
        array_pop($rows);
        return $rows;
    }
    
    // 查询数据条数
    public function getCount($conditions){
        $where = '';
        if(is_array($conditions)){
            $join = array();
            foreach( $conditions as $key => $value ){
                $value =  '\''.$value.'\'';
                $join[] = "{$key} = {$value}";
            }
            $where = "WHERE ".join(" AND ",$join);
        }else{
            if(null != $conditions)$where = "WHERE ".$conditions;
        }
        $sql = "SELECT count(*) as Frcount FROM {$this->table} {$where}";
        $result = $this->getData($sql);
        return $result[0]['Frcount'];
        
    }
    // 获取单一字段内容
    public function getField($where=null,$fields=null){
        if( $record = $this->findAll($where, null, $fields, 1) ){
            $res = array_pop($record);
            return $res[$fields];
        }else{
            return FALSE;
        }
    }
    // 递增数据
    public function goInc($conditions,$field,$vp=1){
        $where = "";
        if(is_array($conditions)){
            $join = array();
            foreach( $conditions as $key => $value ){
                $value = '\''.$value.'\'';
                $join[] = "{$key} = {$value}";
            }
            $where = "WHERE ".join(" AND ",$join);
        }else{
            if(null != $conditions)$where = "WHERE ".$conditions;
        }
        $values = "{$field} = {$field} + {$vp}";
        $sql = "UPDATE {$this->table} SET {$values} {$where}";
        
        return $this->pdo->exec($sql);
        
    }
    
    // 递减
    public function goDec($conditions,$field,$vp=1){
        return $this->goInc($conditions,$field,-$vp);
    }
    
    // 修改数据
    public function update($conditions,$row)
    {
        $where = "";
        $row = $this->__prepera_format($row);
        if(empty($row)){
            return FALSE;
        }
        if(is_array($conditions)){
            $join = array();
            foreach( $conditions as $key => $condition ){
                $condition = '\''.$condition.'\'';
                $join[] = "{$key} = {$condition}";
            }
            $where = "WHERE ".join(" AND ",$join);
        }else{
            if(null != $conditions){
              $where = "WHERE ".$conditions;
            }
        }
        foreach($row as $key => $value){
            $value = '\''.$value.'\'';
            $vals[] = "{$key} = {$value}";
        }
        $values = join(", ",$vals);
        $sql = "UPDATE {$this->table} SET {$values} {$where}";
        $res = $this->pdo->exec($sql);
        if($res){
            return $res;
        }else{
            return $this->pdo->errorInfo();
        }
        
        
    }
 
    // 查询所有
    public function findAll($conditions=null,$order=null,$fields=null,$limit=null)
    {
        $where = '';
        if(is_array($conditions)){
            $join = array();
            foreach( $conditions as $key => $value ){
                $value =  '\''.$value.'\'';
                $join[] = "{$key} = {$value}";
            }
            $where = "WHERE ".join(" AND ",$join);
        }else{
            if(null != $conditions)$where = "WHERE ".$conditions;
        }
      if(is_array($order)){
               $where .= ' ORDER BY ';
            $where .= implode(',', $order);
      }else{
         if($order!=null)$where .= " ORDER BY  ".$order;
      }
        
        if(!empty($limit))$where .= " LIMIT {$limit}";
        $fields = empty($fields) ? "*" : $fields;
 
        $sql = "SELECT {$fields} FROM {$this->table} {$where}";
        
        return $this->getData($sql);
 
    }
 
    // 查询一条
    public function find($where=null,$order=null,$fields=null,$limit=1)
    {
       if( $record = $this->findAll($where, $order, $fields, 1) ){
            return array_pop($record);
        }else{
            return FALSE;
        }
    }
    
    // 执行SQL语句并检查是否错误
    public function query($sql){
        $this->filter[] = $sql;
        $this->Statement = $this->pdo->query($sql);
        if ($this->Statement) {
            return $this;
        }else{
            $msg = $this->pdo->errorInfo();
            if($msg[2]) exit('数据库错误：' . $msg[2] . end($this->filter));
        }
    }
 
    // 执行SQL语句函数
    public function findSql($sql)
    {
        return $this->getData($sql);
    }
    
    // 根据条件 (conditions) 删除
    public function delete($conditions)
    {
       $where = "";
        if(is_array($conditions)){
            $join = array();
            foreach( $conditions as $key => $condition ){
                $condition = '\''.$condition.'\'';
                $join[] = "{$key} = {$condition}";
            }
            $where = "WHERE ( ".join(" AND ",$join). ")";
        }else{
            if(null != $conditions)$where = "WHERE ( ".$conditions. ")";
        }
        $sql = "DELETE FROM {$this->table} {$where}";
        $res = $this->pdo->exec($sql);
        if($res){
            return $res;
        }else{
            return $this->pdo->errorInfo();
        }
    }
 
    // 新增数据
    public function add($row)
    {
       if(!is_array($row)){
         return FALSE;
       }
        $row = $this->__prepera_format($row);
        if(empty($row)){
         return FALSE;
        }
        foreach($row as $key => $value){
            $cols[] = $key;
            $vals[] = '\''.$value.'\'';
        }
        $col = join(',', $cols);
        $val = join(',', $vals);
 
        $sql = "INSERT INTO {$this->table} ({$col}) VALUES ({$val})";
        if( FALSE != $this->pdo->exec($sql) ){
            if( $newinserid = $this->pdo->lastInsertId() ){
                return $newinserid;
            }else{
                $a=$this->find($row, "{$this->primary} DESC",$this->primary);
                return array_pop($a);
            }
        }
        return FALSE;
    }
 
    private function __prepera_format($rows)
    {
        $stmt = $this->pdo->prepare('DESC '.$this->table);  
        $stmt->execute();  
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $newcol = array();
        foreach( $columns as $col ){
            $newcol[$col] = null;
        }
        return array_intersect_key($rows,$newcol);
    }

}

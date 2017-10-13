<?php
/**
 * Created by PhpStorm.
 * User: hasee
 * Date: 2017/4/18
 * Time: 16:57
 */
    //PDO操作类
   class PDO_MYSQL{
       //定义属性
       private $pdo; //保存实例化的pdo对象
       private $user; //数据库用户名
       private $password; //密码
       private $port; //端口号
       private  $charset; //字符集
       private  $dbname; //数据库名
       private  $host; //主机
       private  $stmt; //保存pdoStament类的对象
//       private  $tableName=null; //要操作的数据表

       /*
         *初始化构造方法
         * $config array() 传入一个带有参数的数组
        */
        public function __construct(){
            //$this->user=defined(PI)? PI : '123';
             $this->user=isset($GLOBALS['config']['user']) ? $GLOBALS['config']['user'] : 'root';
             $this->password=isset($GLOBALS['config']['password']) ? $GLOBALS['config']['password'] : 'root';
             $this->host=isset($GLOBALS['config']['host']) ? $GLOBALS['config']['host'] : 'localhost';
             $this->charset=isset($GLOBALS['config']['charset']) ? $GLOBALS['config']['charset'] : 'utf8';
             $this->dbname=isset($GLOBALS['config']['dbname']) ? $GLOBALS['config']['dbname'] : '1503php';
             $this->port=isset($GLOBALS['config']['port']) ? $GLOBALS['config']['port'] : '3306';
             //连接数据库
            $this->db_connect();
             //设置字符串
            $this->db_setCharset();
        }


       //连接数据库
       private function db_connect(){
            try{
                $this->pdo=new PDO("mysql:dbname=".$this->dbname.";host=".$this->host,$this->user,$this->password);
            }catch(PDOException $e){
                echo $e->getMessage();
            }
       }

       //设置字符集
         private  function db_setCharset(){
             $this->pdo->exec("set names {$this->charset}");
         }

       //读
        /*
         * 读操作
         * $sql 要查询的sql语句
         */
         public function db_query($sql){
             try{
               if(!$stmt=$this->pdo->query($sql)) {
                   throw  new PDOException($this->pdo->errorInfo()[2]);
               }
                 return  $stmt;
             }catch(PDOException $e){
                 echo $e->getMessage();
             }
         }

        /*
         * 写操作
         * $sql string 要执行的sql语句
         */
        public function db_exec($sql){
            try{
                if(!$affected_rows=$this->pdo->exec($sql)){
                    throw  new PDOException($this->pdo->errorInfo()[2]);
                }
                //获取自增ID
                $lastInsertId=$this->pdo->lastInsertId();
                return  $lastInsertId ? $lastInsertId : $affected_rows;
            }catch(PDOException $e){
                echo $e->getMessage();
            }
        }

       /*
        * 插入方法
        * @param1 $data array() 插入的数组（数组为关联数组，且数组的键跟数据库的字段一一对应）
        * @param2  $tableName  要操作的数据表
        * @return $id int  自增ID
        */
       public function db_insert($data,$tableName){
           //$sql="insert into money(键) values(值)";
           if(!is_array($data)||count($data)<1){
               return false;
           }
           $keys='';
           $values='';
           foreach($data as $key=>$v){
               $keys.=','.$key;
               $values.=",'$v'";
           }
            $keys=ltrim($keys,',');
            $values=ltrim($values,',');
            $sql="insert into {$tableName} ($keys) values ($values)";
           // return $sql;
            return $this->db_exec($sql);
       }

       /*
        * 删除操作
        *param1 $tableName string  要查询的数据表
        *@param2 $where string 要查询的条件
        *return  int 受影响的行数
        */
        public function db_delete($tableName,$where){
            $sql="delete from {$tableName}".$this->parseWhere($where);
           return $this->db_exec($sql);
        }

       /*
        * 修改操做
         * @param1 $tableName string  要查询的数据表
         * @param2 $where string 要查询的条件
         * @param3 $data array() 要修改成的数组（数组为关联数组，且数组的键跟数据库的字段一一对应）
         * return  受影响的行数
        */
        public function db_update($tableName,$data,$where=''){
            //$sql="update {$tableName} set 字段列表=值 where 条件";
             $keys='';
            foreach($data as $key=>$v){
                $keys.=','.$key.'='."'$v'";
            }
            $keys=ltrim($keys,',');
            $sql="update {$tableName} set {$keys}".$this->parseWhere($where);
           return $this->db_exec($sql);
        }

        /*
         * 计算总记录数
         * @param1 $tableName string  要查询的数据表
         * @param2 $where string 要查询的条件
         * @param3 $group string/int 分组的字段
         * @param4 $order string/array 排序的字段
         * @param5 $limit string 限制条数
         * @param6 $field string 查询的字段
         * return $count int 总记录数
         */
        public  function db_count($tableName,$fields='*',$where='',$group='',$order='',$limit=''){
            $data=$this->db_select($tableName,$fields,$where,$group,$order,$limit);
            return count($data);
        }


        /*
         * 查询操作
         * @param1 $fields string/array 要查询的字段
         * @param2 $tableName string  要查询的数据表
         * @param3 $where string 要查询的条件
         * @param4 $group string/int 分组的字段
         * @param5 $order string/array 排序的字段
         * @param6 $limit string 限制条数
         * return $result 一维数组/二维数组
         */
        public function db_select($tableName,$fields='*',$where='',$group='',$order='',$limit=''){
             //调用查询字段的方法
            $sql=$this->parseField($tableName,$fields).$this->parseWhere($where).$this->parseGroup($group);
             $stmt=$this->db_query($sql);
            $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
            return count($data)>1 ? $data : $data[0];
        }
    /*
   * 根据group by条件生成group字符串
   * @param1 $group string 要操作的条件
   * @return $groupStr
   */
    private  function parseGroup($group){
        $groupStr='';
        if($group){
            $groupStr.=" group by $group";
        }
        return $groupStr;
    }

       /*
        * 根据where条件生成where字符串
        * @param1 $where string 要操作的条件
        * @return $whereStr
        */
       private  function parseWhere($where){
             $whereStr='';
             if($where){
                 $whereStr.=" where $where";
             }
            return $whereStr;
       }


        /*
         * 根据字段生成sql语句
         * @param1 $field string/array 要查询的字段列表
         * @param2 $tableName 要查询的数据表
         * @return $fieldsStr  返回带有字段列表的sql语句
         */
        private function parseField($tableName,$fields){
             $fieldsStr='';
             if($fields==''){
                 $fieldsStr.="select * from {$tableName}";
             }elseif(is_array($fields)){
                 $fields=implode(',',$fields);
                 $fieldsStr.="select {$fields} from {$tableName}";
             }elseif(is_string($fields)&&$fields!=''){
                 $fieldsStr.="select {$fields} from {$tableName}";
             }
            return $fieldsStr;
        }

   }



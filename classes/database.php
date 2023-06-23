<?php
    
    define('DBUSER','root');
    define('DBPASS','');
    define('DNAME','chatapp');
    define('DBHOST','localhost');
class Database{

    private $con;
    function __construct(){
        $this->con= $this->connect();
    }
    private function connect(){
        $string="mysql:host=localhost;dbname=chatapp";
        try{
            $connection=new PDO($string,DBUSER,DBPASS);
            return $connection;
        }
        catch(PDOException $e){
            echo $e->getMessage();
            die;
        }
        return false;
    }
    public function write($query,$data_array=[]){
        $con= $this->connect();
        $statement=$con->prepare($query);
        $check= $statement->execute($data_array);
        if($check){
            return true;
        }
        return false;

    }


    public function read($query,$data_array=[]){
        $con= $this->connect();
        $statement=$con->prepare($query);
        $check= $statement->execute($data_array);
        if($check){
            $result=$statement-> fetchAll(PDO::FETCH_OBJ);
            if(is_array($result)&& count($result)>0){
                return $result;
            }
            return false;
        }
        return false;

    }


    public function get_user($userid){
        $con= $this->connect();
        $arr['userid']=$userid;
        $query="select * from signup where userid= :userid limit 1";
        $statement=$con->prepare($query);
        $check= $statement->execute($arr);
        if($check){
            $result=$statement-> fetchAll(PDO::FETCH_OBJ);
            if(is_array($result)&& count($result)>0){
                return $result[0];
            }
            return false;
        }
        return false;

    }


    public function generate_id(){
        $rand="";
        // digit size
        $rand_count= rand(4,19);
        for($i=0; $i<$rand_count;$i++){
            $r=rand(0,9);
            // the actual random number 
            $rand .=$r;
        }
        return $rand;
    }
}

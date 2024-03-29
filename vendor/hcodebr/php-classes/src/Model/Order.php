<?php

namespace Hcode\Model;
use  \Hcode\DB\Sql;
use  \Hcode\Model;
use  \Hcode\Model\Cart;

class Order extends Model{

const SUCCESS = "Order-Success;save";
const ERROR = "Order-Error";


public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [
			':idorder'=>$this->getidorder(),
			':idcart'=>$this->getidcart(),
			':iduser'=>$this->getiduser(),
			':idstatus'=>$this->getidstatus(),
			':idaddress'=>$this->getidaddress(),
			':vltotal'=>$this->getvltotal()
		]);

		if (count($results) > 0) {
			$this->setData($results[0]);
		}

	}


public function get($idorder)
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.idorder = :idorder
		", [
			':idorder'=>$idorder
		]);

		if (count($results) > 0) {
			$this->setData($results[0]);
		}

	}

public static function listAll(){

$sql = new Sql;

return $sql->select("
            SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			ORDER BY a.dtregister DESC
		");


  }


  public function delete(){


  	$sql= new Sql;

  	$sql->query("DELETE FROM tb_orders WHERE idorder = :idorder",[
    
    ':idorder'=> $this->getidorder()

  	]);
  }  

  public function getCart():Cart{

 $cart = new Cart();

 $cart->get((int)$this->getidcart());

 return $cart;


  }

  public function setError($msg){

     $_SESSION[ORDER::ERROR] = $msg;

    }

    public function getError(){

     $msg = (isset($_SESSION[ORDER::ERROR]) && $_SESSION[ORDER::ERROR]) ? $_SESSION[ORDER::ERROR] : '';
    
     User::clearError();
    
     return $msg;

    }

    public function clearError(){

    $_SESSION[ORDER::ERROR] = NULL;

    }

    public function setSuccess($msg){

     $_SESSION[User::SUCCESS] = $msg;

    }

    public function getSuccess(){

     $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';
    
     User::clearSuccess();
    
     return $msg;

    }

    public function clearSuccess(){

    $_SESSION[User::SUCCESS] = NULL;

    }

public static function getPage($page = 1 , $itensPerPage = 10){

      $start = ($page -1) * $itensPerPage;
              $sql = new Sql();
  $results = $sql->select("
  	          SELECT sql_calc_found_rows * 
              FROM tb_orders a 
							INNER JOIN tb_ordersstatus b USING(idstatus) 
							INNER JOIN tb_carts c USING(idcart)
							INNER JOIN tb_users d ON d.iduser = a.iduser
							INNER JOIN tb_addresses e USING(idaddress)
							INNER JOIN tb_persons f ON f.idperson = d.idperson
							ORDER BY a.dtregister DESC
              LIMIT $start,$itensPerPage

              ");

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

        return [
         'data'=>$results,
         'total'=>(int)$resultTotal[0]["nrtotal"],
         'pages'=>ceil($resultTotal[0]["nrtotal"] / $itensPerPage)
        ];
}

public static function getPageSearch($search, $page = 1 , $itensPerPage = 10){

      $start = ($page -1) * $itensPerPage;
              $sql = new Sql();
  $results = $sql->select("SELECT sql_calc_found_rows 
            * FROM FROM tb_orders a 
							INNER JOIN tb_ordersstatus b USING(idstatus) 
							INNER JOIN tb_carts c USING(idcart)
							INNER JOIN tb_users d ON d.iduser = a.iduser
							INNER JOIN tb_addresses e USING(idaddress)
							INNER JOIN tb_persons f ON f.idperson = d.idperson
							WHERE a.idorder = :id OR f.desperson LIKE :search
				      ORDER BY a.dtregister DESC
	            LIMIT $start,$itensPerPage

              ",[
               'search'=>'%'.$search.'%',
               'id' => $search

                ]);

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

        return [
         'data'=>$results,
         'total'=>(int)$resultTotal[0]["nrtotal"],
         'pages'=>ceil($resultTotal[0]["nrtotal"] / $itensPerPage)
        ];
}



}



?>
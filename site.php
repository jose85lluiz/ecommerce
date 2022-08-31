<?php

use  \Hcode\Page;
use  \Hcode\Model\Products;
use  \Hcode\Model\Category;
use  \Hcode\Model\Cart;
use  \Hcode\Model\Address;
use  \Hcode\Model\User;


$app->get('/', function() {

    $products = Products::listAll();
    $page = new Page();
    $page ->setTpl("index",[

    'products'=> Products::checkList($products)
       

    ]);
});

$app->get("/categories/:idcategory",function($idcategory){

$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

$category = new Category();
$category->get((int)$idcategory);
$pagination = $category->getProductsPage($page);

$pages = [];

for ($i=1; $i<=$pagination['pages'] ; $i++) { 
    array_push($pages, [
    'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
    'page'=> $i
    ]);
}
$page = new Page();
$page ->setTpl("category",[
'category'=>$category->getValues(),
'products'=>$pagination["data"],
"pages"=> $pages

]);

});

$app->get("/products/:desurl",function($desurl){

$product = new Products();
$product -> getFromURL($desurl);

$page = new Page();
$page ->setTpl("product-detail",[

'product'=> $product->getValues(),
'categories'=> $product->getCategories()
]);

});

$app -> get("/cart",function(){

  $cart = Cart::getFromSession(); 
  $page = new Page();
  $page ->setTpl("cart",[
  'cart'=>$cart->getValues(),
  'products'=>$cart->getProducts(),
  'error' => Address::getMsgError()

  ]);

});

$app -> get("/cart/:idproduct/add",function($idproduct){

$product = new Products();

$product ->get((int)$idproduct);

$cart = Cart::getFromSession();

$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1 ;

for($i = 0; $i < $qtd; $i++){

 $cart->addProduct($product);

}

header("Location: /cart");

exit;

});



$app -> get("/cart/:idproduct/minus",function($idproduct){

$product = new Products();

$product ->get((int)$idproduct);

$cart = Cart::getFromSession();

$cart->removeProduct($product);

header("Location: /cart");

exit;

});

$app -> get("/cart/:idproduct/remove",function($idproduct){

$product = new Products();

$product ->get((int)$idproduct);

$cart = Cart::getFromSession();


$cart->removeProduct($product, true);

header("Location: /cart");


exit;

});


$app-> post("/cart/freight",function(){

$cart = cart::getFromSession();

$cart -> setFreight($_POST['zipcode']);

header("location: /cart");

exit;
});


$app -> get("/checkout", function(){

User::verifyLogin(false);

$address = new Address();
$cart = Cart::getFromSession();

if(isset($_GET['zipcode'])){

 $_GET['zipcode'] = $cart->getdeszipcode();

 }

if(isset($_GET['zipcode'])){

$address->loadFromCEP($_GET['zipcode']);

$cart->setdesipcode($_GET['zipcode']);

$cart->save();

$cart->getCalculateTotal();

}

if(!$address->getdesaddress()) $address->setdesaddress('');
if(!$address->getdescomplement()) $address->setdescomplement('');
if(!$address->getdesdistrict()) $address->setdesdistrict('');
if(!$address->getdescity()) $address->setdescity('');
if(!$address->getdesstate()) $address->setdesstate('');
if(!$address->getdescountry()) $address->setdescountry('');
if(!$address->getdeszipcode()) $address->setdeszipcode('');


$page = new Page();

$page ->setTpl("checkout",[

  'cart'=> $cart->getValues(),
  'address'=> $address->getValues(),
  'products'=>$cart->getProducts(),
  'error'=> Address::getMsgError()

   ]);

});

$app->post("/checkout",function(){

User::verifyLogin(false);

if(!isset($_POST['zipcode']) || $_POST['zipcode'] === ''){

    Address::setMsgError("Informe o Cep.");
    header("Location:/checkout");
    exit;
}
if(!isset($_POST['desaddress']) || $_POST['desaddress'] === ''){

    Address::setMsgError("Informe o Endereço.");
    header("Location:/checkout");
    exit;
}
if(!isset($_POST['desdistrict']) || $_POST['desdistrict'] === ''){

    Address::setMsgError("Informe o Bairro.");
    header("Location:/checkout");
    exit;
}
if(!isset($_POST['descity']) || $_POST['descity'] === ''){

    Address::setMsgError("Informe a Cidade.");
    header("Location:/checkout");
    exit;
}
if(!isset($_POST['desstate']) || $_POST['desstate'] === ''){

    Address::setMsgError("Informe o Estado.");
    header("Location:/checkout");
    exit;
}
if(!isset($_POST['descountry']) || $_POST['descountry'] === ''){

    Address::setMsgError("Informe o País.");
    header("Location:/checkout");
    exit;
}



$user = User::getFromSession();

$address = new Address();
$_POST['deszipcode'] = $_POST['zipcode'];
$_POST['idperson'] = $user->getidperson();
$address->setData($_POST);

$address->save();
header("Location: /order");
exit;

});


$app -> get("/login", function(){

$page = new Page();

$page ->setTpl("login",[

'error'=>User::getError(),
'errorRegister'=> User::getErrorRegister(),
'registerValues'=> (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'','email'=>'','phone'=>'']

]);

});


$app->post("/login", function(){

try{

User::login($_POST['login'], $_POST['password']);

}catch(Excption $e){

User::setError($e->getMessage());

}

header("Location:/checkout");

exit;

});

$app->get("/logout",function(){

User::logout();

header("Location: /login");

exit;

});


$app -> post("/register", function(){

 $_SESSION['registerValues'] = $_POST;

 if(!isset($_POST['name']) || $_POST['name'] == '') {

User::setErrorRegister("Preencha seu nome.");

header("Location: /login");

exit;

 } 

 if(!isset($_POST['email']) || $_POST['email'] == '') {

User::setErrorRegister("Preencha seu email.");

header("Location: /login");

exit;

 }

 if(!isset($_POST['password']) || $_POST['password'] == '') {

User::setErrorRegister("Preencha a senha.");

header("Location: /login");

exit;

 }  

 if (User::checkLoginExist($_POST['email']) === true){

User::setErrorRegister("Este endereço de email ja esta sendo usado!!!.");

header("Location: /login");

 }   

$user = new User();

$user -> setData([

'inadmin'=> 0,
'deslogin'=>$_POST['email'],
'desperson'=>$_POST['name'],
'desemail'=>$_POST['email'],
'despassword'=> $_POST['password'],
'nrphone'=>$_POST['phone']

  ]);

$user->save();

User::login($_POST['email'],$_POST['password']);

header("Location: /checkout");

exit;

});

$app->get('/admin/logout',function(){

User::logout();
header("Location:/admin/login");
exit;
});

//FORGOT



$app->get("/forgot",function(){
$page = new Page();
   $page -> setTpl("forgot");


});

$app->post("/forgot",function(){


$user = User::getForgot($_POST["email"],false);
header("Location:/forgot/sent");
exit;

});

$app ->get("/forgot/sent",function(){
$page = new Page();
   $page -> setTpl("forgot-sent");
   

});

$app->get ("/forgot/reset",function(){
$user = User::validForgotDecrypt($_GET['code']);

$page = new Page();
   $page -> setTpl("forgot-reset",array(

   "name"=> $user["desperson"],
   "code"=> $_GET['code']

   ));
});

$app ->post("/forgot/reset",function(){
$forgot = User::validForgotDecrypt($_POST['code']);

User::setForgotUsed($forgot["idrecovery"]);

$user = new User();
$user -> get((int)$forgot["iduser"]);

$password = password_hash($_POST["password"],PASSWORD_DEFAULT,[

 "cost"=>12
 ]);
$user -> setPassword($password);

$page = new Page();
   $page -> setTpl("forgot-reset-success");

});

$app->get("/profile",function(){

User::verifyLogin(false); 

$user = User::getFromSession();

$page = new Page();

$page ->setTpl("profile",[

'user'=> $user->getValues(),
'profileMsg'=>User::getSuccess(),
'profileError'=>User::getError()


 ]);

});

$app->post("/profile",function(){

User::verifyLogin(false);

if(!isset($_POST['desperson']) || $_POST['desperson'] === ''){

User::setError("Preencha seu nome.");
header('Location: /profile');
exit;

}
if(!isset($_POST['desemail']) || $_POST['desemail'] === ''){

User::setError("Preencha seu e-email.");
header('Location: /profile');
exit;

}

$user = User::getFromSession();
if($_POST['desemail'] !== $user->getdesemail()){

 if(User::checkLoginExist($_POST['desemail']) === true){

 User::setError('Este endereço de  E-email já esta cadastrado.'); 
 header('Location: /profile');
 exit;

 }

}

$_POST['inadmin'] = $user->getinadmin();
$_POST['despassword'] = $user->getdespassword();
$_POST['deslogin'] = $_POST['desemail'];
$user->setData($_POST);

$user->update();

User::setSuccess("Dados alterados com sucesso!");

header('Location: /profile ');

exit;

});

?>

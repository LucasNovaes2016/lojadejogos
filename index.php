<?php
  session_start();
  require('config/config.php');

  if (isset($_SESSION['id']))
  {
    header('Location: admin.php');
  }

  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
  $pdo = new PDO ($dsn, DB_USER, DB_PASS);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


  // define variables and set to empty values
  $username = $password = "";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = htmlspecialchars($_POST["username"]);
    $password = htmlspecialchars($_POST["password"]);

    // Verifica se todos os campos foram preenchidos...
    if (empty($username) || empty($password))
    {
      header('Location: signin-page.php?loginattempt=1');
    } else {
      // Verifica se o usuario digitado existe
      $query = "SELECT * FROM usuarios WHERE username = ? LIMIT 1";
      $stm = $pdo->prepare($query);
      $stm->execute([$username]);

      if ($stm->rowCount()==0)
      {
        header('Location: signin-page.php?loginattempt=2');
      } else
      {
        $usuario = $stm->fetch(PDO::FETCH_ASSOC);
        $dbpassword = $usuario['pwd'];
        $password = PASSWORD_VERIFY($password, $dbpassword);

        if ($username==$usuario['username'] && $password==$dbpassword)
        {
          // Deu Certo
          $id = $usuario['id'];
          $_SESSION['id'] = $id;
          header('Location: admin.php');

        } else {
          header('Location: signin-page.php?loginattempt=3');
        }

      }

    }

   }


?>
<?php include('inc/header.php') ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-light-blue">
   <div class="container">
      <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
      </button>
      <a class="navbar-brand ml-5" href="#"><i class="fa fa-gamepad fa-2x mr-2 align-bottom" aria-hidden="true"></i>GameStore</a>
      <div class="collapse navbar-collapse" id="navbarNav">
         <ul class="navbar-nav ml-auto">
             <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="form-inline">
               <label class="sr-only" for="inputUsername">Username</label>
               <input type="text" name="username" class="form-control mb-2 mr-sm-2" id="inlineFormInputUsername2" placeholder="Username">
               <label class="sr-only" for="inputSenha">Senha</label>
               <div class="input-group mb-2 mr-sm-2">
                  <input type="password" name="password" class="form-control" id="inlineFormInputGroupPassword2" placeholder="Senha">
               </div>
               <button type="submit" class="btn btn-success mb-2">Entrar</button>
            </form>
         </ul>
      </div>
   </div>
</nav>
<div id="mainpage">
  <div class="container">
    <div class="row">
      <div class="col-md-12 col-lg-7 mt-5">
        <h1 class="text-white display-4 d-none d-md-block"> GameStore : A maior comunidade de gamers da America Latina</h1>
        <h2 class="text-white"> Ainda não é membro? Crie uma conta gratuitamente: </h2>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6 col-md-4">
        <a class="btn btn-primary btn-lg btn-block mt-2" href="register.php">Realizar Cadastro</a>
      </div>
    </div>
  </div>
</div>

<footer id="myfooter" class="bg-light-blue text-white text-center">
      <p> &#169; 2018 Lucas Novaes </p>
  </footer>
<?php include('inc/footer.php') ?>
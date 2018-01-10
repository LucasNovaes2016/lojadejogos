<?php
   session_start();
   require('config/config.php');

   if (!isset($_SESSION['id']))
   {
     header("Location: " . ROOT_URL);
     exit();
   }

   $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
   $pdo = new PDO ($dsn, DB_USER, DB_PASS);
   $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

   $current_password_error = $new_password_error = $confirm_new_password_error = "";
   $current_password = $new_password = $new_password_confirm = "";

   if ($_SERVER["REQUEST_METHOD"] == "POST") {

     // Verifying current password
     if (empty($_POST["current_password"])) {
       $current_password_error = "Digite sua senha atual";
     } else {
       $current_password = $_POST["current_password"];
       if (!password_verify($current_password, $_SESSION['senha'])) {
           $current_password_error = "Senha digitada não corresponde a senha atual. ";
       }
     }

     // verifying new password

     if (empty($_POST["new_password"])) {
       $new_password_error = "Digite a nova senha";
     } else {
       $new_password = $_POST["new_password"];

       if (strlen($new_password)<8 or strlen($new_password)>14)
       {
         $new_password_error = "A nova senha deve conter entre 8 a 14 caracteres";
       } else if (preg_match('/\s/',$new_password))
       {
         $new_password_error = "A nova senha não pode conter espaços.";
       }
     }

     // Verifying new password confirmation

     if (empty($_POST["new_password_confirm"])) {
       $confirm_new_password_error = "Confirme a sua nova senha";
     } else {
       $new_password_confirm = $_POST["new_password_confirm"];

       if ($_POST["new_password_confirm"] !== $_POST["new_password"])
       {
         $confirm_new_password_error ="A senha não corresponde a senha digitada anteriormente";
       }
     }

     if ($current_password_error == "" and $new_password_error == "" and $confirm_new_password_error == "")
     {
       $query = "UPDATE usuarios SET
       pwd = ?
       WHERE id = ?";
       $stm = $pdo->prepare($query);

       $new_password = PASSWORD_HASH($new_password, PASSWORD_BCRYPT, array('cost' => 12));
       $_SESSION['senha'] = $new_password;

       if($stm->execute([$new_password, $_SESSION['id']]))
       {
         $_SESSION['mudou_senha'] = "Senha alterada com sucesso. ";
         header("Location: " . PROFILE_URL);
       }

     }
   }


   function test_input($data) {
     $data = trim($data);
     $data = stripslashes($data);
     $data = htmlspecialchars($data);
     return $data;
   }

   ?>
<?php include('inc/header.php') ?>
<div class="container">
   <nav class="navbar navbar-expand-lg navbar-dark bg-light-blue">
      <a class="navbar-brand" href="#">GameStore</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
         <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
               <a class="nav-link dropdown-toggle text-white" href="http://example.com" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               Minha conta
               </a>
               <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                  <a class="dropdown-item" href="inicio.php">Voltar para a Loja</a>
                  <?php if (!$_SESSION['admin']) : ?>
                  <a class="dropdown-item" href="meu-carrinho.php">Meu Carrinho</a>
                  <a class="dropdown-item" href="meu-historico-compras.php">Historico de Compras</a>
                  <?php endif; ?>
                  <a class="dropdown-item" href="logout.php">Sair</a>
               </div>
            </li>
         </ul>
      </div>
   </nav>
</div>
<div class="container mt-5">
   <div class="row mt-2">
      <div class="col-md-8 col-lg-6 m-auto bordered">
         <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
            <div class="text-center">
               <h3> Alteração de Senha </h3>
            </div>
            <div class="form-group">
               <label for="currentpassword" class="font-weight-bold">  Senha Atual:  </label>
               <input type="password" name="current_password" value="<?php echo isset($_POST['current_password']) ? $current_password : ''; ?>" class="form-control" placeholder="Digite sua senha atual">
               <span class="error"> <?php echo $current_password_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="newpassword" class="font-weight-bold">  Nova Senha:  </label>
               <input type="password" name="new_password" value="<?php echo isset($_POST['new_password']) ? $new_password : ''; ?>" class="form-control" placeholder="Digite a sua nova senha">
               <span class="error"> <?php echo $new_password_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="newpasswordconfirm" class="font-weight-bold">  Confirme sua nova senha:  </label>
               <input type="password" name="new_password_confirm" value="<?php echo isset($_POST['new_password_confirm']) ? $new_password_confirm : ''; ?>" class="form-control" placeholder="Digite novamente sua nova senha">
               <span class="error"> <?php echo $confirm_new_password_error; ?> </span>
            </div>
            <div class="text-right">
               <a href="meu-perfil.php" class="btn btn-danger">Cancelar Operação</a>
               <input class="btn btn-success" type="submit" value="Alterar Senha">
            </div>
         </form>
      </div>
   </div>
</div>
<?php include('inc/footer.php') ?>

<?php
   session_start();
   require('config/config.php');

   if (!isset($_GET["loginattempt"]))
   {
     header("Location: " . ROOT_URL);
     exit();
   }

   $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
   $pdo = new PDO ($dsn, DB_USER, DB_PASS);
   $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

   $message = "";
   $is_message_error = true;
   $email = $password = "";
   $email_error = $password_error = "";

   $case = test_input($_GET["loginattempt"]);

   if ($case==1)
   {
     $message = "Você deve preencher todos os campos para entrar";
   } else if ($case==2)
   {
     $message = "O email digitado não existe";
   } else if ($case==3)
   {
     $message = "Campos Inválidos";
   }

   if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $message = "Preencha todos os campos abaixo";
     $is_message_error = false;

     $email = htmlspecialchars($_POST["email"]);
     $password = htmlspecialchars($_POST["password"]);

     if (empty($_POST["email"])) {
       $email_error = "Digite seu email";
     }

     if (empty($_POST["password"])) {
       $password_error = "Digite sua senha";
     }

     if ($email_error == "" && $password_error == "")
     {
       // Verifica se o email digitado está vinculado a alguma conta de usuario
       $query = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
       $stm = $pdo->prepare($query);
       $stm->execute([$email]);

       if ($stm->rowCount()==0)
       {
         $message = "O email digitado não está vinculado a nenhuma conta. ";
         $is_message_error = true;
       } else {
         $usuario = $stm->fetch(PDO::FETCH_ASSOC);
         $dbpassword = $usuario['pwd'];
         $password = PASSWORD_VERIFY($password, $dbpassword);

         if ($email==$usuario['email'] && $password==$dbpassword)
         {
           // Deu Certo
           $_SESSION['id'] = $usuario['id'];
           $_SESSION['admin'] = $usuario['admin'];
           $_SESSION['nome'] = $usuario['nome'];
           $_SESSION['sexo'] = $usuario['sexo'];
           $_SESSION['nascimento'] = $usuario['nascimento'];
           $_SESSION['username'] = $usuario['username'];
           $_SESSION['email'] = $usuario['email'];
           $_SESSION['senha'] = $usuario['pwd'];
           header('Location: ' . INICIO_URL);
         } else {
           $message = "Senha inválida";
           $is_message_error = true;
         }
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
<div class="container mt-3">
   <div class="text-center">
      <p <?php if ($is_message_error) { echo "class=error";} ?>> <?php echo $message ?> </p>
   </div>
   <div class="row justify-content-center">
      <form method="post" action="<?php echo htmlspecialchars("signin-page.php?loginattempt=$case");?>" class="col-md-6">
         <div class="form-group">
            <label for="name" class="font-weight-bold"> Email: </label>
            <input type="text" name="email" value="<?php echo isset($_POST['email']) ? $email : ''; ?>" class="form-control" aria-describedby="emailHelp" placeholder="Seu Email">
            <span class="error"> <?php echo $email_error; ?> </span>
         </div>
         <div class="form-group">
            <label for="password" class="font-weight-bold"> Senha: </label>
            <input type="password" name="password" value="<?php echo isset($_POST['password']) ? $password : ''; ?>" class="form-control" placeholder="Digite sua senha">
            <span class="error"> <?php echo $password_error; ?> </span>
         </div>
         <div class="text-right">
            <a href="index.php" class="btn btn-danger"> Cancelar </a>
            <button type="submit" class="btn btn-success"> Entrar </button>
         </div>
         <a href="esqueceu-senha.php">Esqueci minha senha.</a>
         <h4 class="mt-5"> Ainda não tem uma conta? Cadastre-se. É de graça! </h4>
         <a class="btn btn-primary btn-lg mt-2" href="cadastro.php">Realizar Cadastro</a>
      </form>
      <div class="row justify-content-center">
      </div>
   </div>
</div>
<?php include('inc/footer.php') ?>

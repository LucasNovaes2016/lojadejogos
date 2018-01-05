<?php

  session_start();
  require('config/config.php');

  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
  $pdo = new PDO ($dsn, DB_USER, DB_PASS);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  $email = "";
  $email_error = "";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["email"])) {
      $email_error = "Digite seu email";
    } else {
      $email = test_input($_POST["email"]);
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Endereço de email inválido";
      } else {
        $query = "SELECT * FROM usuarios WHERE email = ?";
        $stm = $pdo->prepare($query);
        $stm->execute([$email]);
        if ($stm->rowCount()==0)
        {
          $email_error = "O email digitado não está associado a nenhuma conta. ";
        }
      }
    }

    if ($email_error=="")
    {
      $nova_senha = substr(md5(time()), 0, 8);
      $nova_senha_criptografada = PASSWORD_HASH($nova_senha, PASSWORD_BCRYPT, array('cost' => 12));

      $query = "UPDATE usuarios SET pwd = ? WHERE email = ?";
      $stm = $pdo->prepare($query);

      $message = "Ola. Sua nova senha no site da GameStore é: $nova_senha. Recomendamos que você troque esta senha para uma nova para sua maior segurança.";
      $headers = "From: Equipe Game Score";
      if (mail($email, "Nova Senha na GameStore", $message))
      {
        $stm->execute([$nova_senha_criptografada, $email]);
        $_SESSION['successful_password_change'] = true;
        header('Location: change-password-success.php');
      } else {
        echo "Houve um problema";
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

<div class="container mt-5">
     <div class="row justify-content-center mt-2">
     <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="col-md-6">
       <div class="text-center">
       <h4> Informe seu endereço de email. Enviaremos uma nova senha para você. </h4>
     </div>
        <div class="form-group">
           <label for="name"><b>Email: </b></label>
           <input type="text" name="email" value="<?php echo isset($_POST['email']) ? $email : ''; ?>" class="form-control" aria-describedby="emailHelp" placeholder="Seu Email">
           <span class="error"> <?php echo $email_error; ?> </span>
        </div>
        <div class="text-right">
          <a href="index.php" class="btn btn-danger"> Cancelar </a>
           <button type="submit" class="btn btn-success"> Enviar </button>
        </div>
     </form>
   </div>
 </div>

<?php include('inc/footer.php') ?>

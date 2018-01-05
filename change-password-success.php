<?php
  session_start();
  require('config/config.php');

  if (!isset($_SESSION['successful_password_change']))
  {
    header("Location: " . ROOT_URL);
    exit();
  }

  session_destroy();

?>

<?php include('inc/header.php') ?>

<div class="container">
     <div class="success mt-3">
       <h1> Uma nova senha foi enviada para o seu email. Use-a para entrar no site.  </h1>
     </div>
     <a href="index.php">Voltar para a pagina inicial. </a>
</div>

<?php include('inc/footer.php') ?>

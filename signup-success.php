<?php
   session_start();
   require('config/config.php');

   if (!isset($_SESSION['waiting_validation']))
   {
     header("Location: " . ROOT_URL);
     exit();
   }

   session_destroy();

   ?>
<?php include('inc/header.php') ?>
<div class="container">
   <div class="success mt-3">
      <h1> Quase Lá! </h1>
      <h5> Agora entre no seu email e clique no link que enviamos para você para validar a sua conta. </h5>
   </div>
   <a href="index.php">Voltar para a pagina inicial. </a>
</div>
<?php include('inc/footer.php') ?>

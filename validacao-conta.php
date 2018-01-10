<?php

  $message = "";

  if (isset($_GET['codigo']))
  {
    require('config/config.php');
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
    $pdo = new PDO ($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $codigo = $_GET['codigo'];
    $query = "UPDATE usuarios SET ativo = 1 WHERE ativo = ?;";
    $stm = $pdo->prepare($query);
    $stm->execute([$codigo]);

    $rows_affected = $stm->rowCount();

    if ($rows_affected>0)
    {
      $message = "Sua conta foi validada com sucesso.";
    } else {
      header("Location: " . ROOT_URL);
      exit();
    }
  }

 ?>

 <?php include('inc/header.php') ?>
 <div class="container">
    <div class="success mt-3">
       <h3> <?php echo $message ?>  </h3>
       <a href="index.php"> Clique Aqui para entrar no site e fazer o login. </a>
    </div>
 </div>

 <?php include('inc/footer.php') ?>

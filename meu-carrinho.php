<?php

  session_start();
  require('config/config.php');

  if ($_SESSION['admin']) {
    header("Location: " . ROOT_URL);
    exit();
  }

  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
  $pdo = new PDO ($dsn, DB_USER, DB_PASS);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


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
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
              <a class="dropdown-item" href="meu-perfil.php">Meu Perfil</a>
              <a class="dropdown-item" href="inicio.php">Voltar para a Loja</a>
              <?php if (!$_SESSION['admin']) : ?>
                <a class="dropdown-item" href="meu-historico-compras.php">Historico de Compras</a>
              <?php endif; ?>
              <a class="dropdown-item" href="logout.php">Sair</a>
            </div>
          </li>
        </ul>
  </div>
</nav>
</div>

<div class="container mt-2">
  <div class="row">
    <div class="col-sm-12 col-md-8 offset-md-2">
      <div class="text-center">
      <h2> Meu Carrinho: </h2>
    </div>
      <table class="table table-hover">
  <thead>
    <tr>
      <th>#</th>
      <th>Produto</th>
      <th>Preco</th>
      <th>Remoção</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>Produto 1</td>
      <td>49.99</td>
      <td> <button type="button" name="button" class="btn btn-danger">Remover</button> </td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Produto 2</td>
      <td>59.99</td>
      <td> <button type="button" name="button" class="btn btn-danger">Remover</button> </td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Produto 3</td>
      <td>59.99</td>
      <td> <button type="button" name="button" class="btn btn-danger">Remover</button> </td>
    </tr>
  </tbody>
</table>
<button type="button" name="button" class="btn btn-success">Comprar</button>
    </div>
  </div>
</div>

<footer>
  <div class="row mt-3">
    <div class="col-12 text-center">
      <p> &#169; 2017 Lucas Novaes </p>
    </div>
  </div>
</footer>

<?php include('inc/footer.php') ?>

<?php
   session_start();
   require('config/config.php');

   if ($_SESSION['admin'] || !isset($_SESSION['id'])) {
     header("Location: " . ROOT_URL);
     exit();
   }

   $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
   $pdo = new PDO ($dsn, DB_USER, DB_PASS);
   $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

   $query = "SELECT compras.compraID, produtos.nome, produtos.preco, compras.data
   FROM ((Compras
   INNER JOIN usuarios ON compras.usuarioID = usuarios.id)
   INNER JOIN produtos ON compras.produtoID = produtos.produtoID)";

   $stm = $pdo->prepare($query);
   $stm->execute();
   $compras = $stm->fetchAll(PDO::FETCH_ASSOC);

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
                  <a class="dropdown-item" href="meu-carrinho.php">Meu Carrinho</a>
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
         <?php if (isset($_SESSION['compras'])):?>
         <div class="mt-2">
            <h3 class="success"> <?php echo $_SESSION['compras']; unset($_SESSION['compras']);?> </h3>
         </div>
         <?php endif; ?>
         <div class="text-center">
            <h2> Historico de Compras: </h2>
         </div>
         <div class="mt-2">
            <table class="table table-hover">
               <thead>
                  <tr>
                     <th>ID</th>
                     <th>Produto</th>
                     <th>Preco</th>
                     <th>Data da Compra</th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach($compras as $compra) : ?>
                  <tr>
                     <th scope="row"> <?php echo $compra['compraID']; ?> </th>
                     <td> <?php echo $compra['nome'];?> </td>
                     <td> <?php echo $compra['preco'];?> </td>
                     <td> <?php echo $compra['data'];?> </td>
                  </tr>
                  <?php endforeach;?>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>
<?php include('inc/footer.php') ?>

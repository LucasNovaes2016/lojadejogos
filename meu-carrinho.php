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

   $price_to_pay = 0;

    if (isset($_POST['emptycart']))
    {
      $_SESSION['cart'] = array();
      $_SESSION['cart_names'] = array();
      $_SESSION['cart_prices'] = array();
    }

   if (isset($_POST['delete']))
   {
     array_splice($_SESSION['cart'], $_POST['remove_index'], 1);
     array_splice($_SESSION['cart_names'], $_POST['remove_index'], 1);
     array_splice($_SESSION['cart_prices'], $_POST['remove_index'], 1);
   }

   if (isset($_POST['buy']))
   {
     if (!count($_SESSION['cart'])==0)
     {
       for ($var=0; $var<count($_SESSION['cart']); $var++)
       {
         $query = "INSERT INTO compras (produtoID, usuarioID) VALUES (?, ?);";
         $stm = $pdo->prepare($query);
         if ($stm->execute([$_SESSION['cart'][$var], $_SESSION['id']]))
         {
           array_splice($_SESSION['cart'], $var, 1);
           array_splice($_SESSION['cart_names'], $var, 1);
           array_splice($_SESSION['cart_prices'], $var, 1);
           $var -=1;
         }
       }

       $_SESSION['compras'] = "Compras realizadas com sucesso";
       header("Location: meu-historico-compras.php");
     }

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
      <div class="col-sm-12 col-md-8 m-auto">
         <div class="text-center">
            <h2> Meu Carrinho: </h2>
         </div>
         <div class="d-flex justify-content-between">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
               <input type="submit" name="buy" value="Comprar" class="btn btn-success">
            </form>
            <a href="inicio.php" class="btn btn-primary">Comprar <?php if (count($_SESSION['cart_names'])>0) {echo "mais";}?> produtos</a>
         </div>
         <div class="mt-2">
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
                  <?php for ($var=0; $var<count($_SESSION['cart']); $var++) { ?>
                  <tr>
                     <th scope="row"><?php echo ($var+1)?></th>
                     <td><?php echo $_SESSION['cart_names'][$var] ?></td>
                     <td><?php $price_to_pay+=$_SESSION['cart_prices'][$var]; echo $_SESSION['cart_prices'][$var]; ?></td>
                     <td>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                           <input type="hidden" name="remove_index" value="<?php echo $var; ?>">
                           <input type="submit" name="delete" value="Remover" class="btn btn-danger btn-block w-75">
                        </form>
                     </td>
                  </tr>
                  <?php } ?>
                  <!-- Se há pelo menos um produto no carrinho, mostrar o botão de esvaziar o carrinho -->
                  <?php if (count($_SESSION['cart_names'])>0): ?>
                  <tr>
                    <th scope="row"></th>
                    <td></td>
                    <td></td>
                    <td>
                      <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                        <input type="submit" name="emptycart" value="Esvaziar Carrinho" class="btn btn-warning btn-block w-75">
                      </form>
                    </td>
                  </tr>
                <?php endif; ?>
            </table>
            <h4> Total a pagar: <?php echo $price_to_pay; ?> </h4>
         </div>
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

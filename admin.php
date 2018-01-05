<?php
   session_start();
   require('config/config.php');

   if (!isset($_SESSION['id'])) {
     header("Location: " . ROOT_URL);
     exit();
   }

   $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
   $pdo = new PDO ($dsn, DB_USER, DB_PASS);
   $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

   // Vendo qual é o usuario que está logado
   $id = test_input($_SESSION['id']);
   $query = "SELECT * FROM usuarios WHERE id = ?";
   $stm = $pdo->prepare($query);
   $stm->execute([$id]);
   $usuario = $stm->fetch(PDO::FETCH_ASSOC);
   $mensagem = "Seja bem-vindo(a), " . $usuario['nome'];


   $products_per_page = 8; // Total de produtos por pagina. Default = 8
   $current_page = 1; // Pagina atual. Default = 1
   $size = 0; // Total de resultados a serem exibidos. Default = 1
   $current_index = 0; // Index atual. Default = 0
   $number_of_pages = 1; // Numero de paginas da busca. Default = 1

   if (isset($_GET['page']))
   {
     $current_page = test_input($_GET['page']);

     if( ! filter_var($current_page, FILTER_VALIDATE_INT) ){
        $current_page = 1;
      }

   } else {
     $current_page = 1;
   }

   if (isset($_GET['game_name']) && isset($_GET['year']) && isset($_GET['category'])  && isset($_GET['platform'])  && isset($_GET['min_age']) && isset($_GET['price_range']))
   {

     $array_options = array(0 => 'tudo', 1 => 'nome', 2 => 'ano', 3 => 'categoria', 4 => 'idadeMinima', 5 => 'plataforma', 6 => 'preco');
     $array_selected_option = array('checked="checked"','','','','','','');

     $game_name = test_input($_GET['game_name']);
     $year = test_input($_GET['year']);
     $category = test_input($_GET['category']);
     $platform = test_input($_GET['platform']);
     $min_age = test_input($_GET['min_age']);
     $price_range = test_input($_GET['price_range']);

     $query = "SELECT * FROM produtos WHERE nome LIKE ? AND ano LIKE ? AND categoria LIKE ? AND plataforma LIKE ? AND idadeMinima LIKE ? AND PRECO BETWEEN 0 AND ?";

     if ($game_name=="empty")
     {
       $arg1 = "%";
     } else {
       $arg1 = $game_name;
     }

     if ($year=="empty")
     {
       $arg2 = "%";
     } else {
       $arg2 = $year;
     }

     if ($category=="Todas")
     {
       $arg3 = "%";
     } else {
       $arg3 = $category;
     }

     if ($platform=="Todas")
     {
       $arg4 = "%";
     } else {
       $arg4 = $platform;
     }

     if ($min_age=="Todas")
     {
       $arg5 = "%";
     } else {
       $arg5 = $min_age;
     }

     if ($price_range=="Todas")
     {
       $arg6 = "100000";
     } else {
       $pieces = explode(' ', $price_range);
       $last_value = array_pop($pieces);
       $arg6 = $last_value;
     }

     // Total de resultados desta busca
     $stm0 = $pdo->prepare($query);
     $stm0->execute([$arg1, $arg2, $arg3, $arg4, $arg5, $arg6]);
     $size = $stm0->rowCount();

     $number_of_pages = ceil($size/$products_per_page);

     $current_index = ($current_page * $products_per_page) - $products_per_page;

     // Query atual com a limitação
     $query .= " LIMIT $current_index, $products_per_page";

     // Prepare statement
     $stm = $pdo->prepare($query);

     // Execute statement
     $stm->execute([$arg1, $arg2, $arg3, $arg4, $arg5, $arg6]);

     // Get result
     $produtos = $stm->fetchAll(PDO::FETCH_ASSOC);

   } else {

     // Total de produtos da busca toda (somando todas as paginas)
     $size = $pdo->query("SELECT * FROM produtos")->rowCount();

     $number_of_pages = ceil($size/$products_per_page);

     // Calculo do primeiro produto a ser mostrado baseado na pagina atual e no numero de produtos por pagina
     $current_index = ($current_page * $products_per_page) - $products_per_page;

     $array_options = array(0 => 'tudo', 1 => 'nome', 2 => 'ano', 3 => 'categoria', 4 => 'idadeMinima', 5 => 'plataforma', 6 => 'preco');
     $array_selected_option = array('checked="checked"','','','','','','');

     // Create query
     $query = "SELECT * FROM produtos ORDER BY nome LIMIT $current_index, $products_per_page;";

     $return = $pdo->query($query);

     $produtos = $return->fetchAll(PDO::FETCH_ASSOC);

   }

   // Submissão para deletar um jogo
   if (isset($_POST['delete'])) {
     $delete_id = test_input($_POST['delete_id']);

     $query2 = "SELECT * FROM produtos WHERE produtoID = ? ";
     // Prepare statement
     $stm = $pdo->prepare($query2);

     // Execute statement
     $stm->execute([$delete_id]);

     $produto = $stm->fetch(PDO::FETCH_ASSOC);

     $query = "DELETE FROM produtos WHERE produtoID = ? ";

     // Prepare statement
     $stm = $pdo->prepare($query);

     // Execute statement
     $stm->execute([$delete_id]);

     unlink(IMG_DIR.$produto['produtoID'].$produto['imgExtension']);
     header("Location: ".ADMIN_URL);

   }

   // Submissão para procurar  um Jogo

   if (isset($_GET['search']) || (isset($_GET['search_text']) && isset($_GET['option']))) {
     $choice = $_GET['option'];
     $search_words = $_GET["search_text"];
     $search_words = test_input($search_words);

     // Create query
     if ($choice=='tudo')
     {
       $query = "SELECT * FROM produtos WHERE nome LIKE ? OR ano LIKE ? OR categoria LIKE ? OR idadeMinima LIKE ? OR plataforma LIKE ? OR preco LIKE ? OR tags LIKE ? ORDER BY nome;";

       $array_selected_option = array('checked="checked"','','','','','','');
     } else {
       // Verificando opcao selecionada atualmente para deixa-la gravada
       $key = array_search($choice, $array_options);

       for ($i=0; $i<count($array_options); $i++)
       {
         if ($i==$key)
         {
           $array_selected_option[$i] = 'checked="checked"';
         }
       }

       $query = "SELECT * FROM produtos WHERE $choice LIKE ? order by nome;";
     }


     if ($choice=="tudo")
     {
       $stm = $pdo->prepare($query);
       $stm->execute(["%".$search_words."%","%".$search_words."%", "%".$search_words."%", "%".$search_words."%", "%".$search_words."%", "%".$search_words."%", "%".$search_words."%"]);
     } else {
       $stm = $pdo->prepare($query);
       $stm->execute(["%".$search_words."%"]);
     }

     // Numero total de resultado
     $size =  $stm->rowCount();

     $number_of_pages = ceil($size/$products_per_page);

     $current_index = ($current_page * $products_per_page) - $products_per_page;

     $query = substr($query, 0, -1);
     $query .= " LIMIT $current_index, $products_per_page";
     $stm = $pdo->prepare($query);
     if ($choice=="tudo")
     {
       $stm->execute(["%".$search_words."%","%".$search_words."%", "%".$search_words."%", "%".$search_words."%", "%".$search_words."%", "%".$search_words."%", "%".$search_words."%"]);
     } else {
       $stm->execute(["%".$search_words."%"]);
     }

     $produtos = $stm->fetchAll(PDO::FETCH_ASSOC);

   }

   function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data; }

   ?>
<?php include('inc/header.php') ?>
<div class="container mt-5">
  <div class="row mt-2">
    <div class="col-12 text-right">
        <p> <b> <?php echo $mensagem?> </b> </p>
       <a class="btn btn-danger" href="logout.php">Sair</a>
    </div>
  </div>
   <div class="row mt-3">
     <div class="col-12 text-center">
        <h1> Lista de Jogos </h1>
     </div>
   </div>
   <div class="row">
      <div class="col-12">
         <a href="adicionar-jogo.php" class="btn btn-success"><i class="fa fa-plus fa-lg" aria-hidden="true"></i><b class="ml-1"> Novo Jogo</b></button></a>
      </div>
   </div>
   <hr class="increased-hr">

   <div class="row">
     <div class="col-md-12 col-lg-9">
  <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
 <div class="form-inline">
    <input class="form-control mt-2" type="text" name="search_text" placeholder="Procurar Jogo">
    <button class="btn btn-primary ml-2 mt-2" type="submit" name="search"><i class="fa fa-search" aria-hidden="true"></i>
    <b> Buscar </b> </button>
 </div>
 <div class="form-group mt-2">
   <label for ="sex"> <b> Pesquisar: </b> </label>
           <div class="form-check form-check-inline ml-2">
              <label class="form-check-label">
              <input class="form-check-input" type="radio" name="option" value="tudo" <?php echo $array_selected_option[0];?>> Tudo
              </label>
           </div>
           <div class="form-check form-check-inline">
              <label class="form-check-label">
              <input class="form-check-input" type="radio" name="option" value="nome" <?php echo $array_selected_option[1];?>> Nome
              </label>
           </div>
           <div class="form-check form-check-inline">
              <label class="form-check-label">
              <input class="form-check-input" type="radio" name="option" value="ano" <?php echo $array_selected_option[2];?>> Ano
              </label>
           </div>
           <div class="form-check form-check-inline">
              <label class="form-check-label">
              <input class="form-check-input" type="radio" name="option" value="categoria" <?php echo $array_selected_option[3];?>> Categoria
              </label>
           </div>
           <div class="form-check form-check-inline">
              <label class="form-check-label">
              <input class="form-check-input" type="radio" name="option" value="idadeMinima" <?php echo $array_selected_option[4];?>> Idade Minima
              </label>
           </div>
           <div class="form-check form-check-inline">
              <label class="form-check-label">
              <input class="form-check-input" type="radio" name="option" value="plataforma" <?php echo $array_selected_option[5];?>> Plataforma
              </label>
           </div>
           <div class="form-check form-check-inline">
              <label class="form-check-label">
              <input class="form-check-input" type="radio" name="option" value="preco" <?php echo $array_selected_option[6];?>> Preco
              </label>
           </div>
        </div>
</form>
</div>
<div class="col-md-12 col-lg-3 mt-1">
  <a class="btn btn-primary pull-right" href="busca-personalizada.php"> <b>Busca Personalizada</b> </a>
</div>
</div>

   <div class="row mt-2">
     <?php if ($size==0) { ?>
       <div class="col-12 ml-1">
       <p> <b> Nenhum resultado foi encontrado para a sua busca :( </b> </p>
       <a href="<?php echo ADMIN_URL; ?>"> Voltar para a página principal </a>
     </div>
   <?php } else { ?>
     <div class="col-12 ml-1">
       <div class="d-flex justify-content-between">
         <div>
           <p> <b> <?php echo "Mostrando " . ($current_index+1) . "-" . ($current_index+count($produtos)) . " de $size resultados encontrados "; ?> </b> </p>
         </div>
         <div>
           <nav aria-label="Page navigation example">
              <ul class="pagination">
                <?php

                  $url = "";
                  $url  =  (htmlspecialchars($_SERVER["PHP_SELF"])) . "?";
                  if (isset($_GET['game_name']) && isset($_GET['year']) && isset($_GET['category'])  && isset($_GET['platform'])  && isset($_GET['min_age']) && isset($_GET['price_range']))
                  {
                    $acumulator = "";
                    $acumulator .= "game_name=" . $_GET['game_name'] . "&year=" .  $_GET['year'] . "&category=" . $_GET['category'] . "&platform=" . $_GET['platform'] . "&min_age=" . $_GET['min_age'] . "&price_range=" . $_GET['price_range'];
                    $url .= "$acumulator";
                  } else if (isset($_GET['search_text']) && isset($_GET['option']))
                  {
                    $acumulator = "";
                    $acumulator .= "search_text=" . $_GET['search_text'] . "&option=" . $_GET['option'];
                    $url .= "$acumulator";
                  }

                $start_page = 1;
                $end_page = 1;
                $nexturl = "";

                if ($current_page==1)
                {
                  $start_page = 1;
                } else if ($current_page==2)
                {
                  $start_page = 1;
                } else if ($current_page==3)
                {
                  $start_page = 1;
                } else if ($current_page>3)
                {
                  $page = $current_page - 1;
                  $urlanterior = $url . "&page=" . "$page";
                  echo '<li class="page-item"><a class="page-link" href="' . $urlanterior . '">Anterior</a></li>';
                  $start_page = $current_page - 2;
                }

                if ($current_page==$number_of_pages)
                {
                  $end_page = $current_page;
                } else if ($current_page==$number_of_pages-1)
                {
                  $end_page = $current_page + 1;
                } else if ($current_page==$number_of_pages-2)
                {
                  $end_page = $current_page + 2;
                } else if ($current_page<$number_of_pages-2)
                {
                  $page = $current_page + 1;
                  $urlposterior = $url . "&page=" . "$page";
                  $nexturl = '<li class="page-item"><a class="page-link" href="' . $urlposterior . '">Próxima</a></li>';
                  $end_page = $current_page + 2;
                }

                for ($var=$start_page; $var<=$end_page; $var++) {?>
                  <li class="page-item <?php if ($var==$current_page) {echo "active";}
                  ?>"><a class="page-link active" href="<?php
                  $finalurl = $url . "&page=$var";
                  echo $finalurl;
                  ?>"><?php echo $var ?></a></li>
                <?php }
                  echo $nexturl;
                ?>
              </ul>
            </nav>
         </div>
       </div>
   </div>
   <?php } ?>
      <?php foreach($produtos as $produto): ?>
        <div class="col-sm-6 col-md-4 col-lg-3 mt-2">
         <div class="col-12 bordered">
            <img class="img-fluid img-center" src="<?php echo IMG_DIR.$produto['produtoID'].$produto['imgExtension']?>" alt="Card image cap">
            <h4 class="mt-2"> <?php echo $produto['nome']; ?> </h4>
            <p> <b> Ano:</b> <?php echo $produto['ano']; ?> </p>
            <p> <b> Categoria:</b> <?php echo $produto['categoria']; ?> </p>
            <p> <b> Idade Minima:</b> <?php echo $produto['idadeMinima']; ?> </p>
            <p> <b> Plataforma:</b> <?php echo $produto['plataforma']; ?> </p>
            <p> <b> Preço:</b> <?php echo $produto['preco']; ?> </p>
            <div class="d-flex justify-content-between">
               <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                  <input type="hidden" name="delete_id" value="<?php echo $produto['produtoID']; ?>">
                  <input type="submit" name="delete" value="Excluir" onclick="return ConfirmDelete()" class="btn btn-danger">
               </form>
               <a class="btn btn-primary" href="editar-jogo.php?id=<?php echo $produto['produtoID']; ?>">Editar</a>
            </div>
         </div>
       </div>
      <?php endforeach; ?>
   </div>
</div>
<?php include('inc/footer.php') ?>

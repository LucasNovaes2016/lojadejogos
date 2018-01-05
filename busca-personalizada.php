<?php
  session_start();
  require('config/config.php');

  if (!isset($_SESSION['id'])) {
    header("Location: " . ROOT_URL);
    exit();
  }

   $name_error = $year_error = $category_error = $platform_error = $min_age_error = $price_range_error = "";
   $game_name = $year = $category = $platform = $min_age = $price_range = "";
   $array_categories = array(0 => 'Todas', 1 => 'Acao', 2 => 'Aventura', 3 => 'Corrida', 4 => 'Esportes', 5 => 'Tiro', 6 => 'Mundo Aberto', 7 => 'Infantil', 8 => 'RPG', 9 => 'Luta', 10 => 'Outro');
   $array_selected_category = array('','','','','','','','','','','');
   $array_plataforms = array(0 => 'Todas', 1 => 'Playstation 2', 2 => 'Playstation 3', 3 => 'Playstation 4', 4 => 'Xbox', 5 => 'Xbox 360', 6 => 'Xbox One', 7 => 'Game Cube', 8 => 'Nintendo Wii', 9 => 'Nintendo Wii U', 10 => 'Nintendo Switch');
   $array_selected_platform = array('','','','','','','','','','');
   $array_ages = array(0 => 'Todas', 1 => 'Livre', 2 => '6', 3 => '8', 4 => '10', 5 => '12', 6 => '13', 7 => '14', 8 => '16', 9 => '18');
   $array_selected_age = array('','','','','','','','','');
   $array_price_ranges = array(0 => 'Todas',1 => 'Até R$ 50',2 => 'Maior que R$ 50 e menor ou igual a R$ 100', 3 => 'Maior que R$ 100');
   $array_selected_price_range = array('','','','');

   if ($_SERVER["REQUEST_METHOD"] == "POST") { // Quando a pessoa clica em "submit"

     // Fazendo tratamento do nome
       $game_name = test_input($_POST["name"]);
       // check if name only contains letters and whitespace
       if (preg_match('/[^A-Za-z0-9]/', preg_replace('/\s+/', '', $game_name)))
       {
         $name_error = "O nome dos jogos só contem números e letras";
       }

     // Fazendo tratamento do ano de Lançamento
      $year = test_input($_POST["year"]);

      $year = (int)$year;

      if (!empty($year))
      {
        if(!($year>1900 && $year<2100))
        {
          $year_error = "Digite um ano válido com 4 dígitos (exemplos: 1998, 2004, 2011 etc...)";
        }
      }

     // Fazendo tratamento da Categoria
     if(isset($_POST['categoria'])){
       $category = test_input($_POST["categoria"]);
       $key = array_search($category, $array_categories);

       for ($i=0; $i<count($array_selected_category); $i++)
       {
         if ($i==$key)
         {
           $array_selected_category[$i] = "selected";
         }
       }

     } else {
       $category_error = "Escolha a categoria em que o jogo se encaixa. ";
     }

     // Fazendo tratamento da plataforma
     if(isset($_POST['plataforma'])){
       $platform = test_input($_POST["plataforma"]);
       $key = array_search($platform, $array_plataforms);

       for ($i=0; $i<count($array_selected_platform); $i++)
       {
         if ($i==$key)
         {
           $array_selected_platform[$i] = "selected";
         }
       }
     } else {
       $platform_error = "Selecione a plataforma (videogame) do jogo. ";
     }

     // Fazendo tratamento da idade minima
     if(isset($_POST['idade_minima'])){
       $min_age = test_input($_POST["idade_minima"]);
       $key = array_search($min_age, $array_ages);

       for ($i=0; $i<count($array_selected_age); $i++)
       {
         if ($i==$key)
         {
           $array_selected_age[$i] = "selected";
         }
       }
     } else {
       $min_age_error = "Selecione a idade minima do jogo. ";
     }

     // Fazendo tratamento da faixa de preço
     if(isset($_POST['faixa_preco'])){
       $price_range = test_input($_POST["faixa_preco"]);
       $key = array_search($price_range, $array_price_ranges);

       for ($i=0; $i<count($array_selected_price_range); $i++)
       {
         if ($i==$key)
         {
           $array_selected_price_range[$i] = "selected";
         }
       }
     } else {
       $price_range_error = "Selecione a faixa de preço do jogo. ";
     }

     if ($name_error == "" and $year_error == "" and $category_error == "" and $platform_error == "" and $min_age_error == "" and $price_range_error == "")
     {
       if (empty($game_name))
       {
         $game_name = "empty";
       }

       if (empty($year))
       {
         $year = "empty";
       }
       // Redirecionando para a pagina inicial com os argumentos da busca personalizada
       header("Location: ".ADMIN_URL."?game_name=$game_name&year=$year&category=$category&platform=$platform&min_age=$min_age&price_range=$price_range");
     }

   }

   function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data; }

   function isCurrency($number)
   {
   return preg_match("/^-?[0-9]+(?:\.[0-9]{1,2})?$/", $number);
   }

   ?>
<?php include('inc/header.php') ?>
<div class="container mt-5">
   <div class="row">
      <div class="col-12 text-center">
         <h1> Faça sua busca personalizada: </h1>
      </div>
   </div>
   <div class="row mt-2">
      <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3 bordered">
         <p class="text-center"> <b> Preencha todos os campos que desejar. Se não preencher um ou mais campos, a pesquisa irá considerar qualquer resultado na categoria do(s) campo(s) deixados em branco. </b> </p>
         <form enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
            <div class="form-group">
               <label for="name"><b> Nome: </b></label>
               <input type="text" name="name" class="form-control" aria-describedby="name" placeholder="Nome do Jogo" value="<?php echo isset($_POST['name']) ? $game_name : ''; ?>">
               <span class="error"> <?php echo $name_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="name"><b> Ano de Lançamento: </b></label>
               <input type="text" name="year" class="form-control" aria-describedby="year" placeholder="Ano de Lançamento" maxlength="4"  value="<?php echo isset($_POST['year']) ? $year : ''; ?>">
               <span class="error"> <?php echo $year_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="exampleSelect1"> <b> Categoria: </b> </label>
               <select name="categoria" class="form-control" id="exampleSelect1">
                  <option <?php echo $array_selected_category[0];?>> Todas </option>
                  <option <?php echo $array_selected_category[1];?>> Acao </option>
                  <option <?php echo $array_selected_category[2];?>> Aventura </option>
                  <option <?php echo $array_selected_category[3];?>> Corrida </option>
                  <option <?php echo $array_selected_category[4];?>> Esportes </option>
                  <option <?php echo $array_selected_category[5];?>> Tiro </option>
                  <option <?php echo $array_selected_category[6];?>> Mundo Aberto </option>
                  <option <?php echo $array_selected_category[7];?>> Infantil </option>
                  <option <?php echo $array_selected_category[8];?>> RPG </option>
                  <option <?php echo $array_selected_category[9];?>> Luta </option>
                  <option <?php echo $array_selected_category[10];?>> Outro </option>
               </select>
               <span class="error"> <?php echo $category_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="exampleSelect1"> <b> Plataforma: </b> </label>
               <select name="plataforma" class="form-control" id="exampleSelect1">
                 <option <?php echo $array_selected_platform[0];?>> Todas </option>
                  <option <?php echo $array_selected_platform[1];?>> Playstation 2 </option>
                  <option <?php echo $array_selected_platform[2];?>> Playstation 3 </option>
                  <option <?php echo $array_selected_platform[3];?> > Playstation 4 </option>
                  <option <?php echo $array_selected_platform[4];?> > Xbox </option>
                  <option <?php echo $array_selected_platform[5];?> > Xbox 360 </option>
                  <option <?php echo $array_selected_platform[6];?> > Xbox One </option>
                  <option <?php echo $array_selected_platform[7];?> > Game Cube </option>
                  <option <?php echo $array_selected_platform[8];?> > Nintendo Wii </option>
                  <option <?php echo $array_selected_platform[9];?> > Nintendo Wii U</option>
                  <option <?php echo $array_selected_platform[10];?> > Nintendo Switch </option>
               </select>
               <span class="error"> <?php echo $platform_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="exampleSelect1"> <b> Idade Minima: </b> </label>
               <select name = "idade_minima" class="form-control" id="exampleSelect1">
                 <option <?php echo $array_selected_age[0];?>> Todas </option>
                  <option <?php echo $array_selected_age[1];?>> Livre </option>
                  <option <?php echo $array_selected_age[2];?>> 6 </option>
                  <option <?php echo $array_selected_age[3];?>> 8 </option>
                  <option <?php echo $array_selected_age[4];?>> 10 </option>
                  <option <?php echo $array_selected_age[5];?>> 12 </option>
                  <option <?php echo $array_selected_age[6];?>> 13 </option>
                  <option <?php echo $array_selected_age[7];?>> 14 </option>
                  <option <?php echo $array_selected_age[8];?>> 16 </option>
                  <option <?php echo $array_selected_age[9];?>> 18 </option>
               </select>
               <span class="error"> <?php echo $min_age_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="exampleSelect1"> <b> Faixa de Preco: </b> </label>
               <select name = "faixa_preco" class="form-control" id="exampleSelect1">
                 <option <?php echo $array_selected_price_range[0];?>> Todas </option>
                 <option <?php echo $array_selected_price_range[1];?>> Até R$ 50 </option>
                  <option <?php echo $array_selected_price_range[2];?>> Até R$ 100</option>
                  <option <?php echo $array_selected_price_range[3];?>> Até R$ 200</option>
               </select>
               <span class="error"> <?php echo $min_age_error; ?> </span>
            </div>
            <div class="text-right">
            <a href="<?php echo ADMIN_URL;?>" class="btn btn-danger">Cancelar</a>
            <input class="btn btn-success" type="submit" value="Pesquisar">
          </div>
         </form>
      </div>
   </div>
</div>
<?php include('inc/footer.php') ?>

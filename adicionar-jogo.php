<?php
   session_start();
   require('config/config.php');

   if (!isset($_SESSION['admin'])) {
     header("Location: " . ROOT_URL);
     exit();
   } else if (!$_SESSION['admin'])
   {
     header("Location: " . INICIO_URL);
     exit();
   }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
    $pdo = new PDO ($dsn, DB_USER, DB_PASS);

    $name_error = $year_error = $category_error = $platform_error = $min_age_error = $price_error = $image_error = "";
    $game_name = $year = $category = $platform = $min_age = $price = $extension = "";
    $array_categories = array(0 => 'Acao', 1 => 'Aventura', 2 => 'Corrida', 3 => 'Esportes', 4 => 'Tiro', 5 => 'Mundo Aberto', 6 => 'Infantil', 7 => 'RPG', 8 => 'Luta', 9 => 'Outro');
    $array_selected_category = array('','','','','','','','','','');
    $array_plataforms = array(0 => 'Playstation 2', 1 => 'Playstation 3', 2 => 'Playstation 4', 3 => 'Xbox', 4 => 'Xbox 360', 5 => 'Xbox One', 6 => 'Game Cube', 7 => 'Nintendo Wii', 8 => 'Nintendo Wii U', 9 => 'Nintendo Switch');
    $array_selected_platform = array('','','','','','','','','','');
    $array_ages = array(0 => 'Livre', 1 => '6', 2 => '8', 3 => '10', 4 => '12', 5 => '13', 6 => '14', 7 => '16', 8 => '18');
    $array_selected_age = array('','','','','','','','','');

    if ($_SERVER["REQUEST_METHOD"] == "POST") { // Quando a pessoa clica em "submit"

      // Fazendo tratamento do nome

      if (empty($_POST["name"])) {
        $name_error = "Digite seu nome";
      } else {
        $game_name = test_input($_POST["name"]);
        // check if name only contains letters and whitespace
        if (preg_match('/[^A-Za-z0-9]/', preg_replace('/\s+/', '', $game_name)))
        {
          $name_error = "O nome do jogo só deve conter letras e numeros";
        }
      }

      // Fazendo tratamento do ano de Lançamento

      if (empty($_POST["year"])) {
        $year_error = "Digite o ano de lançamento do jogo";
      } else {
        $year = test_input($_POST["year"]);

        $year = (int)$year;

        if(!($year>1900 && $year<2100))
        {
          $year_error = "Digite um ano válido com 4 dígitos: exemplos: 1998, 2004, 2011 etc...";
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

      // Fazendo tratamento do preço

      if (empty($_POST["price"])) {
        $price_error = "Digite o preço do jogo em Reais. ";
      } else {
        $price = test_input($_POST["price"]);

        if (!isCurrency($price))
        {
          $price_error = "Digite um valor de preço valido. Exemplos: 49.99, 99.99, 109.99";
        }
      }

      // Tratamento da imagem:
      if (!empty($_FILES['file']['name']) and $_FILES['file']['error'] == UPLOAD_ERR_OK) { // Verifica se a pessoa submeteu algum arquivo e se o submetimento foi bem sucedido.

          $name = $_FILES['file']['name']; // Nome do arquivo + extensão

          // Pegando a extensão da imagem submetida...
          $extension =  '.'.pathinfo($name)['extension'];

          if ( strstr ( '.jpg;.jpeg;.gif;.png', $extension ) ) // Verificando se o arquivo é uma imagem mesmo
          {
            // Testar se não houve

            if ($name_error == "" and $year_error == "" and $category_error == "" and $platform_error == "" and $min_age_error == "" and $price_error == "")
            {
              // Inserir arquivo no banco de dados e imagem no diretorio
              $img_name = "ERRO";

              $query = "INSERT INTO produtos(nome, ano, categoria, idadeMinima, plataforma, preco, imgExtension) VALUES(?, ?, ?, ?, ?, ?, ?)";

              $stm = $pdo->prepare($query);

              $stm->execute([$game_name, $year, $category, $min_age, $platform, $price, $extension]);
              $img_name = $pdo->lastInsertId();

              // Inserir a imagem na pasta de imagens

              $tmp_name = $_FILES['file']['tmp_name']; // Nome anterior da imagem...

              $error = $_FILES['file']['error'];
              if ($error !== UPLOAD_ERR_OK) {
                  $image_error = 'Erro ao fazer o upload: '.$error;
              } else if (move_uploaded_file($tmp_name, IMG_DIR . $img_name . $extension)) {
                  header("Location: ". INICIO_URL);
              }
            }

          } else {
            $image_error = "ERRO: O arquivo enviado não é uma imagem (somente arquivos .jpg, .jpeg, .png e .gif são aceitos)";
          }
      } else {
        $image_error = "Selecione um arquivo para fazer upload";
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
         <h1> Adicione um novo Jogo: </h1>
      </div>
   </div>
   <div class="row mt-2">
      <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3 bordered">
         <p class="text-center"> <b> Preencha todos os campos abaixo para adicionar um novo jogo: </b> </p>
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
                  <option <?php echo $array_selected_category[0];?>> Acao </option>
                  <option <?php echo $array_selected_category[1];?>> Aventura </option>
                  <option <?php echo $array_selected_category[2];?>> Corrida </option>
                  <option <?php echo $array_selected_category[3];?>> Esportes </option>
                  <option <?php echo $array_selected_category[4];?>> Tiro </option>
                  <option <?php echo $array_selected_category[5];?>> Mundo Aberto </option>
                  <option <?php echo $array_selected_category[6];?>> Infantil </option>
                  <option <?php echo $array_selected_category[7];?>> RPG </option>
                  <option <?php echo $array_selected_category[8];?>> Luta </option>
                  <option <?php echo $array_selected_category[9];?>> Outro </option>
               </select>
               <span class="error"> <?php echo $category_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="exampleSelect1"> <b> Plataforma: </b> </label>
               <select name="plataforma" class="form-control" id="exampleSelect1">
                  <option <?php echo $array_selected_platform[0];?>> Playstation 2 </option>
                  <option <?php echo $array_selected_platform[1];?>> Playstation 3 </option>
                  <option <?php echo $array_selected_platform[2];?> > Playstation 4 </option>
                  <option <?php echo $array_selected_platform[3];?> > Xbox </option>
                  <option <?php echo $array_selected_platform[4];?> > Xbox 360 </option>
                  <option <?php echo $array_selected_platform[5];?> > Xbox One </option>
                  <option <?php echo $array_selected_platform[6];?> > Game Cube </option>
                  <option <?php echo $array_selected_platform[7];?> > Nintendo Wii </option>
                  <option <?php echo $array_selected_platform[8];?> > Nintendo Wii U</option>
                  <option <?php echo $array_selected_platform[9];?> > Nintendo Switch </option>
               </select>
               <span class="error"> <?php echo $platform_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="exampleSelect1"> <b> Idade Minima: </b> </label>
               <select name = "idade_minima" class="form-control" id="exampleSelect1">
                  <option <?php echo $array_selected_age[0];?>> Livre </option>
                  <option <?php echo $array_selected_age[1];?>> 6 </option>
                  <option <?php echo $array_selected_age[2];?>> 8 </option>
                  <option <?php echo $array_selected_age[3];?>> 10 </option>
                  <option <?php echo $array_selected_age[4];?>> 12 </option>
                  <option <?php echo $array_selected_age[5];?>> 13 </option>
                  <option <?php echo $array_selected_age[6];?>> 14 </option>
                  <option <?php echo $array_selected_age[7];?>> 16 </option>
                  <option <?php echo $array_selected_age[8];?>> 18 </option>
               </select>
               <span class="error"> <?php echo $min_age_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="valor"> <b> Preço: </b> </label>
               <div class="input-group">
                  <span class="input-group-addon">R$</span>
                  <input type="text" name="price" class="form-control" placeholder="00.00" maxlength="6" value="<?php echo $price;?>">
               </div>
               <span class="error"> <?php echo $price_error; ?> </span>
            </div>
            <div class="form-group">
               <label for="imagem"><b> Selecione uma imagem para o jogo: </b></label>
               <input type="file" name="file" required> <br> <br>
               <div class="text-right">
                  <a href="<?php echo INICIO_URL;?>" class="btn btn-danger">Cancelar</a>
                  <input class="btn btn-success" type="submit" value="Adicionar Jogo">
               </div>
               <span class="error"> <?php echo $image_error; ?> </span>
            </div>
         </form>
      </div>
   </div>
</div>
<?php include('inc/footer.php') ?>

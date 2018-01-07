<?php
session_start();
require('config/config.php');

  if (!isset($_SESSION['id']))
  {
    header("Location: " . ROOT_URL);
    exit();
  }

  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
  $pdo = new PDO ($dsn, DB_USER, DB_PASS);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  $change_message = "";
  $name_error = $username_error = $email_error = $birthday_error = "";
  $sexo = "";
  $name = $_SESSION['nome'];
  $username = $_SESSION['username'];
  $email = $_SESSION['email'];
  $birthday = $_SESSION['nascimento'];

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validação do "novo" nome...
    if (empty($_POST["name"])) {
      $name_error = "Digite seu nome";
    } else {
      $name = test_input($_POST["name"]);
      // check if name only contains letters and whitespace
      if (strlen($name) > 256)
      {
        $name_error = "O nome deve ter ate no maximo 256 caracteres.";
      } else if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
        $name_error = "Apenas letras e espaços em branco são aceitos";
      }
    }

    // Validação do "novo" username...

    if (empty($_POST["username"])) {
      $username_error = "Digite sua username";
    } else {

      $username = test_input($_POST["username"]);

      // check if name only contains letters and whitespace
      if (strlen($username) < 8 || strlen($username) > 16)
      {
        $username_error = "A username deve conter entre 8 a 16 caracteres";
      } else if (preg_match('/\s/',$username) )
      {
        $username_error = "A username não pode conter espaços em branco";
      } else if (!preg_match("/^[A-Za-z0-9_]+$/", $username))
      {
        $username_error = "A username pode conter apenas letras, numeros e underlines.";
      }

      $number_of_users = 0;

      if ($username!=$_SESSION['username'])
      {
        // Verificando se ja existe um usuario com esta username
        $query = "SELECT * FROM usuarios WHERE username = ?";
        $stm = $pdo->prepare($query);
        $stm->execute([$username]);
        $number_of_users = $stm->rowCount();
      }

      if ($number_of_users>0)
      {
          $username_error = "Esta username já está sendo usado por outra conta. ";
      }

    }

    // Verifying birthday
    if (empty($_POST["birthday"])) {
      $birthday_error = "Digite sua data de nascimento";
    } else {
      $birthday = $_POST["birthday"];
    }

    // Verifying sex
    $sex = test_input($_POST["sex"]);

    if ($sex=="feminino")
    {
      $sex = "F";
    } else {
      $sex = "M";
    }

    // verifying email...
    if (empty($_POST["email"])) {
      $email_error = "Digite um endereço de email";
    } else {
      $email = test_input($_POST["email"]);
      // check if e-mail address is well-formed

      if (strlen($email) > 256)
      {
        $email_error = "O endereço de email deve conter até 256 caracteres";
      } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Endereço de email invalido";
      } else {
        // Se o endereço de email for valido
        $number_of_users = 0;

        if ($email!=$_SESSION['email'])
        {
          // Verificando se ja existe um usuario com esta username
          $query = "SELECT * FROM usuarios WHERE email = ?";
          $stm = $pdo->prepare($query);
          $stm->execute([$email]);
          $number_of_users = $stm->rowCount();
        }

        if ($number_of_users>0)
        {
            $email_error = "Este email já está sendo vinculado a outra conta. ";
        }

      }

    }

    if ($name_error == "" and $username_error == "" and $email_error == "" and $birthday_error == "")
    {
      $query = "UPDATE usuarios SET
      nome = ?,
      sexo = ?,
      nascimento = ?,
      username = ?,
      email = ?
      WHERE id = ?";
      $stm = $pdo->prepare($query);
      if($stm->execute([$name, $sex, $birthday, $username, $email, $_SESSION['id']]))
      {
        $_SESSION['nome'] = $name;
        $_SESSION['sexo'] = $sex;
        $_SESSION['nascimento'] = $birthday;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $change_message = "Alterações feitas com sucesso";
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
            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
              <a class="dropdown-item" href="inicio.php">Voltar para a Loja</a>
              <?php if (!$_SESSION['admin']) : ?>
                <a class="dropdown-item" href="meu-carrinho.php">Meu Carrinho</a>
                <a class="dropdown-item" href="meu-historico-compras.php">Historico de Compras</a>
              <?php endif; ?>
              <a class="dropdown-item" href="logout.php">Sair</a>
            </div>
          </li>
        </ul>
  </div>
</nav>
</div>

<div class="container mt-5">
  <div class="row">
     <div class="col-12 text-center succcess">
       <div class="success">
        <h3> <?php echo $change_message; ?> </h3>
      </div>
     </div>
  </div>
  <div class="row mt-2">
     <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3 bordered">
       <div class="text-center success">
         <h3> <?php if (isset($_SESSION['mudou_senha'])) { echo $_SESSION['mudou_senha']; unset($_SESSION['mudou_senha']);} ?> </h3>
       </div>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
          <div class="text-right">
            <a href="change-password.php">Quero mudar minha senha</a>
          </div>
          <div class="form-group">
             <label for="name"><b>Nome Completo: </b></label>
             <input type="text" name="name" value="<?php echo $name; ?>" class="form-control" aria-describedby="emailHelp" placeholder="Seu Nome">
             <span class="error"> <?php echo $name_error; ?> </span>
          </div>
          <div class="form-group">
             <label for="name"><b>Username: </b></label>
             <input type="text" name="username" value="<?php echo $username; ?>" class="form-control" aria-describedby="emailHelp" placeholder="Sua username">
             <span class="error"> <?php echo $username_error; ?> </span>
          </div>
          <div class="form-group">
             <label for="birthday"> <b> Data de nascimento: </b> </label>
             <input type="date" name="birthday" value="<?php echo $birthday; ?>" class="form-control">
             <span class="error"> <?php echo $birthday_error; ?> </span>
          </div>
          <div class="form-group">
             <label for="email"> <b> Email: </b> </label>
             <input type="text" name="email" value="<?php echo $email; ?>" class="form-control" placeholder="Seu Email">
             <span class="error"> <?php echo $email_error; ?> </span>
          </div>
          <div class="form-group">
             <label for ="sex"> <b> Sexo: </b> </label>
             <br>
             <div class="form-check form-check-inline">
                <label class="form-check-label">
                <input class="form-check-input" type="radio" name="sex" value="masculino" <?php if ($_SESSION['sexo']=='M') {echo 'checked="checked"';} ?>> Masculino
                </label>
             </div>
             <div class="form-check form-check-inline">
                <label class="form-check-label">
                <input class="form-check-input" type="radio" name="sex"value="feminino" <?php if ($_SESSION['sexo']=='F') {echo 'checked="checked"';} ?>> Feminino
                </label>
             </div>
          </div>
          <div class="text-right">
             <a href="<?php echo INICIO_URL;?>" class="btn btn-primary">Cancelar</a>
             <input class="btn btn-success" type="submit" value="Aplicar Alterações">
          </div>
        </form>
     </div>
  </div>
</div>

<?php include('inc/footer.php') ?>

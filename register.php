<?php

session_start();
require('config/config.php');
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';';
$pdo = new PDO ($dsn, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


// define variables and set to empty values
$name_error = $username_error = $sexo = $email_error = $password_error = $password_mismatch_error = $birthday_error = "";
$name =  $username = $email = $password = $password_confirm = $birthday = $c1 = $c2 = $c3 = $c4 = $c5 = "";

//form is submitted with POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Verifying name...

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

  // Verifying username...

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

    // Verificando se ja existe um usuario com esta username
    $query = "SELECT * FROM usuarios WHERE username = ?";
    $stm = $pdo->prepare($query);
    $stm->execute([$username]);
    $number_of_users = $stm->rowCount();

    if ($number_of_users>0)
    {
        $username_error = "Ja existe um usuario com esta username.";
    }

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
    }
  }

  // verifying password

  if (empty($_POST["password"])) {
    $password_error = "Digite uma senha";
  } else {
    $password = $_POST["password"];

    if (strlen($password)<8 or strlen($password)>14)
    {
      $password_error = "A senha deve conter entre 8 a 14 caracteres";
    } else if (preg_match('/\s/',$password))
    {
      $password_error = "A senha não pode conter espaços.";
    }
  }

  // Verifying password confirmation

  if (empty($_POST["confirmPassword"])) {
    $password_mismatch_error = "Confirme sua senha";
  } else {
    $password_confirm = $_POST["confirmPassword"];

    if ($_POST["confirmPassword"] !== $_POST["password"])
    {
      $password_mismatch_error = "A senha não corresponde a senha digitada anteriormente";
    }
  }

  // Verifying birthday

  if (empty($_POST["birthday"])) {
    $birthday_error = "Digite sua data de nascimento";
  } else {
    $birthday = $_POST["birthday"];
  }

  // Verificando o sexo
  $sex = test_input($_POST["sex"]);

  if ($sex=="feminino")
  {
    $sex = "F";
  } else {
    $sex = "M";
  }

  if ($name_error == "" and $username_error == "" and $email_error == "" and $password_error == "" and $password_mismatch_error == "" and $birthday_error == "")
  {

    $password = PASSWORD_HASH($password, PASSWORD_BCRYPT, array('cost' => 12));
    $query = "INSERT INTO usuarios (nome, sexo, nascimento, username, email, pwd) VALUES (?, ?, ?, ?, ?, ?)";
    $stm = $pdo->prepare($query);
    $stm->execute([$name, $sex, $birthday, $username, $email, $password]);
    $_SESSION['successful_login'] = true;
    header('Location: signup-success.php');
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

<div class="container mt-3">
    <div class="text-center">
      <h1> Inscreva-se no site </h1>
    </div>
     <div class="row justify-content-center">
     <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="col-md-6">
        <div class="form-group">
           <label for="name"><b>Nome Completo: </b></label>
           <input type="text" name="name" value="<?php echo isset($_POST['name']) ? $name : ''; ?>" class="form-control" aria-describedby="emailHelp" placeholder="Seu Nome">
           <span class="error"> <?php echo $name_error; ?> </span>
        </div>
        <div class="form-group">
           <label for="name"><b>Username: </b></label>
           <input type="text" name="username" value="<?php echo isset($_POST['username']) ? $username : ''; ?>" class="form-control" aria-describedby="emailHelp" placeholder="Sua username">
           <span class="error"> <?php echo $username_error; ?> </span>
        </div>
        <div class="form-group">
           <label for="email"> <b> Email: </b> </label>
           <input type="text" name="email" value="<?php echo isset($_POST['email']) ? $email : ''; ?>" class="form-control" placeholder="Seu Email">
           <span class="error"> <?php echo $email_error; ?> </span>
        </div>
        <div class="form-group">
           <label for="password"> <b> Senha: </b> </label>
           <input type="password" name="password" value="<?php echo isset($_POST['password']) ? $password : ''; ?>" class="form-control" placeholder="Digite sua senha">
           <span class="error"> <?php echo $password_error; ?> </span>
        </div>
        <div class="form-group">
           <label for="password"> <b> Digite sua senha novamente: </b> </label>
           <input type="password" name="confirmPassword" value="<?php echo isset($_POST['confirmPassword']) ? $password_confirm : ''; ?>" class="form-control" placeholder="Confirme sua senha">
           <span class="error"> <?php echo $password_mismatch_error; ?> </span>
        </div>
        <div class="form-group">
           <label for="birthday"> <b> Data de nascimento: </b> </label>
           <input type="date" name="birthday" value="<?php echo isset($_POST['birthday']) ? $birthday : ''; ?>" class="form-control">
           <span class="error"> <?php echo $birthday_error; ?> </span>
        </div>
        <div class="form-group">
           <label for ="sex"> <b> Sexo: </b> </label>
           <br>
           <div class="form-check form-check-inline">
              <label class="form-check-label">
              <input class="form-check-input" type="radio" name="sex" value="masculino" checked="checked"> Masculino
              </label>
           </div>
           <div class="form-check form-check-inline">
              <label class="form-check-label">
              <input class="form-check-input" type="radio" name="sex"value="feminino"> Feminino
              </label>
           </div>
        </div>
        <div class="text-right">
          <a href="index.php" class="btn btn-danger"> Cancelar </a>
           <button type="submit" class="btn btn-success"> Criar Conta </button>
        </div>
     </form>
   </div>
   </div>

<?php include('inc/footer.php') ?>

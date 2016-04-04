<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Ok test</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

</head>

<body>

  <section class="container">
      <div class="login">
        <h1>Login to Web App</h1>
        <form method="post" action="ok.php">
          <p><input type="text" name="email" value="" placeholder="Username or Email"></p>
          <p><input type="password" name="password" value="" placeholder="Password"></p>
          <p class="remember_me">
            <label>
              <input type="checkbox" name="remember_me" id="remember_me">
              Remember me on this computer
            </label>
          </p>
          <p class="submit"><input type="submit" name="commit" value="Login" id="sendform"></p>
        </form>
      </div>

      <div class="login-help">
        <p>Forgot your password? <a href="404.php">Click here to reset it</a>.</p>
      </div>
    </section>



</body>
</html>

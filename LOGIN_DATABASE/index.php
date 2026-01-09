<?php

   session_start();

   if(isset($_SESSION['name']))
   {
      header("location: visualizzaUtente.php");
      exit();
   }

?>


<!doctype html>
<html lang="en">
    <head>
        <title>SISTEMA LOGIN/REGISTRAZIONE SWAPHUB - MECJA KEVIN 5IE </title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />

        <style>
            body {
            background-color: #3a3a3a;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Inter', sans-serif;
        }

        .login-container {
            background: #2a2a2a;
            color: #008000; 
            padding: 40px 35px;
            border-radius: 15px;
             box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            width: 350px;
        }

        .login-container h2 {
            font-weight: 700;
            font-size: 1.6rem;
            margin-bottom: 10px;
        }

        .login-container p {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 25px;
        }

        .form-control {
            height: 45px;
            border-radius: 10px;
            font-size: 0.95rem;
        }

        .btn-primary {
            width: 100%;
            height: 45px;
            border-radius: 10px;
            background-color: #008000;
            border: none;
            font-weight: 500;
        }

        

        .btn-primary:hover {
            background-color: grey;
        }

        .bottom-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;

        }

        .bottom-text a {
            color: #ffff;
            text-decoration: none;
            font-weight: 500;
        }


        .alert {
            text-align: center;
            font-size: 0.9rem;
            padding: 8px;
        }
        .btn-warning {
            
        }
    </style>
        
    </head>

    <body>
        <header>
            <!-- place navbar here -->
        </header>
        <main>
            <div class="login-container">
                  <p>Inserisci qui le tue credenziali</p>
                  <h2>Bentornato Swapper!</h2>
                 <?php if(isset($_GET["errore"])) { ?>
                <h2 id="erroreMessaggio" class = "alert alert-danger">  <?php echo $_GET["errore"];?> </h2>
                <?php } ?>
                
                <?php if(isset($_GET["logout"])): ?>
                <h2  id="logoutMessaggio"class="alert alert-success text-center">
                Logout effettuato in modo corretto
                </h2>
                <?php endif; ?>
                
                <form action = "login.php" method = "POST">
                   
                  <div class="mb-3">
                  <input type="text" name="username" class="form-control" placeholder="Username" required>
                   </div>

                   <div class="mb-3">
                   <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                    <input class = "btn btn-primary w-100 me-2" type = "submit" value = "EFFETTUA LOGIN"/>
                </form>

                <div class="bottom-text">
                <a href="register.php">REGISTRATI</a>
                </div>

                
                <div class="text-center mt-3">
                <a href=".." class="btn btn-secondary btn-sm">TORNA ALLA HOME</a>
                </div>

            </div>
            </div>
            

        </main>
        <footer>
            <!-- place footer here -->
        </footer>
        <!-- Bootstrap JavaScript Libraries -->
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <?php if (isset($_GET["errore"]) || isset($_GET["logout"])) { ?>
        <script>
          
          setTimeout(() => {
            const errore = document.getElementById("erroreMessaggio");
            if(errore) errore.style.display = "none";

            const logout = document.getElementById("logoutMessaggio");
            if(logout) logout.style.display = "none";
          }, 3000);

        
        </script>



        <?php } ?>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>
    </body>
</html>
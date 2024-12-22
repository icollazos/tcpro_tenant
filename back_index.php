<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<script src="head.js" defer></script>
</head>
<body>
	<div id="app">
		<div class="hidden container-fluid d-flex justify-content-center align-items-center vh-100">
			<div id="login-form" class="text-center">
				<img src="img/bcc.png" width="25%" />
				<h2 class="mb-4">Iniciar Sesión</h2>
				<form id="formLogin" class="row g-3">
					<div class="col-auto">
						<label for="usuario" class="">Usuario</label>
						<input type="text" class="form-control" id="usuario" placeholder="Usuario" required value="A">
					</div>
					<div class="col-auto">
						<label for="clave" class="">Contraseña</label>
						<input type="text" class="form-control" id="clave" placeholder="Contraseña" required  value="A">
					</div>
					<div class="col-auto">
						<button type="submit" class="btn btn-primary mb-3">Entrar</button>
					</div>
				</form>
			</div>
    </div>
  </div>
</body>

<script type="text/javascript" src="funciones.js"></script>

<script>

  document.getElementById('formLogin').addEventListener('submit', async function(event) {
    event.preventDefault();
    const usuario = document.getElementById('usuario').value; 
    const clave = document.getElementById('clave').value; 

    var argumentos={
      usuario:usuario,
      clave:clave
    };
    l(argumentos);
    postData('api/login.php', { 
      argumentos:argumentos
    })
    .then(data => {
      l("Marcador")
      l("Recibiendo Datos Tabla")
      l(data)

        // Verificar si data es igual a "ok"
        if (data.loggedIn === true) {
          window.location.href = "dt.html?v=v_aaa_texto";
        } else {
          console.log("Error en la autenticación o datos no válidos");
        }

      });
  });

</script>
</html>

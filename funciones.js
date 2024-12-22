async function postData(url = url, data = {}) {
	const response = await fetch(url, {
		method: 'POST', 
		mode: 'cors', 
		cache: 'no-cache',  
		credentials: 'same-origin', 
		headers: {
			'Content-Type': 'application/json'
		},
		redirect: 'follow', 
		referrerPolicy: 'no-referrer', 
		body: JSON.stringify(data)
	});
	return response.json();
}

async function getData(url = '', params = {}) {
    // Convertir los parámetros en una cadena de consulta
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? `${url}?${queryString}` : url;
    const response = await fetch(fullUrl, {
    	method: 'GET', 
    	mode: 'cors',
    	cache: 'no-cache',
    	credentials: 'same-origin',
    	headers: {
    		'Content-Type': 'application/json'
    	},
    	redirect: 'follow',
    	referrerPolicy: 'no-referrer'
    });
    return response.json();
}

function getDataSincrona(url = '', params = {}) {
    // Convertir los parámetros en una cadena de consulta
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? `${url}?${queryString}` : url;

    return fetch(fullUrl, {
        method: 'GET', 
        mode: 'cors',
        cache: 'no-cache',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        redirect: 'follow',
        referrerPolicy: 'no-referrer'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    });
}

function inputs_selects(pagina,vista,funcion){
    //console.log("GET_SET: ",funcion)
    var argumentos = {
        pagina: pagina,
        vista: vista,
        funcion: funcion
    };
    var inputs = document.getElementsByTagName('input');
    var resultado = {}; 
    Array.from(inputs).forEach((input) => {
        resultado[input.id] = input.value; 
    });
    argumentos.inputs =JSON.stringify(resultado); 
    var selects = document.getElementsByTagName('select');
    resultado = {};             
    Array.from(selects).forEach((select) => {
        var valorSeleccionado = select.value; 
        if (valorSeleccionado === "") {
            valorSeleccionado = 0; 
        }
        resultado[select.id] = valorSeleccionado; 
    });
    argumentos.selects = JSON.stringify(resultado);
    return(argumentos);
}

function inputs_selectsG1(pagina,vista,funcion){
    //console.log("GET_SET: ",funcion)
    var argumentos = {
        pagina: pagina,
        vista: vista,
        funcion: funcion
    };
    var inputs = document.getElementsByTagName('input');
    var resultado = {}; 
    Array.from(inputs).forEach((input) => {
        resultado[input.id] = input.value; 
    });
    argumentos.inputs =JSON.stringify(resultado); 
    return(argumentos);
}

async function getValoresDefecto(pagina, vista) {
    var argumentos=inputs_selects(pagina,vista,'get');
    console.log("GET VALORES DEFECTO: ", argumentos)
    var url = 'api/cargarValoresDefecto.php';
    try {
        const data = await getData(url, argumentos); 
        console.log("RECIBE GET___: ", data);
        return data; 
    } catch (error) {
        console.error('Error:', error); // Cambia l() a console.error()
    }
}

async function setValoresDefecto(pagina, vista) {
    const urlParams = new URLSearchParams(window.location.search);
    vista = urlParams.get('v');
    //alert(vista);
    var argumentos = inputs_selects(pagina, vista, 'set'); 

    console.log("SET VALORES DEFECTO: ", argumentos);
    var url = 'api/cargarValoresDefecto.php';
    
    try {
        const data = await getData(url, argumentos); 
        console.log("RECIBE SET___: ", data);
        restaurarInput(data);

        // Verificar si hay un error en la respuesta
        if (data.error) {
            throw new Error(data.error); // Lanza un error con el mensaje recibido
        }

        return data; 
    } catch (error) {
        console.error('Error:', error.message); // Muestra el mensaje de error
        alert('Se produjo un error: ' + error.message); // Muestra una alerta al usuario
    }
}

async function setValoresDefectoG1(pagina, vista) {
    const urlParams = new URLSearchParams(window.location.search);
    vista = urlParams.get('v');
    //alert(vista);
    var argumentos = inputs_selectsG1(pagina, vista, 'set'); 

    console.log("SET VALORES DEFECTO: ", argumentos);
    var url = 'api/cargarValoresDefecto.php';
    
    try {
        const data = await getData(url, argumentos); 
        console.log("RECIBE SET___: ", data);
        restaurarInput(data);

        // Verificar si hay un error en la respuesta
        if (data.error) {
            throw new Error(data.error); // Lanza un error con el mensaje recibido
        }

        return data; 
    } catch (error) {
        console.error('Error:', error.message); // Muestra el mensaje de error
        alert('Se produjo un error: ' + error.message); // Muestra una alerta al usuario
    }
}

function restaurarInput(valoresDefecto){
    console.log("RESTAURA INPUTS: ",valoresDefecto);
    var inputs=valoresDefecto.inputs;
    //console.log(inputs)
    var selects = valoresDefecto['selects'];
    //console.log(selects);
    inputs.forEach(item => {
        //console.log(item);
        var inputElement = document.getElementById(item.id);
        if (inputElement) {
            inputElement.value = item.valor; // Asigna el valor al input solo si existe
        } else {
            console.warn(`El input con ID '${item.id}' no se encontró.`);
        }
    });
    for (var i = selects.length - 1; i >= 0; i--) {
        //console.log(selects[i]);
        var el = document.getElementById(selects[i].id);
        if (el) {
            el.value = selects[i].valor; 
        } else {
            console.warn(`Elemento con ID ${selects[i].id} no encontrado.`); 
        }
    }
}

function restaurarInputG1(valoresDefecto){
    console.log("RESTAURA INPUTS: ",valoresDefecto);
    var inputs=valoresDefecto.inputs;
    //console.log(inputs)
    inputs.forEach(item => {
        //console.log(item);
        var inputElement = document.getElementById(item.id);
        if (inputElement) {
            inputElement.value = item.valor; // Asigna el valor al input solo si existe
        } else {
            console.warn(`El input con ID '${item.id}' no se encontró.`);
        }
    });
}


async function cargarYAML(archivo) {
    var opcion="YAML";
    var opcion="JSON";
    var url;
    var archivo;
    var ar;
    if(opcion=="JSON"){
        url = 'JSON_cargar.php';
        ar=archivo+'.json';        
    } else {
        url = 'YAML_cargar.php';
        ar=archivo+'.yaml';        
    }
    const params = {
        fuente: ar
    };
    try {
        const data = await getData(url, params); // Esperar a que se resuelva la promesa
        if(opcion=='JSON'){
            var d2=JSON.parse(data);
        } else {
            var d2=data;
        }
        return d2; // Retornar los datos
    } catch (error) {
        l('Error:', error); 
    }
}

async function verificarSesion() {
	var accion="check_session";
	try {
		const response = await fetch('api/check_session.php', { 
			method: 'POST',
			headers: {
				'Content-Type': 'application/json' 
			},
			body: JSON.stringify({ accion }) 
		});

		if (!response.ok) {
			throw new Error('Error en el inicio de sesión');
		}

		const data = await response.json();
		return data.loggedIn; 


		if (data.loggedIn) {
			mostrarDashboard();
		} else {
			alert('Usuario o contraseña incorrectos'); 
		}
	} catch (error) {
		console.error('Error al enviar los datos:', error);
		alert('Ocurrió un error al intentar iniciar sesión.');
	}
}

async function datosUsuario() {
	const url = 'api/datosUsuario.php';
	const params = {};
	try {
        const data = await getData(url, params); // Esperar a que se resuelva la promesa
        l(data); 
        return data; // Retornar los datos
    } catch (error) {
    	l('Error:', error); 
    }
}

function crearMenu(data, session) {
    console.log("Crea Menu");
    const menuDiv = document.getElementById('menu'); // Asegúrate de tener un ul con id="menu"
    menuDiv.innerHTML = ''; // Limpiar contenido previo
    const section = data; // Obtener la sección correspondiente al ID

    // Iterar sobre las subsecciones (pri, rep, etc.)
    for (const subSectionKey in section) {
        if (section.hasOwnProperty(subSectionKey)) {
            const subSection = section[subSectionKey];
            // Crear un elemento de lista para la subsección
            const li = document.createElement('li');
            li.classList.add('nav-item', 'dropdown'); // Clase de Bootstrap para elementos de lista y dropdown
            
            // Crear el enlace que activará el dropdown
            const a = document.createElement('a');
            a.classList.add('nav-link', 'dropdown-toggle');
            a.href = '#'; // Puedes cambiar esto si es necesario
            a.setAttribute('role', 'button');
            a.setAttribute('data-bs-toggle', 'dropdown');
            a.setAttribute('aria-expanded', 'false');
            a.textContent = subSection.titulo; // Título de la subsección

            li.appendChild(a); // Añadir el enlace al elemento de lista

            // Crear la lista desplegable
            const ul = document.createElement('ul');
            ul.classList.add('dropdown-menu');

            // Iterar sobre los items de la subsección
            for (const key in subSection.items) {
                if (subSection.items.hasOwnProperty(key)) {
                    const item = subSection.items[key];
                    // Crear un elemento de lista para cada item
                    const itemLi = document.createElement('li');
                    const itemA = document.createElement('a');
                    itemA.classList.add('dropdown-item'); // Clase para los items del dropdown

                    switch(item.f) {
                        case 'dt':
                            itemA.href = `${item.f}.html?v=${item.v || ''}`; // Construir el href usando 'f' y 'v'
                            break;
                        case 'g1':
                            itemA.href = `${item.f}.html?v=${item.v || ''}&tipo=${item.tipo}`; // Construir el href usando 'f' y 'v'
                            break;
                        case 'gNube':
                            itemA.href = `${item.f}.html?v=${item.v || ''}`; // Construir el href usando 'f' y 'v'
                            break;
                        case 'gArbol':
                            itemA.href = `${item.f}.html?v=${item.v || ''}`; // Construir el href usando 'f' y 'v'
                            break;
                        case 'link':
                            itemA.href = `${item.v}`; // Construir el href usando 'f' y 'v'
                            break;                        
                        case 'dashboard':
                            itemA.href = `${item.f}.html?v=${item.v || ''}`; // Construir el href usando 'f' y 'v'
                            break;                        
                    }

                    itemA.textContent = item.alias; // Usar el alias como texto del enlace

                    itemLi.appendChild(itemA); // Añadir el enlace al elemento de lista
                    ul.appendChild(itemLi); // Añadir el elemento de lista a la lista desplegable
                }
            }

            li.appendChild(ul); // Añadir la lista desplegable al elemento de lista principal
            menuDiv.appendChild(li); // Añadir el elemento de lista al div del menú
        }
    }
}

function modificar(argumentos,pagina) {
    var argElements = document.getElementsByClassName('arg');
    for (var i = 0; i < argElements.length; i++) {
        argElements[i].addEventListener('change', function() {
            switch (pagina) {
                case 'dt':
                cargarDatos(argumentos); 
                break;
                case 'g1':
                cargarDatosG1(argumentos); 
                break;
                default:
                break;
            }
        });
    }
}

function botonesEspeciales(vista){
    var caja=document.getElementById('botonesEspeciales');
    caja.innerHTML='';
    switch(vista){
        case 'v_aaa_texto':
            var nuevoDiv = document.createElement('div');
            nuevoDiv.className = 'col-3'; 
            var nuevoBoton = document.createElement('button');
            nuevoBoton.className="btn btn-success btn-sm";
            nuevoBoton.innerText = 'Cargar Nuevos Textos'; 
            nuevoBoton.onclick = cargarNuevosTextos; 
            nuevoDiv.appendChild(nuevoBoton);
            caja.appendChild(nuevoDiv);
        break;
    }
}

async function cargarNuevosTextos(){
    var argumentos={};
    var url = 'apiCustom/cron_newsapi_ampliado.php';
    try {
        const data = getDataSincrona(url, argumentos); 
        alert('Cargando Data... Espere unos segundos hasta que los datos se muestren en pantalla.');
        console.log("RECIBE NEWSAPI: ", data);
        main();
    } catch (error) {
        console.error('Error:', error); // Cambia l() a console.error()
    }
}

function inicializarTabla() {
    if ($.fn.DataTable.isDataTable('#tabla')) {
        $('#tabla').DataTable().clear().destroy();
    }

    $(document).ready(function() {
        $('#tabla').DataTable({
            // Aquí puedes agregar opciones de configuración si es necesario
            language: {
                "decimal": "",
                "emptyTable": "No hay datos disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });
    });
}

function l(x){
	console.log(x);
}


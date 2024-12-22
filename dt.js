async function crearFiltrosPadres(tablaFrom, fuentes){
  l("Creando Filtros Padres")
  const alias = await cargarYAML('YAML_alias'); 

  const contenedor = document.getElementById('filtrosPadres'); 
  contenedor.innerHTML='';

  for (const id in fuentes) {
    if (fuentes.hasOwnProperty(id)) {
      const div = document.createElement('div');
      div.id = `div_`+id; 

      const label = document.createElement('label');
      label.textContent = alias[id]; 

      const select = document.createElement('select');
      select.className = 'form-control fp arg'; 
      select.id = id; 

      div.appendChild(label);
      div.appendChild(select);

      llenarSelect(tablaFrom, select.id);

      contenedor.appendChild(div);
    }
  }
}

async function llenarSelect(vista, campo) {
  l("Llenando Select");
  const url = 'api/data_llenarFiltrosPadres.php';
  const argumentos = {
    vista: vista,
    campo: campo
  };
  try {
    const data = await getData(url, argumentos); 
    const select = document.getElementById(campo); 
    select.innerHTML = '';
    const option = document.createElement('option'); 
    option.value = 0; 
    option.textContent = "TODOS"; 
    select.appendChild(option); 
    data.forEach(item => {
      const option = document.createElement('option'); 
      option.value = item.id; 
      option.textContent = item.descriptor; 
      select.appendChild(option); 
    });
  } catch (error) {
  }
}

async function cargarFiltrosPadres(){
  l("Cargar Filtros Padres");
  const url = 'api/data_llenarFiltrosPadres.php';
  const params = {
  };
  try {
    const data = await getData(url, params); 
    llenarFiltrosPadres(data)
  } catch (error) {
  }
}

function mostrarFiltrosPadres(yaml_filtrosPadres,argumentos) {
  const claves = Object.keys(yaml_filtrosPadres);
  const divs = document.querySelectorAll('div[data-padres]');
  divs.forEach(div => {
    const habilitado = claves.includes(div.id); 
    if (habilitado) {
      div.style.display = ''; 
      const select = div.querySelector('select'); 
      if (select) {
        select.setAttribute('data-activo', 'true'); 
      }
    } else {
      div.style.display = 'none'; 
    }
  });
  modificar(argumentos);
}

async function cargarDatos(){
  l("CARGA DATOS")
  var selects = document.getElementsByClassName('fp'); 
  console.log("SSSSS: ", selects);
  var padres = [];
  var a = {};
  Array.from(selects).forEach((select) => {
    if (select.options.length > 0 && select.selectedIndex >= 0) {
      a[select.id] = parseInt(select.options[select.selectedIndex].value);
    } else {
      a[select.id] = 0;
    }
  });
  padres.push(a);
  var inputs = document.getElementsByTagName('input');
  const urlParams = new URLSearchParams(window.location.search);
  const vista = urlParams.get('v'); 
  const fechaInicial=document.getElementById('fechaInicial').value;
  const fechaFinal=document.getElementById('fechaFinal').value;
  const palabra=document.getElementById('palabra').value;
  const excepto=document.getElementById('excepto').value;
  const paginaOrigen='dt';
  var argumentos={
    paginaOrigen:paginaOrigen,
    vista:vista,
    fechaInicial:fechaInicial,
    fechaFinal:fechaFinal,
    palabra:palabra,
    excepto:excepto
  };
  argumentos.selects = padres; 
  var llenar = [];
  Array.from(inputs).forEach((input)=>{
    var i={};
    i[input.id] = input.value;
    llenar.push(i);
  });
  argumentos.inputs = llenar; 
  fuente='api/data_datatables.php';
  console.log("ARG - DATATABLE", argumentos);
  postData(fuente, { 
    argumentos: argumentos
  })
  .then(data => {
    console.log("TABLA:::::::", data)
    fillCSVInTextarea(data.resultados);
    switch (vista) {
      case 'v_aaa_item':
      llenarTablaItem(data['resultados']);
      break;
      case 'v_aaa_texto':
      llenarTablaTextos(data['resultados'],vista);      
      break;
      default:
      llenarTabla(data['resultados']);
    }

    setValoresDefecto('dt',vista);
    return data; 
  })
  .catch(error => {
    l('Error al cargar datos: ', error); 
  });
}

document.getElementById('copiarCSV').addEventListener('click', function(event) {
    event.preventDefault(); // Evitar la acción por defecto del enlace
    const textarea = document.getElementById('csv'); // Obtener el textarea
    textarea.select(); // Seleccionar el contenido del textarea
    // Copiar el contenido seleccionado al portapapeles
    navigator.clipboard.writeText(textarea.value)
        .then(() => {
            console.log('Contenido copiado al portapapeles');
            alert('CSV copiado al portapapeles'); // Mensaje de éxito
        })
        .catch(err => {
            console.error('Error al copiar: ', err);
            alert('Error al copiar el CSV'); // Mensaje de error
        });
});




function convertToCSV(objArray) {
    // Obtener las claves del primer objeto como encabezados
    const headers = Object.keys(objArray[0]);
    
    // Crear el contenido CSV
    const csvContent = [
        headers.join(","), // Encabezados
        ...objArray.map(item => headers.map(header => item[header]).join(",")) // Filas de datos
    ].join("\n");

    return csvContent;
}

function fillCSVInTextarea(data) {
    const csvData = convertToCSV(data); // Convertir a CSV
    const textarea = document.getElementById('csv'); // Obtener el textarea
    textarea.value = csvData; // Asignar el contenido CSV al textarea
}



async function cargarDatosDetalle() {
  console.log("CARGA DATOS DETALLE");
  const urlParams = new URLSearchParams(window.location.search);
  const id = urlParams.get('id'); 
  var argumentos = {
    id: id
  };
  const url = 'api/data_detalle.php';
  try {
    const data = await getData(url, argumentos); 
    console.log(data);
    var claves = {
      'descriptor': 'Titular', 
      'image_url': 'Imagen Principal',
      'description': 'Texto',
      'id': 'Id',
      'ai_region': 'Region',
      'fecha': 'Fecha',
      'link': 'Enlace a la noticia',
      'source_name': 'Nombre de la fuente',
      'source_url': 'Enlace a la fuente'
    };
    llenarTablaDetalle(data.datos[0], claves);
    llenarTablaEtiquetas(data.etiquetas);
  } catch (error) {
    console.error("Error al cargar los datos:", error); 
  }
}

function llenarTablaDetalle(data, claves) {
  var links = ['link', 'source_url'];
  const tablaBody = document.getElementById('tablaDetalle').getElementsByTagName('tbody')[0];
  tablaBody.innerHTML = '';     
  for (const clave in claves) { 
    const row = document.createElement('tr');

    const cellClave = document.createElement('td');
    cellClave.textContent = claves[clave]; 
    row.appendChild(cellClave);

    const cellValor = document.createElement('td');


    if (links.includes(clave)) { 
      const linkButton = document.createElement('a');
      linkButton.href = data[clave];
      linkButton.target = '_blank'; 
      linkButton.textContent = 'Abrir enlace';
      linkButton.classList.add('btn', 'btn-primary'); 
      cellValor.appendChild(linkButton);
    } else if (clave === 'image_url') { 
      const img = document.createElement('img');
      img.src = data[clave];
      img.alt = clave; 
      img.style.width = '100%'; 
      cellValor.appendChild(img);
    } else {
      cellValor.textContent = data[clave] !== undefined ? data[clave] : 'No disponible'; 
    }

    row.appendChild(cellValor); 
    tablaBody.appendChild(row); 
  }
}

function llenarTablaEtiquetas(etiquetas) {
  const tablaBody = document.getElementById('tablaetiquetas').getElementsByTagName('tbody')[0];
  tablaBody.innerHTML = ''; 
  etiquetas.forEach(etiqueta => {
    const row = document.createElement('tr');
    const cellVariable = document.createElement('td');
    cellVariable.textContent = etiqueta.variable; 
    row.appendChild(cellVariable);
    const cellValor = document.createElement('td');
    cellValor.textContent = etiqueta.valor; 
    row.appendChild(cellValor);
    const cellPuntaje = document.createElement('td');
    cellPuntaje.textContent = etiqueta.puntaje; 
    row.appendChild(cellPuntaje);
    tablaBody.appendChild(row);
  });
}

function llenarTablaItem(data) {
  console.log("LLENANDO LA TABLA");
  const tablaHeader = document.getElementById('tabla-header'); 
  const tablaBody = document.getElementById('tabla-body');
  tablaHeader.innerHTML = ''; 
  tablaBody.innerHTML = ''; 
  
  if (data.length > 0) {
    const headers = Object.keys(data[0]);
    const headerRow = document.createElement('tr');
    headerRow.classList.add('table-info'); 
    
    headers.forEach(header => {
      const th = document.createElement('th');
      th.textContent = header.charAt(0).toUpperCase() + header.slice(1); 
      headerRow.appendChild(th);
    });
    
    // Agregar columna de acciones
    const thAcciones = document.createElement('th');
    thAcciones.textContent = "ACCIONES"; 
    headerRow.appendChild(thAcciones);
    
    tablaHeader.appendChild(headerRow); 
    
    data.forEach(item => {
      const row = document.createElement('tr');
      
      headers.forEach(header => {
        const cell = document.createElement('td');
        cell.textContent = item[header]; 
        row.appendChild(cell);
      });
      
      const cellAcciones = document.createElement('td');

      // Botón para eliminar
      const btnEliminar = document.createElement('button');
      btnEliminar.textContent = 'Eliminar';
      btnEliminar.classList.add('btn', 'btn-danger', 'btn-sm'); 
      btnEliminar.onclick = function() {
        confirmarEliminacion(item); 
      };

      // Botón para ver detalles
      const btnInfo = document.createElement('button');
      btnInfo.textContent = 'Info';
      btnInfo.classList.add('btn', 'btn-info', 'btn-sm'); 
      btnInfo.onclick = function() {
        verDetalles(item); 
      };

      // Nuevo botón para actualizar textos
      /*
      const btnActualizar = document.createElement('button');
      btnActualizar.textContent = 'Actualizar';
      btnActualizar.classList.add('btn', 'btn-warning', 'btn-sm'); 
      btnActualizar.onclick = function() {
        actualizarTextos(item.id); // Asumiendo que el id del item es item.id
      };
      */

      // Agregar botones a la celda de acciones
      cellAcciones.appendChild(btnInfo);
      cellAcciones.appendChild(btnEliminar);
      //cellAcciones.appendChild(btnActualizar); // Agregar el nuevo botón
      row.appendChild(cellAcciones);
      
      tablaBody.appendChild(row); 
    });
  }
}
async function actualizarTextos(item){
    l("ACTUALIZANDO....");
  const url = 'apiCustom/cron_newsapi_onclick_2024_12_07.php';
  const argumentos = {
    item: item
  };
  try {
    alert("Llamando al proveedor de datos. Por favor clique en el botón y espere unos momentos. El proceso puede tardar de uno a varios minutos dependiendo del volumen de datos por cargar. Espere con paciencia");;
    const data = await getData(url, argumentos); 
    alert(data);
    cargarDatos();
  } catch (error) {
  }
}


function llenarTablaTextos(data, vista) {
  const tablaHeader = document.getElementById('tabla-header'); 
  const tablaBody = document.getElementById('tabla-body');
  tablaHeader.innerHTML = ''; 
  tablaBody.innerHTML = ''; 
  if (data.length > 0) {
    const headers = Object.keys(data[0]);
    const headerRow = document.createElement('tr');
    headerRow.classList.add('table-info'); 
    headers.forEach(header => {
      const th = document.createElement('th');
      th.textContent = header.charAt(0).toUpperCase() + header.slice(1); 
      headerRow.appendChild(th);
    });
    const thAcciones = document.createElement('th');
    thAcciones.textContent = "ACCIONES"; 
    headerRow.appendChild(thAcciones);
    tablaHeader.appendChild(headerRow); 
    data.forEach(item => {
      const row = document.createElement('tr');
      headers.forEach(header => {
        const cell = document.createElement('td');
        cell.textContent = item[header]; 
        row.appendChild(cell);
      });
      const cellAcciones = document.createElement('td');     
      const btnEliminar = document.createElement('button');
      btnEliminar.textContent = 'Eliminar';
      btnEliminar.classList.add('btn', 'btn-danger', 'btn-sm'); 
      btnEliminar.onclick = function() {
        confirmarEliminacion(item); 
      };      
      const btnInfo = document.createElement('button');
      btnInfo.textContent = 'Info';
      btnInfo.classList.add('btn', 'btn-info', 'btn-sm'); 
      btnInfo.onclick = function() {
        verDetalles(item); 
      };      
      const btnDetalle = document.createElement('a');
      btnDetalle.textContent = 'Ver detalles';
      btnDetalle.classList.add('btn', 'btn-success', 'btn-sm');      
      btnDetalle.href = `fichaTexto.html?id=${item.id}`;
btnDetalle.target = '_blank'; 
btnDetalle.rel = 'noopener noreferrer'; 

btnDetalle.style.display = 'inline-block'; 
btnDetalle.style.padding = '5px 10px'; 
btnDetalle.onclick = function() {
  window.open(`fichaTexto.html?id=${item.id}`, '_blank'); 
};
      cellAcciones.appendChild(btnInfo);
      cellAcciones.appendChild(btnEliminar);      
      if (vista === 'v_aaa_texto') {
        cellAcciones.appendChild(btnDetalle);
      }
      row.appendChild(cellAcciones);
      tablaBody.appendChild(row); 
    });
  }
}

function llenarTabla(data) {
  console.log("LLENANDO LA TABLA")
  const tablaHeader = document.getElementById('tabla-header'); 
  const tablaBody = document.getElementById('tabla-body');
  tablaHeader.innerHTML = ''; 
  tablaBody.innerHTML = ''; 
  if (data.length > 0) {
    const headers = Object.keys(data[0]);
    const headerRow = document.createElement('tr');
    headerRow.classList.add('table-info'); 
    headers.forEach(header => {
      const th = document.createElement('th');
      th.textContent = header.charAt(0).toUpperCase() + header.slice(1); 
      headerRow.appendChild(th);
    });
    const thAcciones = document.createElement('th');
    thAcciones.textContent = "ACCIONES"; 
    headerRow.appendChild(thAcciones);
    tablaHeader.appendChild(headerRow); 
    data.forEach(item => {
      const row = document.createElement('tr');
      headers.forEach(header => {
        const cell = document.createElement('td');
        cell.textContent = item[header]; 
        row.appendChild(cell);
      });
      const cellAcciones = document.createElement('td');
      const btnEliminar = document.createElement('button');
      btnEliminar.textContent = 'Eliminar';
      btnEliminar.classList.add('btn', 'btn-danger', 'btn-sm'); 
      btnEliminar.onclick = function() {
        confirmarEliminacion(item); 
      };
      const btnInfo = document.createElement('button');
      btnInfo.textContent = 'Info';
      btnInfo.classList.add('btn', 'btn-info', 'btn-sm'); 
      btnInfo.onclick = function() {
        verDetalles(item); 
      };
      cellAcciones.appendChild(btnInfo);
      cellAcciones.appendChild(btnEliminar);
      row.appendChild(cellAcciones);
      tablaBody.appendChild(row); 
    });
  }
}

function confirmarEliminacion(item) {
  const modalBody = document.getElementById('modalConfirmacionBody');
  modalBody.innerHTML = ''; 
  for (const key in item) {
    if (item.hasOwnProperty(key)) {
      const p = document.createElement('p');
      p.textContent = `${key.charAt(0).toUpperCase() + key.slice(1)}: ${item[key]}`;
      modalBody.appendChild(p);
    }
  }    
  const modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
  modal.show();
  document.getElementById('btnConfirmarEliminar').onclick = function() {
    eliminarRegistro(item.id); 
    modal.hide(); 
  };
}

async function eliminarRegistro(id) {
  try {
    const urlParams = new URLSearchParams(window.location.search);
    const vistaActual = urlParams.get('v'); 
    l(vistaActual);    
    const response = await fetch(`api/eliminarRegistro.php?id=${id}&vistaActual=${vistaActual.slice(2)}`, {
      method: 'GET', 
      headers: {
        'Content-Type': 'application/json',
      },
    });    
    if (!response.ok) {
      throw new Error('Error en la eliminación del registro');
    }   
    const result = await response.json();
    console.log('Registro eliminado:', result);    
    var argumentos = { vista: vistaActual };
    var datos = await cargarDatos(argumentos);
    llenarTabla(datos);
  } catch (error) {
    console.error('Error:', error);
  }
}

async function eliminarRegistro_POST(id) {
  try {
    const urlParams = new URLSearchParams(window.location.search);
    const vistaActual = urlParams.get('v'); 
    l(vistaActual);
    const response = await fetch('api/eliminarRegistro.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        id: id,
        vistaActual: vistaActual.slice(2) 
      }),
    });
    if (!response.ok) {
      throw new Error('Error en la eliminación del registro');
    }
    const result = await response.json();
    console.log('Registro eliminado:', result);
    var argumentos={ vista:vistaActual }
    var datos= await cargarDatos(argumentos);
    llenarTabla(datos);
  } catch (error) {
    console.error('Error:', error);
  }
}

function verDetalles(item) {
  const modalBody = document.getElementById('modalDetallesBody');
  modalBody.innerHTML = ''; 
  const table = document.createElement('table');
  table.classList.add('table', 'table-bordered');     
  const tbody = document.createElement('tbody');    
  for (const key in item) {
    if (item.hasOwnProperty(key)) {
      const row = document.createElement('tr');
      const cellKey = document.createElement('td');
      cellKey.textContent = key.charAt(0).toUpperCase() + key.slice(1); 
      row.appendChild(cellKey);
      const cellValue = document.createElement('td');
      cellValue.textContent = item[key]; 
      row.appendChild(cellValue);
      tbody.appendChild(row);
    }
  }    
  table.appendChild(tbody);    
  modalBody.appendChild(table);    
  const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
  modal.show();
}

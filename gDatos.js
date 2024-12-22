function generarGraficoTorta(data) {
  console.log("GRAFICANDO: ", data);

    // Transformar el array de objetos a un formato adecuado para Highcharts
    const seriesData = data.map(item => ({
      name: item.descriptor,
      y: item.cuenta
    }));

    const urlParams = new URLSearchParams(window.location.search);
    var tipo = urlParams.get('tipo');

    // Crear el gráfico inicial como un gráfico de torta
    const chart = Highcharts.chart('chart', {
      chart: {
        type: tipo
      },
      title: {
        text: 'Distribución por Descriptor'
      },
      legend: {
            useHTML: true, // Permitir HTML para la leyenda
            layout: 'horizontal', // Asegurarse de que la leyenda sea vertical
            align: 'center', // Alinear a la derecha
            verticalAlign: 'bottom', // Alinear verticalmente al medio
            floating: false, // No flotar la leyenda
            borderWidth: 1, // Ancho del borde de la leyenda (opcional)
            itemStyle: {
              cursor: 'pointer',
              fontSize: '12px'
            }
          },
          series: [{
            name: 'Cuentas',
            data: seriesData,
            showInLegend: true,
            dataLabels: {
              enabled: true,
              format: '{point.name}: {point.y}'
            }
          }]
        });

    // Mover la leyenda al div #legend
    const legendContainer = document.getElementById('legend');
    const legend = chart.legend.group.element;
    legendContainer.appendChild(legend);

    // Cambiar a gráfico de barras
    $('#btnBar').on('click', function() {
      chart.update({
        chart: {
          type: 'bar'
        }
      });
        // Mover la leyenda nuevamente después del cambio
        legendContainer.appendChild(chart.legend.group.element);
      });

    // Cambiar a gráfico de columnas
    $('#btnColumn').on('click', function() {
      chart.update({
        chart: {
          type: 'column'
        }
      });
        // Mover la leyenda nuevamente después del cambio
        legendContainer.appendChild(chart.legend.group.element);
      });

    // Volver al gráfico de torta
    $('#btnPie').on('click', function() {
      chart.update({
        chart: {
          type: 'pie'
        }
      });
        // Mover la leyenda nuevamente después del cambio
        legendContainer.appendChild(chart.legend.group.element);
      });
  }

function generateJson(data) {
    console.log("ARBOL - GENERANDO JSON", data);

    const result = [];

    // Agrupar por seguimiento
    data.forEach(item => {
        // Verificar si ya existe el seguimiento
        let seguimientoNode = result.find(node => node.name === item.seguimiento);
        if (!seguimientoNode) {
            // Si no existe, crear un nuevo nodo para el seguimiento
            seguimientoNode = {
                name: item.seguimiento,
                children: []
            };
            result.push(seguimientoNode);
        }

        // Verificar si ya existe la variable dentro del seguimiento
        let variableNode = seguimientoNode.children.find(child => child.name === item.variable);
        if (!variableNode) {
            // Si no existe, crear un nuevo nodo para la variable
            variableNode = {
                name: item.variable,
                children: []
            };
            seguimientoNode.children.push(variableNode);
        }

        // Agregar el valor y lemapar como hijos de la variable
        variableNode.children.push({
            name: item.lemapar,
            value: item.valor // Puedes incluir lemapar si es necesario
        });
    });

    return result;
}

  async function cargarDatosArbol(){
    console.log("ARBOL - CARGANDO DATOS")
    l("Cargar Datos");
    const url = 'apiCustom/gDatos.php';
    l(url)
    const params={
      buscar:'datosArbol',
    };
    var d=1000;
    let data; 
    console.log("PARAMS::::::",params);
    var d=await getData(url, params);
    return (d);
}


function generarGraficoArbol(data) {
    console.log("ARBOL - GENERANDO GRAFICO", data);

    var json = generateJson(data); 
    var j = {};
    j.name = "flare";
    j.children = json;
    console.log("JSON", j);

    const width = 928;
    const marginTop = 10;
    const marginRight = 10;
    const marginBottom = 10;
    const marginLeft = 40;

    const root = d3.hierarchy(j); 
    const dx = 10;
    const dy = (width - marginRight - marginLeft) / (1 + root.height);

    const tree = d3.tree().nodeSize([dx, dy]);
    const diagonal = d3.linkHorizontal().x(d => d.y).y(d => d.x);

    // Crear el SVG y agregar comportamiento de zoom
    const svg = d3.create("svg")
        .attr("width", width)
        .attr("height", dx)
        .attr("viewBox", [-marginLeft, -marginTop, width, dx])
        .attr("style", "max-width: 100%; height: auto; font: 10px sans-serif; user-select: none;")
        .call(d3.zoom() // Agregar comportamiento de zoom
            .scaleExtent([0.5, 5]) // Rango de zoom
            .on("zoom", (event) => {
                gLink.attr("transform", event.transform); // Aplicar transformación a los enlaces
                gNode.attr("transform", event.transform); // Aplicar transformación a los nodos
            }));

    const gLink = svg.append("g")
        .attr("fill", "none")
        .attr("stroke", "#555")
        .attr("stroke-opacity", 0.4)
        .attr("stroke-width", 1.5);

    const gNode = svg.append("g")
        .attr("cursor", "pointer")
        .attr("pointer-events", "all");

    function update(event, source) {
        const duration = event?.altKey ? 2500 : 250; 
        const nodes = root.descendants().reverse();
        const links = root.links();

        tree(root);

        let left = root;
        let right = root;
        root.eachBefore(node => {
            if (node.x < left.x) left = node;
            if (node.x > right.x) right = node;
        });

        const height = right.x - left.x + marginTop + marginBottom;

        const transition = svg.transition()
            .duration(duration)
            .attr("height", height)
            .attr("viewBox", [-marginLeft, left.x - marginTop, width, height])
            .tween("resize", window.ResizeObserver ? null : () => () => svg.dispatch("toggle"));

        const node = gNode.selectAll("g")
            .data(nodes, d => d.id);

        const nodeEnter = node.enter().append("g")
            .attr("transform", d => `translate(${source.y0},${source.x0})`)
            .attr("fill-opacity", 0)
            .attr("stroke-opacity", 0)
            .on("click", (event, d) => {
                d.children = d.children ? null : d._children;
                update(event, d);
            });

        nodeEnter.append("circle")
            .attr("r", 2.5)
            .attr("fill", d => d._children ? "#555" : "#999")
            .attr("stroke-width", 10);

        nodeEnter.append("text")
            .attr("dy", "0.31em")
            .attr("x", d => d._children ? -6 : 6)
            .attr("text-anchor", d => d._children ? "end" : "start")
            .text(d => d.data.name)
            .attr("stroke-linejoin", "round")
            .attr("stroke-width", 3)
            .attr("stroke", "white")
            .attr("paint-order", "stroke");

        const nodeUpdate = node.merge(nodeEnter).transition(transition)
            .attr("transform", d => `translate(${d.y},${d.x})`)
            .attr("fill-opacity", 1)
            .attr("stroke-opacity", 1);

        const nodeExit = node.exit().transition(transition).remove()
            .attr("transform", d => `translate(${source.y},${source.x})`)
            .attr("fill-opacity", 0)
            .attr("stroke-opacity", 0);

        const link = gLink.selectAll("path")
            .data(links, d => d.target.id);

        const linkEnter = link.enter().append("path")
            .attr("d", d => {
                const o = {x: source.x0, y: source.y0};
                return diagonal({source: o, target: o});
            });

        link.merge(linkEnter).transition(transition)
            .attr("d", diagonal);

        link.exit().transition(transition).remove()
            .attr("d", d => {
                const o = {x: source.x, y: source.y};
                return diagonal({source: o, target: o});
            });

        root.eachBefore(d => {
            d.x0 = d.x;
            d.y0 = d.y;
        });
    }

    root.x0 = dy / 2;
    root.y0 = 0;
    root.descendants().forEach((d, i) => {
        d.id = i;
        d._children = d.children;
        if (d.depth && d.data.name.length !== 7) d.children = null; 
    });

    update(null, root);

    return svg.node();
}


async function cargarDatosG1(){
  console.log("Cargar Datos G1");

  const urlParams = new URLSearchParams(window.location.search);
  var vista = urlParams.get('v');
  const idvariable=document.getElementById('idvariable').value;
  const fechaInicial=document.getElementById('fechaInicial').value;
  const fechaFinal=document.getElementById('fechaFinal').value;
  const palabra=document.getElementById('palabra').value;
  const excepto=document.getElementById('excepto').value;
  const selectElement = document.getElementById('idvalor');
  
  var valoresSeleccionados2;
  var valoresSeleccionados = Array.from(selectElement.selectedOptions).map(option => option.value);
  valoresSeleccionados2 = valoresSeleccionados.join(',');
  
  const inputidvalor=document.getElementById('inputidvalor');
  inputidvalor.value=valoresSeleccionados2;
  
  const params={
    buscar:'datosG1',
    idvariable:idvariable,
    fechaInicial:fechaInicial,
    fechaFinal:fechaFinal,
    palabra:palabra,
    excepto:excepto,
    inputidvalor:valoresSeleccionados2
  };
  console.log(params);
  let data; 
  const url = 'apiCustom/gDatos.php';
  l(url)
  try {
    const data = await getData(url, params);
    console.log("Cuenta de Valores: ", data);
    generarGraficoTorta(data);
    setValoresDefectoG1('g1', vista);
    
    // Agregar datos a la tabla
    await agregarDatosATabla(data);
    
    // Inicializar DataTable después de agregar los datos
    //const t = datatable('#tabla');
    
  } catch (error) {
    console.error("Error al cargar seguimientos:", error);
  }

}

function datatable(tabla){
    //const t = new DataTable('#tabla');
}


async function agregarDatosATabla(data) {
  console.log("AGREGANDO DATOS A TABLA",data);
  console.log("##############################################################")
  const headerRow= document.getElementById('tabla').getElementsByTagName('thead')[0];
  headerRow.classList.add('table-info'); 

  const tablaBody = document.getElementById('tabla').getElementsByTagName('tbody')[0];
  tablaBody.innerHTML='';
  const registrosAgregados = new Set();

  for (const key in data) {
    const item = data[key];
    const row = tablaBody.insertRow();
    row.insertCell(0).innerText = item.descriptor; 
    row.insertCell(1).innerText = item.cuenta; 
    if(key==15){
      return;
    }
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

function generarGraficoBurbujas(data) {
    const container = document.getElementById('grafico');
    const width = container.getBoundingClientRect().width; // Obtener el ancho del contenedor
    const height = 800;

    // Vaciar el gráfico antes de dibujar uno nuevo
    d3.select("#grafico").html("");

    // Crear un SVG para el gráfico
    const svg = d3.select("#grafico")
        .append("svg")
        .attr("width", width)
        .attr("height", height)
        .call(d3.zoom() // Agregar comportamiento de zoom
            .scaleExtent([0.5, 5]) // Rango de zoom
            .on("zoom", function (event) {
                svg.attr("transform", event.transform); // Aplicar la transformación de zoom
            }))
        .append("g"); // Crear un grupo para los elementos del gráfico

    // Crear nodos a partir de los datos
    const nodes = data.map(d => ({
        descriptor: d.descriptor,
        radius: Math.pow(d.cuenta, 0.33) * 15, // Aumenta el tamaño del radio multiplicando por un factor
        x: Math.random() * width,
        y: Math.random() * height
    }));

    // Inicializar la simulación de fuerza
    const simulation = d3.forceSimulation(nodes)
        .force("charge", d3.forceManyBody().strength(100)) // Fuerza de repulsión negativa, ajustada para atraer
        .force("center", d3.forceCenter(width / 2, height / 2).strength(0.1)) // Aumentar la fuerza hacia el centro
        .force("collide", d3.forceCollide().radius(d => d.radius + 5).iterations(2))
        .on("tick", ticked);

    // Crear burbujas
    const bubbles = svg.selectAll("circle")
        .data(nodes)
        .enter()
        .append("circle")
        .attr("class", "bubble")
        .attr("r", d => d.radius) // Radio basado en cuenta
        .attr("fill", "#2980b9") // Color por defecto
        .call(d3.drag() // Permitir arrastrar las burbujas
            .on("start", dragstarted)
            .on("drag", dragged)
            .on("end", dragended));

    // Añadir etiquetas en el centro de las burbujas
    svg.selectAll("text.label")
        .data(nodes)
        .enter()
        .append("text")
        .attr("class", "label")
        .text(d => d.descriptor)
        .attr("dy", ".35em") // Ajusta la posición vertical del texto
        .style("fill", "#fff") // Color del texto
        .style("font-size", d => (d.radius / 4) + "px"); // Ajusta el tamaño del texto según el radio

    function ticked() {
        bubbles.attr("cx", d => d.x)
            .attr("cy", d => d.y);

        svg.selectAll("text.label")
            .attr("x", d => d.x)
            .attr("y", d => d.y);
    }

    function dragstarted(event, d) {
        if (!event.active) simulation.alphaTarget(0.03).restart();
        d.fx = d.x;
        d.fy = d.y;
    }

    function dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }

    function dragended(event, d) {
        if (!event.active) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
    }
}





  async function cargarProyectos(){
    l("Cargar Proyectos");
    const url = 'apiCustom/etiquetador.php';
    const params={
      buscar:'proyectos'
    };
    const data = await getData(url, params); 
    console.log("Proyectos: ",data);
    llenarSelect('idproyecto', data)
    await cargarSeguimientos();
  }

  async function cargarSeguimientos(){
    l("Cargar Seguimientos");
    const url = 'apiCustom/etiquetador.php';
    const idproyecto=document.getElementById('idproyecto').value;
    const params={
      buscar:'seguimientos',
      idproyecto:idproyecto
    };
    let data; 
    try {
      data = await getData(url, params); 
      console.log("Seguimientos: ", data);
    } catch (error) {
      console.error("Error al cargar seguimientos:", error);
      data = [];
    }
    console.log("Seguimientos: ",data);
    llenarSelect('idseguimiento', data)
    await cargarVariables();
  }

  async function cargarVariables(){
    l("Cargar Variables");
    const url = 'apiCustom/etiquetador_2.php';
    //const idseguimiento=document.getElementById('idseguimiento').value;
    const params={
      buscar:'variables',
      //idseguimiento:idseguimiento
    };
    let data; 
    try {
      data = await getData(url, params); 
      console.log("Seguimientos: ", data);
    } catch (error) {
      console.error("Error al cargar seguimientos:", error);
      data = [];
    }
    console.log("Variables: ",data);
    llenarSelect('idvariable', data)
  }

  async function cargarValores(){
    l("Cargar vALORES");
    const url = 'apiCustom/etiquetador.php';
    const idvariable=document.getElementById('idvariable').value;
    const params={
      buscar:'valores',
      idvariable:idvariable
    };
    let data; 
    try {
      data = await getData(url, params); 
      console.log("Valores: ", data);
    } catch (error) {
      console.error("Error al cargar seguimientos:", error);
      data = [];
    }
    console.log("Valores: ",data);
    llenarSelect('idvalor', data)
  }

  function llenarSelect(id, data) {
    const select = document.getElementById(id); 
    select.innerHTML = '';
    const option = document.createElement('option'); 
    option.value = 0; 
    option.textContent = "Seleccione..."; 
    select.appendChild(option); 
    data.forEach(item => {
      const option = document.createElement('option'); 
      option.value = item.id; 
      option.textContent = item.descriptor; 
      select.appendChild(option); 
    });
  }

  async function cargarDatosNube(){
    l("Cargar Datos");
    const url = 'apiCustom/gDatos.php';
    l(url)
    const idvariable=document.getElementById('idvariable').value;
    const fechaInicial=document.getElementById('fechaInicial').value;
    const fechaFinal=document.getElementById('fechaFinal').value;
    const palabra=document.getElementById('palabra').value;
    const excepto=document.getElementById('excepto').value;
    const selectElement = document.getElementById('idvalor');
    const selectedOptions = Array.from(selectElement.selectedOptions).map(option => option.value);
    const valores = selectedOptions.join(','); 
    const params={
      buscar:'datosNube',
      idvariable:idvariable,
      fechaInicial:fechaInicial,
      fechaFinal:fechaFinal,
      palabra:palabra,
      excepto:excepto,
      idvalor:valores
    };
    let data; 
    console.log("PARAMS::::::",params);
    try {
     getData(url, params)
     .then(data => {
      console.log("Cuenta de Valores: ", data);
      generarGraficoBurbujas(data);
      return agregarDatosATabla(data); 
    })
     .then(() => {
      //const t = new DataTable('#tabla');
    })
     .catch(error => {
      data=[{descriptor:"No Definido",cuenta:1}];
      generarGrafico(data);;
      console.error("111  Error al cargar seguimientos:", error);
    });
   } catch (error) {
    console.error("222 Error al cargar seguimientos:", error);
    data = [];
  }
}

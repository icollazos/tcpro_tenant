// head.js
function l(x){
  console.log(x);
}
function agregarHead() {
    const head = document.head;

    // Meta tags
    const metaCharset = document.createElement('meta');
    metaCharset.setAttribute('charset', 'UTF-8');
    head.appendChild(metaCharset);

    const metaViewport = document.createElement('meta');
    metaViewport.name = 'viewport';
    metaViewport.content = 'initial-scale=1,maximum-scale=1,user-scalable=no';
    head.appendChild(metaViewport);

    // Title
    const title = document.createElement('title');
    title.textContent = 'TCPRO';
    head.appendChild(title);

    // jQuery
    const scriptJQuery = document.createElement('script');
    scriptJQuery.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js';
    head.appendChild(scriptJQuery);

    // Canonical link
    const linkCanonical = document.createElement('link');
    linkCanonical.rel = 'canonical';
    linkCanonical.href = 'https://getbootstrap.com/docs/5.1/examples/sidebars/';
    head.appendChild(linkCanonical);

    // Bootstrap CSS
    const linkBootstrapCSS = document.createElement('link');
    linkBootstrapCSS.href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css';
    linkBootstrapCSS.rel = 'stylesheet';
    linkBootstrapCSS.integrity = 'sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9';
    linkBootstrapCSS.crossOrigin = 'anonymous';
    head.appendChild(linkBootstrapCSS);

    // Bootstrap JS
    const scriptBootstrapJS = document.createElement('script');
    scriptBootstrapJS.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js';
    scriptBootstrapJS.integrity = 'sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm';
    scriptBootstrapJS.crossOrigin = 'anonymous';
    head.appendChild(scriptBootstrapJS);

    // Custom styles for placeholder images
    const stylePlaceholderImg = document.createElement('style');
    stylePlaceholderImg.textContent = `
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }
        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
    `;
    
    head.appendChild(stylePlaceholderImg);

    // Sidebars CSS and JS
    const linkSidebarsCSS = document.createElement('link');
    linkSidebarsCSS.href = 'bs/sidebars.css';
    linkSidebarsCSS.rel = 'stylesheet';
    
    const scriptSidebarsJS = document.createElement('script');
    scriptSidebarsJS.src = 'bs/sidebars.js';

    head.appendChild(linkSidebarsCSS);
    head.appendChild(scriptSidebarsJS);

   // Datetime CSS
   const linkDatetimeCSS = document.createElement('link');
   linkDatetimeCSS.href = 'bs/datetime.css'; 
   linkDatetimeCSS.rel = 'stylesheet';

   head.appendChild(linkDatetimeCSS);

   // Theme Lux CSS
   const linkThemeLuxCSS = document.createElement('link');
   linkThemeLuxCSS.rel = 'stylesheet';
   linkThemeLuxCSS.href = 'bs/css/theme_lux.css'; 
   head.appendChild(linkThemeLuxCSS);

   // Highcharts scripts
   const scriptHighcharts = document.createElement('script');
   scriptHighcharts.src = 'highcharts/code/highcharts.js';

   [scriptHighcharts].forEach(script => head.appendChild(script));


   /*
   const scriptHighcharts = document.createElement('script');
   scriptHighcharts.src = 'highcharts/code/highcharts.js';

   const scriptExporting = document.createElement('script');
   scriptExporting.src = 'highcharts/code/modules/exporting.js';

   const scriptOfflineExporting = document.createElement('script');
   scriptOfflineExporting.src = 'highcharts/code/modules/offline-exporting.js';

   const scriptExportData = document.createElement('script');
   scriptExportData.src = 'highcharts/code/modules/export-data.js';

   [scriptExporting, scriptOfflineExporting, scriptExportData].forEach(script => head.appendChild(script));
   */

   // Bootstrap Icons
   const linkIcons = document.createElement('link');
   linkIcons.rel = 'stylesheet';
   linkIcons.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css';
   
   head.appendChild(linkIcons);

   // DataTables CSS and JS
   const linkDataTablesCSS = document.createElement('link');
   linkDataTablesCSS.href = 'DataTables/datatables.min.css';
   linkDataTablesCSS.rel = 'stylesheet';

   const scriptDataTablesJS = document.createElement('script');
   scriptDataTablesJS.src = 'DataTables/datatables.min.js';

   const scriptButtonsPrintJS = document.createElement('script');
   scriptButtonsPrintJS.src ='https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js';

   head.appendChild(linkDataTablesCSS);
   head.appendChild(scriptDataTablesJS);
   head.appendChild(scriptButtonsPrintJS);
   /*
   */

   // Custom styles for scrollbar and other elements
   const styleCustomElements = document.createElement('style');
   styleCustomElements.textContent= `
       /* Works on Firefox */
       * {
           scrollbar-width: thin;
           scrollbar-color: grey white;
       }

       /* Works on Chrome, Edge, and Safari */
       *::-webkit-scrollbar {
           width: 5px;
       }

       *::-webkit-scrollbar-track {
           background: orange;
       }

       *::-webkit-scrollbar-thumb {
           background-color: white;
           border-radius: 2px;
           border: 3px solid white;
       }

       .chart {
           margin-bottom: 50px;
       }
       .scroll{
           overflow-y: scroll;
           max-height: 950px;
       }
       .rot{
           -webkit-transform: rotate(-90deg); 
           -moz-transform: rotate(-90deg);
           display: inline-block;
       }
       .danger, .btn-danger, .bg-danger{
           background-color: #C62828;
       }
       .warning, .btn-warning, .bg-warning{
           background-color: #F9A825;
       }
       .success, .btn-success, .bg-success{
           background-color: #2E7D32;
       }
       .info, .btn-info, .bg-info{
           background-color: #00838F;
       }
       .btn-datatable{
           margin-left: 5px;
           margin-right: 5px;
       }
       
       /* Radio button styles */
       .radio_button input[type="checkbox"]{
           width:0;
           height:0;
           visibility:hidden;
           margin-top: -5px;
       }

       .radio_button .switch_label{
           width:80px !important;
           height:30px !important;
           background-color:#03256C !important;
           background-color: #00838F !important;
           border-radius:100px;
           position:relative;
           cursor:pointer;
           transition:all 0.5s;
       }

       .radio_button label::after{
           content:"";
           position:absolute;
           width:23px;
           height:23px;
           border-radius:50%;
           top:3px;
           left:4px;
           background-color:#2541B2; 
           background-color:#dddddd; 
           transition:all 1s;  
       }

       .radio_button input:checked + label:after{
           left:calc(100% - 28px);
       }

       .radio_button label:active::after{
           width:100%;
       }
       
   `;
   
   head.appendChild(styleCustomElements);

   // Highcharts theme configuration
   const scriptHighchartsThemeConfig= document.createElement('script');
   scriptHighchartsThemeConfig.textContent= `
    Highcharts.theme = {
        colors: ['#1565C0', '#0277BD', '#00838F', '#00695C', '#2E7D32', '#558B2F', '#9E9D24', '#F9A825', '#FF8F00', '#EF6C00', '#D84315', '#4E342E', '#424242', '#37474F', '#D32F2F', '#AD1457', '#6A1B9A', '#4527A0', '#283593' ],
        chart: {
            backgroundColor: '#ffffff'
        },
        title: {
            style: {
                color: '#000',
                font: 'bold 24px "Arial", Verdana, sans-serif'
            }
        },
        subtitle: {
            style: {
                color: '#666666',
                font: 'bold 12px "Arial", Verdana, sans-serif'
            }
        },
        legend: {
            itemStyle: {
                font: '9pt "Arial", Verdana, sans-serif',
                color: 'black'
            },
            itemHoverStyle:{
                color: 'gray'
            }
        }
    };
    Highcharts.setOptions(Highcharts.theme);
`;
   
//head.appendChild(scriptHighchartsThemeConfig);
}

// Llamar a la función para agregar el contenido al head
agregarHead();

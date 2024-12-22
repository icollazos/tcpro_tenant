<!DOCTYPE html>
<html>
<head>

	<meta charset='utf-8' />
	<title>TCPRO</title>
	<meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

	<!--
	<script src="https://code.jquery.com/jquery-3.5.1.js"/>
-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>


<link rel="canonical" href="https://getbootstrap.com/docs/5.1/examples/sidebars/">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
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
</style>

<link href="../bs/sidebars.css" rel="stylesheet">

<script src="../bs/sidebars.js"></script>

	<!--
	<link href="bs/css/bootstrap.min.css" rel="stylesheet">
	<script src="bs/js/bootstrap.min.js"></script>
-->


	<!--
<script type="text/javascript" src="../bs/moment.js"></script>
<script type="text/javascript" src="../bs/datetime.js"></script>

	<link rel="stylesheet" href="bootstrap_sandstone.css" />
	<link rel="stylesheet" href="bootstrap_lumen.css" />
	<link rel="stylesheet" href="bootstrap_cosmo.css" />
-->


<link rel="stylesheet" href="../bs/datetime.css" />
<!--<link rel="stylesheet" href="bootstrap_cerulean.css" />-->
<? 
$b=9;
$preferidos=array(4,9,10,15);
$a=explode(',', 'cerulean,cosmo,cyborg,darkly,flatly,journal,lumen,paper,readable,sandstone,simplex,slate,spacelab,superhero,united,yeti');
?>
<link rel="stylesheet" href='../bs/css/theme_lux.css' />
<!--
<link rel="stylesheet" href='bs/css/theme_lux.css' />
-->
<!--
	<link rel="stylesheet" href='https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/sandstone/bootstrap.min.css' />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.1/cosmo/bootstrap.min.css" integrity="sha512-JgCD7Z6KLhjvj7BHfCVHejM/9bpZfsmS4WYXpgv3A8huU56TAiCfona3AZ9KSMS2mOIUG8rqp3X+PYSTVqbUgQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.1/cerulean/bootstrap.rtl.min.css" integrity="sha512-8dZ7f6kl/XBuITppOjUspNxDEeYrGCAUowgZQJIYIgUWzvPIiXfjshs4RSW060af+EnRZPoyS5WzDzozVQecIA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.1/cosmo/bootstrap.min.css" integrity="sha512-JgCD7Z6KLhjvj7BHfCVHejM/9bpZfsmS4WYXpgv3A8huU56TAiCfona3AZ9KSMS2mOIUG8rqp3X+PYSTVqbUgQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
-->
<!--
	<link rel="stylesheet" href='https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.2.1/<?echo $a[$b];?>/bootstrap.min.css' />
-->

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<!-- optional -->
<script src="https://code.highcharts.com/modules/offline-exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<!--
<link href="https://cdn.datatables.net/v/bs5/dt-1.13.6/af-2.6.0/b-2.4.1/b-colvis-2.4.1/b-html5-2.4.1/cr-1.7.0/datatables.min.css" rel="stylesheet">
-->

<!--

<link href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css" rel="stylesheet"/>
<script src="https://cdn.datatables.net/v/bs5/dt-1.13.6/af-2.6.0/b-2.4.1/b-colvis-2.4.1/b-html5-2.4.1/cr-1.7.0/datatables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/state/1.1.2/js/dataTables.stateSave.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
-->
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>



<link href="../DataTables/datatables.min.css" rel="stylesheet">
 
<script src="../DataTables/datatables.min.js"></script>



<style type="text/css">
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

</style>





<script>

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
</script>

<style type="text/css">
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

.radio_button input:checked  + label:after{
    left:calc(100% - 28px);
}

.radio_button label:active::after{
    width:100%;
}
</style>
</head>


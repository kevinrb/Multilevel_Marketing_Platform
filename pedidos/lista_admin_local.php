<?php
	$ttime=microtime(true);
	require("../sec.php");
	
	
	$ae=array("1"=>"Pedido Aprobado","2"=>"Pedido Cancelado");
	$ms="";
	if(isset($_GET["e"]))
	{ $ms=$ae[$_GET["e"]]; }
	
	
	$res=qry("select a.idop,a.hora,a.local,a.localdst,a.login,a.estado,a.tipo,a.total,b.puntos,a.idpadre2,if(b.tipo=3,'AUTOCONSUMO',if(b.tipo=5,'COMPRA ACUMU.','')),b.fecha_venc from operacionesp a left join puntos b on a.idop=b.idop and b.tipo in (3,5) where b.login='$Xlogin' and a.estado between 104 and 510 and a.estado not between 200 and 299 and a.tipo in ('PE','DV') AND idpadre2>0 order by a.hora desc;");
	$html ="";
	$html2="";
	$estados=array("104"=>"Pendiente Aprobacion","105"=>"Pendiente Aprobacion","106"=>"Aprobado Por Destino","504"=>"Pendiente","505"=>"Pendiente Aprobacion","506"=>"Aceptado");
	while($temp=mysql_fetch_row($res))
	{
		if($temp[5]=="107")
		{
			$dst="admin_local_detalle.php";
			$tte="";
			if($temp[11]!=""){
				$tte="(DEL $temp[11])";
			}
			$html .= "<tr inf='$temp[0]'><td><a href='#' inf='$temp[0]'>$temp[9]</a></td><td>$temp[1]</td><td>$temp[10] $tte</td><td>$temp[2]</td><td>$temp[8]</td><td>$temp[7]</td><td><button inf='$temp[0]' tipo='$temp[7]' class='btn btn-success btn-sm dp'>Detalles</button></td></tr>"; 
		}
		else
		{
			$dst="detalle_pedido_admin_local.php";
			$html2 .= "<tr inf='$temp[0]'><td><a href='#' inf='$temp[0]'>$temp[9]</a></td><td>$temp[1]</td><td>$temp[10]</td><td>$temp[2]</td><td>".$estados[$temp[5]]."</td><td>$temp[8]</td><td>$temp[7]</td><td><button inf='$temp[0]' tipo='$temp[7]' class='btn btn-success btn-sm dp'>Detalles</button></td></tr>"; 
		}
	}
	
	
	
	
	
	$res=qry("select local,nombre from locales where grupolocal=(select grupolocal from locales where local='$Xlocal');");
	$loc="<option selected disabled>--Elija--</option>";
	if(mysql_num_rows($res)>0)
	{ while($temp=mysql_fetch_row($res)){ $loc .= "<option value='$temp[0]'>$temp[1]</option>"; } }
	
?>
<!doctype html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Venta Productos</title>
		<link href="../css/bootstrap.min.css" rel="stylesheet">
		<link href="../css/dataTables.bootstrap.css" rel="stylesheet">
		<script src="../js/jquery-1.10.2.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/jquery.dataTables.js"></script>
		<script src="../js/dataTables.bootstrap.js"></script>
		<link href="../sb/font-awesome/css/font-awesome.css" rel="stylesheet">
		<link href="../sb/css/sb-admin.css" rel="stylesheet">
		<script>
			
			var ms="<?php echo $ms;?>";
			$(window).load(function(){
				if(ms!=""){$(".container").prepend("<div style='top:2px;' class='text-center alert alert-warning'><b>"+ms+"</b></div>");}
				setTimeout(function(){$(".alert").fadeOut();},3000);
				///////////////////////////////////////////MAKING A REAL TABLE RESPONSIVE ///////////////////////////////////////
				if (screen.width < 500){$('.datatab').parent().css({ overflowX: 'scroll'});} 
				window.onresize = resize;
				function resize(){
					var w1=$('.datatab').parent().width(); var w2=$('.datatab').width();
					if(w1<w2){$('.datatab').parent().css({ overflowX: 'scroll'});}
					else if(w1>=w2){$('.datatab').parent().removeAttr("style");}
				}
				var w1=$('.datatab').parent().width(); var w2=$('.datatab').width();
				if(w1<w2){$('.datatab').parent().css({ overflowX: 'scroll'});}
				else if(w1==w2){$('.datatab').parent().removeAttr("style");}
				//$('#datatab').dataTable();
				$('.datatab').dataTable({"order": [[ 0, "desc" ]],"oLanguage": {"sUrl": "../css/spanish.txt"}});
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				$("#filtra").click(function(){
					$.post("../res/productos/admin_local.php",{a:"fd",l:$("#loc").val(),hi:$("#hi").val(),hf:$("#hf").val()},function(data){$("#tabdata").html(data);});
				});	 
				$(".dp").click(function(){
					$.post("../res/pedidos/venta_canales.php",{a:"detalle_pedido",idop:$(this).attr("inf")},function(data){$("#cm").html(data); $("#myModal").modal();});
				});
				$("body").on("click",".cancel",function(){
					var te=$(this).data("idop");
					$.post("../res/pedidos/venta_canales.php",{a:"anular_ped",idop:te},function(data){
						if(data==1){
							alert("PEDIDO ANULADO!");
							location.reload();
							}else{
							alert("NO SE PUDO ANULAR.");
						}
						
					});
				});
				
				
			});
		</script>
		<style>
			.nomargin{
			padding-left:0px;
			padding-right:0px;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="panel panel-success">
					<div class="panel-heading">Lista de Pedidos en Proceso</div> 
					<div class="panel-body">
						<table class="table table-bordered table-hover datatab">
							<thead><tr><th>IDOP</th><th>HORA</th><th>TIPO</th><th>ALMACEN</th><th>ESTADO</th><th>PUNTOS</th><th>A PAGAR</th><th>Detalles</th></tr></thead>
							<tbody><?php echo $html2;?></tbody>
						</table>
					</div> 
				</div>
			</div>
			<div class="row">
				<div class="panel panel-success">
					<div class="panel-heading">Lista de Pedidos Terminados</div> 
					<div class="panel-body">
						
						<table class="table table-bordered table-hover datatab" >
							<thead><tr><th>IDOP</th><th>HORA</th><th>TIPO</th><th>ALMACEN</th><th>PUNTOS</th><th>PAGADO</th><th>Detalles</th></tr></thead>
							<tbody id='tabdata'><?php echo $html;?></tbody>
						</table>
						
					</div> 
				</div>
			</div>
			
		</div>
		
		
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Detalle Pedido</h4>
					</div>
					<div id='cm' class="modal-body"></div>
				</div>
			</div>
		</div>
	</body>
</html>
<?php //echo microtime(true)-$ttime;
	$ttro= microtime(true)-$ttime;
	$ttt=$ttro;
	echo $ttt;
?>
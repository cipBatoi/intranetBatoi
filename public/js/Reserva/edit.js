/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

const maxDiasReserva=30;
const esDireccion=2;

$(function() {
    var token = $("#_token").text();
    var errores = [];
	$(".errores").empty();
	$("#recurso").focus();
	$("#gestion").hide();	// Ocultamos los inputs de añadir

    //datepickers ui
//    $('#dia').datepicker();
	var maxFecha=new Date();
	$('#dia').attr('minDate',maxFecha.toISOString().substr(0,10));
	if ($('#rol').text()%esDireccion == 0) {
//	    $('#dia_fin').datepicker();
//	    datepickerCastellano();
		$('#dia').removeAttr('max');
		$("#periodica").show();
	} else {
//	    datepickerCastellano(0, maxDiasReserva);
		var maxFecha=new Date();
		maxFecha.setDate(maxFecha.getDate()+maxDiasReserva);
		$('#dia').attr('max',maxFecha.toISOString().substr(0,10));
		$("#periodica").hide();
	}

	$("#recurso").on("change", function () {
		if ($("#recurso").val()!=0) {	// Hay un recurso seleccionado
			$("#gestion").show();	// Ocultamos los inputs de añadir
			if ($("#dia").val()) {		// Si hay fecha se están iendo las reservas de ese día y hay que cambiarlas
				$("#dia").trigger('change');	
			}
		} else {
			$("#gestion").hide();	// Ocultamos los inputs de añadir
		}
	});

	$("#dia").on("dp.change", function () {
		// borramos los datos actuales
		$("#horario").find("td").removeClass("warning").html("Libre");

		var arrFecha = $("#dia").val().split("-");
		var fecha=arrFecha[2]+"-"+arrFecha[1]+"-"+arrFecha[0];
		// Ponemos el día en la fecha de fin de reserva para dirección
		var nomDias=["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
		var queFecha=new Date(fecha);
		$("#nom_dia_fin").html(nomDias[queFecha.getDay()]);
		// pedimos las reservas del recurso para el día seleccionado
		$.ajax ({
	    	url: "api/reserva/idEspacio="+$("#recurso").val()+"&dia="+fecha,
	    	type: "GET",
	    	dataType: "json",
            data: {api_token: token},
		}).then(function(res){
			for (i in res.data) {
				$("#hora-"+res.data[i].hora).addClass("warning").html(res.data[i].nomProfe+' ('+res.data[i].observaciones+')<span class="hidden idProfe">'+res.data[i].idProfesor+'</span>'+'<span class="hidden idReserva">'+res.data[i].id+'</span>');
			}
		}, function(error){
			showMessage(["Error "+error.status+": "+error.statusText, "error"], 'error');
		})
	});

	$("#desde").on("change", function () {
		var indice = $("#desde option:selected").index()
		$("#hasta").val( $("#desde").val() );
		$("#hasta").children().slice(0, indice).attr("disabled","disabled");
		$("#hasta").children().slice(indice, $("#desde option").length).removeAttr("disabled");
		if ($("#desde").val()>0) {	// Hay un recurso seleccionado
			$("#hasta").val( $("#desde").val() );
		} else {
			$("#desde").val(0);
		}
	});

	$("#reservar").on("click", function (ev) {
	    errores.length=0;
		ev.preventDefault();
		if (checkData()) {
			// Antes de reservar comprobamos que todas las horas estén libres
			$("#horario td").slice($("#desde").val()-1, $("#hasta").val()).each(function() {
					if ($(this).text()!="Libre")
						errores.push("En el intervalo indicado no está libre la hora "+$(this).prev().text());
				});
			if (errores.length==0) 
				modDatos("reserva");
			else
				showMessage(errores, 'error');
		}
	});

	$("#liberar").on("click", function (ev) {
	    errores.length=0;
		ev.preventDefault();
		if (checkData()) {
			// Antes de liberar comprobamos que todas las horas las haya reservado este profe o que sea de dirección
			if ($('#rol')%esDireccion == 0) {
				// Si es direccion se libera y punto
				modDatos("libera");
			} else {
				$("#horario td").slice($("#desde").val()-1, $("#hasta").val()).each(function() {
					if ($(this).text()!="Libre" && $(this).find('span.idProfe').text()!=$('#dni').text())
						errores.push("En el intervalo indicado la hora "+$(this).prev().text()+" está reservada por "+$(this).text() );
					});
				if (errores.length==0) 
					modDatos("libera");
				else
					showMessage(errores, 'error');
			}
		}
	});

});

function checkData() {
    var errores = [];

    // recurso
    if ($("#recurso").val()=="0") {
        errores.push("el campo 'Recurso' debe estar seleccionado");
	}
    if ($("#observaciones").val().length>20){
        errores.push("el campo 'Observaciones' tiene un máximo de 20 caracteres");
	}

    // dia
    var fecha = $("#dia").val();
    if (fecha=="") {
        errores.push("el campo 'Dia' debe estar seleccionado");
	}

    // desde
	var desde = parseInt( $("#desde").val() );
    if (desde==0) {
        errores.push("el campo 'Desde hora' debe estar seleccionado");
	}

    // hasta
	var hasta=parseInt($("#hasta").val());
    if (hasta==0) {
        errores.push("el campo 'hasta hora' debe estar seleccionado");
	} else if (hasta < desde)
        errores.push("el campo 'hasta hora' debe ser mayor que 'desde hora'");

    // dia_fin
    var fecha_fin = $("#dia_fin").val();
    if (fecha_fin!="")
//    {
//        errores.push("el campo 'Hasta Dia' debe tener formato dd/mm/aaaa");
//	} else 
		if (fecha_fin < fecha) {
        	errores.push("el campo 'hasta día' debe ser mayor que 'día'");		
		}
	if (errores.length>0) {
		showMessage(errores,'error');
		return false;
	} else {
		return true;
	}
}

function modDatos(accion) {
	var arrFecha = $("#dia").val().split("-");
	var fecha=arrFecha[2]+"-"+arrFecha[1]+"-"+arrFecha[0];
	var fechaDate=new Date(fecha);
    var respuestas=[];	// donde guardo el nº de hora si es una reserva o la id si es liberar
    var peticiones=[];	// donde guardo las respuestas para saber si ya ha acabado el proceso

	if (accion=="reserva") {
		var msg="Se va a reservar el recurso entre las fechas indicadas. Si hubiera alguna hora ya reservada fallará la reserva. ¿Deseas continuar?";
	} else {
		var msg="Se van a liberar todas las reservas del recurso entre las fechas indicadas ¿Deseas continuar?";
	}
	if (!confirm(msg))
		return false;

	if (accion=="reserva") {
		var tipo="POST";
		var url="api/reserva?api_token="+$("#_token").text();
		var datos={
				idEspacio: $("#recurso").val(), 
//				dia: fecha, 
				idProfesor: $('#dni').text(),
                                observaciones: $('#observaciones').val(),
				api_token: $("#_token").text()
			};
//		datos.dia_fin=$("#dia_fin").val();
		if ($("#dia_fin").val()) {
			var arrFecha = $("#dia_fin").val().split("-");
			var fechaFin=arrFecha[2]+"-"+arrFecha[1]+"-"+arrFecha[0];
			var fechaFinDate=new Date(fechaFin);
		} else {
			var fechaFinDate=new Date(fecha);			
		}
		while (fechaDate<=fechaFinDate) {
		    for (var i=Number($("#desde").val()); i<=Number($("#hasta").val()); i++) {
		    	peticiones.push({fecha: fecha, hora: i});
		    }
			fechaDate.setDate(fechaDate.getDate()+7);
			fecha=getFechaInt(fechaDate);
		}
	} else {
		var tipo="DELETE";
		var datos={
				api_token: $("#_token").text()
			};
	    for (var i=Number($("#desde").val()); i<=Number($("#hasta").val()); i++) {
	    	if ($('#hora-'+i).find('span.idReserva').text()) {
	    		peticiones.push({fecha: fecha, hora: $('#hora-'+i).find('span.idReserva').text()});
	    	}
	    }
	}
    
    for (var peticion of peticiones) {
    	datos.dia=peticion.fecha;
    	if (accion==='reserva') {
			datos.hora=peticion.hora;
    	} else {
			var url="api/reserva/"+peticion.hora;    		
    	}
		$.ajax ({
		   	url: url,
		   	type: tipo,
		   	dataType: "json",
		   	data: datos
		}).then(function(res){
			console.log(res);
			respuestas.push('ok');
		}).fail(function(err) {
			console.error(err);
			respuestas.push(err);
		}).always(function(res) {
			if (respuestas.length==peticiones.length) {
				// Ya han acabado todas
				if (respuestas.some(resp => resp!='ok')) {
					// Ha habido algún error
					showMessage(["Algunas horas no se han podido "+accion+"r"], 'error');
				} else {
					showMessage(["El recurso se ha "+accion+"do correctamente"], 'ok');
				}
				$("#dia").trigger('change');
			}
		});
    }
}

function showMessage(msgs, tipo) {
	var $msg=$(`
		<div id="msg" class="alert `+(tipo=='error'?`alert-danger `:`alert-success `)+`alert-block fade in">
			<button type="button" class="close" data-dismiss="alert">×</button></div>`).appendTo($('.x_content').last());
	msgs.forEach(function(msg) {
		$msg.append(`<p><strong>`+msg+`</strong></p>`);
	});
}

function getFechaInt(fechaDate) {
	var mes='0'+(fechaDate.getMonth()+1);
	mes=mes.substr(mes.length-2,2);
	var dia='0'+fechaDate.getDate();
	dia=dia.substr(dia.length-2,2);
	return fechaDate.getFullYear()+'-'+mes+'-'+dia;
}
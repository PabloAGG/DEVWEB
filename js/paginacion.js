/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/JavaScript.js to edit this template
 */
var pagina_actual=1;
var limite_publicaciones=10;
var contador_publicaciones = 0;
var numPaginas = 0;
var publicacionInicial = 0;

$( document ).ready(function() {

contadorPaginas();
ocultarPublicaciones();
mostrarPublicaciones();
agregarPaginas();

});

function ocultarPublicaciones(){
      
$("#listaPublicaciones li").each(function(){
      $(this).attr("hidden",true);  
           });

}

function contadorPaginas(){
   $("#listaPublicaciones li").each(function(){
    
    contador_publicaciones++;
         
    });
      numPaginas = Math.ceil(contador_publicaciones/limite_publicaciones);
}

function mostrarPublicaciones(){
for(var i=0;i<10;i++){
    $("#listaPublicaciones li").eq(publicacionInicial).attr("hidden",false);
   
  
    publicacionInicial++;
    }
}

function agregarPaginas(){
  
   for(var i=0;i<numPaginas;i++){
       var pagina = i+1;
       var itemList = "";
       
       if(i==0){
            itemList += "<li id='idAnterior' class='page-item disabled'><a class='page-link' onclick='paginaAnterior()'>Anterior</a></li>";      
            itemList += "<li class='page-item active'><a class='page-link' onclick='asignarPublicacionInicial("+pagina+")'>"+pagina+"</a></li>";
      
       }else{
            itemList += "<li class='page-item'><a class='page-link' onclick='asignarPublicacionInicial("+pagina+")'>"+pagina+"</a></li>";
     
           if(pagina==numPaginas){
            itemList += "<li id='idSiguiente' class='page-item'><a class='page-link' onclick='paginaSiguiente()'>Siguiente</a></li>";       
       }
       
       }
       $("#paginacionPublicaciones").append(itemList); 
   }

}

function asignarPublicacionInicial(pag){
   
   publicacionInicial = ((limite_publicaciones*pag)-limite_publicaciones);
   ocultarPublicaciones();
   mostrarPublicaciones();
   
   $("#paginacionPublicaciones li").each(function(){
    
      $(this).removeClass("active");
        
           });
               
   $("#paginacionPublicaciones li").eq(pag).addClass("active");
   
       pagina_actual=pag;
   
       if(pagina_actual>1){
            $("#idAnterior").removeClass("disabled"); 
       }else{
            $("#idAnterior").addClass("disabled"); 
       }
       
       if(pagina_actual<numPaginas){
               $("#idSiguiente").removeClass("disabled");
       }else{
           $("#idSiguiente").addClass("disabled");
       }
       
}


function paginaAnterior(){
   
   asignarPublicacionInicial(pagina_actual-1);
}

function paginaSiguiente(){
   
   asignarPublicacionInicial(pagina_actual+1);
}
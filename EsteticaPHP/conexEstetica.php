<?php
function conectarDB(){
    $conex=mysqli_connect("localhost","root","","esteticadb");
    return $conex;
}
?>
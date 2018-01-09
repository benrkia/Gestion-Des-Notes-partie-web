<?php

    /*
    *this is the main database connection file
    */

    $username = 'root';
    $password = 'root';

    //setup the server host and the database name
    $dsn = 'mysql:host=localhost;dbname=gestiondesnotes';

    $PDOOptions = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', 
    );
    
    try{
        
        //Start the connection using PDO class
        $connect = new PDO($dsn, $username, $password, $PDOOptions);
        
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }catch(PDOException $e){
        echo '<div style="background: #d49393; border: 1px solid #a90000; padding: 15px; color: #a90000;">error in connection : '.$e->getMessage().'</div>';
		exit();
    }
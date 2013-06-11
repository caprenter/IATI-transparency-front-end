<?php
require('settings.php');
$publishers = array();
$tests = array();

if (isset($_GET['id'])) { //DON'T TRUST THIS VALUE!!!
  $id = filter_var($_GET["id"],FILTER_SANITIZE_NUMBER_INT);
  //Now get a list of valid id's from the database
  //Might as well match them with publishers
  try {
        $conn = new PDO('mysql:host=' . $host . ';dbname=' . $dbname, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        //Get a usable array of Publishers
        $stmt = $conn->prepare('SELECT id,`registry_name` FROM publishers ORDER BY `registry_name`');
        $stmt->execute();
     
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
     
        if ( count($result) ) { 
          foreach($result as $row) {
            $publishers[$row->id] = $row->{"registry_name"};
           // echo $row->{"registry_name"} . "<br/>";
          }   
        } else { //We failed to get a list of ids so return to home page
          header('Location: ' . $domain);
          //echo "id fail";
        }
    } catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
  if (!in_array($id,array_keys($publishers))) { //id supplied is not in database so return to home page
     header('Location: ' . $domain);
     //echo "id match fail";
  }
}



$html ='<table class="table-fixed-header" border ="1">';
try {
    $conn = new PDO('mysql:host=' . $host . ';dbname=' . $dbname, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    //Get a usable array of Tests
    $stmt = $conn->prepare('SELECT test_id, title FROM tests ORDER BY test_id');
    $stmt->execute();
 
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
 
    if ( count($result) ) { 
      
      foreach($result as $row) {
        $tests[$row->{"test_id"}] = $row->title;
       // echo $row->{"registry_name"} . "<br/>";
      }   
    } else {
      echo "No rows returned.";
    }
    ksort($tests);
    
    $html .=  '<thead class="header"><tr><td>Date</td>';
    foreach ($tests as $key => $value) {
      $html .=  '<td><a href="#" class="titles" data-toggle="tooltip" title="' .$value .'">' . $key . '</td>';
    }
    $html .=  "</tr></thead><tbody>";
    //Get test results
    
    
    
    $query = "SELECT publishers.registry_name, results.test_id, results.date, results.result FROM results LEFT JOIN  publishers ON results.publisher_id = publishers.id WHERE publishers.id = (:id) ORDER BY date,test_id";

# Prepare the query ONCE
  $stmt = $conn->prepare($query);
  $stmt->execute(array('id' => $id));
  $result = $stmt->fetchAll();
    //<tr><td><a href="publisher.php?id=' . $id . '">' . $publisher . '</a></td>
    //print_r($result); //die;
    $i = 0;
    $html .="<tr>";
    $date = NULL;
    foreach($result as $row) {
      //echo $row["result"] . "<br/>";
      if ($date !== NULL && $date !==$row["date"]) {
        $i=0;
        $html .= "</tr>";
      }  
      if ($row["result"] != NULL) {
        if ($i==0) {
          $html .="<td>" . date("Y-m-d",strtotime($row["date"])) . "</td>";
          $date = $row["date"];
        }
        $i++;
        $html .= "<td>" . $row["result"] . "</td>";
      } else {
        $html .= "<td> </td>";
      }
      //$html .= "<td>" . $row["publishers.registry_name"] . "</td>"
      //$html .= "<td></td>"
      //print_r($row);
    }   
    $html .= "</tr>";
  
  $html .= "</tbody></table>";
  /*# Second insertion
  $id = 2;
  $result = $stmt->execute();
  foreach($result as $row) {
        echo $row["result"] . "<br/>";
        //$html .= "<td>" . $row["publishers.registry_name"] . "</td>"
        //$html .= "<td></td>"
        //print_r($row);
      }   
/*
    $stmt = $conn->prepare($query);
    $stmt->execute();
 
    $result = $stmt->fetchAll();
 
    if ( count($result) ) { 
      foreach($result as $row) {
        echo $row["result"] . "<br/>";
        //$html .= "<td>" . $row["publishers.registry_name"] . "</td>"
        //$html .= "<td></td>"
        //print_r($row);
      }   
    } else {
      echo "No rows returned.";
    }
    /*$stmt = $conn->prepare('SELECT id,`registry_name` FROM publishers ORDER BY `registry_name`');
    $stmt->execute();
 
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
 
    if ( count($result) ) { 
      foreach($result as $row) {
        $publishers[$row->id] = $row->{"registry_name"};
        echo $row->{"registry_name"} . "<br/>";
      }   
    } else {
      echo "No rows returned.";
    }*/
    
    $stmt = $conn->prepare('SELECT id,`test_id`, title FROM tests ORDER BY test_id');
    $stmt->execute();
 
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
 
    if ( count($result) ) { 
      foreach($result as $row) {
        $tests[$row->id] = array($row->{"test_id"},$row->title);
        //echo $row->title . "<br/>";
      }   
    } else {
      echo "No rows returned.";
    }
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
//print_r($publishers);
?>
<html>
  <head>
    <title>Bootstrap 101 Template</title>
    <!-- Bootstrap -->
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
     <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
      table {
        margin-top:40px;
      }

      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
      table .header-fixed {
  position: fixed;
  width:100%;
  top: 40px;
  z-index: 1020; /* 10 less than .navbar-fixed to prevent any overlap */
  border-bottom: 1px solid #d5d5d5;
  -webkit-border-radius: 0;
     -moz-border-radius: 0;
          border-radius: 0;
  -webkit-box-shadow: inset 0 1px 0 #fff, 0 1px 5px rgba(0,0,0,.1);
     -moz-box-shadow: inset 0 1px 0 #fff, 0 1px 5px rgba(0,0,0,.1);
          box-shadow: inset 0 1px 0 #fff, 0 1px 5px rgba(0,0,0,.1);
  filter: progid:DXImageTransform.Microsoft.gradient(enabled=false); /* IE6-9 */
}
.header-fixed td {
  width:20px;
}
    </style>
    <!-- Project
    <link href="public/css/application.css" rel="stylesheet">
     -->
     <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->
  </head>
   <body>
     
     


    <div class="navbar navbar-inverse navbar-fixed-top">
      <?php include("theme/navbar.php"); ?>
    </div>

    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span2">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">Sidebar</li>
              <li class="active"><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li class="nav-header">Sidebar</li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li class="nav-header">Sidebar</li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
              <li><a href="#">Link</a></li>
            </ul>
          </div><!--/.well -->
        </div><!--/span-->
        <div class="span10">
          <div class="hero-unit">
            <?php
            //If we have got this far then $id is set so no need to check
            echo "<h2>" . $publishers[$id] . "</h2>";
            echo $html;
            ?>
          </div>
          <div class="row-fluid">
            <div class="span4">
              <h2>Heading</h2>
              <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
              <p><a class="btn" href="#">View details &raquo;</a></p>
            </div><!--/span-->
            <div class="span4">
              <h2>Heading</h2>
              <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
              <p><a class="btn" href="#">View details &raquo;</a></p>
            </div><!--/span-->
            <div class="span4">
              <h2>Heading</h2>
              <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
              <p><a class="btn" href="#">View details &raquo;</a></p>
            </div><!--/span-->
          </div><!--/row-->
          <div class="row-fluid">
            <div class="span4">
              <h2>Heading</h2>
              <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
              <p><a class="btn" href="#">View details &raquo;</a></p>
            </div><!--/span-->
            <div class="span4">
              <h2>Heading</h2>
              <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
              <p><a class="btn" href="#">View details &raquo;</a></p>
            </div><!--/span-->
            <div class="span4">
              <h2>Heading</h2>
              <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
              <p><a class="btn" href="#">View details &raquo;</a></p>
            </div><!--/span-->
          </div><!--/row-->
        </div><!--/span-->
      </div><!--/row-->

      <hr>

      <footer>
        <p>&copy; Company 2013</p>
      </footer>

    </div><!--/.fluid-container-->

    <!-- Bootstrap -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
    <script src="public/js/table-fixed-header.js"></script>
    <script src="public/js/bootstrap.min.js"></script>
    <script>
      $(function () { 
        $(".titles").tooltip();
        }) 
      </script>
<script language="javascript" type="text/javascript" >
    $(document).ready(function(){

 
      // make the header fixed on scroll
      $('.table-fixed-header').fixedHeader();
    });
  </script>
    <!-- Project 
    <script src="public/js/application.js"></script>
    -->
  </body>
</html>

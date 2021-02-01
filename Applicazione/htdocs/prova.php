<html>
  <head>
    <link href="css/bootstrap.min.css" rel="stylesheet"/>
    <script src="js/bootstrap.min.js"></script>
  </head>
  <body>
 
  <?php
  /* ARC2 static class inclusion */ 
  include_once('semsol/ARC2.php');  
 
  /* $dbpconfig = array(
  "remote_store_endpoint" => "http://dbpedia.org/sparql",
   );
    */

   $dbpconfig = array(
  "remote_store_endpoint" => "http://192.168.184.1:7200/repositories/JobSearchOntology",
   );
 
  $store = ARC2::getRemoteStore($dbpconfig); 
 
  if ($errs = $store->getErrors()) {
     echo "<h1>getRemoteSotre error<h1>" ;
  }
 


  $query = '
  PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
  PREFIX foaf: <http://xmlns.com/foaf/0.1/>
  PREFIX frapo: <http://purl.org/cerif/frapo/>

  SELECT ?candidato ?ls
  WHERE {
    ?candidato :possiede ?cv.
    ?cv :contiene ?campi_cv.
    ?campi_cv :haCampo ?ls.
    ?cv rdf:type :CV.
    ?campi_cv rdf:type :Campi_CV.
    ?ls rdf:type :LaureaSpecialistica.
  }
  
  ';


  /* execute the query */
  $rows = $store->query($query, 'rows'); 
 
    if ($errs = $store->getErrors()) {
       echo "Query errors" ;
       print_r($errs);
    }
 

  /* display the results in an HTML table */
  echo "<table border='1'>
  <thead>
      <th>id</th>
      <th>candidato</th>
      <th>ls</th>
  </thead>";

  /* loop for each returned row */
  foreach( $rows as $row ) { 
  print "<tr><td>".++$id. "</td>
  <td><a href='". $row['id'] . "'>" . 
  $row['candidato']."</a></td><td>" . 
  $row['ls']. "</td></tr>";
  }
  echo "</table>" 

  ?>

  </body>
</html>
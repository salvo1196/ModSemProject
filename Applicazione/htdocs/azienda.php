<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ModSem con Bootstrap</title>
    <!-- Librerie di Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/ X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

    <title>Azienda</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Style the header */
        header {
            background-color: #666;
            padding: 30px;
            text-align: center;
            font-size: 35px;
            color: white;
        }

        /* Create two columns/boxes that floats next to each other */
        nav {
            float: left;
            width: 30%;
            height: 300px; /* only for demonstration, should be removed */
            background: #ccc;
            padding: 20px;
        }

        /* Style the list inside the menu */
        nav ul {
            list-style-type: none;
            padding: 0;
        }

        article {
            float: left;
            padding: 20px;
            width: 70%;
            background-color: #f1f1f1;
            height: 300px; /* only for demonstration, should be removed */
        }

        /* Clear floats after the columns */
        section::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Style the footer */
        footer {
            background-color: #777;
            padding: 10px;
            text-align: center;
            color: white;
        }


        /* Responsive layout - makes the two columns/boxes stack on top of each other instead of next to each other, on small screens */
        @media (max-width: 600px) {
            nav, article {
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>

<h2>Azienda cerca lavoratore</h2>
<p>Recluta qualcuno nella tua azienda e rendilo tuo schiavo</p>

<header>
    <h4>Scegli i parametri necessari ha trovare il tuo candidato ideale</h4>
</header>

<section>
    <nav>
        <?php
        $sector= isset($_POST['settore']) ? $_POST['settore'] : false;
        $university= isset($_POST['studiUniversitari']) ? $_POST['studiUniversitari'] : false;
        $certificate= isset($_POST['certificati']) ? $_POST['certificati'] : false;
        $selected_radio = $_POST['nome'];




        if ($sector && $university && $certificate && $selected_radio) {
            $settore_v = htmlentities($_POST['settore'], ENT_QUOTES, "UTF-8");
            $university_v = htmlentities($_POST['studiUniversitari'], ENT_QUOTES, "UTF-8");
            $certificate_v = htmlentities($_POST['certificati'], ENT_QUOTES, "UTF-8");

        }
        else {
            echo "task option is required";
            exit;
        }
        ?>

        <label >Settore:</label> <?php echo $settore_v?>
        <br>
        <label> Mostrare nome del settore: </label> <?php echo $selected_radio ?>
        <br>
        <label>Studi universitari:</label> <?php echo $university?>
        <br>
        <label>Certificati Linguistici:</label> <?php echo $certificate_v?>

    </nav>



    <article>
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


        switch ($university) {
            case 'triennale':
                $livelloStudi = 'LaureaTriennale'; //valore utilizzato  nella query
                $livelloStudi_h = 'Laurea Triennale'; //header tabella
                break;

            case 'magistrale':
                $livelloStudi = 'LaureaMagistrale';
                $livelloStudi_h = 'Laurea Magistrale';
                break;
            case 'specialistica':
                $livelloStudi = 'LaureaSpecialistica';
                $livelloStudi_h = 'Laurea Specialistica';
                break;
            case 'dottorato':
                $livelloStudi = 'Dottorato';
                $livelloStudi_h = 'Dottorato';

            default :
                $livelloStudi ='-';
                $livelloStudi_h = '-';;
        }


        $query1 = "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
        
          SELECT ?candidato ?ls ?nomeTitoloDiStudi
          WHERE {
            ?candidato :possiede ?cv.
            ?cv :contiene ?campi_cv.
            ?campi_cv :haCampo ?ls.
            ?ls :nome ?nomeTitoloDiStudi.
            
            ?cv rdf:type :CV.
            ?campi_cv rdf:type :Campi_CV.
            ?ls rdf:type :$livelloStudi.
            
          }
        
          ";


        $query2= "
        PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
        PREFIX foaf: <http://xmlns.com/foaf/0.1/>
        PREFIX frapo: <http://purl.org/cerif/frapo/>
        
         SELECT  distinct ?candidato  ?tipoCertificato
         WHERE {
                ?candidato :possiede ?cv.
                ?cv :contiene ?campi_cv.
                ?campi_cv :haCampo ?studiUniversitari;
                       :haCampo ?certificatoLingua.
                ?certificatoLingua :nome ?tipoCertificato.
                       
               FILTER regex(str(?tipoCertificato), '$certificate_v').
                
                
                ?cv rdf:type :CV.
                ?campi_cv rdf:type :Campi_CV.
                ?studiUniversitari rdf:type :StudiUniversitari.
                ?certificatoLingua rdf:type :CertificatiLingua.
               
            }
          ORDER BY DESC(?tipoCertificato)
        ";

        $query3= "
        PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
        PREFIX foaf: <http://xmlns.com/foaf/0.1/>
        PREFIX frapo: <http://purl.org/cerif/frapo/>
        
         SELECT distinct ?candidato  ?settoreStudi ?studiUniversitari ?nomeTitoloDiStudi
            WHERE {
                ?candidato :possiede ?cv.
                ?cv :contiene ?campi_cv.
                ?campi_cv :haCampo ?studiUniversitari.
                ?studiUniversitari :nome ?nomeTitoloDiStudi;
                                   :haSettoreStudi ?settoreStudi.
                ?settoreStudi rdf:type :Informatico.
                ?cv rdf:type :CV.
                ?campi_cv rdf:type :Campi_CV.
                ?studiUniversitari rdf:type :StudiUniversitari.   
            }
           ORDER BY ?Candidato

        ";


        ////////////////////////////////
        $sector= isset($_POST['settore']) ? $_POST['settore'] : false;
        $university= isset($_POST['studiUniversitari']) ? $_POST['studiUniversitari'] : false;
        $certificate= isset($_POST['certificati']) ? $_POST['certificati'] : false;

        ////////////////////// QUERY 1: Basta selezionare Studi universitari //////////////////////////
        if ((strcmp($university, $university_v)==0) && (strcmp($selected_radio, "no")==0) && (strcmp($certificate, "-")==0) && (strcmp($sector, "-")==0))  {

            $rows = $store->query($query1, 'rows');


            /* display the results in an HTML table */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Candidato</th>
                      <th>$livelloStudi_h</th>
                      <th> Corso di Studi </th>
                  </thead>";


            /* loop for each returned row */
            foreach( $rows as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['candidato'], strpos($row['candidato'], "#") + 1)."</td> 
                             <td>" .substr($row['ls'], strpos($row['ls'], "#") + 1). "</td>
                             <td> ".$row['nomeTitoloDiStudi']." </td>
                             </tr>";
                 }
             echo "</table>";


        }
        //else if((strcmp($sector, "informatica") == 0) &&  (strcmp($university, "qualsiasi")==0) && (strcmp($selected_radio, "si")==0) ){
        ////////////////////// QUERY 2: Basta selezionare il certificato //////////////////////////
        else if((strcmp($certificate, $certificate_v) == 0) && (strcmp($university, "-")==0) && (strcmp($selected_radio, "no")==0)  && (strcmp($sector, "-")==0) ){
            $rows2 = $store->query($query2, 'rows');
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Candidato</th>
                      <th>Certificato linguistico</th>
                 </thead>";


            /* loop for each returned row */
            foreach( $rows2 as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['candidato'], strpos($row['candidato'], "#") + 1)."</td>
                             <td>" .$row['tipoCertificato']."</td>
                            </tr>";
            }
            echo "</table>";
        }

        ////////////////////// QUERY 3: Settore= informatico; nomeSettore=si; university= qualsiasi //////////////////////////
        else if((strcmp($sector, "informatica") == 0) && (strcmp($certificate, "-") == 0) && (strcmp($university, "qualsiasi")==0) && (strcmp($selected_radio, "si")==0)){
            $rows3 = $store->query($query3, 'rows');
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Candidato</th>
                      <th>Settore</th>
                      <th>Studi universitari</th>
                      <th>Corso di studi</th>
                 </thead>";

            /* loop for each returned row */
            foreach( $rows3 as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['candidato'], strpos($row['candidato'], "#") + 1)."</td>
                             <td>".substr($row['settoreStudi'], strpos($row['settoreStudi'], "#") + 1)."</td>
                             <td>".substr($row['studiUniversitari'], strpos($row['studiUniversitari'], "#") + 1)."</td>
                             <td>" .$row['nomeTitoloDiStudi']."</td>
                            </tr>";
            }
            echo "</table>";

        }

        else {
            echo "not working";
            exit;
        }


        /////////////////////////////////////

        /* execute the query */
        //$rows = $store->query($query, 'rows');


        if ($errs = $store->getErrors()) {
            echo "Query errors" ;
            print_r($errs);
        }





        ?>

    </article>
</section>

<footer>
    <p>Footer</p>
</footer>

</body>
</html>

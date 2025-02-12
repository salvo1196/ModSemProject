<!-- Azienda.php definisce ed implementa la logica applicata alle query, eseguite attraverso l'endpoint di GraphDB-->

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
            color: #fefff6;
            background-image: url('imgApplication/linked-data-and-semantics-1024x440.jpeg');
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: cover;
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
            height: 380px; /* only for demonstration, should be removed */
            background: #ccc;
            color: black;
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
            height: 380px; /* only for demonstration, should be removed */
            color: black;
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

<h2>Job Search Ontology</h2>

<header>
    <h4>Lista Candidati</h4>
</header>

<section>
    <nav>
        <?php
        /*
         * vengono recuperati i valori selezionati nella pagina "azienda.html"
         */
        $sector= isset($_POST['settore']) ? $_POST['settore'] : false;  //campo settore
        $university= isset($_POST['studiUniversitari']) ? $_POST['studiUniversitari'] : false; //campo università
        $certificate= isset($_POST['certificati']) ? $_POST['certificati'] : false; //campo certificato
        $selected_radio = $_POST['nome']; //valore del radio button selezionato. Se "SI" mostra il nome del settore, se "NO" altrimenti


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

        <!-- Stampa del valore selezionato per ogni campo -->
        <label><b>Settore:</b></label> <?php echo $settore_v?>
        <br>
        <label><b> Mostrare nome del settore: </b></label> <?php echo $selected_radio ?>
        <br>
        <label><b>Studi universitari:</b></label> <?php echo $university?>
        <br>
        <label><b>Certificati Linguistici:</b></label> <?php echo $certificate_v?>

    </nav>



    <article>
        <?php
        /* ARC2 static class inclusion */
        include_once('semsol/ARC2.php');
        // endpoint dell'ontologia JSO
        $jsoconfig = array(
            "remote_store_endpoint" => "http://192.168.184.1:7200/repositories/JobSearchOntologyProject",
        );

        $store = ARC2::getRemoteStore($jsoconfig);

        if ($errs = $store->getErrors()) {
            echo "<h1>getRemoteSotre error<h1>" ;
        }


        /*
         * Switch necessario per allineare i valori di University selezionati dalla Web Application
         * con i valori presenti nell'ontologia.
         */
        switch ($university) {
            case 'triennale':
                $livelloStudi = 'LaureaTriennale'; // valore utilizzato  nella query
                $livelloStudi_h = 'Laurea Triennale'; // header della tabella
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
                break;
            default :
                $livelloStudi ='-';
                $livelloStudi_h = '-';;
        }

        /*
         * Switch necessario per allineare i valori di Sector selezionati dalla Web Application
         * con i valori presenti nell'ontologia.
         */
        switch ($sector) {
            case 'informatica':
                $sector_query = 'Informatico'; //valore utilizzato  nella query
                break;

            case '-':
                $sector_query = 'Settore';
                break;
            case 'automobilismo':
                $sector_query = 'Automobilistico';
                break;
            case 'economico':
                $sector_query = 'Economico';
                break;
            case 'agroAlimentare':
                $sector_query = 'AgroAlimentare';
        }


        /*
         * La seguente query viene utilizzata dalle aziende per  visualizzare i candidati con il titolo di studi specificato,
         *
         */
        $query1 = "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
        
          SELECT ?candidato ?livelloStudi ?nomeTitoloDiStudi
          WHERE {
            ?candidato :possiede ?cv.
            ?cv :contiene ?campi_cv.
            ?campi_cv :haCampo ?livelloStudi.
            ?livelloStudi :nome ?nomeTitoloDiStudi.
            
            ?cv rdf:type :CV.
            ?campi_cv rdf:type :Campi_CV.
            ?livelloStudi rdf:type :$livelloStudi.   
          }
          ";

        /*
         * La query permette di cercare tutti i lavoratori che hanno almeno un titolo distudi  universitario
         *   e  che  posseggono  un  certificato  di  inglese,  ordinandoli  dal livello più alto al livello più basso
         */
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


        /*
         *  La query cerca tutti i candidati nel settore selezionato con i relativi percorsi di studi.
         */
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
                ?settoreStudi rdf:type :$sector_query.
                ?cv rdf:type :CV.
                ?campi_cv rdf:type :Campi_CV.
                ?studiUniversitari rdf:type :StudiUniversitari.   
            }
           ORDER BY ?Candidato

        ";


        /*
         ***** Esecuzione QUERY 1
         */
        if ((strcmp($university, $university_v)==0) && (strcmp($selected_radio, "no")==0) && (strcmp($certificate, "-")==0) && (strcmp($sector, "-")==0))  {

            $rows = $store->query($query1, 'rows');

            /* Mostra i risultati in una tabella HTML */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Candidato</th>
                      <th>$livelloStudi_h</th>
                      <th> Corso di Studi </th>
                  </thead>";

            /* loop per ogni riga ritornata */
            foreach( $rows as $row ) {
                /*Stampo le sottostringe dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['candidato'], strpos($row['candidato'], "#") + 1)."</td> 
                             <td>" .substr($row['livelloStudi'], strpos($row['livelloStudi'], "#") + 1). "</td>
                             <td> ".$row['nomeTitoloDiStudi']." </td>
                             </tr>";
                 }
             echo "</table>";


        }
        /*
         ******* Esecuzione QUERY 2
         */
        else if((strcmp($certificate, $certificate_v) == 0) && (strcmp($university, "-")==0) && (strcmp($selected_radio, "no")==0)  && (strcmp($sector, "-")==0) ){

            $rows2 = $store->query($query2, 'rows');

            /* Mostra i risultati in una tabella HTML */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Candidato</th>
                      <th>Certificato linguistico</th>
                 </thead>";


            /* loop per ogni riga ritornata */
            foreach( $rows2 as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['candidato'], strpos($row['candidato'], "#") + 1)."</td>
                             <td>" .$row['tipoCertificato']."</td>
                            </tr>";
            }
            echo "</table>";
        }

        /*
         ****** QUERY 3
         */
        else if((strcmp($sector, "$sector") == 0) && (strcmp($certificate, "-") == 0) && (strcmp($university, "qualsiasi")==0) && (strcmp($selected_radio, "si")==0)){

            $rows3 = $store->query($query3, 'rows');

            /* Mostra i risultati in una tabella HTML */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Candidato</th>
                      <th>Settore</th>
                      <th>Studi universitari</th>
                      <th>Corso di studi</th>
                 </thead>";

            /* loop per ogni riga ritornata */
            foreach( $rows3 as $row ) {
                /*Stampo le sottostringe dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['candidato'], strpos($row['candidato'], "#") + 1)."</td>
                             <td>".substr($row['settoreStudi'], strpos($row['settoreStudi'], "#") + 1)."</td>
                             <td>".substr($row['studiUniversitari'], strpos($row['studiUniversitari'], "#") + 1)."</td>
                             <td>" .$row['nomeTitoloDiStudi']."</td>
                            </tr>";
            }
            echo "</table>";

        }

        else {
            echo "No matching";
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Candidato</th>
                      <th>Settore</th>
                 </thead>
              </table>";

            exit;
        }

        /* execute the query */

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

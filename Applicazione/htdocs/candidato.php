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
        $competenze= $_POST['competenze'];
        $salario = $_POST['salario'];
        $selected_radio = $_POST['nome'];



        if ($sector) {
            $settore_v = htmlentities($_POST['settore'], ENT_QUOTES, "UTF-8");


        }
        else {
            echo "task option is required";
            exit;
        }
        ?>

        <label >Settore:</label> <?php echo $sector?>
        <br>
        <label> Competenze </label> <?php echo $competenze; ?>
        <br>
        <label> Salario mensile </label> <?php echo $salario; ?>
        <br>
        <label> Mostrare nome del settore: </label> <?php echo $selected_radio ?>


    </nav>



    <article>
        <?php
        /* ARC2 static class inclusion */
        //include_once('semsol/ARC2.php');
        include_once('semsol-arc2-586f303/ARC2.php');
        //require 'vendor/autoload.php';

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

        //////divide la stringa in base allo spazio e inserisci i vari token in un array
        //$subStringcomp = explode(" ", $competenze);

        //Variabili per il controllo del settore nella query 2
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
                $sector_query = 'ManifatturieroArtiginato';
        }

        $query1 = "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
        
          SELECT ?azienda ?annuncio
            WHERE {
                ?annuncio :ePubblicato ?azienda.
                ?annuncio rdf:type :Annuncio.
              }
          ";


        $query2 = "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
        
          SELECT ?azienda ?annuncio ?competenzeRichiesteDettagli ?contratto ?salario
            WHERE {
                ?azienda :pubblica ?annuncio;
                               :haSettoreAzienda ?informatico.
                ?annuncio :salarioMensile ?salario;
                               :competenzeRichiesteDettagli ?competenzeRichiesteDettagli;
                               :contrattoProposto ?contratto.
                FILTER (?salario = $salario).
                FILTER regex(str(?competenzeRichiesteDettagli),  '$competenze').
                
                ?informatico rdf:type :$sector_query.
                ?annuncio rdf:type :Annuncio.
            }
          ";

        $query22 = "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
        
          SELECT ?azienda ?annuncio ?competenzeRichiesteDettagli ?contratto ?salario
            WHERE {
                ?azienda :pubblica ?annuncio;
                               :haSettoreAzienda ?informatico.
                ?annuncio :salarioMensile ?salario;
                                :contrattoProposto ?contratto;
                               :competenzeRichiesteDettagli ?competenzeRichiesteDettagli.
                FILTER regex(str(?competenzeRichiesteDettagli),  '$competenze').
                
                ?informatico rdf:type :$sector_query.
                ?annuncio rdf:type :Annuncio.
            }
          ";

        ////// è stata ppartata qualche modifica rispetto alla query originaria n7 del file word // vengono mostrati gli stessi risultati della query11 ma solo per i "dottorati"
        $query3 = "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
        
          SELECT distinct ?annuncio ?azienda ?competenze ?contratto ?salario
            WHERE{

                ?annuncio :haSettoreAnnuncio ?settoreAnnuncio;
                    foaf:maker ?azienda;
                    :contrattoProposto ?contratto;
                    :competenzeRichiesteDettagli ?competenze;
                    :salarioMensile ?salario.
                    
                ?candidato rdf:type :CandidatoRicercatore.
                ?annuncio rdf:type :Annuncio.
                ?azienda rdf:type :Azienda. 
                ?settoreAnnuncio rdf:type :$sector_query.
            }
          ";

        ////////////mi ritorna tutta gli annunci che hanno il settore da me scelto
        $query33 = "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
        
          SELECT distinct ?azienda ?annuncio ?competenze ?contratto
            WHERE {
                ?annuncio :ePubblicato ?azienda.
                ?annuncio :haSettoreAnnuncio ?settoreAnnuncio;
                          :competenzeRichiesteDettagli ?competenze;
                           :contrattoProposto ?contratto.
                ?annuncio rdf:type :Annuncio.

                ?settoreAnnuncio rdf:type :$sector_query.
              }
          ";



        ////////////////////////////////
        $sector= isset($_POST['settore']) ? $_POST['settore'] : false;

        ////////////////////// QUERY 1: sector= - ; salario=-  //////////////////////////
        /*  Ritorna la lista di tutti gli annunci con le relative aziende  */
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ((strcmp($sector, "-")==0) && (strcmp($salario, "-")==0) && (strcmp($selected_radio, "no")==0))  {
            echo 1;
            $rows = $store->query($query1, 'rows');
            /* display the results in an HTML table */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Azienda</th>
                      <th>Annuncio</th>
                  </thead>";

            /* loop for each returned row */
            foreach( $rows as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['azienda'], strpos($row['azienda'], "_") + 1)."</td> 
                             <td>" .substr($row['annuncio'], strpos($row['annuncio'], "#") + 1). "</td>
                             </tr>";
            }
            echo "</table>";

        }


        ////////////////////// QUERY 22: sector != - ; salario=qualsiasi  E volendo posso scrivere qualsiasi cosa nelle competenze//////////////////////////
        /* Ritorna la lista degli annuncio in base al settore scelto e le competenze scritte.
        Quindi bisogna sempre settare il settore, cliccare sul bottone aggiungi competente e scrivere qualcosa */
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        else if ((strcmp($sector, "-")!=0)  && (strcmp($salario, "-")==0) && (strcmp($selected_radio, "no")==0)  ){
            echo 2;
            $rows = $store->query($query22, 'rows');
            /* display the results in an HTML table */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Azienda</th>
                      <th>Annuncio</th>
                      <th>Competenze richieste</th>
                      <th>Contratto proposto</th>
                      <th>Salario</th>
                  </thead>";

            /* loop for each returned row */
            foreach( $rows as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['azienda'], strpos($row['azienda'], "_") + 1)."</td> 
                             <td>" .substr($row['annuncio'], strpos($row['annuncio'], "#") + 1). "</td>
                             <td> ".$row['competenzeRichiesteDettagli']." </td>
                             <td> ".substr($row['contratto'], strpos($row['contratto'], "#") + 1)." </td>
                             <td> ".$row['salario']." </td>
                             </tr>";
            }
            echo "</table>";
        }

        ////////////////////// QUERY 2 //////////////////////////
        /* Ritorna la lista degli annuncio in base al "settore scelto", le $competenze scritte$ e il "salario indicato"
        NOTA: che volendo posso anche solo inserire il settore e il salario, ma si deve stare attenti che la text di competende deve essere vuota
        NOTA: ricordarsi che il salario deve avere un valore ben definito */
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        else if ((strcmp($sector, "$sector")==0) && (strcmp($selected_radio, "no")==0) &&  (strcmp($salario, "-")!=0) && (strcmp($salario, "qualsiasi")!=0)  ){
            echo 3;
            $rows = $store->query($query2, 'rows');
            /* display the results in an HTML table */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Azienda</th>
                      <th>Annuncio</th>
                      <th>Competenze richieste</th>
                      <th>Salario</th>
                  </thead>";


            /* loop for each returned row */
            foreach( $rows as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['azienda'], strpos($row['azienda'], "_") + 1)."</td> 
                             <td>" .substr($row['annuncio'], strpos($row['annuncio'], "#") + 1). "</td>
                             <td> ".$row['competenzeRichiesteDettagli']." </td>
                             <td> ".$row['salario']." </td>
                             </tr>";
            }
            echo "</table>";
        }

        ////////////////////// QUERY 3 //////////////////////////
        /*Ritorna la lista degli annunci solo se è stato scelto un settore d'interesse e si è DOTTORATI (SI)
        NOTA: ricordarci di settare il salario a QUALSIASI (non per ragioni interne alla query, ma per non fare sovrapporre la query 3 con la 2)*/
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        else if ((strcmp($sector, "$sector")==0) && (strcmp($selected_radio, "si")==0) && (strcmp($salario, "qualsiasi")==0)  ){
            echo 4;
            $rows = $store->query($query3, 'rows');
            /* display the results in an HTML table */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Azienda</th>
                      <th>Annuncio</th>
                      <th>Competenze richieste</th>
                      <th>Contratto Proposto</th>
                  </thead>";


            /* loop for each returned row */
            foreach( $rows as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['azienda'], strpos($row['azienda'], "_") + 1)."</td> 
                             <td>" .substr($row['annuncio'], strpos($row['annuncio'], "#") + 1). "</td>
                             <td> ".$row['competenze']." </td>
                             <td> ".substr($row['contratto'], strpos($row['contratto'], "#") + 1)." </td>     
                             <td> ".$row['salario']." </td>
                             </tr>";
            }
            echo "</table>";
        }

        ////////////////////// QUERY 33 //////////////////////////
        /*mi permette semplicemente di coprire qui casi in sui si seleziona un settore e il salario è qualsiasi e dottoraro a No*/
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        else if ((strcmp($sector, "-")!=0) && (strcmp($salario, "qualsiasi")==0) && (strcmp($selected_radio, "no")==0) )  {
            echo 5;
            $rows = $store->query($query33, 'rows');

            /* display the results in an HTML table */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Azienda</th>
                      <th>Annuncio</th>
                      <th>competenze richieste</th>
                      <th>Contratto proposto</th>
                  </thead>";

            /* loop for each returned row */
            foreach( $rows as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['azienda'], strpos($row['azienda'], "_") + 1)."</td> 
                             <td>" .substr($row['annuncio'], strpos($row['annuncio'], "#") + 1). "</td>
                             <td> ".$row['competenze']." </td>
                             <td> ".substr($row['contratto'], strpos($row['contratto'], "#") + 1)." </td>
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

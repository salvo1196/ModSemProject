<!-- Tale file implementa le query poste dal candidato al sistema per andare
alla ricerca degli annunci ad esso più consoni, attraverso i parametri specificati
-->

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
            padding: 20px;
            color: black;
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
    <h4>Lista Annunci</h4>
</header>

<section>
    <nav>
        <?php
        /*
        * vengono recuperati i valori selezionati nella pagina "azienda.html"
        */
        $sector= isset($_POST['settore']) ? $_POST['settore'] : false; //campo settore
        $comGenerali= $_POST['compGenerali']; // campo Competenze generali
        $competenze= $_POST['competenze'];  // campo Competenze e conoscenze dell'utente
        $salario = $_POST['salario'];  //campo salario
        $selected_radio = $_POST['nome']; // campo dottorato
        $citta= $_POST['citta']; // campo città

        if ($sector) {
            $settore_v = htmlentities($_POST['settore'], ENT_QUOTES, "UTF-8");
        }

        else {
            echo "task option is required";
            exit;
        }
        ?>

        <!-- Stampa del valore selezionato per ogni campo -->
        <label ><b>Settore:</b></label> <?php echo $sector?>
        <br>
        <label ><b>Competenze:</b></label> <?php echo $comGenerali?>
        <br>
        <label><b> Conoscenze:</b></label> <?php echo $competenze ?>
        <br>
        <label><b> Dottorato: </b> </label> <?php echo $selected_radio ?>
        <br>
        <label><b> Città: </b></label> <?php echo $citta ?>

    </nav>



    <article>
        <?php
        /* ARC2 static class inclusion */
        include_once('semsol-arc2-586f303/ARC2.php');

        $jsoconfig = array(
            "remote_store_endpoint" => "http://192.168.184.1:7200/repositories/JobSearchOntology",
        );

        $store = ARC2::getRemoteStore($jsoconfig);

        if ($errs = $store->getErrors()) {
            echo "<h1>getRemoteSotre error<h1>" ;
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
         * La seguente query permette al candidato di vedere nella bacheca tutte le aziendeche hanno pubblicato degli annunci.
         */
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


        /*
         *La  seguente  query  trova  tutte  le  aziende  che  hanno  pubblicato  un  annuncio
         * avente un salario mensile compreso nel range indicato e come competenze richieste le conoscenze scritte dall'utente
         */
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

        /*
         * Piccola variante della query due dove non è necessaio andare ad inserire il salario
         */
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


        /*
         * Se si è ricercatori (candidato di tipo ”candidato ricercatore”), vengono trovati tutti gli annunci con le relative aziende
         * che hanno lo stesso settore del candidato ricercatore.
         */
        $query3 = "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
        
           SELECT   ?annuncio ?azienda  ?contratto 
                WHERE{
                
                    ?candidato :haSettoreDiInteresse ?settoreCandidato.
                    
                    ?annuncio :haSettoreAnnuncio ?settoreAnnuncio;
                        foaf:maker ?azienda;
                                    :contrattoProposto ?contratto.
                     
                    ?candidato rdf:type :CandidatoRicercatore.
                    ?annuncio rdf:type :Annuncio.
                    ?azienda rdf:type :Azienda.
                    ?settoreCandidato rdf:type :$sector_query.
                    FILTER( ?settoreCandidato= ?settoreAnnuncio)
                }
          ";

        /*
         * Trova tutti gli annunci riguardanti il settore scelto dal candidato
         */
        $query33 = "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
        
          SELECT ?annuncio ?azienda  ?contratto 
                WHERE{  
                    ?annuncio :haSettoreAnnuncio ?settoreAnnuncio;
                              foaf:maker ?azienda;
                              :contrattoProposto ?contratto.
                     
                    ?annuncio rdf:type :Annuncio.
                    ?azienda rdf:type :Azienda.
                    ?settoreAnnuncio rdf:type :$sector_query.
                }
          ";

///////////////////////////////////////////
        ///
        ///
        ///  SIAMO ARRIVATI QUIIIIIIIIIIIIIIII
        //////////////////////

        /*
         * Restituisce l'annuncio, la sua descrizone, il contratto proposto e le ore giornaliere per tutti gli annunci che si trovano
         * nella città scelta dell'utente con il settore richiesto
         */
        $query4= "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
          
           SELECT DISTINCT ?annuncio ?descrizione ?contratto ?ore
                WHERE{
                    ?candidato :possiede ?cv.
                     ?cv :contiene ?campi_cv.
                     ?campi_cv :haCampo ?infoPersonali;
                                    :haCampo?competenze.
                          ?competenze :haCompetenze ?competenzeCandidato.

                                  
                    ?annuncio :haCompetenzeRichieste ?competenzeRichieste;
                               :haSettoreAnnuncio ?settore;
                               frapo:hasCountry ?countyAnnuncio;
                               :descrizione ?descrizione;
                               :contrattoProposto ?contratto;
                               :oreDiLavoro ?ore.
    
    				FILTER regex(str(?countyAnnuncio),  '$citta')               
    
                    ?annuncio rdf:type :Annuncio.
                    ?cv rdf:type :CV.
                    ?settore rdf:type :$sector_query.
                   
                    ?campi_cv rdf:type :Campi_CV.
                                  ?infoPersonali rdf:type :InfoPersonali.
                }
        ";

        $query5= "
          PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
          PREFIX foaf: <http://xmlns.com/foaf/0.1/>
          PREFIX frapo: <http://purl.org/cerif/frapo/>
          
             SELECT  ?annuncio (MAX(?salario) AS ?salarioPiùAlto) ?oreLavoro ?settore
                WHERE{
                    ?annuncio :haCompetenzeRichieste ?altreCompetenze;
                    :salarioMensile ?salario;
                    :oreDiLavoro ?oreLavoro;
                    :haSettoreAnnuncio ?settore.
                        
                    FILTER(?oreLavoro > 5 && ?oreLavoro <9).
                    ?annuncio rdf:type :Annuncio.
                    ?altreCompetenze rdf:type :$comGenerali.
                    ?settore rdf:type :$sector_query.
                            } 
                   GROUP BY ?annuncio ?oreLavoro ?settore
        ";


        ////////////////////////////////
        $sector= isset($_POST['settore']) ? $_POST['settore'] : false;

        ////////////////////// QUERY 1: sector= - ; salario=-  //////////////////////////
        /*  Ritorna la lista di tutti gli annunci con le relative aziende  */
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ((strcmp($sector, "-")==0) && (strcmp($salario, "-")==0) && (strcmp($selected_radio, "no")==0) && (strcmp($citta, "-")==0) && (strcmp($comGenerali, "-")==0))  {
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
        else if ((strcmp($sector, "-")!=0)  && (strcmp($salario, "-")==0) && (strcmp($selected_radio, "no")==0) && (strcmp($citta, "-")==0) && (strcmp($comGenerali, "-")==0) ){
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
        else if ((strcmp($sector, "$sector")==0) && (strcmp($selected_radio, "no")==0) &&  (strcmp($salario, "-")!=0) && (strcmp($salario, "qualsiasi")!=0)  && (strcmp($comGenerali, "-")==0) ){
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
        else if ((strcmp($sector, "$sector")==0) && (strcmp($selected_radio, "si")==0) && (strcmp($salario, "qualsiasi")==0) && (strcmp($comGenerali, "-")==0) ){
            echo 4;
            $rows = $store->query($query3, 'rows');
            /* display the results in an HTML table */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Azienda</th>
                      <th>Annuncio</th>
                      <th>Contratto Proposto</th>
                  </thead>";


            /* loop for each returned row */
            foreach( $rows as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['azienda'], strpos($row['azienda'], "_") + 1)."</td> 
                             <td>" .substr($row['annuncio'], strpos($row['annuncio'], "#") + 1). "</td>
                             <td> ".substr($row['contratto'], strpos($row['contratto'], "#") + 1)." </td>     
                             </tr>";
            }
            echo "</table>";
        }

        ////////////////////// QUERY 33 //////////////////////////
        /*Riotrna la lista di annuncio per il settore selezionato
        NOTA: bisogna scegliere il SETTORE,  il salario a QUALSIASI e  il dottorato a NO*/
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        else if ((strcmp($sector, "-")!=0) && (strcmp($salario, "qualsiasi")==0) && (strcmp($selected_radio, "no")==0) && (strcmp($comGenerali, "-")==0) )  {
            echo 5;
            $rows = $store->query($query33, 'rows');

            /* display the results in an HTML table */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Azienda</th>
                      <th>Annuncio</th>
                      <th>Contratto proposto</th>
                  </thead>";

            /* loop for each returned row */
            foreach( $rows as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['azienda'], strpos($row['azienda'], "_") + 1)."</td> 
                             <td>" .substr($row['annuncio'], strpos($row['annuncio'], "#") + 1). "</td>
                             <td> ".substr($row['contratto'], strpos($row['contratto'], "#") + 1)." </td>
                             </tr>";
            }
            echo "</table>";
        }
        ///////////////////////// QUERY 4 ///////////////////////////////////////////////////////////////
        /*la query ritorna tutti gli annunci che hanno la stessa città indicata dal candidato e che hanno il settore SCELTO DAL CANDIDATO
        LE possibili combinazioni oper ottenere output sono: Economico=Torino, Informatico:Milano, Automobilistico:Roma, Agroalimentare:Potenza*/
        else if ((strcmp($sector, "$sector")==0) && (strcmp($salario, "-")==0) && (strcmp($selected_radio, "no")==0)  && (strcmp($citta, "-")!=0) && (strcmp($comGenerali, "-")==0))  {
            echo 6;
            $rows = $store->query($query4, 'rows');

            /* display the results in an HTML table */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>

                      <th>Annuncio</th>
                      <th>Descrizione</th>
                      <th>Contratto proposto</th>
                      <th>Ore giornaliere</th>
                  </thead>";

            /* loop for each returned row */
            foreach( $rows as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['annuncio'], strpos($row['annuncio'], "#") + 1). "</td>
                             <td> ".substr($row['descrizione'], strpos($row['descrizione'], "#") + 1)." </td>
                              <td> ".$row['contratto']." </td>
                               <td> ".$row['ore']." </td>
                             </tr>";
            }
            echo "</table>";
        }

        ///////////////////////// QUERY 5 ///////////////////////////////////////////////////////////////
    /*Trova tutti gli annunci che, una volta SCELTA LA COMPETENZA, abbiano il salario più alto  con un monte ore compreso tra le 6 e 8 e che abbia come competenze richieste QUELLE SELEZIONATE DALL'UTENTE

    */
        else if ((strcmp($sector, "$sector")==0) && (strcmp($salario, "-")==0) && (strcmp($selected_radio, "no")==0)  && (strcmp($citta, "-")==0) && (strcmp($comGenerali, "-")!=0) )  {
            echo 7;
            $rows = $store->query($query5, 'rows');

            /* display the results in an HTML table */
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>

                      <th>Annuncio</th>
                      <th>Salario</th>
                      <th>Ore di lavoro</th>
                      <th>Settore</th>
                      

                  </thead>";

            /* loop for each returned row */
            foreach( $rows as $row ) {
                /*Stampo le sottostinge dell'URI contente solo i nomi*/
                print "<tr><td>" .substr($row['annuncio'], strpos($row['annuncio'], "#") + 1). "</td>
                           
                                <td> ".$row['salarioPiùAlto']." </td>
                               <td> ".$row['oreLavoro']." </td>
                                <td>" .substr($row['settore'], strpos($row['settore'], "#") + 1). "</td>
                             </tr>";
            }
            echo "</table>";
        }
        /*Caso di default*/
        else {
            echo "Default";
            echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>

                      <th>Annuncio</th>
                      <th>Azienda</th>
                      <th>Salario</th>
                      <th>Competenze</th>
                      <th>Contratto</th>
                  </thead>
                      </table>";
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

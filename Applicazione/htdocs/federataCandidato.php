<!-- Questo file implementa la query federata lato candidato che permette di cercare
su JSO e DBpedia le aziende locate nella città selezionata
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

    <title>Federata Azienda</title>
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
            width: 100%;
            height: inherit;
            background-color: #f1f1f1;

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

<h2>Job Search Onotology</h2>


<header>
    <h4>Query Federata Azienda</h4>
</header>

<section>


    <article>
        <?php
        /* ARC2 static class inclusion */
        include_once('semsol/ARC2.php');


        // endpoint DBpedia
        $dbpconfig = array(
            "remote_store_endpoint" => "https://dbpedia.org/sparql",
        );

        // endpoint JSO
        $jsoconfig = array(
            "remote_store_endpoint" => "http://192.168.184.1:7200/repositories/JobSearchOntologyProject",

        );

        $store = ARC2::getRemoteStore($dbpconfig);

        $store2 = ARC2::getRemoteStore($jsoconfig);

        if ($errs = $store->getErrors()) {
            echo "<h1>getRemoteSotre error<h1>" ;
        }

        if ($errs = $store2->getErrors()) {
            echo "<h1>getRemoteSotre error<h1>" ;
        }

        $search = $_POST['search'];

        switch ($search) {
            case 'Italia':
                $search_dbo = 'Italy'; //valore utilizzato  nella query
                break;

            case 'Francia':
                $search_dbo = 'France';
                break;
            case 'Germania':
                $search_dbo = 'Germany';
                break;
            case 'USA':
                $search_dbo = 'United_State';
        }

        /*
         * La query permette di estrarre da DBpedia le prime 3 aziendeche  si  trovano  in
         * un  preciso  paese  (in  questo  caso  l’Italia)  e  la  loro  relativadescrizione
         */
        $query1 = "
        PREFIX dbo: <http://dbpedia.org/ontology/> 
        PREFIX dbr: <http://dbpedia.org/resource/> 
        PREFIX dbt: <http://dbpedia.org/resource/Template/>
        PREFIX foaf: <http://xmlns.com/foaf/0.1/>
        
        SELECT distinct ?impresa ?country ?description
        WHERE {
             
               ?impresa a dbo:Company;
                     dbo:country ?country;
                     foaf:name ?name ;
                     dbo:abstract ?description.
                FILTER( langMatches(lang(?description),'it') ).
                FILTER regex(str(?country), '$search_dbo').
        
        } LIMIT 3
        ";

        /*
         * La query permette di estrarre dalla JSO Ontology tutte le aziende locate nel paese scelto dal candidato
         */
        $query2 ="
        PREFIX foaf: <http://xmlns.com/foaf/0.1/> 
        PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
        PREFIX : <http://www.semanticweb.org/OntologiaRicercaLavoro#>
        PREFIX frapo: <http://purl.org/cerif/frapo/>
        
        SELECT distinct ?azienda ?sede ?descrizione
        WHERE
        {    
              ?azienda :pubblica ?annuncio;
                               :haSedePrincipaleIn  ?sede;
                               :descrizioneAzienda ?descrizione.
              
              FILTER regex(str(?sede), '$search').
              
               ?azienda rdf:type :Azienda.
            } 

        ";

        /*
         * Esecuzione query 1
         */
        $rows = $store->query($query1, 'rows');

        /* Mostra i risultati in una tabella HTML */
        echo "<table border='1'  class=\"table table-small-font table-sm table-bordered table-striped\" >
                  <thead>
                      <th>Azienda</th>
                      <th>Sede</th>
                      <th>Descrizione</th>

                  </thead>";

        /* loop per ogni riga ritornata */
        foreach( $rows as $row ) {
            /*Stampo le sottostringe dell'URI contente solo i nomi*/
            print "<tr><td> ".substr($row['impresa'], strpos($row['impresa'], "e/") + 2)." </td>
                        <td> ".substr($row['country'], strpos($row['country'], "e/") + 2)." </td>
                        <td> ".$row['description']." </td>

                    </tr>
                    ";
        }

        /*
        * Esecuzione query 2
        */
        $rows2 = $store2->query($query2, 'rows');
        foreach( $rows2 as $row2 ) {
            /*Stampo le sottostringe dell'URI contente solo i nomi*/
            print "<tr><td> ".substr($row2['azienda'], strpos($row2['azienda'], "#") + 1)." </td>
                        <td> ".substr($row2['sede'], strpos($row2['sede'], "#") + 1)." </td>
                            <td> ".$row2['descrizione']." </td>
                             </tr>";
        }

        echo "</table>";



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


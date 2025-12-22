<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Descriere Proiect - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-container">
    <h1>Prezentarea aplicației web: Black Shield Logistics</h1>

    <section>
        <h2>1. Scopul și funcționalitatea aplicației</h2>
        <p>
            <strong>Black Shield Logistics</strong> este o aplicație web comercială dedicată unei firme de transport
            paramilitar, care oferă servicii de transport securizat pentru companii de securitate privată, PMC-uri
            (Private Military Companies) și alți contractori autorizați.
        </p>
        <p>
            Prin intermediul acestei aplicații, clienții pot vizualiza serviciile disponibile, pot transmite cereri de ofertă
            pentru transport securizat, pot urmări statusul comenzilor acceptate și pot comunica cu personalul de dispecerat.
            Administratorii pot gestiona utilizatorii, comenzile, flota și rutele.
        </p>
    </section>

    <section>
        <h2>2. Arhitectura aplicației</h2>

        <h3>2.1 Roluri principale</h3>
        <ul>
            <li><strong>Vizitator</strong> – poate vedea informații publice despre companie și servicii.</li>
            <li><strong>Client</strong> – utilizator înregistrat care poate trimite cereri de transport și vizualiza statusul acestora.</li>
            <li><strong>Operator / Dispecer</strong> – validează cererile, alocă vehicule, actualizează statusul curselor.</li>
            <li><strong>Administrator</strong> – gestionează utilizatorii, serviciile, flota, rutele și are acces la rapoarte.</li>
        </ul>

        <h3>2.2 Entități principale</h3>
        <ul>
            <li><strong>Utilizator</strong> – date de autentificare și rol (client, operator, admin).</li>
            <li><strong>ClientProfil</strong> – datele companiei client (denumire, CUI, adresă, persoană contact).</li>
            <li><strong>CerereTransport</strong> – solicitare inițială (loc plecare, destinație, tip marfă, nivel securitate, dată).</li>
            <li><strong>ComandaTransport</strong> – cerere aprobată și planificată (vehicul alocat, echipaj, preț, status).</li>
            <li><strong>Ruta</strong> – traseu propus sau confirmat între două locații.</li>
            <li><strong>Vehicul</strong> – mijloc de transport utilizat (tip, capacitate, nivel protecție).</li>
            <li><strong>Serviciu</strong> – tip de serviciu (escortă, transport securizat, transport rapid etc.).</li>
            <li><strong>Factura</strong> – detalii de facturare pentru comenzile finalizate.</li>
        </ul>

        <h3>2.3 Procese principale</h3>
        <ol>
            <li>
                <strong>Înregistrare &amp; autentificare client</strong>:
                clientul își creează cont, este validat și primește rolul de Client.
            </li>
            <li>
                <strong>Creare cerere de transport</strong>:
                Clientul completează formularul cu detaliile transportului.
            </li>
            <li>
                <strong>Analiză și aprobare cerere</strong>:
                Operatorul verifică datele, stabilește ruta și costul, apoi transformă cererea în ComandăTransport.
            </li>
            <li>
                <strong>Alocare resurse</strong>:
                se alocă vehicul și echipaj, se actualizează statusul (Planificat, În derulare, Finalizat).
            </li>
            <li>
                <strong>Monitorizare și actualizare status</strong>:
                Operatorul actualizează progresul, Clientul poate vizualiza statusul în contul său.
            </li>
            <li>
                <strong>Facturare</strong>:
                la finalizarea cursei se generează factura asociată comenzii.
            </li>
        </ol>

        <h3>2.4 Relații între entități</h3>
        <ul>
            <li>Un <strong>Utilizator</strong> (Client) are un singur <strong>ClientProfil</strong>.</li>
            <li>Un <strong>Client</strong> poate avea mai multe <strong>CereriTransport</strong>.</li>
            <li>O <strong>CerereTransport</strong> aprobată devine o <strong>ComandaTransport</strong>.</li>
            <li>O <strong>ComandaTransport</strong> folosește o <strong>Ruta</strong> și unul sau mai multe <strong>Vehicule</strong>.</li>
            <li>Fiecare <strong>ComandaTransport</strong> are asociată, la final, o <strong>Factura</strong>.</li>
        </ul>

        <h3>2.5 Descriere succintă a bazei de date</h3>
        <p>Baza de date va fi relațională (ex: MySQL) și va include tabele precum:</p>
        <ul>
            <li><strong>users</strong>(id, username, parola_hash, rol, email, data_creare)</li>
            <li><strong>client_profiles</strong>(id, user_id, nume_companie, cui, adresa, persoana_contact)</li>
            <li><strong>services</strong>(id, nume, descriere, nivel_securitate)</li>
            <li><strong>vehicles</strong>(id, cod, tip, capacitate, nivel_protectie, disponibil)</li>
            <li><strong>routes</strong>(id, plecare, destinatie, distanta_km)</li>
            <li><strong>transport_requests</strong>(id, client_id, plecare, destinatie, tip_marfa, id_serviciu, data_ceruta, status)</li>
            <li><strong>orders</strong>(id, request_id, id_vehicul, id_ruta, pret, status, data_start, data_final)</li>
            <li><strong>invoices</strong>(id, order_id, suma, data_emitere, status_plata)</li>
        </ul>
    </section>

    <section>
        <h2>3. Descrierea soluției de implementare</h2>

        <h3>3.1 Tehnologii utilizate</h3>
        <ul>
            <li><strong>Frontend:</strong> HTML5, CSS3, JavaScript pentru interfața utilizator.</li>
            <li><strong>Backend:</strong> PHP (procesare formulare, logica aplicației, acces baza de date).</li>
            <li><strong>Bază de date:</strong> MySQL / MariaDB pentru stocarea datelor aplicației.</li>
            <li><strong>Server:</strong> Apache / Nginx (ex: XAMPP/Laragon pentru dezvoltare locală).</li>
        </ul>

        <h3>3.2 Organizarea aplicației</h3>
        <ul>
            <li>Separarea logicii de prezentare (fișiere PHP pentru procesare, fișiere PHP/HTML pentru interfață, CSS separat).</li>
            <li>Un modul pentru autentificare și gestionare roluri.</li>
            <li>Un modul pentru gestionarea cererilor și comenzilor de transport.</li>
            <li>Un modul pentru administrarea flotei, rutelor și serviciilor.</li>
        </ul>

        <h3>3.3 Model UML (descriere conceptuală)</h3>
        <p>
            Pentru documentație, se poate utiliza:
        </p>
        <ul>
            <li><strong>Diagramă de cazuri de utilizare</strong> – ilustrează interacțiunile dintre Client, Operator, Admin și sistem.</li>
            <li><strong>Diagramă de clase</strong> – reprezintă entitățile (User, ClientProfil, CerereTransport, ComandaTransport, Vehicul etc.) și relațiile dintre ele.</li>
            <li><strong>Diagramă de secvență</strong> pentru fluxul „Client trimite cerere transport –&gt; Operator aprobă –&gt; Sistem generează comandă”.</li>
        </ul>
        <p>
            Implementarea efectivă va urma această structură modulară, facilitând extinderea ulterioară a aplicației (ex: tracking în timp real, rapoarte detaliate, integrare API).
        </p>
    </section>

    <a class="btn" href="index.php">Înapoi la pagina principală</a>
</div>
</body>
</html>

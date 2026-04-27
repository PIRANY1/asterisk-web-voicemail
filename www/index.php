<?php
session_start();

// --- KONFIGURATION: Hier deine Daten eintragen ---
$default_extension = "100"; // Deine Nebenstelle
$default_password  = "deinpw123"; // Dein Passwort
// ------------------------------------------------

// Wir führen den Login-Prozess sofort automatisch aus
$socket = connect_to_server();
$m = "/login?extension=" . urlencode($default_extension) . "&password=" . urlencode($default_password);

socket_write($socket, $m, strlen($m));
socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 5, "usec" => 0));

$a = socket_read($socket, 2048);
$j = json_decode($a);

if ($j && $j->answer == "OK") {
    // Session füllen wie vorher
    $_SESSION["token"]     = $j->token;            
    $_SESSION["extension"] = $default_extension;            
    $_SESSION["folder"]    = "INBOX";            
    
    socket_close($socket);
    
    // Direkt weiter zum Dashboard
    header("Location: dashboard.php");
    exit(0);
} else {
    // Falls der Server doch mal "Nein" sagt oder nicht erreichbar ist
    die("Automatischer Login fehlgeschlagen. Antwort vom Server: " . ($j->message ?? 'Keine Antwort'));
}

// Die Verbindungsfunktion bleibt gleich, damit der Socket-Aufruf klappt
function connect_to_server() {
    $address = "127.0.0.1";
    $port = 4444;
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        die("socket_create() fehlgeschlagen: " . socket_strerror(socket_last_error()));
    }
    $result = socket_connect($socket, $address, $port);
    if ($result === false) {
        die("socket_connect() fehlgeschlagen. Server (Asterisk) unter $address:$port nicht erreichbar.");
    }
    return $socket;    
}
?>
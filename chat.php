<?php
    /* MIT license
     * 
     * Copyright 2024 Christian Lampe (alias Lampe2020)
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
     */
    
    /* Code formatting notice:
     * The code in this script is split into sections, that each start with a comment with ten dashes, the word "BEGIN" in all caps, the section name and another ten dashes. Each section ends with a similar comment, except that "BEGIN" is replaced with "END".   
     * All indentations are 4 spaces wide and saved as spaces, not tab characters, to make them look equal everywhere no matter the set tab width.   
     * All comments start on a column that's divisible by four (so wherever a tabstop lands), if needed indented by spaces.   
     * All multiline documentation comments have a title on their first line that optionally ends with a colon and each line begins with an asterisk in the same column as the asterisk in the multiline comment start token. If a multiline is inserted to disable certain functionality it does not have a title or asterisks on each line. The multiline comment end marker is placed such that its asterisk is in the same column as the start token's asterisk.   
     * As most text editors nowadays support soft-wrapping and most people can handle long lines, this script does not have any line length limitation.   
     */
    
    // ---------- BEGIN config variables ----------
    // This is where you configure this script: 
    
    $do_debug = true;    // (default: false) IMPORTANT: Set this to false when deploying!
    $lang = 'en';   // (default: 'en') Set this to any valid HTML two-letter language code (see the code section "translation strings" for more details)
    $allow_user_set_lang = true;    // (default: true) If this is set to true the user can specify through a cookie "lang" what language they want the page to be displayed in. If that cookie is not set or this option is disabled the language set in $lang will be used.
    $password_minimum_length = 8;   // (default: 8) A whole number that decides the minimum length that is accepted for any user's password. It is recommended to let this be on the default value.
    $chat_min_refresh_delay = 5;    // (default: 5) The minimum amount of time to wait between chat refreshes. If this is 1 or lower all refresh requests are honoured and just wait for the set amount of time if the delay isn't over yet and no other waiting refresh request is running in parallel. Negative numbers are treated as 0.
    /* Currently disabled quality-of-life functionality, will maybe enable when base functionality is there. 
    $chat_disable_refreshless_update = false;   // (default: false) If this is set to true the script will not attempt to keep the connection open and automatically append more messages to the already-sent chat log in regular intervals. This will have the benefit of not needing to reload the chat log iframe over and over again, as well as keeping more than the server-side message limit visible, at the cost of automatic scrolling not properly working and there being a connection for each client, that is kept open until the user leaves.
    $chat_refreshless_update_rate = 3;  // (default: 3) This is the interval in which the server will check for new chat messages to append to the client's chat log. 
    */
    $chat_min_msg_delay = 3;    // (default: 3) The minimum amount of time to wait between sent chat messages. If this is 1 or lower all message send requests are honoured and just wait for the set amount of time if the delay isn't over yet and no other waiting message send request is running in parallel. Negative numbers are treated as 0.
    $chat_msg_max_len = 0xfff;  // (default: 4095) The maximum number of characters per message. This is validated both client-side (to avoid unecessary requests) and server-side (to prevent cheaters from circumventing the rules).
    $chat_max_msgs_per_request = 0xff;  // (default: 255) This limits how many messages a user can load in one request.
    $chat_disable_spectate = true;  // (default: true) If this is set to false users can read the chat without being logged in. This allows them to read all chats, including those that they have been banned from. Because of that, spectating is disabled by default and recommended to be left disabled.
    $signin_require_valid_email = true; // (default: true) If this option is disabled (_NOT_ recommended!) the OTC for signin is autofilled in the dialog that asks for it instead of being sent via email.
    $autoredirect_delay = 3;    // (default: 3) The number of seconds to wait before automatically redirecting. Should in most cases be left at the default value. Negative numbers are treated as 0.
    $main_chat_room_name = 'general';   // (default: 'general') The name of the main chat room. It is automatically created, can not be deleted and users that are banned from it are banned from all rooms. This is also where everyone goes if they send the /home command. In most cases this can be left at the default value. 
    $favicon = '/favicon.ico';  // (default: '/favicon.ico') The URL of the favicon to be used on all pages of this script. This can in most cases be left at the default value. 
    $site_name = 'JSFC'; // The name of your site. It is displayed in each page's title.
    $site_admin = 'webmaster'; // (default: 'webmaster') Enter your email address here if you want users to be able to contact you in the case of an error or question. If you set this to a value containing no @ character, the server assumes it means a username on the server this script is ran on and automatically completes this with the value from $_SERVER['SERVER_NAME']. Note that @localhost addresses will not work for this, as that would make users' email service send email to themselves instead of you.
    $chatmaster = 'chatmaster';    // (default: 'chatmaster') This is the username that has full administrative privileges over the entire chat application. This one user is fully immune to any rate limits set by this script. Note that the registration form will block this username as existing even if it doesn't already exist, so its user definition file has to manually be created and populated with username, password hash and email address. This is to make it as hard as possible to illegitmiately gain access to this high-privileged account. 
    $secret_data_location = '/tmp/.chat-secrets';   // (default: '/tmp/.chat-secrets') This is the server-side path to a directory for this script to store logins and the chat logs in. This should not be in a publicly-accessible directory, as otherwise the stored accounts could easily be compromised and possibly also external accounts that use the same or similar login details. By default this is in /tmp, but if you want accounts in this script to be permanent you should change this to a persistent storage location. Note that /tmp and /var/tmp on Linux may actually land inside /tmp/systemd-private-*-apache2.service-*/ (if the apache2.service file has the PrivateTmp option enabled).
    $password_hash_algorithm = PASSWORD_DEFAULT;    // (default: PASSWORD_DEFAULT) The password algorithm to use for storing and validating the users' passwords. This should be left at the default, but if you don't want this to change when you switch PHP versions you should specify which one to use.
    
    // ---------- END config variables ----------
    
    /* Idéer: (kommentaren kommer raderas när alla idéer är implementerade)
     * Implementera chattrum genom kommandot /room, meddelanden sparade i separata loggfil, sessionsvariablerna visar vilket rum en användare är i. Kommandot /exit går tillbaka till huvudrummet och loggar användaren ut om den är i huvudrummet. Bara administratörer kan skapa eller radera rum, alla rumsadministratörer kan utesluta någon ur rummet. Bara administratörer kan ge eller ta bort administratörsrollen från någon, men administratörerna kan säga upp sig själva genom kommandot /resign i rummet de vill säga upp sig i. 
     * Flytta skrivformuläret till huvudfönstret, sparar förfrågningar och uppdaterar chatten automatiskt direkt. 
     * Visa vilka meddelanden är egna genom högerjustering (selektor .chatmsg.msg_by_{username}) { text-align: right; } )
     * Möjliggör visuella pings genom att färglägga alla meddelanden som svarar på användaren
     */
    
    // DO NOT change anything after this line if you don't know what you're doing!
    
    if ($do_debug) { // If debugging is enabled, enable PHP error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
    }
    
    $release_version = '0.1.1';
    $release_channel = 'alpha';
    
    $supported_langs = ['en','de','sv'];    // This array contains every language code that is supported by this script
    if ($allow_user_set_lang && isset($_COOKIE['lang'])) {  // If the 
        $lang = $_COOKIE['lang'];
    }
    if (!in_array($lang, $supported_langs)) {
        $lang = 'en';   // If an unsupported language is selected anywhere, default back to English
    }
    if (!str_contains('@', $site_admin)) {
        $site_admin = $site_admin.'@'.$_SERVER['SERVER_NAME'];
    }
    $whereami = $_SERVER['PHP_SELF'];   // Retrieve where we are
    //$whereami = $_SERVER['REQUEST_URI'];    // Retrieve where we are, alternative method
    $autoredirect_delay = max(0, round($autoredirect_delay));   // Ensure that the redirect delay is a positive whole number
    $chat_min_msg_delay = max(0, round($chat_min_msg_delay));   // Ensure that the message delay is a positive whole number
    $chat_min_refresh_delay = max(0, round($chat_min_refresh_delay));   // Ensure that the refresh delay is a positive whole number
    $username_regex = '[a-zA-Z_][a-zA-Z0-9_]*';
    
    ob_start();
    // The template for every page this script outputs. ?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
    <head>
        <meta charset="utf-8">
        <link rel="icon" href="<?php echo $favicon; ?>">
        <title>{page_title} • <?php echo $site_name; ?></title>
        <meta name="description" content="{page_desc}">
        <style>
            /* ---------- BEGIN general style ---------- */
            :root {
                color-scheme: light dark;
            }
            
            * {
                                /* fg bg */
                scrollbar-color: grey transparent;
            }
            
            code {
                font-family: monospace;
                color: black;
                background-color: grey;
                padding: .3em;
                padding-top: .1em;
                padding-bottom: .1em;
                border-radius: .3em;
            }
            
            fieldset.warning {
                color: black;
                background-color: red;
            }
            fieldset.warning > legend {
                color: black;
                background-color: red;
            }
            fieldset.warning > legend:before {
                content: "⚠ ";   /* U+26A0 */
            }
            
            /* ---------- END general style ---------- */
            
            /* ---------- BEGIN login style ---------- */
            
            input[type=number][name=otc] {
                -moz-appearance: textfield;
            }
            input[type=number][name=otc]::-webkit-outer-spin-button,
            input[type=number][name=otc]::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            
            .overlay {
                position: fixed;
                top: 0px;
                left: 0px;
                right: 0px;
                bottom: 0px;
                background-color: rgba(0,0,0,.3);
                backdrop-filter: blur(1px);
            }
            .overlay-text {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                border-radius: 8px;
                background-color: darkgreen;
                max-width: 99%;
                max-height: 99%;
                overflow: scroll;
            }
            
            fieldset {
                user-select: none;
            }
            fieldset > legend {
                border-radius: 3px;
                border-bottom: 1px solid grey;
            }
            
            fieldset label:has(input[type=text], input[type=email], input[type=password]) {
                display: flex;
                flex-direction: column;
                margin: .1em;
            }
            
            fieldset div.buttonbar {
                margin-top: .3em;
                border-top: 1px solid grey;
                padding-top: .3em;
                text-align: center;
            }
            
            body:has(#signup:target) #login {
                display: none;
            }
            #signup, #recoveraccount {
                display: none;
            }
            #signup:target, #recoveraccount:target {
                display: block;
            }
            #recoveraccount {
                display: hidden;
            }
            
            #login fieldset, #signup fieldset {
                display: flex;
                flex-direction: column;
            }
            
            #login fieldset input:not([type=submit]), #signup fieldset input:not([type=submit]), #recoveraccount fieldset input:not([type=submit]) {
                float: right;
            }
            
            /* ---------- END login style ---------- */
            
            @media (prefers-color-scheme: dark) {
                /* Darkmode-specific stuff */
            }
            
            /* ---------- BEGIN chat style ---------- */
            
            div.chatmsg:target {
                color: black;
                background-color: orange;
            }
            
            div.chatmsg {
                position: relative;
                border: 1px solid lightblue;
                border-radius: .3em;
                padding: .3em;
                display: flex;
                flex-direction: column;
                margin: .3em;
                transition: color .3s ease-in-out, background-color .3s ease-in-out;
                overflow-anchor: none;
            }
            
            @keyframes newmsg {
                0% {
                    left: 100vw;
                    opacity: 0%;
                },
                100% {
                    left: 0vw;
                    opacity: 100%;
                }
            }
            
            :has(div.chatmsg.chatmsg_new) {
                overflow-x: hidden;
            }
            div.chatmsg.chatmsg_new {
                left: 0px;
                animation: newmsg;
                animation-duration: .3s;
                animation-fill-mode: forwards;
                overflow-anchor: auto;
            }
            
            div.msgbtns {
                position: absolute;
                top: 0px;
                right: 0px;
                padding: .3em;
                display: flex;
                flex-direction: row;
                pointer-events: none;
                opacity: 0%;
                transition: opacity .3s ease-in-out;
            }
            div.chatmsg:hover div.msgbtns {
                opacity: 50%;
                pointer-events: initial;
            }
            div.chatmsg:hover div.msgbtns:hover {
                opacity: 100%;
            }
            
            div.servermsg, div.chatmsg.msg_by_<?php echo $chatmaster; ?> {
                background-color: red;
                color: white;
                border: 1px dashed yellow;
            }
            
            small.msg_timestamp {
                color: grey;
                float: right;
            }
            
            span.username {
                font-weight: bold;
                width: fit-content;
                padding: .1em;
                border-radius: .3em;
                float: left;
            }
            
            a.answerto {
                padding: .3em;
                padding-bottom: 0px;
                text-decoration: none;
                border-radius: .3em;
                color: grey;
                max-width: fit-content;
                transition: color .3s ease-in-out, background-color .3s ease-in-out;
            }
            a.answerto::before {
                content: "⮣ "; /* U+2BA3 */
            }
            a.answerto:hover {
                color: blue;
                background-color: orange;
            }
            
            #scrolltobottom {
                scroll-margin-top: 0px;
                scroll-margin-bottom: 100vh;
            }
            
            /* ---------- END chat style ---------- */
            
            /* ---------- BEGIN chat view style ---------- */
            
            body:has(iframe.chatview) {
                display: flex;
                flex-direction: column;
                width: 100vw;
                height: 100vh;
                padding: 0px;
                margin: 0px;
            }
            
            iframe.chatview {
                flex: 1;
                margin: 0px;
                border: none;
            }
            
            iframe.writeform {
                height: 2em;
                margin: 0px;
                border: none;
            }
            
            div.btnbar {
                position: fixed;
                bottom: 1em;
                right: 1em;
                margin: .3em;
            }
            
            input[type=submit]#pausebtn {
                font-family: monospace;
                padding: 1em;
            }
            
            /* ---------- END chat view style ---------- */
            
            /* ---------- BEGIN message write form style ---------- */
            
            body:has(input[type=text][name=msg]) {
                width: 100vw;
                height: 100vh;
                padding: 0px;
                margin: 0px;
            }
            
            body:has(input[type=text][name=msg]) form {
                display: flex;
                flex-direction: row;
            }
            
            input[type=text][name=msg] {
                flex: 1;
            }
            
            /* ---------- END message write form style ---------- */
        </style>
        {additional_headers}
    </head>
    <body>
        {page_body}
    </body>
</html><?php
    $emptydoc = ob_get_clean();
    
    function untemplate($to_insert, $template) {
        $result = $template;
        foreach($to_insert as $placeholder=>$value) {
            $result = str_replace(
                '{'.$placeholder.'}',
                $value,
                $result
            );
        }
        global $do_debug;
        if ($do_debug) {
            global $lang;
            global $emptydoc;
            if ($template===$emptydoc && $lang!=='en') {    // If the template being used is $emptydoc and debugging is enabled, insert the translation summary at the bottom.
                ob_start();
                global $untranslated;
                var_dump($untranslated);
                $result = str_replace(
                    "</body>\n</html>",
                    "    <pre>Untranslated messages (in order of first translation request): \n".htmlentities(ob_get_clean())."        </pre>\n    </body>\n</html>",
                    $result);
            }
        }
        return $result;
    }
    
    $untranslated = [];
    $translations = [
        /* Translation notice:
         * All translation strings are defined in the section "translation strings" below, in the form of arrays.   
         * The whole section is inside an array, whose elements have the English string as their key and an array with translations of that single string as their value.   
         * Each translation subarray has keys for each language that that specific string is translated to (a two-letter HTML language code, as accepted in the HTML lang="" attribute) with the corresponding value being the translated string.   
         * Some strings contain words in squirly braces {}. These are variables that _MUST NOT_ be translated. They are placeholders for values to be inserted after translation by simple string replacement; changing these in any way can disrupt functionality that involves the changed strings.   
         */
        // ---------- BEGIN translation strings ----------
        'JS-free chat' => [
            'de' => 'JS-freier Chat',
            'sv' => 'JS-fri chatt'
        ],
        'An online chat room that works purely in HTML5 and CSS3' => [
            'de' => 'Ein Onlinechatraum der in purem HTML5 und CSS3 funktioniert',
            'sv' => 'Ett onlinechattrum som funkar helt i HTML5 och CSS3'
        ],
        'Login' => [
            'de' => 'Anmeldung',
            'sv' => 'Inloggning'
        ],
        'Log in' => [
            'de' => 'Anmelden',
            'sv' => 'Logga in'
        ],
        'Signup' => [
            'de' => 'Registrierung',
            'sv' => 'Registrering'
        ],
        'Sign up' => [
            'de' => 'Registrieren',
            'sv' => 'Registrera'
        ],
        'Username: ' => [
            'de' => 'Benutzername: ',
            'sv' => 'Användarnamn: '
        ],
        'Password: ' => [
            'de' => 'Passwort: ',
            'sv' => 'Lösenord: '
        ],
        'Your username' => [
            'de' => 'Dein Benutzername',
            'sv' => 'Ditt användarnamn'
        ],
        'Your password' => [
            'de' => 'Dein Passwort',
            'sv' => 'Ditt lösenord'
        ],
        'Repeat your password: ' => [
            'de' => 'Wiederhole Dein Passwort: ',
            'sv' => 'Upprepa ditt lösenord: '
        ],
        'I lost my password' => [
            'de' => 'Passwort verloren',
            'sv' => 'Tappade lösenordet'
        ],
        'Email: ' => [
            'de' => 'E-Mail: ',
            'sv' => 'Epost-adress: '
        ],
        'Email' => [
            'de' => 'E-Mail',
            'sv' => 'Epost-adress'
        ],
        'Account recovery' => [
            'de' => 'Kontowiederherstellung',
            'sv' => 'Lösenordsåterställning'
        ],
        'Send recovery email' => [
            'de' => 'Wiederherstellungsemail schicken',
            'sv' => 'Skicka återställningsmeddelande'
        ],
        'Cancel' => [
            'de' => 'Abbrechen',
            'sv' => 'Avbryt'
        ],
        'Server-side misconfiguration' => [
            'de' => 'Serverseitige Fehlkonfiguration',
            'sv' => 'Felkonfiguration på servern'
        ],
        'This chat page is non-functional because of an internal error.' => [
            'de' => 'Diese Chatseite funktioniert wegen eines Serverinternen Fehlers nicht.',
            'sv' => 'Denna chattsida funkar inte på grund av ett serverinternt fel.'
        ],
        'The chat application has crashed!' => [
            'de' => 'Die CHatanwendung ist abgestürzt!',
            'sv' => 'Chattapplikationen har kraschat!'
        ],
        'If you don\'t know what\'s going on, try again later.<br>Incase the issue persists contact the administrator at the following email address: {admin_email}.' => [
            'de' => 'Wenn Du nicht weißt was los ist, versuch\'s später noch einmal.<br>Sollte das Problem weiterhin bestehen, kontaktiere den Serveradministrator unter folgender E-Mailadresse: {admin_email}.',
            'sv' => 'Om du inte vet vad som hände, försök igen om en stund.<br>Ifall problemet kvarstår, kontaktera gärna serveradministratören via epost på följande adress: {admin_email}.'
        ],
        'If you are the server admin:' => [
            'de' => 'Wenn Du der Administrator bist:',
            'sv' => 'Om du är administratören:'
        ],
        'Please ensure that the directory in <code title="code section &quot;config variables&quot;">$secret_data_location</code> is readable and writeable by the chat script.' => [
            'de' => 'Stelle bitte sicher dass der Ordner in <code title="Codeabschnitt &quot;config variables&quot;">$secret_data_location</code> vom Chatskript gelesen und geschrieben werden kann.',
            'sv' => 'Säkerställ snälla att platsen i <code title="Kodavsnitt &quot;config variables&quot;">$secret_data_location</code> är läs- och skrivbart av chatskriptet.'
        ],
        'Answer @{username}' => [
            'de' => '@{username} antworten',
            'sv' => 'Svara @{username}'
        ],
        'You can\'t do that!' => [
            'de' => 'Du darfst das nicht tun!',
            'sv' => 'Du får inte göra detta!'
        ],
        'The action you tried to perform is invalid or disabled!' => [
            'de' => 'Die Aktion die Du ausführen wolltest ist ungültig oder deaktiviert!',
            'sv' => 'Aktionen du ville göra är ogiltig eller inaktiverad!'
        ],
        'The action you tried to perform, <code>{action}</code>, was not recognized by the chat application or is disabled.' => [
            'de' => 'Die Aktion die Du ausführen wolltest, <code>{action}</code> wurde von der Chatanwendung nicht erkannt oder ist deaktiviert.',
            'sv' => 'Aktionen du ville göra, <code>{action}</code>, kändes inte igen av chattapplikationen eller är inaktiverad.'
        ],
        'Errm, this shouldn\'t happen…' => [
            'de' => 'Ähm, das sollte nicht passieren…',
            'sv' => 'Öhm, detta skulle inte hända…'
        ],
        'It doesn\'t look like you should have access to this session!' => [
            'de' => 'Es sieht nicht so aus, als solltest Du Zugang zu dieser Sitzung haben!',
            'sv' => 'Det ser inte ut som att du skulle ha åtkomst till denna session!'
        ],
        'To the server it looks as if you have illegitmiately gained access to this session!' => [
            'de' => 'Für den Server sieht es aus als ob Du auf unzulässigem Wege Zugang zu dieser Sitzung erhalten hast!',
            'sv' => 'Det ser ut för servern som att du har fått åtkomst till denna session på något otillåtet sätt!'
        ],
        'WARNING!' => [
            'de' => 'WARNUNG!',
            'sv' => 'VARNING!'
        ],
        'It looks like your session is no longer safe!' => [
            'de' => 'Es sieht aus als ob Deine Sitzung nicht mehr sicher wäre!',
            'sv' => 'Det ser ut som att din session inte längre är säker!'
        ],
        'WARNING: Session hijacked!' => [
            'de' => 'WARNUNG! Sitzung gekapert!',
            'sv' => 'VARNING! Session kapad!'
        ],
        'It looks like someone has hijacked your session!<br>It is strongly recommended that you log out and log back in to generate a new session.' => [
            'de' => 'Es sieht so aus als habe jemand Deine Sitzung gekapert!<br>Es wird dringend empfohlen, dass Sie Sich ab- und wieder anmelden, um eine neue Sitzung zu generieren.',
            'sv' => 'Det ser ut som att någon har kapat din session!<br>Det rekommenderas starkt att du loggar ut och in igen för att generera en ny session.'
        ],
        'Log out' => [
            'de' => 'Ausloggen',
            'sv' => 'Logga out'
        ],
        'Ignore' => [
            'de' => 'Ignorieren',
            'sv' => 'Ignorera'
        ],
        'Successfully ignored session hijack!' => [
            'de' => 'Sitzungskaperung erfolgreich ignoriert!',
            'sv' => 'Sessionskapringen ignorerades framgångsrikt!'
        ],
        'The suspected session hijack was successfully erased from the session variables.' => [
            'de' => 'Die mutmaßliche Sitzungskaperung wurde erfolgreich aus den Sitzungsvariabeln entfernt.',
            'sv' => 'Den misstänkta sessionskapningen har framgångsrikt raderats från sessionsvariablerna.'
        ],
        'The session hijack has been cleared out of the server\'s memory.<br>Note that if another hijack should be detected, the warning will be issued again.<br>You will be redirected to the action you tried to perform when you received the warning; this will fail should that action have required hidden variables.' => [
            'de' => 'Die mutmaßliche Sitzungskaperung wurde erfolgreich aus dem Arbeitsspeicher des Servers entfernt.<br>Hinweis: sollte ein erneuter Übernahmeversuch entdeckt werden, wird die Warnung wieder angeszeigt.<br>Du wirst zu der Aktion die Du ausführen wolltest als Du die Warnung bekommen hast. Dies wird fehschlagen, sollte diese Aktion versteckte Variabeln benötigen.',
            'sv' => 'Den misstänkta sessionskapningen raderades framgångsrikt ur serverns arbetsminne.<br>Tips: om ett nytt kapningsförsök detekteras ser du varningen igen.<br>Du kommer att omdirigeras till aktionen du försökte göra när du fick varningen; detta kommer att misslyckas om denna aktion behövde dolda variabler.'
        ],
        'Signup error' => [
            'de' => 'Registrierugsfehler',
            'sv' => 'Registreringsfel'
        ],
        'There was an error creating an account for you.' => [
            'de' => 'Beim Erstellen eines Benutzerkontos für Dich ist ein Fehler aufgetreten.',
            'sv' => 'Ett fel uppstod när kontot skulle skapas för dig.'
        ],
        '<h2>Illegal username!</h2><br>Allowed usernames…<ul><li>…begin with a letter from a~z in upper- or lowercase, or an underscore.</li><li>…only contain letters from a~z in upper- or lowercase, numbers from 0~9, or underscores.</li><li>…are 3~15 characters long.</li></ul>' => [
            'de' => '<h2>Illegaler Benutzername!</h2><br>Erlaubte Benutzernamen…<ul><li>…fangen mit einem Groß- oder Kleinbuchstaben von a~z oder einem Unterstrich an.</li><li>…enthalten nur Groß- oder Kleinbuchstaben von a~z, Ziffern von 0~9 oder Unterstriche.</li><li>…sind 3~15 Zeichen lang.</li></ul>',
            'sv' => '<h2>Illegalt användarnamn!</h2><br>Tillåtna användarnamn…<ul><li>…börjar med en små- eller storbokstav från a~z eller ett understreck.</li><li>…innehåller bara små- eller storbokstäver från a~z, siffror från 0~9 eller understreck.</li><li>…är 3~15 tecken långa.</li></ul>'
        ]
        // ---------- END translation strings ----------
    ];
    function translate($str) {
        global $lang;
        if ($lang==='en') {
            return $str;
        }
        global $translations;
        global $do_debug;
        global $untranslated;
        if ($do_debug && !array_key_exists($str, $translations) && !in_array($str, $untranslated)) {
            array_push($untranslated, $str);
        }
        return $translations[$str][$lang] ?? $str;  // ??-operator: return the first, otherwise the second.
    }
    
    function mkpath($path) {    // https://stackoverflow.com/a/6650840
        if(@mkdir($path) or file_exists($path)) {
            return true;
        }
        return (mkpath(dirname($path)) and mkdir($path));
    }
    
    // Ensure the file system is set up correctly for this script to work
    mkpath($secret_data_location);  // Create the dir if it doesn't exist, ignore it if it already does.
    if (!is_dir($secret_data_location)) {
        http_response_code(500);
        die(untemplate([
            'page_title' => translate('Server-side misconfiguration'),
            'page_desc' => translate('This chat page is non-functional because of an internal error.'),
            'additional_headers' => '',
            'page_body' => untemplate([
                'location' => $secret_data_location,
                'admin_email' => '<a href="mailto:'.htmlentities($site_admin).'">'.htmlentities($site_admin).'</a>'
            ], 
            '<h2>'.translate('The chat application has crashed!').'</h2>'.translate('If you don\'t know what\'s going on, try again later.<br>Incase the issue persists contact the administrator at the following email address: {admin_email}.').'<br><br><details><summary>'.translate('If you are the server admin:').'</summary>'.translate('Please ensure that the directory in <code title="code section &quot;config variables&quot;">$secret_data_location</code> is readable and writeable by the chat script.').'</details>'), 
        ], $emptydoc));
    }
    
    // Start the session
    session_set_cookie_params(['path'=>$whereami]); // Specify that the session cookie should only be set for the chat application
    session_start();    // Try to pick up an existing session or create a new one
    if (isset($_SESSION['username'])) {
        if (!isset($_SESSION['ip'])) {  // Save the IP address from which the session has been created, to make session hijacking harder
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        } else if ($_SESSION['ip']!==$_SERVER['REMOTE_ADDR']) { // Block the request if the session seems to be hijacked
            http_response_code(403);    // 403 Forbidden
            $_SESSION['stolen'] = $_SERVER['REMOTE_ADDR'];  // Remember that the session has been stolen and by whom
            unset($_COOKIE['PHPSESSID']);   // Remove the server-side cookie entry
            setcookie('PHPSESSID', '', -1, $whereami);  // and also tell the user that they've been removed from the session.
            die(untemplate([
                'page_title' => translate('Errm, this shouldn\'t happen…'),
                'page_desc' => translate('It doesn\'t look like you should have access to this session!'),
                'additional_headers' => '<meta name="robots" content="noindex">',
                'page_body' => translate('To the server it looks as if you have illegitmiately gained access to this session!')
            ], $emptydoc));
        } else if (isset($_SESSION['stolen']) and !(isset($_GET['action']) && in_array($_GET['action'], ['ignorehijack', 'logout']))) { // Tell the user that their session has been hijacked and they should log off and on again
            ob_start();
            ?>
            <div class="overlay">
                <fieldset class="overlay-text warning"><legend><?php echo translate('WARNING!'); ?></legend>
                    <span style="text-align:center;"><?php echo untemplate([
                        'suspect_ip' => $_SESSION['stolen']
                    ], translate('It looks like someone has hijacked your session!<br>It is strongly recommended that you log out and log back in to generate a new session.')); ?></span>
                    <div style="display:flex;flex-direction:row;padding:.3em;margin-top:.3em;border-top:1px solid grey;">
                        <form action="<?php echo $whereami; ?>" method="GET" style="padding-right:.3em;" target="<?php
                            if (isset($_GET['action']) && in_array($_GET['action'], ['viewchat','answertomsg','getchat','spectatechat','sendmsg','getwriteform'])) {
                                echo '_parent';
                            } else {
                                echo '_self';
                            }
                        ?>">
                            <input type="hidden" name="action" value="logout">
                            <input type="submit" value="<?php echo translate('Log out'); ?>" autofocus>
                        </form>
                        <form action="<?php echo $whereami; ?>?action=ignorehijack" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo ((isset($_GET['action']))?$_GET['action']:''); ?>">
                            <?php //TODO: If the request is POST, also save the POST request parameters to fulfill the original request ?>
                            <input type="submit" value="<?php echo translate('Ignore'); ?>">
                        </form>
                    </div>
                </fieldset>
            </div><?php
            http_response_code(409);    // 409 Conflict (conflict with the server's state, in this case the session is potentialy compromised and to perform any action the user must first confirm they )
            die(untemplate([
                'page_title' => translate('WARNING: Session hijacked!'),
                'page_desc' => translate('It looks like your session is no longer safe!'),
                'additional_headers' => '<meta name="robots" content="noindex">',
                'page_body' => ob_get_clean()
            ], $emptydoc));
        }
        
        // Ensure that the user always has a place to go:
        if (!isset($_SESSION['chatroom'])) {
            $_SESSION['chatroom'] = $main_chat_room_name;
        }
    } else {
        // If no username is set, don't check if the session might be hijacked as no user is logged into it.
    }
    
    function getFileExclusiveWriteAccess($fname) {
        if (!is_file($fname)) {
            $fh = fopen($fname, 'w+');
        } else {
            $fh = fopen($fname, 'r+');
        }
        if (flock($fh, LOCK_EX)) {
            return $fh;
        } else {
            throw new Error(untemplate(['filename'=>$fname], translate("Could not aquire write lock for {filename}!")));
        }
    }
    
    function getFileExclusiveReadAccess($fname) {
        $fh = fopen($fname, 'r');
        if (flock($fh, LOCK_EX)) {
            return $fh;
        } else {
            throw new Error(untemplate(['filename'=>$fname], translate("Could not aquire read lock for {filename}!")));
        }
    }
    
    function releaseFileLock($fh) {
        fflush($fh);
        return fclose($fh, LOCK_UN);
    }
    
    function createFile($fname) {
        if (!is_file($fname) && !is_dir($fname)) {
            return file_put_contents('');
        }
    }
    
    $cached_user_defs = [];
    function getUserDef($user) {
        global $cached_user_defs;
        if (in_array($user, $cached_user_defs)) {
            return $cached_user_defs[$user];
        } else {
            $userfile = "$secret_data_location/user/by-name/$user.json";
            $fh = getFileExclusiveReadAccess($userfile);
            fseek($fh, 0);  // Ensure that we are at the beginning of the file
            $userdef = json_decode(fread($fh, filesize($userfile)), true);
            flock($fh, LOCK_UN);    // Release the lock
            fclose($fh);
            $cached_user_defs[$user] = $userdef;    // Cache the user definition to reduce disk usage
            return $userdef;
        }
    }
    
    function checkUserPermission($user, $perm) {
        global $chatmaster;
        if ($user===$chatmaster) {
            // Chatmaster is allowed to do whatever they desire, no matter if the permission actually is defined in their user file or not.
            return true;
        }
        $userdef = getUserDef($user);
        return in_array($perm, $userdef['permissions']);
    }
    
    $cached_user_room_perms = [
        $main_chat_room_name => []
    ];
    function roomCheckUserPermission($user, $perm, $room) {
        //TODO: Implement this!
        echo "roomCheckUserPermission not implemented!";
        return false;
    }
    
    function formatChatMsg($msgid, $msg, $timeLastLoaded=null, $interactive=true) {
        // Example message:             1726215308<Lampe2020@>Hello, how're you feelin' today?
        // Example answering message:   1726215357<Testuser@Lampe2020:0>Very well, you?
        global $whereami;
        [$timestamp, $rest] = explode('<', $msg, 2);
        [$at, $message] = explode('>', $rest, 2);
        [$username, $answerto] = explode('@', $at, 2);
        if ($interactive) {
            ob_start();
        ?><div class="msgbtns">
                <form action="<?php echo $whereami; ?>?action=answertomsg&paused=true" method="POST" enctype="multipart/form-data" target="_parent">
                    <input type="hidden" name="username" value="<?php echo $username; ?>">
                    <input type="hidden" name="timestamp" value="<?php echo $msgid; ?>">
                    <input type="submit" title="<?php echo str_replace('{username}', $username, translate('Answer @{username}')); ?>" value="&#x2BA3;">
                </form>
            </div><?php
            $buttons = ob_get_clean();
        } else {
            $buttons = '';
        }
        ob_start();
        if ($timestamp >= $timeLastLoaded) {
            $isnew = ' chatmsg_new';
        } else {
            $isnew = '';
        }
        $retval = '<div class="chatmsg'.$isnew.' msg_by_'.$username.'" id="msg_'.$msgid.'">';
        if ($answerto) {    // Add the answer reference
            [$answerto_username, $answerto_msgid] = explode(':', $answerto, 2);
            ob_start();
        ?>
            <!-- TODO: Maybe pause the chat when the user clicks on a message highlight link? -->
            <a class="answerto" id="answerto_<?php echo $answerto_username; ?>_<?php echo $answerto_msgid; ?>" href="<?php echo $whereami; ?>?action=getchat&paused=true#msg_<?php echo $answerto_msgid; ?>"> @<?php echo $answerto_username; ?></a>
            <style>
                body:has(a.answerto#answerto_<?php echo $answerto_username; ?>_<?php echo $answerto_msgid; ?>:hover) div.chatmsg.msg_by_<?php echo $answerto_username; ?>#msg_<?php echo $answerto_msgid; ?> {
                    background-color: orange;
                    color: black;
                }
            </style>
        <?php
            $retval = $retval.ob_get_clean();
        }
        return $retval.'<small class="msg_timestamp">'.date('Y-m-d H:i:s', ((int)$timestamp)).'</small><span class="username">@'.$username.'</span><span class="msg_body">'.htmlentities($message).'</span>'.$buttons.'</div>';
    }
    
    function find_user($name) {
        global $secret_data_location;
        return is_file("$secret_data_location/user/by-name/$name.json");
    }
    
    function find_user_by_email($email) {
        global $secret_data_location;
        $hash = md5($email);
        return is_file("$secret_data_location/user/by-email/$hash.json");
    }
    
    function verify_user_password($username, $password) {
        global $secret_data_location;
        $userfile = $secret_data_location.'/user/by-name/'.$_POST['username'].'.json';
        $fh = getFileExclusiveReadAccess($userfile);
        $result = password_verify($_POST['password'], json_decode(fread($fh, filesize($userfile)), true)['password']);
        flock($fh, LOCK_UN);
        fclose($fh);
        return $result;
    }
    
    function msgfilter($msg) {
        $msg = str_replace("\r", ' ', str_replace("\n", ' ', $msg));    // Remove all line breaks from the message
        return $msg;
    }
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            //TODO: Rate-limit each action seperately to reduce server load, using HTTP error "429 Too Many Requests" with the timeout in the "Retry-After" header.
            case 'testchat': {
                if (!$do_debug) {
                    goto invalidaction;
                }
                die(untemplate([
                    'page_title' => 'Chat test',
                    'page_desc' => base64_encode('Alle meine Entchen...'),
                    'additional_headers' => '<meta name="robots" content="noindex">',
                    'page_body' => formatChatMsg(0, "1726215308<Lampe2020@>Hello, how're you feelin' today?", 1726215309).formatChatMsg(1, "1726215357<Testuser@Lampe2020:0>Very well, you?", 1726215309)
                ], $emptydoc)); //debug
            }
            case 'logout': {
                session_destroy();
                die(untemplate([
                    'page_title' => translate('Logout'),
                    'page_desc' => translate('Your logout request has been processed.'),
                    'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'">',
                    'page_body' => translate('You\'ve been logged out.')
                ], $emptydoc));
                break;
            }
            case 'signup': {
                function signup_error($reason) {
                    global $whereami;
                    global $emptydoc;
                    die(untemplate([
                        'page_title' => translate('Signup error'),
                        'page_desc' => translate('There was an error creating an account for you.'),
                        'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'#signup">',
                        'page_body' => $reason
                    ], $emptydoc));
                }
                if (
                    isset($_POST['username']) &&
                    isset($_POST['password']) &&
                    isset($_POST['password2']) &&
                    isset($_POST['email'])
                ) {
                    if ($_POST['password'] !== $_POST['password2']) {
                        signup_error(translate('The passwords don\'t match!'));
                    } else if (strlen($_POST['password'] < $password_minimum_length)) {
                        signup_error(translate('Your password is too short with {len} characters, it must be at least {minlen} characters long!'));
                    } else if (!preg_match("/^(?:$username_regex)$/", $_POST['username']) || strlen($_POST['username'])<3 || strlen($_POST['username'])>15) {
                        signup_error(translate('<h2>Illegal username!</h2><br>Allowed usernames…<ul><li>…begin with a letter from a~z in upper- or lowercase, or an underscore.</li><li>…only contain letters from a~z in upper- or lowercase, numbers from 0~9, or underscores.</li><li>…are 3~15 characters long.</li></ul>'));
                    } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        signup_error(translate('That\'s not an email address, silly!'));
                    } else if (find_user($_POST['username']) || $_POST['username']===$chatmaster) {
                        signup_error(translate('That account name is unfortunately already taken!'));
                    } else if (find_user_by_email($_POST['email'])) {
                        signup_error(translate('Please use another email address or try to log into your existing account!'));
                    } else if (isset($_SESSION['signin_otc']) || isset($_SESSION['signin_details'])) {
                        signup_error(translate('Sorry, you can\'t register right now!'));
                    } else {
                        $_SESSION['signin_otc'] = rand(100000, 999999); // Generate a random number with 6 digits
                        $_SESSION['signin_details'] = json_encode([ // Save the requested account details in the session in order to be able to use them in a different request without revalidation
                            'username' => $_POST['username'],
                            'password' => $_POST['password'],
                            'email' => $_POST['email']
                        ]);
                        if ($signin_require_valid_email) {
                            mail(
                                $_POST['email'],
                                translate('[AUTOMATED] Sign-up verification code'),
                                untemplate([
                                    'name' => explode('@', $_POST['email'], 2)[0],  // Extract the email user name from the email address
                                    'username' => $_POST['username'],
                                    'site_name' => json_encode($site_name),
                                    'email' => $_POST['email'],
                                    'password' => preg_replace('/./', '•', $_POST['password']), // Return as many big dots (U+2022) as there are characters in the chosen password
                                    'otc' => $_SESSION['signin_otc']
                                ], translate("Hello {name}!\r\n\r\nYou requested to create an account for the chat on {site_name} with the following account details:\r\nUser name:     {username}\r\nEmail address: {email}\r\nPassword:      {password}\r\n\r\nIf this was you, please enter the following code on the signin confirmation page: {otc}\r\nIf this wasn't you, just ignore this email and the signin attempt will not succeed.\r\n\r\nGreetings, the signin bot at {site_name}")),
                                ['From'=>$site_admin]  // Incase the user has a question they can contact the admin
                            );
                        }
                        ob_start();
                    ?><form method="POST" action="<?php echo $whereami; ?>?action=confirmsignup" enctype="multipart/form-data">
            <label><?php echo translate('One Time Code: '); ?><input type="number" name="otc" min="100000" max="999999" placeholder="<?php echo translate('OTC'); ?>" value="<?php if ($signin_require_valid_email) { echo ''; } else { echo $_SESSION['signin_otc']; } ?>" autofocus required></label>
            <input type="submit" value="<?php echo translate('Sign up'); ?>"><?php
                        die(untemplate([
                            'page_title' => translate('Email verification required'),
                            'page_desc' => translate('You need to verify your email address.'),
                            'additional_headers' => '<meta name="robots" content="noindex">',
                            'page_body' => translate('You need to verify your email address by entering the code that got sent to you below:').ob_get_clean()
                        ], $emptydoc));
                    }
                } else {
                    http_response_code(400);    // 400 Bad Request
                    signup_error(translate('You have to specify all fields of the signup form to sign up!'));
                }
                break;
            }
            case 'confirmsignup': {
                if (isset($_SESSION['signin_otc']) && isset($_SESSION['signin_details'])) { // If there is a pending signin request
                    if (isset($_POST['otc']) && $_POST['otc']==$_SESSION['signin_otc']) { // If the OTC is correct
                        mkpath($secret_data_location.'/user/by-name/');
                        $signin_details = json_decode($_SESSION['signin_details'], true);
                        file_put_contents(
                            $secret_data_location.'/user/by-name/'.$signin_details['username'].'.json',
                            json_encode([
                                'username' => $signin_details['username'],
                                'password' => password_hash($signin_details['password'], $password_hash_algorithm),
                                'email' => $signin_details['email'],
                                'permissions' => [
                                    'chat.read',
                                    'chat.write'
                                ],
                                'rank' => 'user',   // The rank the user has.
                                'attrib' => [   // Extra attributes of the account.
                                    'created' => time(),    // The UNIX timestamp of when the account was created
                                    'standing' => 'new',    // Starts out at 'new', moves on to better or worse depending on the user's actions
                                    'banned_times' => [ // The amount of times the account has been banned in each specific chat room
                                        $main_chat_room_name => 0   // The amount of times the user has been banned in the main chat room
                                    ],
                                    'banned_times_total' => 0,  // The amount of times the account has been banned in total
                                    'latest_ban' => null,   // The timestamp of the latest ban
                                    'latest_kick' => null   // The timestamp of the latest kick (i.e. force-move to main chat)
                                ]
                            ]),
                            LOCK_EX
                        );
                        @symlink(   // Make finding the user by email easy
                            $secret_data_location.'/user/by-name/'.$signin_details['username'].'.json', 
                            $secret_data_location.'/user/by-email/'.md5($signin_details['email']).'.json'
                        );
                        session_destroy();  // Get rid of the signup session, create a new one on login.
                        die(untemplate([
                            'page_title' => translate('Signin success!'),
                            'page_desc' => translate('Your account has been successfully created!'),
                            'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'#login">',
                            'page_body' => translate('Your account was successfully created!<br>Please log in on the next page using your newly-created account.')
                        ], $emptydoc));
                    } else {
                        session_destroy();  // Destroy the erroneous session
                        die(untemplate([
                            'page_title' => translate('Signup error'),
                            'page_desc' => translate('There was an error creating an account for you.'),
                            'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'#signup">',
                            'page_body' => translate('You did not specify the correct OTC for signup, please try again.')
                        ], $emptydoc));
                    }
                } else {
                    die(untemplate([
                        'page_title' => translate('Signup error'),
                        'page_desc' => translate('There was an error creating an account for you.'),
                        'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'#signup">',
                        'page_body' => translate('There is no signup for you to validate!<br>Please ensure that you have cookies enabled and try to sign up again!')
                    ], $emptydoc));
                }
                break;
            }
            case 'login': {
                function login_success() {
                    global $autoredirect_delay;
                    global $whereami;
                    global $emptydoc;
                    die(untemplate([
                        'page_title' => translate('Login success'),
                        'page_desc' => translate('You logged in successfully.'),
                        'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'?action=viewchat">',
                        'page_body' => untemplate([
                                'whereami' => $whereami
                            ], translate('You logged in successfully and will soon be redirected to <a href="{whereami}?action=viewchat">the chat view</a>.'))
                    ], $emptydoc));
                }
                if (isset($_POST['username']) && isset($_POST['password'])) {
                    if (
                        preg_match("/^(?:$username_regex)$/", $_POST['username']) &&
                        find_user($_POST['username']) &&
                        verify_user_password($_POST['username'], $_POST['password'])
                    ) {
                        // Correct login details
                        $_SESSION['username'] = $_POST['username']; // Store that the user is logged into this session
                        $_SESSION['chatroom'] = $main_chat_room_name;   // Assign the user to the default chat room
                        login_success();
                    } else {
                        // Wrong login details, more detail isn't given to the user
                        http_response_code(401);    // 401 Unauthorized
                        die(untemplate([
                            'page_title' => translate('Login error'),
                            'page_desc' => translate('Your login attempt failed.'),
                            'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'#login">',
                            'page_body' => translate('The credentials you provided are incorrect!')
                        ], $emptydoc));
                    }
                } else if (isset($_SESSION['username'])) {
                    // The user is already logged in, redirect them to the chat view
                    login_success();
                } else {
                    http_response_code(401);    // 401 Unauthorized
                    die(untemplate([
                        'page_title' => translate('Please don\'t do that!'),
                        'page_desc' => translate('You submitted an incorrect login request.'),
                        'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'#login">',
                        'page_body' => translate('You submitted an incorrect login request with either the user name, password or both missing.')
                    ], $emptydoc));
                }
                break;
            }
            case 'recoveraccount': {
                //TODO: Implement this!
                break;
            }
            case 'viewchat':
            case 'answertomsg': {
                /* About answertomsg:
                 * The same as viewchat, except that the embedded chat view is paused and focused on the selected message, as well as that the message writing form answers to the selected message instead of just writing a message to the entire chatroom.   
                 * To stop answering and just write a normal message again, just submit an empty answer.   
                 * While generating the chat view, the $interactive argument for formatChatMsg is set to false only for the message that is being answered to.   
                 * The message submission form will also have a little "Answer to @{username}" text above the message input field.   
                 */
                if (!isset($_SESSION['username'])) {
                    die(untemplate([
                        'page_title' => translate('Spectate chat'),
                        'page_desc' => translate('Sorry, you aren\'t logged in!'),
                        'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'?action=spectatechat">',
                        'page_body' => translate('You have to be logged in to interact with the chat!').'<br><a href="'.$whereami.'#login">'.translate('Log in').'</a> <a href="'.$whereami.'?action=spectatechat" autofocus>'.untemplate(['delay'=>$autoredirect_delay], translate('Spectate &lpar;wait {delay}s&rpar;')).'</a>'
                    ], $emptydoc));
                }
                //TODO: Implement this!
                ob_start();
                $answeringto = ((
                    $_GET['action']==='answertomsg' &&
                    isset($_POST['username']) &&
                    isset($_POST['timestamp'])
                ) ? $_POST['username'].':'.$_POST['timestamp'] : '');
                $iframe_errmsg = translate('If you see this error message it means that your browser does not support the &lt;iframe&gt; element. Iframes are <b>absolutely necessary</b> for this chat application to work, op please switch to a browser that follows the HTML5 standard!');
                ?><iframe class="chatview" src="<?php echo $whereami; ?>?action=getchat<?php if ($_GET['action']==='answertomsg') { echo '&paused=true&answeringto='.$answeringto; } ?>#scrolltobottom"><?php echo $iframe_errmsg; ?></iframe>
        <iframe class="writeform" src="<?php echo $whereami; ?>?action=getwriteform<?php if ($_GET['action']==='answertomsg') { echo '&answeringto='.$answeringto; } ?>"><?php echo $iframe_errmsg; ?></iframe><?php
                die(untemplate([
                    'page_title' => translate('"{room_name}" • Chat view'),   // {room_name} is replaced below because the insertions are done sequentially by untemplate
                    'page_desc' => translate('Viewing &quot;{room_name}&quot; in JSFC as {user_name}'),
                    'additional_headers' => '<meta name="robots" content="noindex">',
                    'page_body' => ob_get_clean(),
                    'room_name' => $_SESSION['chatroom'],
                    'user_name' => (($_SESSION['username'])?$_SESSION['username']:'')
                ], $emptydoc));
                break;
            }
            case 'getchat': {
                if (!isset($_SESSION['username'])) {
                    die(untemplate([
                        'page_title' => translate('Spectate chat'),
                        'page_desc' => translate('Sorry, you aren\'t logged in!'),
                        'additional_headers' => '<meta name="robots" content="noindex">',
                        'page_body' => translate('You have to be logged in to interact with the chat!').'<br><a href="'.$whereami.'#login" target="_parent">'.translate('Log in').'</a> <a href="'.$whereami.'?action=spectatechat" autofocus>'.untemplate(['delay'=>$autoredirect_delay], translate('Spectate &lpar;wait {delay}s&rpar;')).'</a>'
                    ], $emptydoc));
                }
                /* Currently disabled, see section "config variables"
                if (!$chat_disable_refreshless_update && set_time_limit(0)) {
                    //TODO: Implement live chat update through permanent connection
                } else {
                    // Either the config disallows it or setting the time limit to 0 failed, we'll just do it the old-fashioned way of client-initiated refreshes. 
                }
                */
                if (!isset($_SESSION['last_time_chat_refreshed'])) {
                    $_SESSION['last_time_chat_refreshed'] = time();
                } else if ((time() - $_SESSION['last_time_chat_refreshed']) < $chat_min_refresh_delay) {
                    $time_left_to_wait = $chat_min_refresh_delay - (time() - $_SESSION['last_time_chat_refreshed']);
                    if ($time_left_to_wait < 5) {
                        sleep($time_left_to_wait);  // Just wait out the remaining time if it's less than 5 seconds, a waiting thread is less bad than several chat formattings running in parallel for the same user.
                    } else {
                        die(untemplate([
                            'page_title' => translate('You\'re too fast!'),
                            'page_desc' => translate('You are refreshing your chat view too often!'),
                            'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$time_left_to_wait.'">',
                            'page_body' => untemplate([
                                'timeout' => $chat_min_refresh_delay,
                                'wait_time' => $time_left_to_wait
                            ], translate('You must not reload your chat view faster than {timeout} seconds after the last reload!<br>Please wait another {wait_time}s, the page should automatically update.'))
                        ], $emptydoc));
                    }
                }
                //TODO: Implement this!
                $formatted_chat = '';
                if (!isset($_SESSION['chat_msg_count'])) {
                    $_SESSION['chat_msg_count'] = 0xff; // Set this to 255 by default, can later probably be changed and should get a configurable limit.
                }
                //TODO: Format the chat here!
                $chatlog_location = $secret_data_location.'/chatroom/'.$_SESSION['chatroom'].'.log';
                if (is_file($chatlog_location)) {
                    $chatlog = file_get_contents($chatlog_location);
                } else {
                    die(untemplate([
                        'page_title' => translate('Chat not found!'),
                        'page_desc' => translate('The chat room you were trying to read does not exist.'),
                        'additional_headers' => '<meta name="robots" content="noindex">',
                        'page_body' => untemplate([
                            'chatroom' => htmlentities($_SESSION['chatroom'])
                        ], translate('The chat room you tried to access, "{chatroom}", could not be found on the server. Please ensure you haven\'t mistyped it.'))
                    ], $emptydoc));
                }
                ob_start();
                if ($chatlog!=='') {
                    foreach (array_slice(explode("\n", $chatlog), -$chat_max_msgs_per_request, $chat_max_msgs_per_request, true) as $msgid => $msg) {
                        if ($msg!=='') {
                            echo formatChatMsg($msgid, $msg, $_SESSION['last_time_chat_refreshed'], true);
                        }
                    }
                    if (isset($_GET['answeringto']) && str_contains($_GET['answeringto'], ':')) {
                        [$answerto_username, $answerto_msgid] = explode(':', $_GET['answeringto'], 2);
                        echo "<style>div.chatmsg#msg_$answerto_msgid { background-color: orange; color: black; }</style>";
                    }
                } else {
                    echo translate('Pssssht, it\'s very quiet in here!');
                }
                $_SESSION['last_time_chat_refreshed'] = time();
                ?><div class="btnbar">
            <form action="<?php echo $whereami; if ($_GET['action']!=='answertomsg') { echo '#scrolltobottom'; } ?>" method="GET"<?php if ($_GET['action']==='answertomsg') { echo ' target="_parent"'; } ?>>
                <input type="hidden" name="action" value="<?php
                    if ($_GET['action']==='answertomsg') {
                        echo 'viewchat';
                    } else {
                        echo 'getchat';
                    }
                ?>">
                <?php
                    if (!isset($_GET['paused'])) {
                        echo '<input type="hidden" name="paused" value="true">';
                    }
                ?><input type="submit" id="pausebtn" value="<?php if (isset($_GET['paused'])) {
                    echo '|>';
                } else {
                    echo '||';
                } ?>" title="<?php
                    if (isset($_GET['paused'])) {
                        echo translate('Click to unpause');
                    } else {
                        echo translate('Click to pause');
                    }
                ?>">
            </form>
        </div>
        <style>
            div.chatmsg.msg_by_<?php echo htmlentities($_SESSION['username']); ?> span.username {
                background-color: green;
                color: white;
            }
        </style>
        <div id="scrolltobottom"><!-- FIXME: Targetting this with a URL hash only seems to work on initial load and button press-caused reload, not on time-based reload… --></div><?php
                die(untemplate([
                    'page_title' => translate('"{room_name}" • Chat view'),   // {room_name} is replaced below because the insertions are done sequentially by untemplate
                    'page_desc' => translate('Viewing &quot;{room_name}&quot; in JSFC as {user_name}'),
                    'additional_headers' => ((isset($_GET['paused']))
                        ?'<meta name="robots" content="noindex">'
                        :'<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.((isset($_SESSION['chat_refresh_delay']))?max($_SESSION['chat_refresh_delay'],$chat_min_refresh_delay):$chat_min_refresh_delay).'; url=#scrolltobottom">'),
                    'page_body' => ob_get_clean(),
                    'room_name' => $_SESSION['chatroom'],
                    'user_name' => $_SESSION['username']
                ], $emptydoc));
                break;
            }
            //TODO: Maybe merge getchat and spectatechat?
            case 'spectatechat': {
                //TODO: Implement this!
                if ($chat_disable_spectate) {
                    goto invalidaction;
                }
                break;
            }
            case 'sendmsg': {
                if (!isset($_SESSION['username'])) {
                    die(untemplate([
                        'page_title' => translate('Please log in first!'),
                        'page_desc' => translate('WgXcQ'),
                        'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'">',
                        'page_body' => translate('You have to be logged in to send messages!')
                    ], $emptydoc));
                }
                $loglocation = $secret_data_location.'/chatroom/'.$_SESSION['chatroom'].'.log';
                if (!is_file($loglocation)) {
                    @mkpath(dirname($loglocation)); // Ensure that the folder for the chat logs exists
                }
                if (isset($_POST['msg']) && $_POST['msg']!=='') {
                    if (isset($_POST['answeringto']) && str_contains($_POST['answeringto'], ':')) {
                        [$answerto_username, $answerto_msgid] = explode(':', $_POST['answeringto'], 2);
                        $answeringto = htmlentities($answerto_username).':'.((int)$answerto_msgid);
                    } else {
                        $answeringto = '';
                    }
                    file_put_contents($loglocation, untemplate([
                        'timestamp' => time(),
                        'username' => $_SESSION['username'],
                        'answerto' => $answeringto,
                        'msg' => msgfilter($_POST['msg'])
                    ], "{timestamp}<{username}@{answerto}>{msg}\n") , FILE_APPEND | LOCK_EX);
                }
                if (isset($_GET['answeringto']) && str_contains($_GET['answeringto'], ':')) {   //TODO: Find out why this seems to act inverted!
                    $nextaction = 'getchat';
                } else {
                    $nextaction = 'viewchat';
                }
                die(untemplate([
                    'page_title' => translate('Message writing form'),
                    'page_desc' => base64_encode('Never gonna give you up!'),
                    'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$chat_min_msg_delay.'; url='.$whereami.'?action='.$nextaction.'">',
                    'page_body' => untemplate([
                        'n' => $chat_min_msg_delay
                    ], translate('Your message has been sent! It should appear soon in the chat window. To reduce server load you cannot send messages within less than {n} seconds of eachother.'))
                ], $emptydoc));
                break;
            }
            case 'getwriteform': {
                if (!isset($_SESSION['username'])) {
                    die(untemplate([
                        'page_title' => translate('Please log in first!'),
                        'page_desc' => translate('WgXcQ'),
                        'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.'">',
                        'page_body' => translate('You have to be logged in to send messages!')
                    ], $emptydoc));
                }
                ob_start();
                ?><form action="<?php echo $whereami; ?>?action=sendmsg" method="POST" enctype="multipart/form-data"<?php
                    if (isset($_GET['answeringto']) && str_contains($_GET['answeringto'], ':')) {
                        echo 'target="_parent"';
                    }
                ?>>
            <span class="msginfo"><?php
                if (isset($_GET['answeringto']) && str_contains($_GET['answeringto'], ':')) {
                    [$answerto_username, $answerto_msgid] = explode(':', $_GET['answeringto'], 2);
                    echo untemplate([
                        'username' => htmlentities($answerto_username),
                        'chatroom' => $_SESSION['chatroom']
                    ], translate('Answer to @{username} in "{chatroom}": '));
                } else {
                    echo untemplate([
                        'chatroom' => $_SESSION['chatroom']
                    ], translate('Talk in "{chatroom}": '));
                }
            ?></span>
            <input type="text" name="msg" value="" placeholder="<?php echo translate('Write your message and press [ENTER] to send it.'); ?>" autofocus>
            <?php
                if (isset($_GET['answeringto']) && str_contains($_GET['answeringto'], ':')) {
                    ?><input type="hidden" name="answeringto" value="<?php echo htmlentities($_GET['answeringto']); ?>">;
            <?php } ?><input type="submit" value="&#x2b00;" title="<?php echo translate('Send!'); ?>">
        </form><?php
                die(untemplate([
                    'page_title' => translate('Message writing form'),
                    'page_desc' => base64_encode('Never gonna give you up!'),
                    'additional_headers' => '<meta name="robots" content="noindex">',
                    'page_body' => ob_get_clean()
                ], $emptydoc));
                break;
            }
            case 'ignorehijack': {
                unset($_SESSION['stolen']); // Stop remembering that the session was potentially hijacked
                if (isset($_POST['action']) && $_POST['action']!=='') {
                    $redirectaction = '?action='.$_POST['action'];
                } else {
                    $redirectaction = '';
                }
                die(untemplate([
                    'page_title' => translate('Successfully ignored session hijack!'),
                    'page_desc' => translate('The suspected session hijack was successfully erased from the session variables.'),
                    'additional_headers' => '<meta name="robots" content="noindex"><meta http-equiv="refresh" content="'.$autoredirect_delay.'; url='.$whereami.$redirectaction.'">',
                    'page_body' => translate('The session hijack has been cleared out of the server\'s memory.<br>Note that if another hijack should be detected, the warning will be issued again.<br>You will be redirected to the action you tried to perform when you received the warning; this will fail should that action have required hidden variables.')
                ], $emptydoc));
                break;
            }
            default: {
                invalidaction:  // The label to jump to to tell the user that the action they requested is invalid
                http_response_code(400);    // 400 Bad Request
                die(untemplate([
                    'page_title' => translate('You can\'t do that!'),
                    'page_desc' => translate('The action you tried to perform is invalid or disabled!'),
                    'additional_headers' => '<meta name="robots" content="noindex">',
                    'page_body' => untemplate([
                        'action' => $_GET['action']
                    ], translate('The action you tried to perform, <code>{action}</code>, was not recognized by the chat application or is disabled.'))
                ], $emptydoc));
            }
        }
    }
    ob_start();
    ?><form id="login" action="<?php echo "$whereami?action=login"; ?>" method="POST" enctype="multipart/form-data" class="overlay">
            <fieldset class="overlay-text"><legend><?php echo translate('Login'); ?></legend>
                <label><?php echo translate('Username: '); ?><input type="text" name="username" pattern="<?php echo $username_regex; ?>" minlength="3" maxlength="15" value="" autocapitalize="off" placeholder="<?php echo translate('Your username'); ?>" required></label>
                <label><?php echo translate('Password: '); ?><input type="password" name="password" minlength="8" value="" autocapitalize="off" autocomplete="off" placeholder="<?php echo translate('Your password'); ?>" required></label>
                <div class="buttonbar"¨>
                    <input type="submit" value="<?php echo translate('Log in'); ?>"> | <a href="#recoveraccount"><?php echo translate('I lost my password'); ?></a> | <a href="#signup"><?php echo translate('Sign up'); ?></a>
                </div>
            </fieldset>
        </form>
        <form id="signup" action="<?php echo $whereami; ?>?action=signup" method="POST" enctype="multipart/form-data" class="overlay">
            <fieldset class="overlay-text"><legend><?php echo translate('Signup'); ?></legend>
                <label><?php echo translate('Username: '); ?><input type="text" name="username" pattern="<?php echo $username_regex; ?>" minlength="3" maxlength="15" value="" autocapitalize="off" placeholder="<?php echo translate('Your username'); ?>" required></label>
                <label><?php echo translate('Password: '); ?><input type="password" name="password" minlength="8" value="" autocapitalize="off" autocomplete="off" placeholder="<?php echo translate('Your password'); ?>" required></label>
                <label><?php echo translate('Repeat your password: '); ?><input type="password" name="password2" minlength="8" value="" autocapitalize="off" autocomplete="off" placeholder="<?php echo translate('Your password'); ?>" required></label>
                <label><?php echo translate('Email: '); ?><input type="email" name="email" value="" autocapitalize="off" placeholder="<?php echo translate('Email'); ?>" required></label>
                <div class="buttonbar">
                    <input type="submit" value="<?php echo translate('Sign up'); ?>"> | <a href="#login"><?php echo translate('Log in'); ?></a>
                </div>
            </fieldset>
        </form>
        <form id="recoveraccount" action="<?php echo $whereami; ?>?action=recoveraccount" method="POST" enctype="multipart/form-data" class="overlay">
            <fieldset class="overlay-text"><legend><?php echo translate('Account recovery'); ?></legend>
                <label><?php echo translate('Email: '); ?><input type="text" name="email" value="" autocapitalize="off" placeholder="<?php echo translate('Email'); ?>" required></label>
                <div class="buttonbar">
                    <input type="submit" value="<?php echo translate('Send recovery email'); ?>"> | <a href="#login"><?php echo translate('Cancel'); ?></a>
                </div>
            </fieldset>
        </form><?php
    if (isset($_SESSION['username'])) {
        $redirect_or_not = '<meta http-equiv="refresh" content="0; url='.$whereami.'?action=viewchat">';  // Redirect the user to the chat view page if they're already logged in and go to the login page.
    } else {
        $redirect_or_not = '';
    }
    die(untemplate([
        'page_title' => translate('JS-free chat'),
        'page_desc' => translate('An online chat room that works purely in HTML5 and CSS3'),
        'additional_headers' => $redirect_or_not,
        'page_body' => ob_get_clean()
    ], $emptydoc));
?>

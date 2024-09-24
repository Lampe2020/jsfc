<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    
    $langs = [  // List of supported languages and their labels
        'de' => 'Deutsch',
        'sv' => 'Svenska',
        'fr' => 'Français'
        // Add languages to support here
    ];
    $lang = ((isset($_GET['lang'])) ? $_GET['lang'] : 'en');
    $existing_transl = @eval(file_get_contents('chat_strings.php'));
    if (!$existing_transl) {
        // Extract the strings from chat.php
        $strings = NULL;
        preg_match_all(
            '/translate\(([^\)]*)\)/',
            file_get_contents('chat.php'),
            $strings
        );
        $existing_transl = [];
        foreach ($strings[1] as $str) { // Get all matches for the first unnamed group
            if (!in_array($str[0], ['"', "'"])) {
                continue;   // Allow only string literals to pass through
            }
            $existing_transl[eval("return $str;")] = [];    // Also allow strings that are in pieces in the code because single-quotes were used and several lines stitched together with a double-quoted "\n"
        }
    }
    
    if (isset($_GET['lang']) && isset($_GET['action']) && $_GET['action']==='submit') {
        if (!in_array($lang, array_keys($langs))) {
            die('Please only submit translations for supported languages!');
        }
        
        $transl = [];
        
        foreach ($existing_transl as $orig => $arr_transl) {
            $transl[$orig] = $existing_transl[$orig];   // Copy all existing translations for this string
            $md5hash = md5($orig);
            if (isset($_POST[$md5hash]) && $_POST[$md5hash]!=='') {
                $transl[$orig][$lang] = $_POST[$md5hash];   // Update the translation for this string in the selected language if applicable
            }
        }
        
        ob_start();
        var_export($transl);
        file_put_contents('chat_strings.php', 'return '.ob_get_clean().';', LOCK_EX);   //FIXME: Doesn't seem to affect state saved on disk!
        //var_dump(is_writable('chat_strings.php'));
        die('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Translation successfully saved!</title><style>:root{color-scheme:light dark;}</style></head><body>Your translation was successfully submitted and saved!<br><button onclick="history.back();">← Back</button></body></html>');
    } else {
        if (!in_array($lang, array_keys($langs))) {
            $lang = 'en';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <link rel="icon" href="/favicon.ico">
        <title>Translation helper for JSFC</title>
        <meta name="description" content="A little PHP script that helps in translating JSFC">
        <meta name="robots" content="noindex">
        <style>
            :root {
                color-scheme: light dark;
            }
            
            span#note {
                background-color: darkred;
                color: yellow;
                padding: 3px;
                border-radius: 3px;
            }
            span#note::before {
                content: "⚠";    /* U+25A0 */
            }
            
            hr {
                margin-top: 1em;
            }
            
            pre {
                border: 1px solid grey;
                padding: 3px;
                padding-top: 1em;
                padding-bottom: 1em;
                border-radius: 3px;
                width: fit-content;
                max-width: calc(100% - 8px);
                overflow-x: scroll;
                scrollbar-width: thin;
            }
            
            textarea {
                box-sizing: border-box;
                padding: 1em;
                width: 100vw;
                max-width: calc(100vw - 1em);
                scrollbar-width: thin;
                resize: none;
            }
            /*textarea:not(:placeholder-shown) {
                box-shadow: 3px 3px 3px green;
            }*/
        </style>
    </head>
    <body>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <label>I want to translate JSFC to: 
                <select name="lang" required>
                    <option value="en"<?php if ($lang==='en') { echo ' selected'; } ?> disabled>English (original)</option>
                    <?php
                        foreach ($langs as $ln => $ll) {
                            if ($lang===$ln) {
                                echo "<option value=\"$ln\" selected>$ll</option>";
                            } else {
                                echo "<option value=\"$ln\">$ll</option>";
                            }
                        }
                    ?></select>
            </label>
            <input type="submit" value="Choose language">
        </form><?php
            if ($lang==='en') {
                ?><hr><span id="note">Please select a target language above to start translating.</span><?php
            } else {
                ?><form action="<?php echo $_SERVER['PHP_SELF']; ?>?lang=<?php echo $lang; ?>&action=submit" method="POST" enctype="multipart/form-data" onsubmit="if (!confirm('Do you really want to submit your translation? It will immediately overwrite any string that it doesn\'t leave empty!')) { event.preventDefault(); }" onkeydown="if (event.key==='Enter' && event.ctrlKey) { this.querySelector('input[type=submit]')?.click?.(); }">
                    <hr style="margin-top:1em;"><span id="note">Be aware that some strings have trailing spaces, do not omit those in your translation!<br>As soon as you submit your translation it will instantly overwrite any strings in the stored translation that you did not leave empty!<br>To install the translation, just copy the array in chat_strings.php and replace the array $translate in chat.php with the new one.</span>
                    <?php
                        foreach ($existing_transl as $orig => $arr_transl) {
                            echo '<hr><label style="max-width:100vw;"><pre>'.htmlentities($orig).'</pre><textarea name="'.md5($orig).'" rows="'.(substr_count($orig, "\n")+1).'" placeholder="Write your translation for the above string here.">'.((isset($arr_transl[$lang])) ? htmlentities($arr_transl[$lang]) : '').'</textarea></label>';
                        }
                    ?>
                    <hr>
                    <input type="submit" value="Submit translation" style="padding:1em;margin:1em;">
                </form><?php
            }
            echo "\n";  // Fix page code formatting
        ?>
    </body>
</html>

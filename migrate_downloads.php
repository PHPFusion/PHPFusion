<?php
require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';


// Move download files to new infusions folder
/*if (file_exists(BASEDIR.'downloads/')) {
    $files = makefilelist(BASEDIR.'downloads/');
    if (!empty($files)) {
        foreach($files as $filename) {
            rename(BASEDIR.'downloads/'.$filename, INFUSIONS.'downloads/files/'.$filename);
        }
    }
    unset($files);

    if (file_exists(BASEDIR.'downloads/images/')) {
        // Images folder
        $files = makefilelist(BASEDIR.'downloads/images/');
        if (!empty($files)) {
            foreach($files as $filename) {
                rename(BASEDIR.'downloads/images/'.$filename, INFUSIONS.'downloads/images/'.$filename);
            }
        }
        unset($files);
    }

    if (file_exists(BASEDIR.'downloads/submissions/')) {
        // Images folder
        $files = makefilelist(BASEDIR.'downloads/submissions/');
        if (!empty($files)) {
            foreach($files as $filename) {
                rename(BASEDIR.'downloads/submissions/'.$filename, INFUSIONS.'downloads/submissions/'.$filename);
            }
            unset($files);
        }
        if (file_exists(BASEDIR.'downloads/submissions/images/')) {
            // Images folder
            $files = makefilelist(BASEDIR.'downloads/submissions/images/');
            if (!empty($files)) {
                foreach($files as $filename) {
                    rename(BASEDIR.'downloads/submissions/images/'.$filename, INFUSIONS.'downloads/submissions/images/'.$filename);
                }
            }
            unset($files);
        }
    }

}*/

// upgrade all basic file paths
/* $result = dbquery("SELECT download_id, download_url FROM ".DB_DOWNLOADS." WHERE download_url!=''");
if (dbrows($result)) {
    while ($data = dbarray($result)) {
        //$url = fusion_get_settings('siteurl');
        $url = strtr(fusion_get_settings('siteurl'), [
            'http://' => '',
            'https://' => '',
        ]);
       if (stristr($data['download_url'], $url)) {
           //https://www.php-fusion.co.uk/downloads/v7themepsds.zip
           if (stristr($data['download_url'], 'downloads/images/')) {
               $data['download_url'] = str_replace('downloads/images/', 'infusions/downloads/images/', $data['download_url']); // change to download file path
           } else {
               $data['download_url'] = str_replace('downloads/', 'infusions/downloads/files/', $data['download_url']); // change to download file path
           }
           dbquery_insert(DB_DOWNLOADS, $data, 'update');
       }
    }
}*/


require_once THEMES.'templates/footer.php';
<?php

function conversion($number) {
    // Vérifier si l'extension intl est disponible
    if (class_exists('NumberFormatter')) {
        $digit = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
        return $digit->format($number);
    }

    // Fonction alternative sans intl
    return convertirNombreEnLettres($number);
}

function convertirNombreEnLettres($nombre) {
    $nombre = (int) $nombre;

    if ($nombre == 0) {
        return 'zéro';
    }

    $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
    $dizaines = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
    $exceptions = ['dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];

    $resultat = '';

    // Milliards
    if ($nombre >= 1000000000) {
        $milliards = intval($nombre / 1000000000);
        $resultat .= convertirNombreEnLettres($milliards) . ' milliard';
        if ($milliards > 1) $resultat .= 's';
        $nombre %= 1000000000;
        if ($nombre > 0) $resultat .= ' ';
    }

    // Millions
    if ($nombre >= 1000000) {
        $millions = intval($nombre / 1000000);
        $resultat .= convertirNombreEnLettres($millions) . ' million';
        if ($millions > 1) $resultat .= 's';
        $nombre %= 1000000;
        if ($nombre > 0) $resultat .= ' ';
    }

    // Milliers
    if ($nombre >= 1000) {
        $milliers = intval($nombre / 1000);
        if ($milliers == 1) {
            $resultat .= 'mille';
        } else {
            $resultat .= convertirNombreEnLettres($milliers) . ' mille';
        }
        $nombre %= 1000;
        if ($nombre > 0) $resultat .= ' ';
    }

    // Centaines
    if ($nombre >= 100) {
        $centaines = intval($nombre / 100);
        if ($centaines == 1) {
            $resultat .= 'cent';
        } else {
            $resultat .= $unites[$centaines] . ' cent';
        }
        $nombre %= 100;
        if ($nombre == 0 && $centaines > 1) {
            $resultat .= 's';
        }
        if ($nombre > 0) $resultat .= ' ';
    }

    // Dizaines et unités
    if ($nombre >= 17) {
        $dizaine = intval($nombre / 10);
        $unite = $nombre % 10;

        if ($dizaine == 7 || $dizaine == 9) {
            $resultat .= $dizaines[$dizaine - 1];
            if ($unite == 1) {
                $resultat .= ' et ' . $exceptions[$unite];
            } else {
                $resultat .= '-' . $exceptions[$unite];
            }
        } elseif ($dizaine == 8) {
            $resultat .= $dizaines[$dizaine];
            if ($unite > 0) {
                $resultat .= '-' . $unites[$unite];
            } else {
                $resultat .= 's';
            }
        } else {
            $resultat .= $dizaines[$dizaine];
            if ($unite == 1) {
                $resultat .= ' et un';
            } elseif ($unite > 1) {
                $resultat .= '-' . $unites[$unite];
            }
        }
    } elseif ($nombre >= 10) {
        if ($nombre == 16) {
            $resultat .= 'seize';
        } else {
            $resultat .= $exceptions[$nombre - 10];
        }
    } else {
        $resultat .= $unites[$nombre];
    }

    return trim($resultat);
}

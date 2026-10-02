<?php

return [
    'date_format'         => 'Y-m-d',
    'time_format'         => 'H:i:s',
    // Nom du rôle donné aux comptes créés sans rôle (inscription libre). Doit rester un rôle sans droits sensibles.
    'registration_default_role' => 'User',

    'contact' => [
        'organisation' => "Ministère de l'Emploi et de la Formation Professionnelle et Technique",
        'service'      => 'Cellule Informatique – SYGEP',
        'adresse'      => env('CONTACT_ADRESSE') ?: 'Sphère ministérielle Ousmane Tanor Dieng, Diamniadio – Sénégal',
        'telephone'    => env('CONTACT_TELEPHONE') ?: '+221 33 000 00 00',
        'email'        => env('CONTACT_EMAIL') ?: 'sygep@mefpt.gouv.sn',
        'horaires'     => env('CONTACT_HORAIRES') ?: 'Lundi – Vendredi, 8h00 – 17h00',
    ],

];

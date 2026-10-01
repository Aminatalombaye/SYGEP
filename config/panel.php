<?php

return [
    'date_format'         => 'Y-m-d',
    'time_format'         => 'H:i:s',
    'registration_default_role' => '2',

    'contact' => [
        'organisation' => "Ministère de l'Emploi et de la Formation Professionnelle et Technique",
        'service'      => 'Cellule Informatique – SYGEP',
        'adresse'      => env('CONTACT_ADRESSE') ?: 'Sphère ministérielle Ousmane Tanor Dieng, Diamniadio – Sénégal',
        'telephone'    => env('CONTACT_TELEPHONE') ?: '+221 33 000 00 00',
        'email'        => env('CONTACT_EMAIL') ?: 'sygep@mefpt.gouv.sn',
        'horaires'     => env('CONTACT_HORAIRES') ?: 'Lundi – Vendredi, 8h00 – 17h00',
    ],

];

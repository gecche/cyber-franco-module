<?php


/*
 * 'model' => <MODELNAME>
 * <FORMNAME> =>  [ //nome del form da route
 *      type => <FORMTYPE>, //tipo di form (opzionale se non c'è viene utilizzato il nome)
 *              //search, list, edit, insert, view, csv, pdf
 *      fields => [ //i campi del modello principale
 *          <FIELDNAME> => [
 *              'default' => <DEFAULTVALUE> //valore di default del campo (null se non presente)
 *              'options' => array|belongsto:<MODELNAME>|dboptions|boolean
 *                          //le opzioni possibili di un campo, prese da un array associativo,
 *                              da una relazione (gli id del modello correlato
 *                              dal database (enum ecc...)
 *                              booleano
 *              'nulloption' => true|false|onchoice //onchoice indica che l'opzione nullable è presente solo se i valori
 *                                  delle options sono più di uno; default: true,
 *              'null-label' => etichetta da associare al null
 *              'bool-false-value' => valore da associare al false
 *              'bool-false-label' => etichetta da associare al false
 *              'bool-true-value' => valore da associare al true
 *              'bool-true-label' => etichetta da associare al true
 *          ]
 *      ],
 *      relations => [ // le relazioni del modello principale
 *          <RELATIONNAME> => [
 *              fields => [ //i campi del modello principale
 *                  <FIELDNAME> => [
 *                      'default' => <DEFAULTVALUE> //valore di default del campo (null se non presente)
 *                      'options' => array|relation:<RELATIONNAME>|dboptions|boolean
 *                          //le opzioni possibili di un campo, prese da un array associativo,
 *                              da una relazione (gli id del modello correlato,
 *                              dal database (enum ecc...)
 *                              booleano
 *                      'nulloption' => true|false|onchoice //onchoice indica che l'opzione nullable è presente solo se i valori
 *                                    delle options sono più di uno; default: true,
 *                  ]
 *              ],
 *              savetype => [ //metodo di salvataggio della relazione
 *                              (in caso di edit/insert) da definire meglio
 *              ]
 *          ]
 *      ],
 *      params => [ // altri parametri opzionali
 *
 *      ],
 * ]
 */

return [

    'models' => [
        \App\Models\PdfRequest::class => 'pdf_request',
    ],

    'listener' => \App\Listeners\HandleStatusTransition::class,

    'models_listeners' => [
        \App\Models\PdfRequest::class => \App\Listeners\HandlePdfRequestStatusTransition::class,
    ],

    'types' => [
        'pdf_request' => [

            'groups' => [
                'deletable' => "The PDF Request can be deleted",
                /*
                    groupcode => description|null
                 */
            ],
            'states' => [
                'created' => [
                    'description' => "PDF Request created",
                    'groups' => ['deletable'],
                    'final' => false,
                ],
                'in_verification' => [
                    'description' => "E-mail to be verified",
                    'groups' => ['deletable'],
                    'final' => false,
                ],
                'verification_expired' => [
                    'description' => "Verification E-mail expired",
                    'groups' => ['deletable'],
                    'final' => false,
                ],
                'in_progress' => [
                    'description' => "The PDF Request is in progress",
                    'groups' => [],
                    'final' => false,
                ],

                'done' => [
                    'description' => "The PDF Request has been successfully elaborated",
                    'groups' => [],
                    'final' => false,
                ],
                'failed' => [
                    'description' => "There has been an error while processing the PDF Request",
                    'groups' => ['deletable'],
                    'final' => false,
                ],
                'rejected' => [
                    'description' => "The final user has declined the PDF request",
                    'groups' => [],
                    'final' => false,
                ],
                'expired' => [
                    'description' => "The PDF Request is expired",
                    'groups' => ['deletable'],
                    'final' => true,
                ],

                /*
                    code => [
                        description => string (optional, default, code)
                        groups => [ <GROUPCODES> ] (optional, default [])
                        final => boolean (optional, default false)
                    ]
                */
            ],

            'root' => 'created', //code,

            'transitions' => [
                /*
                    code => [ <CODES> ]
                */
                'created' => [
                    'in_verification',
                    'in_progress',
                ],
                'in_verification' => [
                    'verification_expired',
                    'in_progress',
                    'rejected',
                ],
                'verification_expired' => [
                    'expired',
                ],
                'in_progress' => [
                    'done',
                    'failed',
                ],

                'done' => [
                    'expired',
                ],
                'failed' => [
                    'expired',
                ],
                'rejected' => [
                    'expired',
                ],
                'expired' => [

                ],
            ]
        ]
    ],

];

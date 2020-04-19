<?php

return [
    'menu' => [
        'title' => 'Contenu',
        'wakamails' => 'Mails',
        'wakamails_description' => 'Gestion des emails et de leurs blocs',
        'bloc_name' => 'Blocs emails',
        'bloc_type' => 'types de blocs emails',
        'bloc_type_description' => 'Administration des types de blocs et exemples',
        'settings_category' => 'Wakaari Modèle',
    ],
    'bloc' => [
        'name' => 'Intitulé',
        'name_ex' => 'UNiquement utilisé dans le BO',
        'bloc_name' => 'Nom',
        'bloc_name_ex' => 'Liaison avec le wakamail',
        'bloc_type' => 'Type de contenu',
        'code' => 'Code',
        'version' => 'Les versions',
        'nb_content' => 'Variante',
        'opt_section' => 'Liste des options',
        'opt_section_com' => "Si vide : pas d'options",
    ],
    'bloc_name' => [
        'name' => 'Intitulé',
        'name_ex' => 'Uniquement utilisé dans le BO',
        'bloc' => 'Bloc contenu de références',
        'bloc_name' => 'Nom',
        'bloc_name_ex' => 'Liaison avec le wakamail',
        'bloc_type' => 'Type de contenu',
    ],
    'bloc_type' => [
        'name' => 'Intitulé',
        'type' => 'Type de bloc',
        'type_bloc' => "Le contenu sera de type : 'bloc'",
        'type_row' => "Le contenu sera de type : 'row'",
        'code' => "Code d'itentification du bloc",
        'model' => 'Model associé',
        'ajax_method' => 'Méthode Ajax',
        'use_icon' => 'Utiliser une icone October',
        'icon_png' => 'Utiliser une icone PNG',
        'scr_explication' => 'Fichier Mail d explication du bloc',
        'datasource_accepted' => 'Model reservé pour les sources : ',
        'datasource_accepted_comment' => 'Vide si fonctionne avec tous les modèles',
    ],
    'wakamail' => [
        'name' => 'Nom',
        'path' => 'Fichier source',
        'analyze' => "Log d'analyse des codes du fichier source",
        'has_sectors_perso' => 'Personaliser le contenu en fonction du secteur',
        'data_source' => ' Sources des données',
        'data_source_placeholder' => 'Choisissez une source de données',
        'show' => 'Voir un exemple',
        'check' => 'Vérifier',
        'scopes' => 'limiter le wakamail pour une cible ',
        'scopes_prompt' => 'Ajouter des limites',
        'scopes_target' => 'cible ou relation cible',
        'scopes_field' => 'Champ de la cible',
        'scopes_name' => 'Valeur de la cible',
        'add_fields' => [
            'name' => 'Ajouter des champs au formulaire',
            'prompt' => "Ajouter un nouveau champs",
            'label' => "Intitulé du champs",
            'code' => "Code du champ",
            'type' => "Type du champ",
            'required' => "Champs requis ?",
        ],
        'subject' => "Sujet de l'email",
        'slug' => "Slug ou code",
        'mjml' => "Code MJML",
        'is_mjml' => "Template MJML",
        'is_mjml_com' => "Attention, si activé, le code HTML sera écrasé par le MJML",
        'addFunction' => 'Ajouter une fonction/collection',
        'test' => "Tester",
        'template' => "Code HTML",
        'show' => "Voir",

    ],
    'objtext' => [
        'data' => 'Paragraphes',
        'data_prompt' => "Cliquez ici pour ajouter un paragraphe",
        'value' => 'Paragraphe',
        'jump' => 'Saut de ligne entre les deux paragraphes',
    ],
    'content' => [
        'name' => 'Contenu',
        'sector' => "Secteur",
        'sector_placeholder' => 'Choisissez un secteur',
        'versions' => 'Les versions',
        'add_version' => 'Nouvelle version',
        'add_base' => 'Créer le contenu de base',
        'create_content' => "Création d'une version : ",
        'update_content' => "Mise à jour d'une version ",
        'version_for_sector' => 'Version pour le secteur : ',
        'sector' => 'Secteur de cette version',
        'reminder_content' => "Choisisir ou créer une version dans le tableau du dessus. Mettre à jour avant de quitter",
    ],

];

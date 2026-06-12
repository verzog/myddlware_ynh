<?php

/*
 * Field metadata for the WordPress connector (REST API v2 schema, context=edit).
 *
 * Added by the YunoHost package and copied to
 * src/Solutions/lib/wordpress/metadata.php, where wordpress::setMetadata()
 * require's it. Defines $moduleFields keyed by module, then by field name, in
 * Myddleware's descriptor shape (label / type / type_bdd / required). Without
 * this file the connector exposes no mappable fields and no rule can be built.
 */

$moduleFields = [
    'posts' => [
        'date' => ['label' => 'Publication date', 'type' => 'datetime', 'type_bdd' => 'datetime', 'required' => 0],
        'date_gmt' => ['label' => 'Publication date (GMT)', 'type' => 'datetime', 'type_bdd' => 'datetime', 'required' => 0],
        'modified' => ['label' => 'Modified date', 'type' => 'datetime', 'type_bdd' => 'datetime', 'required' => 0],
        'slug' => ['label' => 'Slug', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
        'status' => ['label' => 'Status', 'type' => 'varchar(20)', 'type_bdd' => 'varchar(20)', 'required' => 0],
        'title' => ['label' => 'Title', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
        'content' => ['label' => 'Content', 'type' => 'text', 'type_bdd' => 'text', 'required' => 0],
        'excerpt' => ['label' => 'Excerpt', 'type' => 'text', 'type_bdd' => 'text', 'required' => 0],
        'author' => ['label' => 'Author (user id)', 'type' => 'int', 'type_bdd' => 'int', 'required' => 0],
        'featured_media' => ['label' => 'Featured media (id)', 'type' => 'int', 'type_bdd' => 'int', 'required' => 0],
        'comment_status' => ['label' => 'Comment status', 'type' => 'varchar(20)', 'type_bdd' => 'varchar(20)', 'required' => 0],
        'ping_status' => ['label' => 'Ping status', 'type' => 'varchar(20)', 'type_bdd' => 'varchar(20)', 'required' => 0],
        'sticky' => ['label' => 'Sticky', 'type' => 'int', 'type_bdd' => 'int', 'required' => 0],
        'format' => ['label' => 'Format', 'type' => 'varchar(20)', 'type_bdd' => 'varchar(20)', 'required' => 0],
        'link' => ['label' => 'Permalink', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
    ],
    'pages' => [
        'date' => ['label' => 'Publication date', 'type' => 'datetime', 'type_bdd' => 'datetime', 'required' => 0],
        'modified' => ['label' => 'Modified date', 'type' => 'datetime', 'type_bdd' => 'datetime', 'required' => 0],
        'slug' => ['label' => 'Slug', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
        'status' => ['label' => 'Status', 'type' => 'varchar(20)', 'type_bdd' => 'varchar(20)', 'required' => 0],
        'title' => ['label' => 'Title', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
        'content' => ['label' => 'Content', 'type' => 'text', 'type_bdd' => 'text', 'required' => 0],
        'excerpt' => ['label' => 'Excerpt', 'type' => 'text', 'type_bdd' => 'text', 'required' => 0],
        'author' => ['label' => 'Author (user id)', 'type' => 'int', 'type_bdd' => 'int', 'required' => 0],
        'featured_media' => ['label' => 'Featured media (id)', 'type' => 'int', 'type_bdd' => 'int', 'required' => 0],
        'parent' => ['label' => 'Parent (page id)', 'type' => 'int', 'type_bdd' => 'int', 'required' => 0],
        'menu_order' => ['label' => 'Menu order', 'type' => 'int', 'type_bdd' => 'int', 'required' => 0],
        'comment_status' => ['label' => 'Comment status', 'type' => 'varchar(20)', 'type_bdd' => 'varchar(20)', 'required' => 0],
        'template' => ['label' => 'Template', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
        'link' => ['label' => 'Permalink', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
    ],
    'comments' => [
        'post' => ['label' => 'Post (id)', 'type' => 'int', 'type_bdd' => 'int', 'required' => 1],
        'parent' => ['label' => 'Parent (comment id)', 'type' => 'int', 'type_bdd' => 'int', 'required' => 0],
        'author' => ['label' => 'Author (user id)', 'type' => 'int', 'type_bdd' => 'int', 'required' => 0],
        'author_name' => ['label' => 'Author name', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
        'author_email' => ['label' => 'Author email', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
        'author_url' => ['label' => 'Author URL', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
        'date' => ['label' => 'Date', 'type' => 'datetime', 'type_bdd' => 'datetime', 'required' => 0],
        'content' => ['label' => 'Content', 'type' => 'text', 'type_bdd' => 'text', 'required' => 1],
        'status' => ['label' => 'Status', 'type' => 'varchar(20)', 'type_bdd' => 'varchar(20)', 'required' => 0],
        'link' => ['label' => 'Permalink', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0],
    ],
];

<?php
require 'vendor/autoload.php';
use League\HTMLToMarkdown\HtmlConverter;

$converter = new HtmlConverter();
$mysqli = new mysqli("localhost", "root", "secret", "test");

/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$query = "SELECT rewrite, h1, text, date, lang FROM `website_pages` Where parrent != '0'
ORDER BY `website_pages`.`date` DESC, `website_pages`.`lang` ASC";
/* Select queries return a resultset */
if ($result = $mysqli->query($query)) {
    printf("<b>Select returned %d rows.</b><br><br>\n", $result->num_rows);
    $line = '';
    while ($obj = $result->fetch_object()) {
        $filename = $obj->date.'-'.$obj->rewrite.'-'.$obj->lang.'.md';
        $title = $obj->h1;
        $mdmeta = '---
layout: post
title:  "'.str_replace(array('"', '\''), '', $title).'"
date:   '.$obj->date.' 14:00:00 +0300
permalink: "/'.$obj->lang.'/'.str_replace(array('\\', ':', '/', '"', '\''), '', $obj->rewrite).'/"
lang: '.$obj->lang.'
---'."\n";
        $mdcontent = $mdmeta.$converter->convert($obj->text);
        $line.= $filename.'<br>';
        $line.= '<h2>'.$title.'</h2>';
        $line.= '<pre>'.$mdcontent.'</pre>';
        $line.="<hr>\n";
        file_put_contents("_posts/$filename", $mdcontent);
    }
    echo $line;
    /* free result set */
    $result->close();
}

$mysqli->close();

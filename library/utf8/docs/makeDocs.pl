#!/usr/bin/perl -w

use strict;
use Pod::Xhtml;
use File::Basename;

my $filename = 'phputf8.pod';

my $basename = basename($filename);
$basename =~ s/.[a-z]{3}$//;


my $POD = Pod::Xhtml->new();

$POD->addHeadText('<meta http-equiv="Content-Type"
    content="text/html; charset=UTF-8" />');
$POD->addHeadText('<link rel="stylesheet" media="screen" type="text/css" href="screen.css" />');
$POD->addHeadText('<link rel="stylesheet" media="print" type="text/css" href="print.css" />');
$POD->addBodyOpenText('<div id="header">');
$POD->addBodyOpenText('<h1 class="title">'.$basename.'</h1>');
$POD->addBodyOpenText('</div>');
$POD->addBodyOpenText('<div id="nav">');
$POD->addBodyOpenText('[ <a href="http://phputf8.sourceforge.net/api/">API docs</a> ]');
$POD->addBodyOpenText('</div>');

$POD->parse_from_file('phputf8.pod');


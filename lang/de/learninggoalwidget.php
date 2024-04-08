<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'learninggoalwidget', language 'de'
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['title'] = 'Lernziele Taxonomie';
$string['learninggoalwidget:addinstance'] = 'Lernziele Widget hinzufügen';
$string['learninggoalwidget:view'] = 'Lernziele Widget anzeigen';
$string['learninggoalwidgettext'] = 'Lernziele Widget Text';
$string['modulename'] = 'Lernziele Widget';
$string['modulename_help'] = '';
$string['modulename_link'] = 'mod/learninggoalwidget/view';
$string['modulenameplural'] = 'Lernziele Widgets';
$string['privacy:metadata'] = '';
$string['pluginadministration'] = 'Lernziele Widget Administration';
$string['pluginname'] = 'Lernziele Widget';
$string['search:activity'] = 'learninggoalwidget';

$string['settings:header'] = 'Themenbereiche und Lernziele';
$string['settings:topic'] = 'Themenbereich';
$string['settings:goal'] = 'Lernziel';
$string['settings:description'] = 'Kurzbezeichnung';
$string['settings:link'] = 'Web Adresse';
$string['settings:addtopic'] = 'Neuen Themenbereich hinzufügen';
$string['settings:edittopic'] = 'Themenbereich editieren';
$string['settings:deletetopic'] = 'Themenbereich löschen';
$string['settings:deletetopicmsg'] = 'Möchten Sie den Themenbereich wirklich löschen?';
$string['settings:save'] = 'Speichern';
$string['settings:delete'] = 'Löschen';
$string['settings:addgoal'] = 'Neues Lernziel hinzufügen';
$string['settings:editgoal'] = 'Lernziel editieren';
$string['settings:deletegoal'] = 'Lernziel löschen';
$string['settings:deletegoalmsg'] = 'Möchten Sie das Lernziel wirklich löschen?';
$string['settings:showgoals'] = 'Klicken Sie auf einen Themenbereich, um die Lernziele hier anzuzeigen';
$string['settings:nogoals'] = 'Für den gewählten Themenbereich sind keine Lernziele vorhanden';
$string['settings:topicheader'] = 'Themenbereiche';
$string['settings:goalheader'] = 'Lernziele';
$string['settings:btnnewtopic'] = 'Neuer Themenbereich';
$string['settings:btnnewgoal'] = 'Neues Lernziel';
$string['settings:jsonheader'] = 'JSON Format';
$string['settings:btnjsonupload'] = 'Hochladen';
$string['settings:btnjsondownload'] = 'Herunterladen';
$string['settings:notopicsmessage'] = 'Es sind noch keine Themenbereiche vorhanden';
$string['settings:newtaxonomyheader'] = 'Neue Themenbereiche und Lernziele';
$string['settings:newtaxonomymsg'] = 'Möchten Sie die aktuellen Themenbereiche und Lernziele mit den folgenden ersetzen?';
$string['settings:replace'] = 'Ersetzen';

$string['validation:missingtitle'] = 'Der Titel ist verpflichtend einzugeben';
$string['validation:invalidlink'] = 'Die URL ist ungültig';
$string['validation:invalid'] = 'Ungültige Taxonomie';
$string['validation:close'] = 'Schließen';
$string['validation:invalidfile'] = 'Die hochgeladene Datei ist ungültig';
$string['validation:jsontop1'] = '"name" Eigenschaft fehlt.';
$string['validation:jsontop2'] = '"children" Eigenschaft fehlt.';
$string['validation:jsontop3'] = '"children" Eigenschaft ist kein Array.';
$string['validation:jsontop4'] = '"children" Array ist leer.';
$string['validation:jsontopic1'] = '"name" Eigenschaft fehlt bei einem Themenbereich.';
$string['validation:jsontopic2'] = '"name" Eigenschaft bei dem Themenbereich "{$a}" ist kein String.';
$string['validation:jsontopic3'] = '"link" Eigenschaft bei dem Themenbereich "{$a}" ist kein String.';
$string['validation:jsontopic4'] = '"link" Eigenschaft bei dem Themenbereich "{$a}" ist ungültig.';
$string['validation:jsontopic5'] = '"keyword" Eigenschaft bei dem Themenbereich "{$a}" ist kein String.';
$string['validation:jsontopic6'] = '"children" Eigenschaft bei dem Themenbereich "{$a}" ist kein Array.';
$string['validation:jsongoal1'] = 'Ein Lernziel von dem Themenbereich "{$a}" hat keine "name" Eigenschaft.';
$string['validation:jsongoal2'] = '"name" Eigenschaft bei einem Lernziel von dem Themenbereich "{$a}" ist kein String.';
$string['validation:jsongoal3'] = '"link" Eigenschaft bei dem Lernziel "{$a}" ist kein String.';
$string['validation:jsongoal4'] = '"link" Eigenschaft bei dem Lernziel "{$a}" ist ungültig.';
$string['validation:jsongoal5'] = '"keyword" Eigenschaft bei dem Lernziel "{$a}" ist kein String.';
$string['validation:missinggoal'] = 'Die Themenbereiche [{$a}] haben keine Lernziele. Bitte fügen Sie mindestens eins pro Themenbereich.';

$string['guestaccess'] = 'Dafür müssen sie angemeldet sein';
$string['noaccess'] = 'Dafür müssen sie angemeldet sein';
$string['contentview'] = 'Lernziele';
$string['examview'] = 'Mein Lernfortschritt';
$string['progresslabel0'] = 'Gering (0%)';
$string['progresslabel50'] = 'Durchschnittlich (50%)';
$string['progresslabel100'] = 'Hoch (100%)';
$string['progressdialogtitle'] = 'Lernzielfortschritt aktualisieren';
$string['progresslegendlabel'] = 'Lernfortschritt';
$string['textualbulletpointlisttitle'] = 'Überblick über die Themenbereiche und Lernziele';
$string['treemapaccessibilitytext'] = 'Dieses Tool hilft Ihnen, über die gelernten Themen zu reflektieren und hält Ihren Fortschritt fest.<br/><br/>
Auf der linken Seite befinden sich die Themen und auf der rechten Seite
die Lernziele. Jedes dieser Ziele zeigt den Fortschritt als Prozentzahl an.
  Sie können diesen Wert ändern, indem Sie auf das Thema oder auf das Lernziel klicken
  und dann den Schieberegler in die gewünschte Position bewegen.<br/>
  Auf der rechten Seite eines jeden Themas finden Sie einen Durchschnittswert
  für den Fortschritt, den Sie über die zum Thema untergeordnete Lernziele gemacht haben.<br/><br/>
  Wenn Sie einen Screen Reader verwenden, können Sie mit der Tabulatortaste
  durch das Dokument navigieren. Durch Drücken der Eingabetaste können Sie die Themen erweitern.
  Wenn eine Aktion für die Tastatur verfügbar ist, wird sie angekündigt.<br/>
  Dieses Tool ist optimiert für Google Chrome und dessen Erweiterung namens
  Screen Reader. Für ein optimales Erlebnis empfehlen wir Ihnen, diese zu verwenden.<br/><br/>
  Unter dem Symbol für Barrierefreiheit finden Sie zwei Schaltflächen zum Ändern der Schriftgröße.
  Wenn Sie stattdessen den Zoom ändern möchten, drücken Sie die Tasten STRG und +
  oder STRG und -. Für ein optimales Erlebnis empfehlen wir Ihnen, diese zu verwenden.<br/>
  Das dritte Symbol wird verwendet, um die Hintergrund und Textfarben des Widgets zu ändern.
  Wenn Sie farbenblind sind, können Sie dort einige Farbschemata finden, die eine
  dunkelblaue Umrandung haben. Diese haben einen hohen Kontrast und sollten für Sie geeignet sein.<br/><br/>
  Wir wünschen Ihnen viel Spaß beim Lernen';

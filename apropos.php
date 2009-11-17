<?php
//==========================================================================
//
//Université de Strasbourg - Direction Informatique
//Auteur : Guilhem BORGHESI
//Création : Février 2008
//
//borghesi@unistra.fr
//
//Ce logiciel est régi par la licence CeCILL-B soumise au droit français et
//respectant les principes de diffusion des logiciels libres. Vous pouvez
//utiliser, modifier et/ou redistribuer ce programme sous les conditions
//de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA 
//sur le site "http://www.cecill.info".
//
//Le fait que vous puissiez accéder à cet en-tête signifie que vous avez 
//pris connaissance de la licence CeCILL-B, et que vous en avez accepté les
//termes. Vous pouvez trouver une copie de la licence dans le fichier LICENCE.
//
//==========================================================================
//
//Université de Strasbourg - Direction Informatique
//Author : Guilhem BORGHESI
//Creation : Feb 2008
//
//borghesi@unistra.fr
//
//This software is governed by the CeCILL-B license under French law and
//abiding by the rules of distribution of free software. You can  use, 
//modify and/ or redistribute the software under the terms of the CeCILL-B
//license as circulated by CEA, CNRS and INRIA at the following URL
//"http://www.cecill.info". 
//
//The fact that you are presently reading this means that you have had
//knowledge of the CeCILL-B license and that you accept its terms. You can
//find a copy of this license in the file LICENSE.
//
//==========================================================================

session_start();

include 'variables.php';
include 'bandeaux.php';

//changement de la langue
if ($_POST["uk"]){
	$_SESSION["langue"]="EN";
}
if ($_POST["germany"]){
	$_SESSION["langue"]="DE";
}
if ($_POST["france"]){
	$_SESSION["langue"]="FR";
}
if ($_POST["espagne"]){
	$_SESSION["langue"]="ES";
}

//Choix de la langue
if ($_SESSION["langue"]=="FR"){ include 'lang/fr.inc';}
if ($_SESSION["langue"]=="EN"){ include 'lang/en.inc';}
if ($_SESSION["langue"]=="DE"){ include 'lang/de.inc';}
if ($_SESSION["langue"]=="ES"){ include 'lang/es.inc';}

// action du bouton annuler
if ($_POST["annuler"]){
        header("Location:index.php");
        exit();
}

//action si bouton intranet est activé. Entrée dans l'intranet
if ($_POST["intranet"]){

        header("Location:./admin/index.php");
        exit();
}

if ($_POST["contact"]){
        header("Location:contacts.php");
        exit();
}

if ($_POST["sources"]){
        header("Location:sources/sources.php");
        exit();
}

if ($_POST["exemple"]){
        header("Location:studs.php?sondage=aqg259dth55iuhwm");
        exit();
}

if ($_POST["apropos"]){
        header("Location:apropos.php");
        exit();
}

	//affichage de la page
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">'."\n";
	echo '<html>'."\n";
	echo '<head>'."\n";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'."\n";
	echo '<title>STUdS !</title>'."\n";
	echo '<link rel="stylesheet" type="text/css" href="style.css">'."\n";
	echo '</head>'."\n";
	echo '<body>'."\n";

    //debut du formulaire
    echo '<form name=formulaire action="apropos.php" method="POST">'."\n";

	//bandeaux de tete
	logo();
	bandeau_tete();
	bandeau_titre_apropos();
	sous_bandeau();

	//blablabla
	echo '<div class=corps>'."\n";

	echo '<b>'.$tt_apropos_techno.'</b><br><br>'."\n";
	echo '- <a href="http://www.php.net/">PHP</a><br>'."\n";
	echo '- <a href="http://www.postgresql.org/">PostgreSQL</a><br>'."\n";
	echo '- <a href="http://www.apache.org/">Apache</a><br>'."\n";
	echo '- <a href="http://subversion.tigris.org/">Subversion</a><br>'."\n";
	echo '- <a href="http://www.kigkonsult.se/iCalcreator/">iCalcreator</a><br>'."\n";
	echo '- <a href="http://www.fpdf.org/">FPDF</a><br>'."\n";
	echo '- Ic&ocirc;nes : <a href="http://deleket.deviantart.com/">Deleket</a>, <a href ="http://pixel-mixer.com">PixelMixer</a> et <a href="http://dryicons.com">DryIcons</a><br><br>'."\n";
	
	echo '<b>'.$tt_apropos_compat.'</b><br><br>'."\n";
	echo '- <a href="http://www.mozilla.com/firefox/">Firefox</a><br>'."\n";
	echo '- <a href="http://www.opera.com/">Op&eacute;ra</a><br>'."\n";
	echo '- <a href="http://www.konqueror.org/">Konqueror</a><br>'."\n";
	echo '- <a href="http://www.jikos.cz/~mikulas/links/">Links</a><br>'."\n";
	echo '- <a href="http://www.apple.com/fr/safari/">Safari</a><br>'."\n";
	echo '- <a href="http://www.mozilla.com/firefox/">IE</a><br><br>'."\n";

	echo '<b>'.$tt_apropos_validation_titre.'</b><br><br>'."\n";
	echo $tt_apropos_validation."\n";
 	echo '<p>'."\n"; 
	echo '<img src="http://www.w3.org/Icons/valid-html401-blue" alt="Valid HTML 4.01 Strict" height="31" width="88"><img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss-blue" alt="CSS Valide !">'."\n";
 	echo'</p>'."\n"; 

	echo '<b>'.$tt_apropos_merci_titre.'</b><br><br>'."\n";
	echo  $tt_apropos_merci.'<br><br>'."\n";

	echo '<b>Licence Logicielle de STUdS</b><br><br>'."\n";
	echo 'STUdS est plac&eacute; sous la <a href="http://www.cecill.info/licences.fr.html">licence CeCILL-B</a>.<br><br>'."\n";
	
	echo '<b>Les prochaines am&eacute;liorations de STUdS </b><br><br>'."\n";
	echo '- Rien pour l\'instant...<br><br>'."\n";
	
	echo 'Si quelquechose venait &agrave; vous manquer et ne pas appara&icirc;tre encore dans cette liste, vous pouvez m\'en faire part <a href="contacts.php">ici</a>. <br><br><br>'."\n";

	echo '<b>Les derni&egrave;res am&eacute;liorations de STUdS sont visibles dans le fichier <a href="CHANGELOG">CHANGELOG</a></b>'."\n";

	echo '</div>'."\n";
	
	sur_bandeau_pied_mobile();
	bandeau_pied_mobile();
	echo '</form>'."\n";
	echo '</body>'."\n";
	echo '</html>'."\n";

?>

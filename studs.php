<?php
session_start();

include 'bandeaux.php';
include 'fonctions.php';


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


// Le fichier studs.php sert a afficher les résultats d'un sondage à un simple utilisateur. 
// C'est également l'interface pour ajouter une valeur à un sondage deja créé.



//On récupère le numéro de sondage par le lien web.
$numsondage=$_GET["sondage"];

// Ouverture de la base de données
$connect=connexion_base();

if (eregi("[a-z0-9]{16}",$numsondage)){

	// récupération des données du sondage en fonction de la valeur passée dans l'URL
	$sondage=pg_exec($connect, "select * from sondage where id_sondage ilike '$numsondage'");
	$sujets=pg_exec($connect, "select * from sujet_studs where id_sondage='$numsondage'");
	$user_studs=pg_exec($connect, "select * from user_studs where id_sondage='$numsondage' order by id_users");

}

//verification de l'existence du sondage
// S'il n'existe pas, il affiche une page d'erreur
if (!$sondage||pg_numrows($sondage)=="0"){

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">'."\n";
	echo '<html>'."\n";
	echo '<head>'."\n";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">'."\n";
	echo '<title>STUdS !</title>'."\n";
	echo '<link rel="stylesheet" type="text/css" href="style.css">'."\n";
	echo '</head>'."\n";
	echo '<body>'."\n";
	logo();
	bandeau_tete();
	bandeau_titre_erreur();
	echo '<div class=corpscentre>'."\n";
	print "<H2>$tt_studs_erreur_titre</H2>"."\n";
	print "$tt_choix_page_erreur_retour <a href=\"index.php\"> STUdS</A>."."\n";
	echo '<br><br><br><br>'."\n";
	echo '</div>'."\n";
	sur_bandeau_pied();
	bandeau_pied();
	
	echo '</body>'."\n";
	echo '</html>'."\n";

}

// Sinon il affiche le sondage concerné
else {

	//bouton annuler
	if ($_POST["annuler"]){
		header("Location:index.php");
		exit();
	}


	if ($_POST["exportics_x"]){
		header("Location:exportics.php");
		exit();

	}
	if ($_POST["exportcsv_x"]){
		$_SESSION["numsondage"]=$_GET["sondage"];
		header("Location:exportcsv.php");
		exit();

	}

//On récupere les données et les sujets du sondage
	$dsondage=pg_fetch_object($sondage,0);
	$dsujet=pg_fetch_object($sujets,0);
	$nbcolonnes=substr_count($dsujet->sujet,',')+1;
	$nblignes=pg_numrows($user_studs);

	// Action quand on clique le bouton participer
	if ($_POST["boutonp"]||$_POST["boutonp_x"]){
	//Si le nom est bien entré
		if ($_POST["nom"]){
			for ($i=0;$i<$nbcolonnes;$i++){
				
				// Si la checkbox est enclenchée alors la valeur est 1
				if (isset($_POST["choix$i"])){
					$nouveauchoix.="1";
				}
				// sinon c'est 0
				else {
					$nouveauchoix.="0";
				}
			}

			for ($compteur=0;$compteur<pg_numrows($user_studs);$compteur++){

					$user=pg_fetch_object($user_studs,$compteur);

					if ($_POST["nom"]==$user->nom){
						$erreur_prénom="yes";
					}
			}

			if (ereg("<|>|\"|\'", $_POST["nom"])){
				$erreur_injection="yes";
			}

			// Ecriture des choix de l'utilisateur dans la base
 			if (!$erreur_prénom&&!$erreur_injection){
				$nom=$_POST["nom"];
 				pg_query($connect,"insert into user_studs values ('$nom', '$numsondage', '$nouveauchoix')");

				if ($dsondage->mailsonde=="yes"){
					mail ("$dsondage->mail_admin", utf8_decode ("[STUdS] $tt_studs_mail_sujet : $dsondage->titre"), "\"$nom\"".utf8_decode ("$tt_studs_mail_corps :\n\nhttp://".getenv('NOMSERVEUR')."/studs.php?sondage=$numsondage \n\n$tt_studs_mail_merci\nSTUdS !"),$headers);
				}
			}
		}
	}
	
			//on teste pour voir si une ligne doit etre modifiée
		for ($i=0;$i<$nblignes;$i++){
			if ($_POST["modifierligne$i"]||$_POST['modifierligne'.$i.'_x']){
				$ligneamodifier=$i;
				$testligneamodifier="true";
			}
		}	

			//test pour voir si une ligne est a modifier

		for ($i=0;$i<$nblignes;$i++){
			if ($_POST['validermodifier'.$i.'_x']){
				$modifier=$i;
				$testmodifier="true";
			}
		}
		//si le test est valide alors on affiche des checkbox pour entrer de nouvelles valeurs
		if ($testmodifier){

			for ($i=0;$i<$nbcolonnes;$i++){
				//recuperation des nouveaux choix de l'utilisateur
				if (isset($_POST["choix$i"])){
					$nouveauchoix.="1";
				}
				else {
					$nouveauchoix.="0";
				}
			}

			$compteur=0;
			while ($compteur<pg_numrows($user_studs)){

				$data=pg_fetch_object($user_studs,$compteur);
				//mise a jour des données de l'utilisateur dans la base SQL
				if ($compteur==$modifier){
					pg_query($connect,"update user_studs set reponses='$nouveauchoix' where nom='$data->nom' and id_users='$data->id_users'");
					if ($dsondage->mailsonde=="yes"){
						mail ("$dsondage->mail_admin", utf8_decode ("[STUdS] $tt_studs_mail_sujet : $dsondage->titre"), "\"$nom\"".utf8_decode ("$tt_studs_mail_corps :\n\nhttp://".getenv('NOMSERVEUR')."/studs.php?sondage=$numsondage \n\n$tt_studs_mail_merci\nSTUdS !"),$headers);
					}
				}
				$compteur++;
			}
		}
	
//recuperation des utilisateurs du sondage
	$user_studs=pg_exec($connect, "select * from user_studs where id_sondage='$numsondage' order by id_users");

// Affichage des balises standards et du titre de la page
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">'."\n";
	echo '<html>'."\n";
	echo '<head>'."\n";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">'."\n";
	echo '<title>STUdS !</title>'."\n";
	echo '<link rel="stylesheet" type="text/css" href="style.css">'."\n";
	
	#bloquer la touche entrée
	blocage_touche_entree();
	
	echo '</head>'."\n";
	echo '<body>'."\n";

// debut du formulaire et affichage des bandeanx
	echo '<form name="formulaire" action="studs.php?sondage='.$numsondage.'#bas" method="POST" onkeypress="javascript:process_keypress(event)">'."\n";
	logo();
 	bandeau_tete();
	bandeau_titre();
	sous_bandeau_light();
	echo '<div class="presentationdate"> '."\n";

//affichage du titre du sondage
        $titre=str_replace("\\","",$dsondage->titre);       
	echo '<H2>'.utf8_decode($titre).'</H2>'."\n";

//affichage du nom de l'auteur du sondage
	echo $tt_studs_auteur.' : '.utf8_decode($dsondage->nom_admin).'<br><br>'."\n";

//affichage des commentaires du sondage
	if ($dsondage->commentaires){
		echo $tt_studs_commentaires.' :<br>'."\n";
                $commentaires=$dsondage->commentaires;
                $commentaires=str_replace("\\","",$commentaires);       
                echo utf8_decode($commentaires);

		echo '<br>'."\n";
	}
	echo '<br>'."\n";
	echo '</div>'."\n";
	echo '<div class="cadre"> '."\n";

	echo $tt_studs_presentation."\n";

	echo '<br><br>'."\n";


// Debut de l'affichage des resultats du sondage
	echo '<table class="resultats">'."\n";

//reformatage des données des sujets du sondage
	$toutsujet=explode(",",$dsujet->sujet);

//si le sondage est un sondage de date
if ($dsondage->format=="D"||$dsondage->format=="D+"){
	
//affichage des sujets du sondage
	echo '<tr>'."\n";
	echo '<td></td>'."\n";

	//affichage des années
	$colspan=1;
	for ($i=0;$i<count($toutsujet);$i++){
		if (strftime("%Y",$toutsujet[$i])==strftime("%Y",$toutsujet[$i+1])){
			$colspan++;
		}
		else {
			echo '<td colspan='.$colspan.' class="annee">'.strftime("%Y",$toutsujet[$i]).'</td>'."\n";
			$colspan=1;
		}
	}
	echo '</tr>'."\n";
	
	echo '<tr>'."\n";	
	echo '<td></td>'."\n";
	//affichage des mois
	$colspan=1;
	for ($i=0;$i<count($toutsujet);$i++){
		if (strftime("%B",$toutsujet[$i])==strftime("%B",$toutsujet[$i+1])&&strftime("%Y",$toutsujet[$i])==strftime("%Y",$toutsujet[$i+1])){
			$colspan++;
		}
		else {
			if ($_SESSION["langue"]=="FR"){setlocale(LC_TIME, "fr_FR");echo '<td colspan='.$colspan.' class="mois">'.strftime("%B",$toutsujet[$i]).'</td>'."\n";}
			if ($_SESSION["langue"]=="ES"){setlocale(LC_ALL, "es_ES");echo '<td colspan='.$colspan.' class="mois">'.strftime("%B",$toutsujet[$i]).'</td>'."\n";}
			if ($_SESSION["langue"]=="EN"){echo '<td colspan='.$colspan.' class="mois">'.date("F",$toutsujet[$i]).'</td>'."\n";}
			if ($_SESSION["langue"]=="DE"){setlocale(LC_ALL, "de_DE");echo '<td colspan='.$colspan.' class="mois">'.strftime("%B",$toutsujet[$i]).'</td>'."\n";}
			$colspan=1;
		}
	}
	echo '</tr>'."\n";

	echo '<tr>'."\n";		
	echo '<td></td>'."\n";
		//affichage des jours
	$colspan=1;
	for ($i=0;$i<count($toutsujet);$i++){
		if (strftime("%a %e",$toutsujet[$i])==strftime("%a %e",$toutsujet[$i+1])&&strftime("%B",$toutsujet[$i])==strftime("%B",$toutsujet[$i+1])){
			$colspan++;
		}
		else {
			if ($_SESSION["langue"]=="FR"){setlocale(LC_TIME, "fr_FR");echo '<td colspan='.$colspan.' class="jour">'.strftime("%a %e",$toutsujet[$i]).'</td>'."\n";}
			if ($_SESSION["langue"]=="ES"){setlocale(LC_ALL, "es_ES");echo '<td colspan='.$colspan.' class="jour">'.strftime("%a %e",$toutsujet[$i]).'</td>'."\n";}
			if ($_SESSION["langue"]=="EN"){echo '<td colspan='.$colspan.' class="jour">'.date("D jS",$toutsujet[$i]).'</td>'."\n";}
			if ($_SESSION["langue"]=="DE"){setlocale(LC_ALL, "de_DE");echo '<td colspan='.$colspan.' class="jour">'.strftime("%a %e",$toutsujet[$i]).'</td>'."\n";}			
			$colspan=1;
		}
	}
	echo '</tr>'."\n";
		//affichage des horaires	
	if (eregi("@",$dsujet->sujet)){
		echo '<tr>'."\n";
		echo '<td></td>'."\n";
				
		for ($i=0;$toutsujet[$i];$i++){
			$heures=explode("@",$toutsujet[$i]);
			echo '<td class="heure">'.$heures[1].'</td>'."\n";
		}
		echo '</tr>'."\n";
	}
}

else {
	$toutsujet=str_replace("°","'",$toutsujet);	

//affichage des sujets du sondage
	echo '<tr>'."\n";
	echo '<td></td>'."\n";

	for ($i=0;$toutsujet[$i];$i++){
	
		echo '<td class="sujet">'.$toutsujet[$i].'</td>'."\n";
	}
	echo '</tr>'."\n";

}
	
//affichage des resultats actuels
	$somme[]=0;
	$compteur = 0;
	while ($compteur<pg_numrows($user_studs)){

		echo '<tr>'."\n";
		echo '<td class="nom">';

		$data=pg_fetch_object($user_studs,$compteur);
// Le nom de l'utilisateur
		$nombase=str_replace("°","'",$data->nom);
		echo $nombase.'</td>'."\n";
// Les réponses qu'il a choisit
		$ensemblereponses=$data->reponses;

			//si la ligne n'est pas a changer, on affiche les données
			if (!$testligneamodifier){
				for ($k=0;$k<$nbcolonnes;$k++){
					$car=substr($ensemblereponses,$k,1);
					if ($car=="1"){
						echo '<td class="ok">OK</td>'."\n";
						$somme[$k]++;
					}
					else {
						echo '<td class="non"></td>'."\n";
					}
				}
			}
			//sinon on remplace les choix de l'utilisateur par une ligne de checkbox pour recuperer de nouvelles valeurs
			else {
				//si c'est bien la ligne a modifier on met les checkbox
				if ($compteur=="$ligneamodifier"){
					for ($j=0;$j<$nbcolonnes;$j++){
							
						$car=substr($ensemblereponses,$j,1);
						if ($car=="1"){
							echo '<td class="vide"><input type="checkbox" name="choix'.$j.'" value="" checked></td>'."\n";
						}
						else {
							echo '<td class="vide"><input type="checkbox" name="choix'.$j.'" value=""></td>'."\n";
						}
					}
				}
				//sinon on affiche les lignes normales
				else {
					for ($k=0;$k<$nbcolonnes;$k++){
						$car=substr($ensemblereponses,$k,1);
						if ($car=="1"){
							echo '<td class="ok">OK</td>'."\n";
							$somme[$k]++;
						}
						else {
							echo '<td class="non"></td>'."\n";
						}
					}
				}

			}
			
			//a la fin de chaque ligne se trouve les boutons modifier
			if (!$testligneamodifier=="true"&&($dsondage->format=="A+"||$dsondage->format=="D+")){
				echo '<td class=casevide><input type="image" name="modifierligne'.$compteur.'" value="Modifier" src="images/info.png"></td>'."\n";
			}
			
			//demande de confirmation pour modification de ligne
			for ($i=0;$i<$nblignes;$i++){
				if ($_POST["modifierligne$i"]||$_POST['modifierligne'.$i.'_x']){
					if ($compteur==$i){
						echo '<td class=casevide><input type="image" name="validermodifier'.$compteur.'" value="Valider la modification" src="images/accept.png" ></td>'."\n";
					}
				}
			}
			$compteur++;
			echo '</tr>'."\n";
	}
	
// affichage de la ligne pour un nouvel utilisateur
	echo '<tr>'."\n";
	echo '<td class=nom>'."\n";
	echo '<input type=text name="nom">'."\n";
	echo '</td>'."\n";

// affichage des cases de formulaire checkbox pour un nouveau choix
	for ($i=0;$i<$nbcolonnes;$i++){
		echo '<td class="vide"><input type="checkbox" name="choix'.$i.'" value=""></td>'."\n";
	}
	// Affichage du bouton de formulaire pour inscrire un nouvel utilisateur dans la base
	echo '<td><input type="image" name="boutonp" value="Participer" src="images/add-24.png"></td>'."\n";
	echo '</tr>'."\n";

//determination de la meilleure date

// On cherche la meilleure colonne
	for ($i=0;$i<$nbcolonnes;$i++){
		if ($i=="0"){
			$meilleurecolonne=$somme[$i];
		}
		if ($somme[$i]>$meilleurecolonne){
			$meilleurecolonne=$somme[$i];
		}
	}

// Affichage des différentes sommes des colonnes existantes
	echo '<tr>'."\n";
	echo '<td align="right">'.$tt_studs_somme.'</td>'."\n";

	for ($i=0;$i<$nbcolonnes;$i++){
		$affichesomme=$somme[$i];
		if ($affichesomme==""){$affichesomme="0";}
		echo '<td class="somme">'.$affichesomme.'</td>'."\n";
	}
	echo '</tr>'."\n";
	
	echo '<tr>'."\n";
	echo '<td class="somme"></td>'."\n";
	for ($i=0;$i<$nbcolonnes;$i++){
		if ($somme[$i]==$meilleurecolonne&&$somme[$i]){
			echo '<td class="somme"><img src="images/medaille.png" alt="Meilleur choix"></td>'."\n";
		}
		else {
			echo '<td class="somme"></td>'."\n";
		}

		}
	echo '</tr>'."\n";

	echo '</table>'."\n";
	echo '</div>'."\n";

	echo '<p class=affichageresultats>'."\n";
	// S'il a oublié de remplir un nom
	if ($_POST["boutonp_x"]&&$_POST["nom"]=="") {
			print "<font color=#FF0000>$tt_studs_erreur_nomvide</font>\n";
		}
	if ($erreur_prénom){
			print "<font color=#FF0000>$tt_studs_erreur_nomdeja</font>\n";
	}
	if ($erreur_injection){
			print "<font color=#FF0000>$tt_studs_erreur_injection</font>\n";
	}
	echo '<br>'."\n";
// Focus javascript sur la case de texte du formulaire
	echo '<script type="text/javascript">'."\n";
	echo 'document.formulaire.nom.focus();'."\n";
	echo '</script>'."\n";

// reformatage des données de la base pour les sujets
	$toutsujet=explode(",",$dsujet->sujet);
	$toutsujet=str_replace("°","'",$toutsujet);

// On compare le nombre de résultat avec le meilleur et si le résultat est égal
//  on concatene le resultat dans $meilleursujet

	$compteursujet=0;
	for ($i=0;$i<$nbcolonnes;$i++){
		if ($somme[$i]==$meilleurecolonne){
			$meilleursujet.=", ";
			  	if ($dsondage->format=="D"||$dsondage->format=="D+"){
					$meilleursujetexport=$toutsujet[$i];
					if (eregi("@",$toutsujet[$i])){
						$toutsujetdate=explode("@",$toutsujet[$i]);
						if ($_SESSION["langue"]=="FR"){setlocale(LC_TIME, "fr_FR");$meilleursujet.=strftime("%A %e %B %Y",$toutsujetdate[0])." $tt_studs_a ".$toutsujetdate[1];}
						if ($_SESSION["langue"]=="ES"){setlocale(LC_ALL, "es_ES");$meilleursujet.=strftime("%A %e de %B %Y",$toutsujetdate[0])." $tt_studs_a ".$toutsujetdate[1];}
						if ($_SESSION["langue"]=="EN"){$meilleursujet.=date("l, F jS Y",$toutsujetdate[0])." $tt_studs_a ".$toutsujetdate[1];}
						if ($_SESSION["langue"]=="DE"){setlocale(LC_ALL, "de_DE");$meilleursujet.=strftime("%A, den %e. %B %Y",$toutsujetdate[0])." $tt_studs_a ".$toutsujetdate[1];}
					}
					else{
						if ($_SESSION["langue"]=="FR"){setlocale(LC_TIME, "fr_FR");$meilleursujet.=strftime("%A %e %B %Y",$toutsujet[$i]);}
						if ($_SESSION["langue"]=="ES"){setlocale(LC_ALL, "es_ES");$meilleursujet.=strftime("%A %e de %B %Y",$toutsujet[$i]);}
						if ($_SESSION["langue"]=="EN"){$meilleursujet.=date("l, F jS Y",$toutsujet[$i]);}
						if ($_SESSION["langue"]=="DE"){setlocale(LC_ALL, "de_DE");$meilleursujet.=strftime("%A, den %e. %B %Y",$toutsujet[$i]);}
					}
				}
				else{
					$meilleursujet.=$toutsujet[$i];
				}
			$compteursujet++;
		}
	}
	$meilleursujet=substr("$meilleursujet",1);

	// Si le résultat est supérieur à 1 on rajoute un S
	if ($meilleurecolonne!="1"&&($_SESSION["langue"]=="FR"||$_SESSION["langue"]=="EN"||$_SESSION["langue"]=="ES")){$pluriel="s";}
	if ($meilleurecolonne!="1"&&$_SESSION["langue"]=="DE"){$pluriel="n";}
	
	// Affichage du meilleur choix

	if ($compteursujet=="1"&&$meilleurecolonne){
			print "<img src=\"images/medaille.png\" alt=\"Meilleur choix\"> $tt_studs_meilleurchoix : <b>$meilleursujet </b>$tt_studs_meilleurchoix_avec <b>$meilleurecolonne </b>$tt_studs_meilleurchoix_vote$pluriel.\n";
 	}
	elseif ($meilleurecolonne){
		print "<img src=\"images/medaille.png\" alt=\"Meilleur choix\"> $tt_studs_meilleurchoix_pluriel : <b>$meilleursujet </b>$tt_studs_meilleurchoix_avec <b>$meilleurecolonne </b>$tt_studs_meilleurchoix_vote$pluriel.\n";
	}

	pg_close($connect);
	
	echo '<br><br>'."\n";
	echo '<p class=affichageexport>'."\n";
	echo $tt_studs_export.' (.CSV) <input type="image" name="exportcsv" value="Export en CSV" src="images/csv.png" alt="Export CSV">  ';
 		if (($dsondage->format=="D"||$dsondage->format=="D+")&&$compteursujet=="1"&&$meilleurecolonne){
  			echo $tt_studs_agenda.' (.ICS) :<input type="image" name="exportics" value="Export en iCal" src="images/ical.png" alt="Export iCal">';
  			$_SESSION["meilleursujet"]=$meilleursujetexport;
  			$_SESSION["numsondage"]=$numsondage;
  			$_SESSION["sondagetitre"]=$dsondage->titre;
  		}
	echo '<br><br>'."\n";
	echo '<a name=bas></a>'."\n";
	echo '</p>'."\n";

	sur_bandeau_pied_mobile();
	bandeau_pied_mobile();
	// Affichage du bandeau de pied
	echo '</form>'."\n";
	echo '</body>'."\n";
	echo '</html>'."\n";
}
?>

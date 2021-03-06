<?php
session_start();
include "functions.php";

$config = readdata("config",true,null,null,null,true);
if(!isset($theme) || theme_exist($theme))
	$theme = $config['theme'];
$limit = $config['news_par_page'];
$is_one_only=false;
$is_news=true;
$is_page=false;
$page = 1;
$pagenum = 1;
$id=null;
$comstemplatee = "";
$errorcoms="";
$template = "";
$error404 = false;
$socials= array(false,false,false);
echo '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script><script src="'.getFolder().'/js/vNews.js" type="text/javascript"></script>';

if(isset($_POST["vnewspseudo"]) && isset($_POST["vnewsmessage"]) && isset($_POST["vnewscaptcha"])){
	$_POST["vnewspseudo"] = htmlspecialchars($_POST["vnewspseudo"]);
	$_POST["vnewsmessage"] = htmlspecialchars($_POST["vnewsmessage"]);
	if(strlen($_POST["vnewspseudo"])<2 || strlen($_POST["vnewspseudo"])>48){$errorcoms = "Votre pseudo est invalide.";}
	if(strlen($_POST["vnewsmessage"])>500){$errorcoms = "Votre commentaire est trop long.";}
	if($_POST["vnewscaptcha"]!=$_SESSION["vnewscaptcha"]){$errorcoms = "Le code n'est pas valide.";}
	$j = array();$j["pseudo"]=$_POST["vnewspseudo"];$j["message"]=$_POST["vnewsmessage"];$j["news_id"]=$_GET['news'];$j["date"]=time();
	if($errorcoms == ""){savedatawithdata("dbcoms",$j,true,true);}
}

/* TRAITEMENT VARIABLES*/
if(isset($_GET['page'])){
	if(!is_numeric($_GET['page'])){
		if(pages_exist($_GET['page'],true)===true){
		$page = $_GET['page'];$is_news = false; $is_page=true;}}
	else if(is_numeric($_GET['page'])){$page=$_GET["page"];}
	else{$error404 = 1;}
}else{$page = 1;}
	$e = getFolder().'/themes/'.$theme.'/css/style.css';
	if(file_exists($e)){echo '<link rel="stylesheet" type="text/css" href="'.getFolder().'/themes/'.$theme.'/css/style.css" />';}
	
if($is_news){
	if(isset($_GET['categorie'])){$cat = $_GET['categorie'];}else{$cat = 10000000000;}
	if(isset($categorie)){$cat = $categorie;}else{$cat = 10000000000;}
	if(isset($_GET['news']) && is_numeric($_GET['news'])){ if(news_exist($_GET['news'],true)){$id=$_GET['news'];$is_one_only=true;}else{$error404 = true;}}

	if($is_one_only){
		$template = getTheme($theme,true,"single");
		$data = readdata('dbnews',true,$id,null,null,true);
	}else{
		$template = getTheme($theme,true,"news");
		$data = readdata('dbnews',true,null,"date",false,true,$limit,$page,10000000000,$cat);
	}
}else if($is_page){
		$template = getTheme($theme,true,"page");
	$idpage = getCatidfromslug($page,true,"dbpages");
	$data = readdata('dbpages',true,$idpage,null,false,true);
}

/* TRAITEMENT DATA*/
if(!$error404 && $data!=null && !empty($data) && is_array($data)){


foreach($data as $id=>$n){
	$finds = array("{titre}","{contenu}","{date}","{auteur}","{categorie}","{url}","{tweet}","{like}","{+1}","{nbcommentaires}","{commentaires}");
	$commentaires=null;
	$nbcommentaires=null;


	if($is_one_only){
	$coms = readdata('dbcoms',true,null,"date",false,true,null,null,$_GET['news']);
	
	if($coms!=null && !empty($coms) && is_array($coms)){
		foreach($coms as $id=>$k){
			$comsfinds = array("{pseudo}","{commentaire}","{date}");
			$comstemplate = getTheme($theme,true,"commentaires");
			$replc = array($k['pseudo'],stripslashes($k['message']),toDate($k['date'],$config));
			$comstemplatee .= str_replace($comsfinds, $replc, $comstemplate);
		}
	}
		else{
			$comstemplatee = "Aucun commentaires, soyez le premier &agrave; en poster!";
		}
		$titre=$n['titre'];
		$commentaires = '
		<hr/>
		<strong>Les Commentaires</strong><br /><br />
		'.$comstemplatee.'
		<br /><br />
		'.$errorcoms.'
		<strong>Ajouter un commentaire</strong>
		<br /><br />
		<form action="" method="post">
		<label for="vnewspseudo">Votre Pseudo :</label><br/>
		<input type="text" name="vnewspseudo" class="vnews-textinput"/><br/>
		<br/>
		<label for="vnewsmessage">Votre Commentaire :</label><br/>
		<textarea name="vnewsmessage" class="vnews-textarea"></textarea><br/>
		<br />
		<label for="vnewscaptcha">Recopier le code :</label><br/>
		<img style="float:left;" src="'.getFolder().'/captcha.php" alt="captcha" />
		<input type="text" name="vnewscaptcha" id="vnewscaptcha" class="vnews-textinput"/>
		<br/><br/>
		<input onclick="this.value=\'Envoi en cours...\';this.disabled=true;this.form.submit();" type="submit" class="vnews-submitinput" value="Ajouter ce commentaire"/><br/>
		</form>
		';
	}
	else if($is_news){
		$titre = '<a  href="?news='.$id.'">'.$n['titre'].'</a>';
		$nbcommentaires=countComs($id,true)." Commentaire";
		if(countComs($id,true)>1){$nbcommentaires.="s";}
	}
	else{
		$titre = $n['titre'];
	}




	/* RENDER */
	$Curl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ;
	$print = $template;
	
	if(preg_match("/\{tweet\}/i", $template))
		echo getSocialScript("twitter");
	if(preg_match("/\{like\}/i", $template))
		echo getSocialScript("facebook");
	if(preg_match("/\{\+1\}/i", $template))
		echo getSocialScript("google");
	
	if($is_news){
	$repl = array($titre,stripslashes($n['contenu']),toDate($n['date'],$config),$n['auteur'],getCatname($n['categorie'],true),'?news='.$id,getSocialButton("twitter",$n['titre'],$Curl),getSocialButton("facebook",$n['titre'],$Curl),getSocialButton("google",$n['titre'],$Curl),$nbcommentaires,$commentaires);
	}
	if($is_page){
	$repl = array($titre,stripslashes($n['contenu']),"",$n['auteur'],"","",getSocialButton("twitter",$n['titre'],$Curl),getSocialButton("facebook",$n['titre'],$Curl),getSocialButton("google",$n['titre'],$Curl),"","");
	}

	$print = str_replace($finds, $repl, $print);
	echo $print;
	
}

}else{
	echo "Pas de contenu disponible.";}
if(!(isset($pagination) && $pagination==false) && (!$error404 && $is_news && !$is_one_only && $data!=null && !empty($data) && is_array($data))){
	echo getPagination($page,$limit,$cat);
}
$errorcoms = "";
?>
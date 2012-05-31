<?php
if(!isset($_GET)){header("Location:index.php?page=admin_users_liste");}
else{
if(!isset($_GET['id'])){header("Location:index.php?page=admin_users_liste");}
$r = readdata("dbuser",true,$_GET['id']);
if(isset($_POST)){
    if(!empty($_POST)){
        if(!empty($_POST['pseudo']) && !empty($_POST['pass']) && !empty($_POST['token']) && !empty($_POST['rang']) ){
            if($_POST['token']==$_SESSION['Auth']['token']){
                $z = array();
                $z["pseudo"] = $_POST['pseudo'];
                $z["motdepasse"] = sha1(getHashcode().$_POST['pass']);
                $z["rang"] = $_POST['rang'];
                replacedatawithdatafromid("dbuser",$z,$_GET['id']);
                header("Location:index.php?page=admin_users_liste");
            }else{$error = "Votre session n'est pas valide reconnectez vous.";}
        }else{$error = "Veuillez remplir tous les champs pour éditer un administrateur.";}
    }
}

}
$selected = "selected='selected'";
?>
<h1>Éditer un Utilisateur</h1>

<form action="index.php?page=admin_users_editer&id=<?php echo $_GET['id']; ?>" method="POST">
    <input type="hidden" name="token" value="<?php echo $_SESSION['Auth']['token']; ?>"/><br />
    <label for="pseudo">Pseudo de l'Utilisateur :</label><br />
    <input type="text" name="pseudo" value="<?php echo $r['pseudo']; ?>"/><br />
    <label for="pass">Mot de passe de l'Utilisateur :</label><br />
    <input type="text" name="pass" /><br />
      <label for="rang">Rang de l'Utilisateur :</label><br />
    <select name="rang">
        <optgroup label="Rang">
        <option value="1" <?php if($r['rang'] == 1){ echo $selected;} ?>>Administrateur</option>
        <option value="2" <?php if($r['rang'] == 2){ echo $selected;} ?>>Journaliste</option>
        </optgroup>
</select><br /><br />
    <input type="submit" value="Éditer cet administrateur"/>
</form>
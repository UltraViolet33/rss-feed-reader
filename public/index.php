<?php require_once('./inc/header.php');
session_start(); 

require_once('./../app/classes/RssLink.php');

use App\RssLink;

  $doc = new RssLink();

  $data = $doc->getRSSlinks();


?>
<h1 class="center">Home page</h1>
<div class="form">
    <form action="<?= ROOT ?>/action.php" method="POST">
        <label for="link">Ajouter un lien</label>
        <input type="text" name="url">
        <button type="submit">Valider</button>
    </form>
    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])) : ?>
        <div>
            <p class="center">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<div>
  <?php foreach($data as $link){
         require('./inc/link.php');
    }


 ?>
       
</div>
<?php require_once('./inc/footer.php'); ?>
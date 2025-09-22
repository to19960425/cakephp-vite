<?= $this->ViteScripts->script('resources/js/main.tsx'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>

<body>
    <div class="el_btn">Count: 0</div>

    <?= $this->fetch('script') ?>
</body>

</html>
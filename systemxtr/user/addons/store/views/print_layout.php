<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= $title ?></title>
        <link rel="stylesheet" href="<?= ee()->store->config->asset_url('css/mcp.css') ?>">
        <link rel="stylesheet" href="<?= ee()->store->config->asset_url('css/print.css') ?>">
        <script type="text/javascript">
        //window.onload = function() { window.print(); }
        </script>
    </head>
    <body class="<?= $class ?>">
        <h1><?= $title ?></h1>
        <?php
            if (is_object($body)) {
                $body->run();
            } else {
                //echo $body;
				print_r($body['body']);
            }
        ?>
    </body>
</html>

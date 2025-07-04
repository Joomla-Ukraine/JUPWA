<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 * @formatter:off
 **/

defined('_JEXEC') or die('Restricted access');

/** @var array $displayData */
$data = (object) $displayData;

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>You are offline - <?= $data->app->get('sitename'); ?></title>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<style>
        body {
            font-family: helvetica, arial, sans-serif;
            margin: 2em;
        }

        h1 {
            font-style: italic;
            color: #373fff;
        }

        p {
            margin-block: 1rem;
        }

        button {
            display: block;
        }
	</style>
</head>
<body>
<h1>You are offline</h1>

<p>
	The page will automatically reload once the connection is re-established.
	Click the button below to try reloading manually.
</p>

<button type="button">⤾ Reload</button>

<p><small><?= $data->app->get('sitename'); ?></small></p>

<div style="margin: 30px;align-content: center"><?= date('Y'); ?> &copy; With ♥️ <a href="https://joomla-ua.org">Joomla! Україна</a></div>

<script>
    document.querySelector('button').addEventListener('click', () => {
        window.location.reload();
    });

    async function checkNetworkAndReload() {
        try {
            const response = await fetch('.');
            if (response.status >= 200 && response.status < 500) {
                window.location.reload();
                return;
            }
        } catch {
            // Unable to connect to the server, ignore.
        }
        window.setTimeout(checkNetworkAndReload, 2500);
    }

    checkNetworkAndReload();
</script>
</body>
</html>
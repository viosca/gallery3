<? defined("SYSPATH") or die("No direct script access.") ?>
<? $error_id = uniqid("error") ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <style type="text/css">
      body {
        background: #fff;
        font-size: 14px;
        line-height: 130%;
      }

      div.big_box {
        padding: 10px;
        background: #eee;
        border: solid 1px #ccc;
        font-family: sans-serif;
        color: #111;
        width: 60em;
        margin: 20px auto;
      }

      div#framework_error {
        text-align: center;
      }

      div#error_details {
        text-align: left;
      }

      code {
        font-family: monospace;
        font-size: 12px;
        margin: 20px 20px 20px 0px;
        color: #333;
        white-space: pre-wrap;
        white-space: -moz-pre-wrap;
        word-wrap: break-word;
      }

      code .line {
        padding-left: 10px;
      }

      h3 {
        font-family: sans-serif;
        margin: 2px 0px 0px 0px;
        padding: 8px 0px 0px 0px;
        border-top: 1px solid #ddd;
      }

      p {
        padding: 0px;
        margin: 0px 0px 10px 0px;
      }

      li, pre {
        padding: 0px;
        margin: 0px;
      }

      .collapsed {
        display: none;
      }

      .highlight {
        font-weight: bold;
        color: darkred;
      }

      #kohana_error .message {
        display: block;
        padding-bottom: 10px;
      }

      .source {
        border: solid 1px #ccc;
        background: #efe;
        margin-bottom: 5px;
      }

      table {
        width: 100%;
        display: block;
        margin: 0 0 0.4em;
        padding: 0;
        border-collapse: collapse;
        background: #efe;
      }

      table td {
        border: solid 1px #ddd;
        text-align: left;
        vertical-align: top;
        padding: 0.4em;
      }

      .args table td.key {
        width: 200px;
      }

      .number {
        padding-right: 1em;
      }
    </style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?= t("Something went wrong!") ?></title>

    <script type="text/javascript">
      function koggle(elem) {
        elem = document.getElementById(elem);
        if (elem.style && elem.style["display"]) {
          // Only works with the "style" attr
          var disp = elem.style["display"];
        } else {
          if (elem.currentStyle) {
            // For MSIE, naturally
            var disp = elem.currentStyle["display"];
          } else {
            if (window.getComputedStyle) {
              // For most other browsers
              var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');
            }
          }
        }

        // Toggle the state of the "display" style
        elem.style.display = disp == 'block' ? 'none' : 'block';
        return false;
      }
    </script>
  </head>
  <body>
    <? try { $user = identity::active_user(); } catch (Exception $e) { } ?>
    <? $admin = php_sapi_name() == "cli" || isset($user) && $user->admin ?>
    <div class="big_box" id="framework_error">
      <h1>
        <?= t("Dang...  Something went wrong!") ?>
      </h1>
      <h2>
        <?= t("We tried really hard, but it's broken.") ?>
      </h2>
      <? if (!$admin): ?>
      <p>
        <?= t("Talk to your Gallery administrator for help fixing this!") ?>
      </p>
      <? endif ?>
    </div>
    <? if ($admin): ?>
    <div class="big_box" id="error_details">
      <h2>
        <?= t("Hey wait, you're an admin!  We can tell you stuff.") ?>
      </h2>
      <div id="kohana_error">
	<h3>
          <span class="type">
            <?= $type?> [ <?= $code ?> ]:
          </span>
          <span class="message">
            <?= $message?>
          </span>
	</h3>
	<div id="<?= $error_id ?>" class="content">
          <ol class="trace">
            <li class="snippet">
              <p>
                <span class="file">
                  <?= Kohana_Exception::debug_path($file)?>[ <?= $line?> ]
                </span>
              </p>

              <div class="source">
                <? if (Kohana_Exception::$source_output and $source_code = Kohana_Exception::debug_source($file, $line)): ?><code><? foreach ($source_code as $num => $row): ?><span class="line <?= ($num == $line) ? "highlight" : ""?>"><span class="number"><?= $num ?></span><?= htmlspecialchars($row, ENT_NOQUOTES, Kohana::CHARSET) ?></span><? endforeach ?></code>
                <? endif ?>
              </div>
            </li>

            <? if (Kohana_Exception::$trace_output): ?>
            <? foreach (Kohana_Exception::trace($trace) as $i => $step): ?>
            <li class="snippet">
              <p>
                <span class="file">
                  <? if ($step["file"]): $source_id = "$error_id.source.$i" ?>
                  <? if (Kohana_Exception::$source_output and $step["source"]): ?>
                  <a href="#<?= $source_id ?>" onclick="return koggle('<?= $source_id ?>')"><?= Kohana_Exception::debug_path($step["file"])?>[ <?= $step["line"]?> ]</a>
                  <? else: ?>
                  <span class="file"><?= Kohana_Exception::debug_path($step["file"])?>[ <?= $step["line"]?> ]</span>
                  <? endif ?>
                  <? else: ?>
                  {<?= t("PHP internal call")?>}
                  <? endif?>
                </span>
                &raquo;
                <?= $step["function"]?>(<? if ($step["args"]): $args_id = "$error_id.args.$i" ?>
                <a href="#<?= $args_id ?>" onclick="return koggle('<?= $args_id ?>')"><?= t("arguments")?></a>
                <? endif?>)
              </p>
              <? if (isset($args_id)): ?>
              <div id="<?= $args_id ?>" class="args collapsed">
                <table cellspacing="0">
                  <? foreach ($step["args"] as $name => $arg): ?>
                  <tr>
                    <td class="key">
                      <pre><?= $name?></pre>
                    </td>
                    <td class="value">
                      <pre><?= Kohana_Exception::dump($arg) ?></pre>
                    </td>
                  </tr>
                  <? endforeach?>
                </table>
              </div>
              <? endif?>
              <? if (Kohana_Exception::$source_output and $step["source"] and isset($source_id)): ?>
              <pre id="<?= $source_id ?>" class="source collapsed"><code><? foreach ($step["source"] as $num => $row): ?><span class="line <?= ($num == $step["line"]) ? "highlight" : "" ?>"><span class="number"><?= $num ?></span><?= htmlspecialchars($row, ENT_NOQUOTES, Kohana::CHARSET) ?></span><? endforeach ?></code></pre>
              <? endif?>
            </li>
            <? unset($args_id, $source_id) ?>
            <? endforeach?>
          </ol>
          <? endif ?>

	</div>
	<h2>
          <a href="#<?= $env_id = $error_id."environment" ?>" onclick="return koggle('<?= $env_id ?>')"><?= t("Environment")?></a>
        </h2>
	<div id="<?= $env_id ?>" class="content collapsed">
          <? $included = get_included_files()?>
          <h3><a href="#<?= $env_id = $error_id."environment_included" ?>" onclick="return koggle('<?= $env_id ?>')"><?= t("Included files")?></a>(<?= count($included)?>)</h3>
          <div id="<?= $env_id ?>" class="collapsed">
            <table cellspacing="0">
              <? foreach ($included as $file): ?>
              <tr>
                <td>
                  <pre><?= Kohana_Exception::debug_path($file)?></pre>
                </td>
              </tr>
              <? endforeach?>
            </table>
          </div>
          <? $included = get_loaded_extensions()?>
          <h3><a href="#<?= $env_id = $error_id."environment_loaded" ?>" onclick="return koggle('<?= $env_id ?>')"><?= t("Loaded extensions")?></a>(<?= count($included)?>)</h3>
          <div id="<?= $env_id ?>" class="collapsed">
            <table cellspacing="0">
              <? foreach ($included as $file): ?>
              <tr>
                <td>
                  <pre><?= Kohana_Exception::debug_path($file)?></pre>
                </td>
              </tr>
              <? endforeach?>
            </table>
          </div>
          <? foreach (array("_SESSION", "_GET", "_POST", "_FILES", "_COOKIE", "_SERVER") as $var): ?>
          <? if ( empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
          <h3><a href="#<?= $env_id = "$error_id.environment" . strtolower($var) ?>"
                 onclick="return koggle('<?= $env_id ?>')">$<?= $var?></a></h3>
          <div id="<?= $env_id ?>" class="collapsed">
            <table cellspacing="0">
              <? foreach ($GLOBALS[$var] as $key => $value): ?>
              <tr>
                <td class="key">
                  <code>
                    <?= $key?>
                  </code>
                </td>
                <td class="value">
                  <pre><?= Kohana_Exception::dump($value) ?></pre>
                </td>
              </tr>
              <? endforeach?>
            </table>
          </div>
          <? endforeach?>
	</div>
      </div>
    </div>
    <? endif ?>
  </body>
</html>

<?php
$toolbar = Nectarine_Debug_Toolbar::getInstance();
$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
$sqlProfiler = $toolbar->getSqlProfiler();
$dbName = Mage::getConfig()->getResourceConnectionConfig('default_setup')->dbname;
?>

<script>
    if (!window.jQuery) {
        var jq = document.createElement('script');
        jq.type = 'text/javascript';
        jq.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js';
        document.write(jq.outerHTML);
        var noConflict = document.createTextNode("jQuery.noConflict();");
        jq = document.createElement('script');
        jq.type = 'text/javascript';
        jq.appendChild(noConflict);
        document.write(jq.outerHTML);
    }
</script>

<link rel="stylesheet" type="text/css" href="<?= $baseUrl ?>media/css/debugbar.css"/>
<script type="text/javascript" src="<?= $baseUrl ?>media/js/debugbar.js"></script>

<div class="phpdebugbar phpdebugbar-minimized">
    <div class="phpdebugbar-drag-capture"></div>
    <div class="phpdebugbar-resize-handle"></div>
    <div class="phpdebugbar-header" style="display: block;">
        <div class="phpdebugbar-header-left">
             <span class="phpdebugbar-indicator">
                <span class="phpdebugbar-text" style="margin-left: 0;"><?= $toolbar->getVersion(); ?></span>
             </span>

            <a class="phpdebugbar-tab">
                <i class="phpdebugbar-fa phpdebugbar-fa-tasks"></i>
                <span class="phpdebugbar-text">Timeline</span>
                <span class="phpdebugbar-badge"></span>
            </a>

            <a class="phpdebugbar-tab">
                <i class="phpdebugbar-fa phpdebugbar-fa-bug"></i>
                <span class="phpdebugbar-text">Exceptions</span>
                <?php if (count($toolbar->getErrors())): ?>
                    <span class="phpdebugbar-badge" style="display: inline;"><?= count($toolbar->getErrors()); ?></span>
                <?php endif; ?>
            </a>

            <a class="phpdebugbar-tab">
                <i class="phpdebugbar-fa phpdebugbar-fa-leaf"></i>
                <span class="phpdebugbar-text">Views</span>
            </a>

            <a class="phpdebugbar-tab">
                <i class="phpdebugbar-fa phpdebugbar-fa-inbox"></i>
                <span class="phpdebugbar-text">Queries</span>
                <span class="phpdebugbar-badge"
                      style="display: inline;"><?= $sqlProfiler ? $sqlProfiler->getTotalNumQueries() : '0' ?></span>
            </a>

            <a class="phpdebugbar-tab">
                <i class="phpdebugbar-fa phpdebugbar-fa-inbox"></i>
                <span class="phpdebugbar-text">Mails</span>
                <span class="phpdebugbar-badge" style="display: none;"></span>
            </a>

            <a class="phpdebugbar-tab">
                <i class="phpdebugbar-fa phpdebugbar-fa-archive"></i>
                <span class="phpdebugbar-text">Session</span>
                <span class="phpdebugbar-badge"></span>
            </a>

            <a class="phpdebugbar-tab">
                <i class="phpdebugbar-fa phpdebugbar-fa-tags"></i>
                <span class="phpdebugbar-text">Request</span>
                <span class="phpdebugbar-badge"></span>
            </a>
        </div>
        <div class="phpdebugbar-header-right">
            <a class="phpdebugbar-close-btn"></a>
            <a class="phpdebugbar-minimize-btn"></a>
            <a class="phpdebugbar-maximize-btn"></a>
            <!--            <a class="phpdebugbar-open-btn" style="display: block;"></a>-->

            <span class="phpdebugbar-indicator">
                <i class="phpdebugbar-fa phpdebugbar-fa-clock-o"></i>
                <span class="phpdebugbar-text"><?= number_format($toolbar->getExecutionTime() * 1000, 2, '.', '') ?>
                    ms</span>
                <span class="phpdebugbar-tooltip">Request Duration</span>
            </span>
            <span class="phpdebugbar-indicator">
                <i class="phpdebugbar-fa phpdebugbar-fa-cogs"></i>
                <span class="phpdebugbar-text"><?= $toolbar->getMemoryUsage() ?></span>
                <span class="phpdebugbar-tooltip">Memory Usage</span>
            </span>
            <span class="phpdebugbar-indicator">
                <i class="phpdebugbar-fa phpdebugbar-fa-share"></i>
                <span class="phpdebugbar-text"><?= $_SERVER['REQUEST_METHOD'] ?></span>
                <span class="phpdebugbar-tooltip">Request method</span>
            </span>
        </div>
    </div>
    <div class="phpdebugbar-body" style="height: 246px;">

        <div class="phpdebugbar-panel">
            <ul class="phpdebugbar-widgets-timeline">
                <?php
                    $startTime = null;
                    $sumTime = null;
                    foreach ($toolbar->getTimers() as $timerName => $timer):
                        if(is_null($startTime) || is_null($sumTime)) {
                            $startTime = $timer['start'];
                            $sumTime = $timer['sum'];
                        }
                ?>
                <li>
                    <div class="phpdebugbar-widgets-measure">
                        <span class="phpdebugbar-widgets-value phpdebugbar-bg-<?= $toolbar->getCategory($timerName); ?>" style="left: <?= (($timer['start'] - $startTime) / $sumTime) * 100 ?>%; width: <?= ($timer['sum'] / $sumTime) * 100 ?>%;"></span>
                        <span class="phpdebugbar-widgets-label"><?= $timerName ?> (<?= $toolbar->convertSeconds($timer['sum']) ?>)<span title="Count" class="phpdebugbar-widgets-param-count"><?= $timer['count'] ?></span></span>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="phpdebugbar-panel">
            <div class="phpdebugbar-widgets-exceptions">
                <ul class="phpdebugbar-widgets-list">
                    <?php foreach ($toolbar->getErrors() as $error): ?>
                        <li class="phpdebugbar-widgets-list-item">
                            <span class="phpdebugbar-widgets-message"><?= $error[1] ?></span>
                            <span class="phpdebugbar-widgets-filename"><?= $error[2] . ":" . $error[3] ?></span>
                            <span
                                class="phpdebugbar-widgets-type"> <?= $toolbar->getErrorMessageByCode($error[0]); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <?php
        $blocksSum = 0;
        function viewMacro($node, &$blocksSum, $level = 0)
        {
            $views = '';
            foreach ($node->getChild() as $block) {
                $views .=
                    '<li class="phpdebugbar-widgets-list-item" style="padding-left:' . ($level * 20 + 10) . 'px">
                    <span class="phpdebugbar-widgets-name">'
                    . $block->getNameInLayout();

                if ($block->getTemplateFile())
                    $views .= ' (<span class="phpdebugbar-widgets-filename">' . $block->getTemplateFile() . '</span>)';

                $views .= '</span>';

                if (count($block->getChild()))
                    $views .= '<span title="Children count" class="phpdebugbar-widgets-param-count">' . count($block->getChild()) . '</span>';

                $views .= '<span title="Class" class="phpdebugbar-widgets-type">' . get_class($block) . '</span>
                </li>';

                if ($block->getChild())
                    $views .= viewMacro($block, $blocksSum, $level + 1);
                ++$blocksSum;
            }
            return $views;
        }

        $viewsList = viewMacro($toolbar->getRootBlock(), $blocksSum);
        ?>

        <div class="phpdebugbar-panel phpdebugbar-active">
            <div class="phpdebugbar-widgets-templates">
                <div class="phpdebugbar-widgets-status"><span><?= $blocksSum ?> blocks were rendered</span></div>
                <ul class="phpdebugbar-widgets-list">
                    <?= $viewsList ?>
                </ul>
            </div>
        </div>

        <div class="phpdebugbar-panel phpdebugbar-active">
            <div class="phpdebugbar-widgets-sqlqueries">
                <div class="phpdebugbar-widgets-status">
                    <span><?= $sqlProfiler ? $sqlProfiler->getTotalNumQueries() : 'N/A' ?>
                        statements were executed</span>
                    <span title="Accumulated duration"
                          class="phpdebugbar-widgets-duration"><?= $toolbar->convertSeconds($sqlProfiler->getTotalElapsedSecs()) ?></span>
                    <span title="Sort" class="phpdebugbar-widgets-time-sort" style="cursor: pointer;"></span>
                    <span title="Connection" class="phpdebugbar-widgets-database"><?= $dbName ?></span>
                </div>
                <div class="phpdebugbar-widgets-toolbar"><a class="phpdebugbar-widgets-filter" rel="quickstart"><?= $dbName ?></a>
                </div>

                <ul class="phpdebugbar-widgets-list" id="phpdebugbar-queries">
                    <?php foreach ($sqlProfiler->getQueryProfiles() as $query): ?>
                        <li class="phpdebugbar-widgets-list-item" data-time="<?= $query->getElapsedSecs() ?>">
                            <code class="phpdebugbar-widgets-sql"><?= trim($query->getQuery()) ?></code>
                            <span title="Duration" class="phpdebugbar-widgets-duration">
                                <?= $toolbar->convertSeconds($query->getElapsedSecs()) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <script>
            (function ($) {
                $('span.phpdebugbar-widgets-time-sort').on('click', function(){
                    var queriesList = $('ul#phpdebugbar-queries');
                    var queryItems =  queriesList.children('li').get();
                    queryItems.sort(function(a, b) {
                        var timeA = $(a).data('time');
                        var timeB = $(b).data('time');
                        return (timeA > timeB) ? -1 : (timeA < timeB) ? 1 : 0;
                    });
                    $.each(queryItems, function(idx, itm) { queriesList.append(itm); });
                });

                $('.phpdebugbar-widgets-sqlqueries code.phpdebugbar-widgets-sql').each(function (i, block) {
                    hljs.highlightBlock(block);
                });
            })(jQuery);
        </script>

        <div class="phpdebugbar-panel">
            <div class="phpdebugbar-widgets-messages">
                <ul class="phpdebugbar-widgets-list">
                    <li>TOD</li>
                </ul>
                <div class="phpdebugbar-widgets-toolbar">
                    <i class="phpdebugbar-fa phpdebugbar-fa-search">TODO</i>
                    <input type="text">
                </div>
            </div>
        </div>

        <div class="phpdebugbar-panel">
            <dl class="phpdebugbar-widgets-kvlist phpdebugbar-widgets-varlist">
                <?php foreach ($_SESSION as $name => $value): ?>
                    <dt class="phpdebugbar-widgets-key"><span title="_token"><?= $name ?></span></dt>
                    <dd class="phpdebugbar-widgets-value"><?php if (is_array($value)) var_export($value); else echo $value; ?></dd>
                <?php endforeach; ?>
            </dl>
        </div>

        <div class="phpdebugbar-panel">
            <dl class="phpdebugbar-widgets-kvlist phpdebugbar-widgets-varlist">
                <?php $request = Mage::app()->getRequest(); ?>
                <dt class="phpdebugbar-widgets-key"><span title="format">path_info</span></dt>
                <dd class="phpdebugbar-widgets-value"><?= $request->getPathInfo(); ?></dd>
                <dt class="phpdebugbar-widgets-key"><span title="format">controller_class</span></dt>
                <dd class="phpdebugbar-widgets-value"><?= $request->getControllerModule() . '_' . ucfirst($request->getControllerName()) . "Controller" ?></dd>
                <dt class="phpdebugbar-widgets-key"><span title="format">action_name</span></dt>
                <dd class="phpdebugbar-widgets-value"><?= $request->getActionName(); ?></dd>
                <?php
                $action = Mage::app()->getFrontController()->getAction();
                if ($action):
                ?>
                    <dt class="phpdebugbar-widgets-key"><span title="format">xml_handler</span></dt>
                    <dd class="phpdebugbar-widgets-value"><?= $action->getFullActionName() ?></dd>
                <?php endif; ?>
                <dt class="phpdebugbar-widgets-key"><span title="format">params</span></dt>
                <dd class="phpdebugbar-widgets-value"><?= var_export($request->getParams()); ?></dd>
            </dl>
        </div>
    </div>
    <a class="phpdebugbar-restore-btn" style="display: none;"></a>
</div>
<script>
    var phpdebugbar = new PhpDebugBar.DebugBar();
    phpdebugbar.restoreState();
</script>
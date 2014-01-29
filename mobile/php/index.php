<?php
require_once dirname(__FILE__) . "/../../core/php/core.inc.php";
include_file('core', 'authentification', 'php');
include_file("core", "pageDescriptor", "config");
global $PAGE_DESCRIPTOR_MOBILE;
$page = 'Connection';
if (isConnect() && init('p') == '') {
    redirect('index.php?v=m&p=home');
}
if (isConnect() && init('p') != '') {
    $page = init('p');
}
if (isset($PAGE_DESCRIPTOR_MOBILE[$page])) {
    $title = $PAGE_DESCRIPTOR_MOBILE[$page]['title'];
} else {
    $title = $page;
}
$module = init('m');
if ($module != '') {
    $module = new module($module);
    if (is_object($module)) {
        $title = $module->getName();
    }
}
?>
<!DOCTYPE html> 
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no"> 
        <META HTTP-EQUIV="Pragma" CONTENT="private">
        <META HTTP-EQUIV="Cache-Control" CONTENT="private, max-age=5400, pre-check=5400">
        <META HTTP-EQUIV="Expires" CONTENT="<?php echo date(DATE_RFC822, strtotime("1 day")); ?>">
        <title>Jeedom</title> 
        <?php
        include_file('3rdparty', 'jquery.mobile/jquery.mobile', 'css');
        include_file('3rdparty', 'font-awesome/css/font-awesome.min', 'css');
        include_file('mobile', 'commun', 'css');
        include_file('core', 'core', 'css');
        include_file('3rdparty', 'jquery/jquery.min', 'js');
        include_file('3rdparty', 'php.js/php.min', 'js');
        include_file('3rdparty', 'jquery.mobile/jquery.mobile.min', 'js');
        include_file('3rdparty', 'highstock/highstock', 'js');
        include_file('3rdparty', 'highstock/highcharts-more', 'js');
        include_file('core', 'core', 'js');
        ?>

    </head> 
    <body> 
        <div data-role="page" class="type-interior" id="div_container" data-title="Jeedom">
            <div data-role="header" data-theme="a" data-position="fixed">
                <h1 style="margin: 0 10px;">
                    <img src="../../core/img/jeedom_ico.png" height="17" width="18" style="position: relative; top : 3px;"/>eedom
                    <span id="horloge"><?php echo date('H:i:s'); ?></span>
                </h1>
                <a href="#leftpanel" data-icon="bars" data-iconpos="notext">Menu</a>
                <a href="#rightpanel" data-icon="gear" data-iconpos="notext">Options</a>
            </div><!-- /header -->
            <br/>
            <div data-role="content">
                <a href="#div_alert" data-rel="popup" data-position-to="window"></a>
                <div data-role="popup" id="div_alert"></div>
                <?php
                if (!isConnect()) {
                    include_file('mobile', 'connection', 'php');
                } else {
                    sendVarToJS('userProfils', $_SESSION['user']->getOptions());
                    sendVarToJS('user_id', $_SESSION['user']->getId());
                    sendVarToJS('user_login', $_SESSION['user']->getLogin());
                    sendVarToJS('nodeJsKey', config::byKey('nodeJsKey'));
                    sendVarToJS('otherUserId', init('otherUserId '));

                    try {
                        if (isset($PAGE_DESCRIPTOR_MOBILE[$page])) {
                            include_file('mobile', $PAGE_DESCRIPTOR_MOBILE[$page]['pageName'], 'php');
                        } else if (isset($module) && is_object($module)) {
                            include_file('modules', $page, 'php');
                        } else {
                            echo '<div class="alert alert-danger div_alert">';
                            echo '404 - Request page not found';
                            echo '</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger div_alert">';
                        echo displayExeption($e);
                        echo '</div>';
                    }
                }
                ?>

                <div id="div_loading" style="position: absolute; top: 45%;left: 45%;display: none">
                    <img src="3rdparty/jquery.mobile/images/ajax-loader.gif" />
                </div>
            </div><!-- /content -->
            <br/>
            <div data-role="footer" data-theme="a" style="padding-top: 8px;padding-bottom: 5px;">
                <span style="margin-left: 0px;">&copy; Jeedom (v<?php echo getVersion('jeedom') ?>) <?php echo date('Y') ?> </span>
                <span style="float: right;">Node JS <span id="span_nodeJsState" class="binary red"></span></span>
            </div><!-- /footer -->


            <div data-role="panel" id="leftpanel" data-position="left" data-display="push" data-theme="b" class="ui-icon-alt">
                <ul data-role="listview">
                    <li><a href="index.php?v=m" data-icon="home" data-ajax="false"><i class="fa fa-home"></i> Accueil</a></li>
                    <li><a href="index.php?v=m&p=equipment" data-ajax="false" data-theme="a"><i class="fa fa fa-tachometer" ></i> Equipements </a></li>
                    <li><a href="index.php?v=m&p=scenario" data-ajax="false" data-theme="a"><i class="fa fa-cogs"></i> Scénario</a></li>
                    <li><a href="index.php?v=m&p=view" data-ajax="false" data-theme="a"><i class="fa fa-picture-o"></i> Vues</a></li>
                    <?php if (config::byKey('enableChat') == 1 && config::byKey('enableNodeJs') == 1) { ?>
                        <li><a href="index.php?v=m&p=chat" data-ajax="false" data-theme="a"><i class="fa fa-comment-o"></i> Chat</a></li>
                    <?php } ?>
                    <li><a href="index.php?v=m&p=message" data-ajax="false" data-theme="a"><i class="fa fa-envelope"></i> <span id="span_nbMessage"><?php echo message::nbMessage(); ?></span> Message(s)</a></li>
                    <li><a href="index.php?v=m&p=log" data-ajax="false" data-theme="a"><i class="fa fa-file-o"></i> Log</a></li>
                    <li><a href="index.php?v=m&p=cron" data-ajax="false" data-theme="a"><i class="fa fa-tasks"></i> Cron</a></li>
                    <li><a href="/core/php/authentification.php?logout=1" data-rel="dialog" data-ajax="false" data-theme="a"><i class="fa fa-sign-out"></i> Se deconnecter</a></li>
                </ul>
            </div>

            <?php if (isset($rightPanel)) { ?>
                <div data-role="panel" id="rightpanel" data-position="right" data-display="push" data-theme="a" class="ui-icon-alt">
                    <?php echo $rightPanel; ?>
                </div>
            <?php } ?>


        </div><!-- /page -->
        <?php
        include_file('3rdparty', 'jquery.value/jquery.value', 'js');
        include_file('3rdparty', 'jquery.alert/jquery.alert', 'js');
        include_file('3rdparty', 'jquery.loading/jquery.loading', 'css');
        include_file('3rdparty', 'jquery.loading/jquery.loading', 'js');
        if (isConnect()) {

            include_file('mobile', 'utils', 'js');
            if (config::byKey('enableChat') == 1 && config::byKey('enableNodeJs') == 1) {
                include_file('mobile', 'chat', 'js');
                include_file('core', 'chatAdapter', 'js');
            }
        }
        ?>
        <script>
            var clientDatetime = new Date();
            var clientServerDiffDatetime = (<?php echo strtotime(date('Y-m-d H:i:s')); ?> * 1000) - clientDatetime.getTime();
        </script>
    </body>
</html>


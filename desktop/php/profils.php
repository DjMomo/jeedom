<?php
if (!isConnect()) {
    throw new Exception('Error 401 Unauthorized');
}

$notifyTheme = array(
    'none' => 'Aucune',
    '' => 'Noir',
    'gritter-light' => 'Blanc',
    'gritter-red' => 'Rouge',
    'gritter-green' => 'Vert',
    'gritter-blue' => 'Bleu',
    'gritter-yellow' => 'Jaune',
);

$homePage = array(
    'dashboard' => 'Dashboard',
    'view' => 'Vue',
);
?>
<legend>Profile</legend>

<div class="panel-group" id="accordionConfiguration">

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionConfiguration" href="#config_notification">
                    Notifications
                </a>
            </h3>
        </div>
        <div id="config_notification" class="panel-collapse collapse in">
            <div class="panel-body">
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-lg-1 control-label">Notifier des évenements</label>
                            <div class="col-lg-3">
                                <select class="profilsKey form-control" key="notifyEvent">
                                    <?php
                                    foreach ($notifyTheme as $key => $value) {
                                        if ($_SESSION['user']->getOptions('notifyEvent') == $key) {
                                            echo "<option value='$key' selected>$value</option>";
                                        } else {
                                            echo "<option value='$key'>$value</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-1 control-label">Notifier du lancement des scénarios</label>
                            <div class="col-lg-3">
                                <select class="profilsKey form-control" key="notifyLaunchScenario">
                                    <?php
                                    foreach ($notifyTheme as $key => $value) {
                                        if ($_SESSION['user']->getOptions('notifyLaunchScenario') == $key) {
                                            echo "<option value='$key' selected>$value</option>";
                                        } else {
                                            echo "<option value='$key'>$value</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-1 control-label">Notifier nouveau message</label>
                            <div class="col-lg-3">
                                <select class="profilsKey form-control" key="notifyNewMessage">
                                    <?php
                                    foreach ($notifyTheme as $key => $value) {
                                        if ($_SESSION['user']->getOptions('notifyNewMessage') == $key) {
                                            echo "<option value='$key' selected>$value</option>";
                                        } else {
                                            echo "<option value='$key'>$value</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionConfiguration" href="#config_interface">
                    Interface
                </a>
            </h3>
        </div>
        <div id="config_interface" class="panel-collapse collapse">
            <div class="panel-body">
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-lg-1 control-label">Page d'accueils</label>
                            <div class="col-lg-3">
                                <select class="profilsKey form-control" key="homePage">
                                    <?php
                                    foreach ($homePage as $key => $value) {
                                        if ($_SESSION['user']->getOptions('homePage', 'plan') == $key) {
                                            echo "<option value='$key' selected>$value</option>";
                                        } else {
                                            echo "<option value='$key'>$value</option>";
                                        }
                                    }
                                    ?>
                                </select>

                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-1 control-label">Vue par default(desktop)</label>
                            <div class="col-lg-3">
                                <select class="profilsKey form-control" key="defaultDesktopView">
                                    <?php
                                    foreach (view::all() as $view) {
                                        if ($_SESSION['user']->getOptions('defaultDesktopView') == $view->getId()) {
                                            echo "<option value='" . $view->getId() . "' selected>" . $view->getName() . "</option>";
                                        } else {
                                            echo "<option value='" . $view->getId() . "'>" . $view->getName() . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-1 control-label">Objet par default(desktop)</label>
                            <div class="col-lg-3">
                                <select class="profilsKey form-control" key="defaultDashboardObject">
                                    <?php
                                    if ($_SESSION['user']->getOptions('defaultDashboardObject') == 'global') {
                                        echo "<option value='global' selected>Global</option>";
                                    } else {
                                        echo "<option value='global'>Global</option>";
                                    }
                                    foreach (object::all() as $object) {
                                        if ($_SESSION['user']->getOptions('defaultDashboardObject') == $object->getId()) {
                                            echo "<option value='" . $object->getId() . "' selected>" . $object->getName() . "</option>";
                                        } else {
                                            echo "<option value='" . $object->getId() . "'>" . $object->getName() . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>

    <br/> 
    <div class="form-actions">
        <a class="btn btn-success" id="bt_saveProfils"><i class="fa fa-check-circle icon-white" style="position:relative;left:-5px;top:1px"></i>Sauvegarder</a>
    </div>
</div>
<?php include_file("desktop", "profils", "js"); ?>
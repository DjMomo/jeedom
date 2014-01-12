<?php
if (!isConnect()) {
    throw new Exception('401 Unauthorized');
}
if (init('interactDef_id') == '') {
    throw new Exception('Interact Def ID ne peut etre vide');
}

$interactQueries = interactQuery::byInteractDefId(init('interactDef_id'));
if (count($interactQueries) == 0) {
    throw new Exception('Aucune phrase trouvée');
}
?>

<div style="display: none;" id="md_displayInteractQueryAlert"></div>

<table class="table table-bordered table-condensed tablesorter" id="table_interactQuery">
    <thead>
        <tr>
            <th>Phrase</th>
            <th>Commande</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($interactQueries as $interactQuery) {
            $trClass = ($interactQuery->getEnable() == 1) ? 'success' : 'danger';
            echo '<tr class="' . $trClass . '" interactQuery_id="' . $interactQuery->getId() . '">';
            echo '<td>' . $interactQuery->getQuery() . '</td>';
            echo '<td>';
            if ($interactQuery->getLink_type() == 'cmd') {
                echo str_replace('#', '', cmd::cmdToHumanReadable('#' . $interactQuery->getLink_id() . '#'));
            }
            echo '</td>';
            echo '<td>';
            if ($interactQuery->getEnable() == 1) {
                echo '<a class="btn btn-danger btn-xs changeEnable" state="0" style="color : white;">Désactiver</a>';
            } else {
                echo '<a class="btn btn-success btn-xs changeEnable" state="1" style="color : white;">Activer</a>';
            }
            echo '</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>

<script>
    initTableSorter();
    
    $('#table_interactQuery .changeEnable').on('click', function() {
        var tr = $(this).closest('tr');
        var btn = $(this);
        $.ajax({
            type: 'POST',
            url: "core/ajax/interact.ajax.php", // url du fichier php
            data: {
                action: 'changeState',
                id: tr.attr('interactQuery_id'),
                enable: btn.attr('state'),
            },
            dataType: 'json',
            error: function(request, status, error) {
                handleAjaxError(request, status, error,$('#md_displayInteractQueryAlert'));
            },
            success: function(data) {
                if (data.state != 'ok') {
                    $('#md_displayInteractQueryAlert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                if (btn.attr('state') == 1) {
                    tr.removeClass('danger').addClass('success');
                    btn.attr('state', 0);
                    btn.removeClass('btn-success').addClass('btn-danger');
                } else {
                    tr.removeClass('success').addClass('danger');
                    btn.attr('state', 1);
                    btn.removeClass('btn-danger').addClass('btn-success');
                }
            }
        });
    });

</script>
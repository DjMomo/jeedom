
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

$(function() {
    printCron();

    $("#bt_refreshCron").on('click', function() {
        printCron();
    });

    $("#bt_addCron").on('click', function() {
        addCron({});
    });

    $("#bt_save").on('click', function() {
        saveCron();
    });

    $("#bt_changeCronState").on('click', function() {
        var el = $(this);
        var value = {enableCron: el.attr('data-state')};
        $.ajax({
            type: 'POST',
            url: 'core/ajax/config.ajax.php',
            data: {
                action: 'addKey',
                value: json_encode(value)
            },
            dataType: 'json',
            error: function(request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function(data) {
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                if (el.attr('data-state') == 1) {
                    el.find('i').removeClass('fa-check').addClass('fa-times');
                    el.removeClass('btn-success').addClass('btn-danger').attr('data-state', 1);
                } else {
                    el.find('i').removeClass('fa-times').addClass('fa-check');
                    el.removeClass('btn-danger').addClass('btn-success').attr('data-state', 1);
                }
            }
        });
    });

    $("#table_cron").delegate(".remove", 'click', function() {
        $(this).closest('tr').remove();
    });

    $("#table_cron").delegate(".stop", 'click', function() {
        changeStateCron('stop', $(this).closest('tr').attr('id'));
    });


    $("#table_cron").delegate(".start", 'click', function() {
        changeStateCron('start', $(this).closest('tr').attr('id'));
    });

    $('#table_cron').delegate('.cronAttr[data-l1key=deamon]', 'change', function() {
        if ($(this).value() == 1) {
            $(this).closest('tr').find('.cronAttr[data-l1key=deamonSleepTime]').show();
        } else {
            $(this).closest('tr').find('.cronAttr[data-l1key=deamonSleepTime]').hide();
        }
    });

    $('body').delegate('.cronAttr', 'change', function() {
        modifyWithoutSave = true;
    });
});

function changeStateCron(_state, _id) {
    $.hideAlert();
    $.ajax({
        type: 'POST',
        url: 'core/ajax/cron.ajax.php',
        data: {
            action: _state,
            id: _id
        },
        dataType: 'json',
        error: function(request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function(data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            printCron();
        }
    });
}

function printCron() {
    $.hideAlert();
    $.ajax({
        type: 'POST',
        url: 'core/ajax/cron.ajax.php',
        data: {
            action: 'all'
        },
        dataType: 'json',
        error: function(request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function(data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#table_cron tbody').empty();
            for (var i in data.result.crons) {
                addCron(data.result.crons[i]);
            }
            $('#span_jeecronMasterRuns').html(data.result.nbMasterCronRun);
            $('#span_jeecronRuns').html(data.result.nbCronRun);
            $('#span_nbProcess').html(data.result.nbProcess);
            $('#span_loadAvg1').html(data.result.loadAvg[0]);
            $('#span_loadAvg5').html(data.result.loadAvg[1]);
            $('#span_loadAvg15').html(data.result.loadAvg[2]);
            $("#table_cron").trigger("update");
            modifyWithoutSave = false;
        }
    });
}

function saveCron() {
    $.hideAlert();
    var crons = $('#table_cron tbody tr').getValues('.cronAttr');
    $.ajax({
        type: 'POST',
        url: 'core/ajax/cron.ajax.php',
        data: {
            action: 'save',
            crons: json_encode(crons),
        },
        dataType: 'json',
        error: function(request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function(data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#div_alert').showAlert({message: '{{Sauvegarde réussie}}', level: 'success'});
            modifyWithoutSave = false;
        }
    });
}

function addCron(_cron) {
    $.hideAlert();
    var tr = '<tr id="' + init(_cron.id) + '">';
    tr += '<td class="option"><span class="cronAttr" data-l1key="id"></span></td>';
    tr += '<td>';
    if (init(_cron.state) == 'run') {
        tr += '<a class="btn btn-danger btn-sm stop"><i class="fa fa-stop"></i></a>';
    }
    if (init(_cron.state) != '' && init(_cron.state) != 'starting' && init(_cron.state) != 'run' && init(_cron.state) != 'stoping') {
        tr += '<a class="btn btn-success btn-sm start"><i class="fa fa-play"></i></a>';
    }
    tr += '</td>';
    tr += '<td class="enable"><center>';
    tr += '<input type="checkbox" class="cronAttr" data-l1key="enable" checked/>';
    tr += '</center></td>';
    tr += '<td>';
    tr += init(_cron.server);
    tr += '</td>';
    tr += '<td>';
    tr += init(_cron.pid);
    tr += '</td>';
    tr += '<td class="deamons">';
    tr += '<input type="checkbox" class="cronAttr" data-l1key="deamon" /></span> ';
    tr += '<input class="cronAttr form-control" data-l1key="deamonSleepTime" style="width : 50px; display : inline-block;"/>';
    tr += '</td>';
    tr += '<td class="once">';
    tr += '<input type="checkbox" class="cronAttr" data-l1key="once" /></span> ';
    tr += '</td>';
    tr += '<td class="class"><input class="form-control cronAttr" data-l1key="class" /></td>';
    tr += '<td class="function"><input class="form-control cronAttr" data-l1key="function" /></td>';
    tr += '<td class="schedule"><input class="cronAttr form-control" data-l1key="schedule" /></td>';
    tr += '<td class="function"><input class="form-control cronAttr" data-l1key="timeout" /></td>';
    tr += '<td class="lastRun">';
    tr += init(_cron.lastRun);
    tr += '</td>';
    tr += '<td class="duration">';
    if (init(_cron.duration) != '-1') {
        if (init(_cron.state) == 'run') {
            tr += '<span class="label label-success">' + init(_cron.duration) + '</span>';
        } else {
            tr += '<span class="label label-warning">' + init(_cron.duration) + '</span>';
        }
    } else {
        tr += '<span class="label label-danger">' + init(_cron.duration) + '</span>';
    }
    tr += '</td>';
    tr += '<td class="state">';
    var label = 'label label-info';
    if (init(_cron.state) == 'run') {
        label = 'label label-success';
    }
    if (init(_cron.state) == 'stop') {
        label = 'label label-danger';
    }
    if (init(_cron.state) == 'starting') {
        label = 'label label-warning';
    }
    if (init(_cron.state) == 'stoping') {
        label = 'label label-warning';
    }
    tr += '<span class="' + label + '">' + init(_cron.state) + '</span>';
    tr += '</td>';
    tr += '<td class="action">';
    tr += '<i class="fa fa-minus-circle remove pull-right cursor"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cron').append(tr);
    $('#table_cron tbody tr:last').setValues(_cron, '.cronAttr');
}

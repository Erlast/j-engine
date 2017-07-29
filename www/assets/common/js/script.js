var $document = $(document);
var $formModal = $('#myModal');
var $datepicker = $('.datepicker');

$datepicker.datepicker($.datepicker.regional["ru"]);

$document.on('click', '.add-mark-to-category', function () {

    var $parent = $(this).parents('.row-form');
    var value = $parent.find('.mark-name').val();
    if (value !== "") {
        site.data = {
            'id': $(this).data('category-id'),
            'name': value
        };
        site.addMarkToCategory(function (data) {
            if (data !== 'ERROR') {
                $parent.parents('.header').next('.table-responsive').find('tbody').append("<tr class='mark-row'><td>" + data + "</td><td><a href='/models/list/" + data + "'> " + value + "</a></td><td><button class=\"btn btn-xs btn-danger mark-delete\" data-id='" + data + "'>Удалить</button></td></tr>");
            }
        })
    }
});

$document.on('click', '.mark-delete', function () {
    var id = $(this).attr('data-id');
    var $parent = $(this).parents('.mark-row');
    if (confirm('Вы точно хотите удалить?')) {
        site.data = {
            'id': id
        };
        site.markDelete(function (data) {
            if (data === 'OK') {
                $parent.remove();
            }
        })
    }

});

$document.on('click', '.add-model-to-mark', function () {
    var $parent = $(this).parents('.row-form');
    var value = $parent.find('.model-name').val();
    if (value !== "") {
        site.data = {
            'id': $(this).data('mark-id'),
            'name': value
        };
        site.addModelToMark(function (data) {
            if (data !== 'ERROR') {
                $parent.next('table').find('tbody').append("<tr class='model-row'><td>" + data + "</td><td>" + value + "</td><td><button class=\"btn btn-xs btn-danger model-delete\" data-id='" + data + "'>Удалить</button></td></tr>");
            }
        })
    }
});
$document.on('click', '.model-delete', function () {
    var id = $(this).attr('data-id');
    var $parent = $(this).parents('.model-row');
    if (confirm('Вы точно хотите удалить?')) {
        site.data = {
            'id': id
        };
        site.modelDelete(function (data) {
            if (data === 'OK') {
                $parent.remove();
            }
        })
    }
});

$document.on('click', '.add-person', function () {
    var $parent = $(this).parents('.row-form');
    var fio = $parent.find('.person-fio').val();
    var profession = $parent.find('.person-profession').val();
    if (fio !== '' && profession !== '') {
        site.data = {
            'fio': fio,
            'profession': profession
        }
        site.addPerson(function (data) {
            if (data !== 'ERROR') {
                $parent.next('table').find('tbody').append("<tr class='person-row'><td>" + data + "</td><td>" + fio + "</td><td>" + profession + "</td><td><button class=\"btn btn-xs btn-danger\" data-toggle=\"modal\" data-target=\"#myModal\" data-id='" + data + "'>Удалить</button></td></tr>");
            }
        })
    }

});
var $parentPerson;
var $parentPc;
$formModal.on('show.bs.modal', function (e) {
    var target = $(e.relatedTarget);
    $(this).find('.person-delete').attr('data-id', target.attr('data-id'));
    $(this).find('.pc-delete').attr('data-id', target.attr('data-id'));
    $parentPerson = target.parents('.person-row');
    $parentPc = target.parents('.pc-row');
});

$formModal.on('hidden.bs.modal', function () {

    $(this).removeData('bs.modal');
});

$document.on('click', '.person-delete', function () {
    var comment = $(this).parents('.modal-content').find('#comment').val();
    var id = $(this).attr('data-id');
    if (confirm('Вы точно хотите удалить?')) {
        site.data = {
            'id': id,
            'comment':comment
        };
        console.log(site.data);
        site.deletePerson(function (data) {
            if (data === 'OK') {
                $formModal.modal('hide');
                $parentPerson.remove();

            }
        })
    }
});

$document.ready(function () {
    $.fn.dataTableExt.afnFiltering.push(
        function (oSettings, aData, iDataIndex) {
            var iFini = document.getElementById('dateStart').value;
            var iFfin = document.getElementById('dateEnd').value;
            var iStartDateCol = 1;
            var iEndDateCol = 1;

            iFini = iFini.substring(6, 10) + iFini.substring(3, 5) + iFini.substring(0, 2);
            iFfin = iFfin.substring(6, 10) + iFfin.substring(3, 5) + iFfin.substring(0, 2);

            var datofini = aData[iStartDateCol].substring(6, 10) + aData[iStartDateCol].substring(3, 5) + aData[iStartDateCol].substring(0, 2);
            var datoffin = aData[iEndDateCol].substring(6, 10) + aData[iEndDateCol].substring(3, 5) + aData[iEndDateCol].substring(0, 2);

            if (iFini === "" && iFfin === "") {
                return true;
            }
            else if (iFini <= datofini && iFfin === "") {
                return true;
            }
            else if (iFfin >= datoffin && iFini === "") {
                return true;
            }
            else if (iFini <= datofini && iFfin >= datoffin) {
                return true;
            }
            return false;
        }
    );
});


$datepicker.datepicker('option', {
    dateFormat: 'dd.mm.yy',
    changeMonth: true,
    changeYear: true,
    firstDay: 1
}).on('change', function () {
    $('.js-basic-example').DataTable().draw();
});

$document.on('click', '.editable', function () {
    var $this = $(this);
    var id_status = $this.data('id');
    site.getStatus(function (data) {
        $('.status').each(function () {
            $(this).addClass('editable');
            $(this).html($(this).data('value'));
        });
        $this.removeClass('editable');
        $this.html(data);
        $this.find('select').children().each(function () {
            if (parseInt($(this).val()) === parseInt(id_status)) {
                $(this).attr('selected', 'selected');

            }
        });
    })
});
$document.on('change', '.change-status', function () {
    var $this = $(this);
    var value = $this.val();
    var name = $this.find("option[value=" + value + "]").text();
    site.data = {
        'id': $this.parent().data('pcid'),
        'status': value
    };
    site.changeStatus(function (data) {
        if (data === 'OK') {
            $this.parent().addClass('editable');
            $this.parent().attr('data-value', name);
            $this.parent().html(name);

        }
    })

});

$document.on('click', '.pc-delete', function () {
    var comment = $(this).parents('.modal-content').find('#comment').val();
    site.data = {
        'id': $(this).data('id'),
        'comment': comment
    };
    if (confirm('Вы точно хотите удалить ПК?')) {
        site.deletePc(function (data) {
            if (data === 'OK') {
                $formModal.modal('hide');
                $parentPc.remove();
            }
        });
    }
});

